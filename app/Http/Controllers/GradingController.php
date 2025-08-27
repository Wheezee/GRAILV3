<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Services\GradingService;

class GradingController extends Controller
{
    protected $gradingService;

    public function __construct(GradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Test endpoint for debugging
     */
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Grading controller is working',
            'received_data' => $request->all()
        ]);
    }

    /**
     * Calculate grade using the grading service
     */
    public function calculateGrade(Request $request)
    {
        $request->validate([
            'percentage' => 'required|numeric|min:0|max:100',
            'method' => 'required|string|in:percentage,linear,curved,pass_fail,custom',
            'params' => 'array'
        ]);

        $percentage = $request->input('percentage');
        $method = $request->input('method');
        $params = $request->input('params', []);

        $grade = $this->gradingService->calculateGrade($percentage, $method, $params);
        $color = $this->gradingService->getGradeColor($grade, $method);
        $formatted = $this->gradingService->formatGrade($grade, $method);

        return response()->json([
            'success' => true,
            'grade' => $grade,
            'formatted' => $formatted,
            'color' => $color
        ]);
    }

    /**
     * Get default parameters for a grading method
     */
    public function getDefaultParams(Request $request)
    {
        $request->validate([
            'method' => 'required|string|in:percentage,linear,curved,pass_fail,custom'
        ]);

        $method = $request->input('method');
        $params = $this->gradingService->getDefaultParams($method);

        return response()->json([
            'success' => true,
            'params' => $params
        ]);
    }

    /**
     * Save grading settings for a class section
     */
    public function saveSettings(Request $request, $subjectId, $classSectionId)
    {
        try {
            // Debug: Log the received data
            \Log::info('Grading settings save request', [
                'subject_id' => $subjectId,
                'class_section_id' => $classSectionId,
                'received_data' => $request->all(),
                'user_id' => auth()->id()
            ]);

            $request->validate([
                'grading_method' => 'required|string|in:percentage,linear,curved,pass_fail,custom',
                'max_score' => 'required|numeric|min:50|max:100',
                'passing_score' => 'required|numeric|min:50|max:90',
                'custom_formula' => 'nullable|string|in:inverse_linear,exponential,step'
            ]);

            // Verify teacher owns this class section
            $subject = auth()->user()->subjects()->findOrFail($subjectId);
            $classSection = ClassSection::where('id', $classSectionId)
                ->where('subject_id', $subject->id)
                ->where('teacher_id', auth()->id())
                ->firstOrFail();

                    // Save settings to class section
        $settings = [
            'grading_method' => $request->input('grading_method'),
            'max_score' => (float) $request->input('max_score'),
            'passing_score' => (float) $request->input('passing_score'),
            'passing_grade' => 3.0, // Hardcoded passing grade
            'custom_formula' => $request->input('custom_formula')
        ];

            $classSection->update([
                'grading_settings' => $settings
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Grading settings saved successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error saving settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get grading settings for a class section
     */
    public function getSettings($subjectId, $classSectionId)
    {
        $subject = auth()->user()->subjects()->findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $settings = $classSection->grading_settings ?? [];
        
        // Return default settings if none are saved
        if (empty($settings)) {
            $settings = $this->gradingService->getDefaultParams('linear');
            $settings['grading_method'] = 'linear';
        }

        return response()->json([
            'success' => true,
            'settings' => $settings
        ]);
    }

    /**
     * Calculate grades for all students in a class section
     */
    public function calculateClassGrades($subjectId, $classSectionId, Request $request)
    {
        $request->validate([
            'method' => 'required|string|in:percentage,linear,curved,pass_fail,custom',
            'params' => 'array'
        ]);

        $subject = auth()->user()->subjects()->findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $students = $classSection->students()->orderBy('last_name')->orderBy('first_name')->get();
        $method = $request->input('method');
        $params = $request->input('params', []);

        $grades = [];
        foreach ($students as $student) {
            // Calculate grades for each student (you'll need to implement this based on your existing logic)
            $midtermGrade = $this->calculateStudentGrade($student, $classSection, 'midterm', $method, $params);
            $finalGrade = $this->calculateStudentGrade($student, $classSection, 'final', $method, $params);
            $overallGrade = $this->calculateOverallGrade($student, $classSection, $method, $params);

            $grades[$student->id] = [
                'student' => [
                    'id' => $student->id,
                    'name' => $student->last_name . ', ' . $student->first_name,
                    'student_id' => $student->student_id
                ],
                'midterm' => $midtermGrade,
                'final' => $finalGrade,
                'overall' => $overallGrade
            ];
        }

        return response()->json([
            'success' => true,
            'grades' => $grades
        ]);
    }

    /**
     * Calculate grade for a specific student and term
     */
    private function calculateStudentGrade($student, $classSection, $term, $method, $params)
    {
        // This is a simplified version - you'll need to implement the full logic
        // based on your existing grade calculation in the gradebook
        
        // For now, return a placeholder
        return null;
    }

    /**
     * Calculate overall grade for a student
     */
    private function calculateOverallGrade($student, $classSection, $method, $params)
    {
        // This is a simplified version - you'll need to implement the full logic
        // based on your existing overall grade calculation
        
        // For now, return a placeholder
        return null;
    }
} 