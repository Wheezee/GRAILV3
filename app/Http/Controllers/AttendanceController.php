<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AttendanceRecord;
use App\Models\ClassSection;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
    /**
     * Show attendance management page for a specific assessment
     */
    public function index($subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        $subject = auth()->user()->subjects()->findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();
        
        $assessment = Assessment::where('id', $assessmentId)
            ->where('assessment_type_id', $assessmentTypeId)
            ->where(function ($q) use ($classSection) {
                $q->whereNull('class_section_id')
                  ->orWhere('class_section_id', $classSection->id);
            })
            ->first();

        // If the provided assessment id isn't valid for this class, auto-create a class-specific one and redirect
        if (!$assessment) {
            $assessmentType = $classSection->subject->assessmentTypes()
                ->where('term', $term)
                ->findOrFail($assessmentTypeId);

            // Create a per-class attendance assessment
            $newAssessment = $assessmentType->assessments()->create([
                'name' => 'Attendance',
                'max_score' => 100,
                'passing_score' => 75,
                'warning_score' => 85,
                'due_date' => null,
                'description' => 'Attendance tracking for ' . ucfirst($term) . ' term',
                'order' => $assessmentType->assessments()
                    ->where('term', $term)
                    ->where(function ($q) use ($classSection) {
                        $q->whereNull('class_section_id')
                          ->orWhere('class_section_id', $classSection->id);
                    })
                    ->count() + 1,
                'term' => $term,
                'class_section_id' => $classSection->id,
            ]);

            return redirect()->route('attendance.index', [
                'subject' => $subjectId,
                'classSection' => $classSectionId,
                'term' => $term,
                'assessmentType' => $assessmentTypeId,
                'assessment' => $newAssessment->id,
            ]);
        }

        // Bind shared attendance assessment to current class on first access
        if ($assessment->class_section_id === null) {
            $assessment->class_section_id = $classSection->id;
            $assessment->save();
        }

        if (!$assessment->isAttendanceAssessment()) {
            abort(404, 'This is not an attendance assessment.');
        }

        $students = $classSection->students()->orderBy('last_name')->orderBy('first_name')->get();
        
        // Get all attendance dates for this assessment
        $attendanceDates = $assessment->attendanceRecords()
            ->select('date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date')
            ->map(function($date) {
                return $date->format('Y-m-d');
            })
            ->toArray();

        // Get attendance data for all students
        $attendanceData = [];
        foreach ($students as $student) {
            $studentRecords = $assessment->getStudentAttendanceRecords($student->id);
            $attendanceData[$student->id] = $studentRecords->keyBy('date');
        }

        return view('teacher.attendance.index', compact(
            'subject',
            'classSection',
            'assessment',
            'students',
            'attendanceDates',
            'attendanceData',
            'term'
        ));
    }

    /**
     * Save attendance records for a specific date
     */
    public function store(Request $request, $subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        // Log the incoming request data for debugging
        \Log::info('Attendance store request:', [
            'date' => $request->date,
            'attendance' => $request->attendance,
            'term' => $term
        ]);

        $request->validate([
            'date' => 'required|date',
            'attendance' => 'required|array',
            'attendance.*.student_id' => 'required|exists:students,id',
            'attendance.*.status' => 'required|in:present,absent',
            'attendance.*.remark' => 'nullable|string',
        ]);

        $subject = auth()->user()->subjects()->findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();
        
        $assessment = Assessment::where('id', $assessmentId)
            ->where('assessment_type_id', $assessmentTypeId)
            ->where(function ($q) use ($classSection) {
                $q->whereNull('class_section_id')
                  ->orWhere('class_section_id', $classSection->id);
            })
            ->firstOrFail();

        // Bind shared attendance assessment to current class on first save
        if ($assessment->class_section_id === null) {
            $assessment->class_section_id = $classSection->id;
            $assessment->save();
        }

        if (!$assessment->isAttendanceAssessment()) {
            abort(404, 'This is not an attendance assessment.');
        }

        try {
            DB::transaction(function () use ($request, $assessment, $term) {
                foreach ($request->attendance as $attendanceData) {
                    // Ensure date is in the correct format
                    $date = \Carbon\Carbon::parse($request->date)->format('Y-m-d');
                    
                    // Log the search criteria for debugging
                    \Log::info('Searching for existing record:', [
                        'assessment_id' => $assessment->id,
                        'student_id' => $attendanceData['student_id'],
                        'date' => $date,
                    ]);
                    
                    // First try to find existing record using raw date comparison
                    $existingRecord = AttendanceRecord::where('assessment_id', $assessment->id)
                        ->where('student_id', $attendanceData['student_id'])
                        ->whereDate('date', $date)
                        ->first();
                    
                    \Log::info('Existing record found:', ['found' => $existingRecord ? 'yes' : 'no']);
                    
                    if ($existingRecord) {
                        // Update existing record
                        $existingRecord->update([
                            'status' => $attendanceData['status'],
                            'term' => $term,
                            'remark' => $attendanceData['remark'] ?? null,
                        ]);
                        \Log::info('Updated existing record');
                    } else {
                        // Create new record
                        AttendanceRecord::create([
                            'assessment_id' => $assessment->id,
                            'student_id' => $attendanceData['student_id'],
                            'date' => $date,
                            'status' => $attendanceData['status'],
                            'term' => $term,
                            'remark' => $attendanceData['remark'] ?? null,
                        ]);
                        \Log::info('Created new record');
                    }
                }
            });
        } catch (\Exception $e) {
            \Log::error('Attendance save error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error saving attendance: ' . $e->getMessage()
            ], 500);
        }

        // Update assessment scores for current class only
        $this->updateAttendanceScores($assessment, $classSection);

        return response()->json([
            'success' => true,
            'message' => 'Attendance saved successfully!'
        ]);
    }

    /**
     * Get attendance data for a specific date
     */
    public function getAttendanceData(Request $request, $subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $subject = auth()->user()->subjects()->findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();
        
        $assessment = Assessment::where('id', $assessmentId)
            ->where('assessment_type_id', $assessmentTypeId)
            ->where(function ($q) use ($classSection) {
                $q->whereNull('class_section_id')
                  ->orWhere('class_section_id', $classSection->id);
            })
            ->firstOrFail();

        // Bind shared attendance assessment to current class on first data fetch
        if ($assessment->class_section_id === null) {
            $assessment->class_section_id = $classSection->id;
            $assessment->save();
        }

        $students = $classSection->students()->orderBy('last_name')->orderBy('first_name')->get();
        
        $attendanceData = [];
        foreach ($students as $student) {
            $record = $assessment->attendanceRecords()
                ->where('student_id', $student->id)
                ->whereDate('date', $request->date)
                ->first();

            $status = $record ? ($record->status === 'late' ? 'present' : $record->status) : 'absent';

            $attendanceData[$student->id] = [
                'student' => $student,
                'status' => $status,
                'remark' => $record ? $record->remark : null,
                'has_record' => $record !== null,
            ];
        }

        return response()->json($attendanceData);
    }

    /**
     * Delete attendance records for a specific date
     */
    public function deleteDate(Request $request, $subjectId, $classSectionId, $term, $assessmentTypeId, $assessmentId)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $subject = auth()->user()->subjects()->findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();
        
        $assessment = Assessment::where('id', $assessmentId)
            ->where('assessment_type_id', $assessmentTypeId)
            ->where(function ($q) use ($classSection) {
                $q->whereNull('class_section_id')
                  ->orWhere('class_section_id', $classSection->id);
            })
            ->firstOrFail();

        // Bind shared attendance assessment to current class on first delete
        if ($assessment->class_section_id === null) {
            $assessment->class_section_id = $classSection->id;
            $assessment->save();
        }

        // Delete all attendance records for this date
        $assessment->attendanceRecords()
            ->where('date', $request->date)
            ->delete();

        // Update assessment scores for this class only
        $this->updateAttendanceScores($assessment, $classSection);

        return response()->json([
            'success' => true,
            'message' => 'Attendance date deleted successfully!'
        ]);
    }

    /**
     * Update attendance scores for all students in this assessment
     */
    private function updateAttendanceScores(Assessment $assessment, ClassSection $classSection)
    {
        // Limit to current class only
        $students = $classSection->students;

        foreach ($students as $student) {
            $attendanceScore = $assessment->calculateAttendanceScore($student->id);
            
            if ($attendanceScore !== null) {
                // Update or create assessment score
                $assessment->scores()->updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'term' => $assessment->term,
                    ],
                    [
                        'score' => $attendanceScore,
                        'percentage_score' => $attendanceScore,
                        'submitted_at' => now(),
                    ]
                );
            }
        }
    }
} 