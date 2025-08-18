<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\ClassSection;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AssessmentController extends Controller
{
    public function index($subjectId, $classSectionId, $term, $assessmentTypeId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessmentType = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId);

        $assessments = $assessmentType->assessments()
            ->where('term', $term)
            ->orderBy('order')
            ->get();

        $students = $classSection->students;

        // Check if this is an attendance assessment type
        $isAttendanceType = $assessmentType->name === 'Attendance';

        // Auto-create attendance assessment if it doesn't exist
        if ($isAttendanceType && $assessments->isEmpty()) {
            $assessment = $assessmentType->assessments()->create([
                'name' => 'Attendance',
                'max_score' => 100, // Will be calculated based on actual attendance days
                'passing_score' => 75, // 75% attendance is considered passing
                'warning_score' => 85, // 85% attendance triggers warning
                'due_date' => null,
                'description' => 'Attendance tracking for ' . ucfirst($term) . ' term',
                'order' => 1,
                'term' => $term,
            ]);
            
            // Refresh the assessments collection
            $assessments = $assessmentType->assessments()
                ->where('term', $term)
                ->orderBy('order')
                ->get();
        }

        return view('teacher.assessments.index', compact(
            'classSection',
            'assessmentType',
            'assessments',
            'students',
            'term',
            'isAttendanceType'
        ));
    }

    public function store(Request $request, $subjectId, $classSectionId, $term, $assessmentTypeId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'max_score' => 'required|numeric|min:0.01|max:999.99',
            'passing_score' => 'nullable|numeric|min:0.01|max:999.99',
            'warning_score' => 'nullable|numeric|min:0.01|max:999.99',
            'due_date' => 'nullable|date|after_or_equal:today',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessmentType = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId);

        $assessment = $assessmentType->assessments()->create([
            'name' => $request->name,
            'max_score' => $request->max_score,
            'passing_score' => $request->passing_score,
            'warning_score' => $request->warning_score,
            'due_date' => $request->due_date,
            'description' => $request->description,
            'order' => $assessmentType->assessments()->where('term', $term)->count() + 1,
            'term' => $term,
        ]);

        return back()->with('success', 'Assessment created successfully!');
    }

    public function saveScores(Request $request, $subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessmentType = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId);

        $validator = Validator::make($request->all(), [
            'grades_data' => 'required|json',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Validation failed'], 422);
            }
            return back()->withErrors($validator);
        }

        $gradesData = json_decode($request->grades_data, true);
        
        if (!is_array($gradesData)) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid grades data format'], 400);
            }
            return back()->with('error', 'Invalid grades data format.');
        }

        $savedCount = 0;
        $errors = [];

        foreach ($gradesData as $gradeData) {
            $studentId = $gradeData['student_id'] ?? null;
            $assessmentId = $gradeData['assessment_id'] ?? null;
            $score = $gradeData['score'] ?? null;
            $isLate = $gradeData['is_late'] ?? false;

            if (!$studentId || !$assessmentId) {
                $errors[] = 'Missing student_id or assessment_id';
                continue;
            }

            // Verify the assessment belongs to this assessment type
            $assessment = $assessmentType->assessments()
                ->where('id', $assessmentId)
                ->where('term', $term)
                ->first();

            if (!$assessment) {
                $errors[] = 'Assessment not found';
                continue;
            }

            // Verify the student belongs to this class section
            $student = $classSection->students()->where('students.id', $studentId)->first();
            if (!$student) {
                $errors[] = 'Student not found';
                continue;
            }

            // Validate score
            if ($score !== null && ($score < 0 || $score > $assessment->max_score)) {
                $errors[] = 'Score out of range';
                continue;
            }

            try {
                // Find existing score for this assessment and student
                $existingScore = AssessmentScore::where('assessment_id', $assessmentId)
                    ->where('student_id', $studentId)
                    ->first();

                if ($existingScore) {
                    // Update existing record
                    $existingScore->update([
                        'term' => $term,
                        'score' => $score,
                        'is_late' => $isLate,
                        'submitted_at' => $score ? now() : null,
                    ]);
                    
                    // Calculate and update percentage score
                    $existingScore->calculatePercentageScore();
                    $existingScore->save();
                } else {
                    // Create new record
                    $newScore = AssessmentScore::create([
                        'assessment_id' => $assessmentId,
                        'student_id' => $studentId,
                        'term' => $term,
                        'score' => $score,
                        'is_late' => $isLate,
                        'submitted_at' => $score ? now() : null,
                    ]);
                    
                    // Calculate and update percentage score
                    $newScore->calculatePercentageScore();
                    $newScore->save();
                }
                $savedCount++;
            } catch (\Exception $e) {
                $errors[] = 'Database error: ' . $e->getMessage();
            }
        }

        if ($request->expectsJson()) {
            if (count($errors) > 0) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Some grades could not be saved',
                    'errors' => $errors,
                    'saved_count' => $savedCount
                ], 400);
            }
            return response()->json([
                'success' => true, 
                'message' => 'Grades saved successfully',
                'saved_count' => $savedCount
            ]);
        }

        if (count($errors) > 0) {
            return back()->with('error', 'Some grades could not be saved: ' . implode(', ', $errors));
        }

        return back()->with('success', 'Grades saved successfully!');
    }

    public function update(Request $request, $subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'max_score' => 'required|numeric|min:0.01|max:999.99',
            'passing_score' => 'nullable|numeric|min:0.01|max:999.99',
            'warning_score' => 'nullable|numeric|min:0.01|max:999.99',
            'due_date' => 'nullable|date|after_or_equal:today',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessment = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId)
            ->assessments()
            ->where('term', $term)
            ->findOrFail($assessmentId);

        $assessment->update([
            'name' => $request->name,
            'max_score' => $request->max_score,
            'passing_score' => $request->passing_score,
            'warning_score' => $request->warning_score,
            'due_date' => $request->due_date,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Assessment updated successfully!');
    }

    public function destroy($subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessment = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId)
            ->assessments()
            ->where('term', $term)
            ->findOrFail($assessmentId);

        $assessment->delete();

        return back()->with('success', 'Assessment deleted successfully!');
    }

    public function scores($subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessmentType = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId);

        $assessments = $assessmentType->assessments()
            ->where('term', $term)
            ->orderBy('order')
            ->get();

        $students = $classSection->students;

        return view('teacher.assessments.scores', compact(
            'classSection',
            'assessmentType',
            'assessments',
            'students',
            'term'
        ));
    }

    /**
     * Show quiz creation form for an assessment
     */
    public function showQuizForm($subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessment = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId)
            ->assessments()
            ->where('term', $term)
            ->findOrFail($assessmentId);

        // Check if quiz already exists
        $hasQuiz = $assessment->questions()->exists();
        $questions = $assessment->questions()->orderBy('order')->get();

        return view('teacher.assessments.quiz-form', compact(
            'classSection', 
            'assessment', 
            'hasQuiz', 
            'questions',
            'term'
        ));
    }

    /**
     * Store quiz questions for an assessment
     */
    public function storeQuiz(Request $request, $subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $request->validate([
            'questions' => 'required|array|min:1',
            'questions.*.type' => 'required|in:multiple_choice,identification,true_false',
            'questions.*.question_text' => 'required|string',
            'questions.*.correct_answer' => 'required|string',
            'questions.*.points' => 'required|numeric|min:0.01',
            'questions.*.options' => 'required_if:questions.*.type,multiple_choice|array',
        ]);

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessment = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId)
            ->assessments()
            ->where('term', $term)
            ->findOrFail($assessmentId);

        // Enable quiz mode and generate unique URL
        $assessment->update([
            'is_quiz' => true,
            'unique_url' => Str::random(10),
            'auto_grade' => true,
            'expires_at' => now()->addDay(), // Quiz expires in 24 hours
        ]);

        // Delete existing questions
        $assessment->questions()->delete();

        // Store new questions and calculate total points
        $totalPoints = 0;
        foreach ($request->questions as $index => $questionData) {
            $assessment->questions()->create([
                'type' => $questionData['type'],
                'question_text' => $questionData['question_text'],
                'options' => $questionData['options'] ?? null,
                'correct_answer' => $questionData['correct_answer'],
                'points' => $questionData['points'],
                'order' => $index + 1,
            ]);
            $totalPoints += $questionData['points'];
        }

        // Update assessment max_score to match total quiz points
        $assessment->update(['max_score' => $totalPoints]);

        return redirect()->route('assessments.quiz.tokens', [
            'subject' => $subjectId,
            'classSection' => $classSectionId,
            'term' => $term,
            'assessmentType' => $assessmentTypeId,
            'assessment' => $assessmentId
        ])->with('success', 'Quiz created successfully!');
    }

    /**
     * Show quiz tokens and QR code
     */
    public function showQuizTokens($subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessment = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId)
            ->assessments()
            ->where('term', $term)
            ->findOrFail($assessmentId);

        if (!$assessment->is_quiz) {
            return back()->with('error', 'This assessment is not a quiz.');
        }

        $students = $classSection->students;
        $tokens = AssessmentToken::where('assessment_id', $assessment->id)
            ->with('student')
            ->get();

        return view('teacher.assessments.quiz-tokens', compact(
            'classSection',
            'assessment',
            'students',
            'tokens',
            'term'
        ));
    }

    /**
     * Generate tokens for all students
     */
    public function generateTokens($subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessment = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId)
            ->assessments()
            ->where('term', $term)
            ->findOrFail($assessmentId);

        if (!$assessment->is_quiz) {
            return back()->with('error', 'This assessment is not a quiz.');
        }

        $students = $classSection->students;
        $generatedCount = 0;

        foreach ($students as $student) {
            AssessmentToken::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => 'active',
                    'expires_at' => $assessment->due_date ?? now()->addDays(7),
                ]
            );
            $generatedCount++;
        }

        return back()->with('success', "Generated {$generatedCount} tokens successfully!");
    }

    /**
     * Regenerate a specific token
     */
    public function regenerateToken($tokenId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $token = AssessmentToken::findOrFail($tokenId);
        
        // Check if teacher owns the assessment
        $assessment = $token->assessment;
        $classSection = $assessment->assessmentType->subject->classSections()
            ->where('teacher_id', auth()->id())
            ->first();
            
        if (!$classSection) {
            abort(403, 'Access denied. You can only regenerate tokens for your own assessments.');
        }

        $token->generateNewToken();

        return response()->json(['success' => true, 'token' => $token->token]);
    }

    /**
     * Get real-time status of quiz tokens for AJAX updates
     */
    public function getQuizTokensStatus($subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessment = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId)
            ->assessments()
            ->where('term', $term)
            ->findOrFail($assessmentId);

        if (!$assessment->is_quiz) {
            return response()->json(['success' => false, 'message' => 'This assessment is not a quiz.']);
        }

        // Get all tokens with their current status
        $tokens = AssessmentToken::where('assessment_id', $assessment->id)
            ->with('student')
            ->get()
            ->map(function ($token) {
                return [
                    'student_id' => $token->student->student_id,
                    'is_used' => $token->isUsed(),
                    'is_expired' => $token->isExpired(),
                    'used_at' => $token->used_at,
                    'just_completed' => $token->used_at && $token->updated_at->diffInSeconds(now()) < 30, // Highlight if completed in last 30 seconds
                ];
            });

        // Get quiz statistics
        $stats = [
            'total_questions' => $assessment->questions->count(),
            'total_points' => $assessment->questions->sum('points'),
            'completed_count' => $tokens->where('is_used', true)->count(),
        ];

        return response()->json([
            'success' => true,
            'tokens' => $tokens,
            'stats' => $stats,
            'last_updated' => now()->toISOString(),
        ]);
    }

    /**
     * Reactivate an expired quiz or extend time for expiring quiz
     */
    public function reactivateQuiz($subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $assessment = $classSection->subject->assessmentTypes()
            ->where('term', $term)
            ->findOrFail($assessmentTypeId)
            ->assessments()
            ->where('term', $term)
            ->findOrFail($assessmentId);

        if (!$assessment->is_quiz) {
            return back()->with('error', 'This assessment is not a quiz.');
        }

        // Check if quiz is expiring soon (less than 2 hours)
        $isExpiringSoon = $assessment->expires_at && $assessment->expires_at->diffInHours(now()) < 2;

        if (!$assessment->isExpired() && !$isExpiringSoon) {
            return back()->with('info', 'This quiz is still active and has plenty of time remaining.');
        }

        // Extend expiration by 24 hours
        $assessment->extendExpiration();

        if ($assessment->isExpired()) {
            return back()->with('success', 'Quiz reactivated successfully! It will expire in 24 hours.');
        } else {
            return back()->with('success', 'Quiz time extended successfully! It will expire in 24 hours.');
        }
    }
} 