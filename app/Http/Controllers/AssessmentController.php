<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\AssessmentType;
use App\Models\ClassSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
} 