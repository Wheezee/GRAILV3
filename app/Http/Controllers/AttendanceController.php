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
            ->firstOrFail();

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
            'attendance.*.status' => 'required|in:present,absent,late',
        ]);

        $subject = auth()->user()->subjects()->findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();
        
        $assessment = Assessment::where('id', $assessmentId)
            ->where('assessment_type_id', $assessmentTypeId)
            ->firstOrFail();

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

        // Update assessment scores based on new attendance data
        $this->updateAttendanceScores($assessment);

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
            ->firstOrFail();

        $students = $classSection->students()->orderBy('last_name')->orderBy('first_name')->get();
        
        $attendanceData = [];
        foreach ($students as $student) {
            $record = $assessment->attendanceRecords()
                ->where('student_id', $student->id)
                ->whereDate('date', $request->date)
                ->first();

            $attendanceData[$student->id] = [
                'student' => $student,
                'status' => $record ? $record->status : 'absent',
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
            ->firstOrFail();

        // Delete all attendance records for this date
        $assessment->attendanceRecords()
            ->where('date', $request->date)
            ->delete();

        // Update assessment scores
        $this->updateAttendanceScores($assessment);

        return response()->json([
            'success' => true,
            'message' => 'Attendance date deleted successfully!'
        ]);
    }

    /**
     * Update attendance scores for all students in this assessment
     */
    private function updateAttendanceScores(Assessment $assessment)
    {
        $students = $assessment->assessmentType->subject->classSections()
            ->where('teacher_id', auth()->id())
            ->with('students')
            ->get()
            ->flatMap->students;

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