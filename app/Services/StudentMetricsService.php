<?php

namespace App\Services;

use App\Models\Student;
use App\Models\AssessmentScore;
use App\Models\Assessment;
use App\Models\AssessmentType;
use Illuminate\Support\Facades\DB;

class StudentMetricsService
{
    /**
     * Calculate comprehensive metrics for a student
     */
    public function calculateStudentMetrics($studentId, $classSectionId, $term = null)
    {
        $student = Student::find($studentId);
        if (!$student) {
            return null;
        }

        // Get the class section to find the subject
        $classSection = \App\Models\ClassSection::find($classSectionId);
        if (!$classSection) {
            return null;
        }

        // Get all assessments for this subject
        $assessments = Assessment::whereHas('assessmentType', function($query) use ($classSection) {
            $query->where('subject_id', $classSection->subject_id);
        })->with(['scores' => function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }])->get();

        // Filter by term if specified
        if ($term) {
            $assessments = $assessments->filter(function($assessment) use ($term) {
                return $assessment->assessmentType->term === $term && $assessment->term === $term;
            });
        }

        $metrics = [
            'avg_score_pct' => 0,
            'variation_score_pct' => 0,
            'late_submission_pct' => 0,
            'missed_submission_pct' => 0,
            'total_assessments' => $assessments->count(),
            'completed_assessments' => 0,
            'late_submissions' => 0,
            'missed_submissions' => 0,
            'scores' => []
        ];

        if ($assessments->count() === 0) {
            return $metrics;
        }

        $scores = [];
        $lateCount = 0;
        $missedCount = 0;
        $completedCount = 0;

        foreach ($assessments as $assessment) {
            $score = $assessment->scores->first();
            
            if ($score && $score->score !== null) {
                // Calculate percentage score
                $percentage = ($score->score / $assessment->max_score) * 100;
                $scores[] = $percentage;
                $completedCount++;

                // Check if late
                if ($score->is_late) {
                    $lateCount++;
                }
            } else {
                // Missed submission
                $missedCount++;
            }
        }

        // Calculate metrics
        if (count($scores) > 0) {
            $metrics['avg_score_pct'] = round(array_sum($scores) / count($scores), 1);
            $metrics['variation_score_pct'] = $this->calculateVariation($scores);
        }

        $totalAssessments = $assessments->count();
        if ($totalAssessments > 0) {
            $metrics['late_submission_pct'] = round(($lateCount / $totalAssessments) * 100, 1);
            $metrics['missed_submission_pct'] = round(($missedCount / $totalAssessments) * 100, 1);
        }

        $metrics['completed_assessments'] = $completedCount;
        $metrics['late_submissions'] = $lateCount;
        $metrics['missed_submissions'] = $missedCount;
        $metrics['scores'] = $scores;

        return $metrics;
    }

    /**
     * Calculate score variation (standard deviation as percentage)
     */
    private function calculateVariation($scores)
    {
        if (count($scores) < 2) {
            return 0; // Need at least 2 scores for variation
        }

        $mean = array_sum($scores) / count($scores);
        $variance = 0;

        foreach ($scores as $score) {
            $variance += pow($score - $mean, 2);
        }

        $variance = $variance / count($scores);
        $standardDeviation = sqrt($variance);

        // Return as percentage of the mean (coefficient of variation)
        return round(($standardDeviation / $mean) * 100, 1);
    }

    /**
     * Get detailed breakdown for debugging
     */
    public function getDetailedBreakdown($studentId, $classSectionId, $term = null)
    {
        $metrics = $this->calculateStudentMetrics($studentId, $classSectionId, $term);
        
        if (!$metrics) {
            return null;
        }

        // Get assessment details for breakdown
        $classSection = \App\Models\ClassSection::find($classSectionId);
        if (!$classSection) {
            return null;
        }

        $assessments = Assessment::whereHas('assessmentType', function($query) use ($classSection) {
            $query->where('subject_id', $classSection->subject_id);
        })->with(['scores' => function($query) use ($studentId) {
            $query->where('student_id', $studentId);
        }, 'assessmentType'])->get();

        if ($term) {
            $assessments = $assessments->filter(function($assessment) use ($term) {
                return $assessment->assessmentType->term === $term && $assessment->term === $term;
            });
        }

        $breakdown = [
            'metrics' => $metrics,
            'assessments' => []
        ];

        foreach ($assessments as $assessment) {
            $score = $assessment->scores->first();
            $assessmentData = [
                'id' => $assessment->id,
                'name' => $assessment->name,
                'type' => $assessment->assessmentType->name,
                'max_score' => $assessment->max_score,
                'has_score' => false,
                'score' => null,
                'percentage' => null,
                'is_late' => false,
                'is_missed' => true
            ];

            if ($score && $score->score !== null) {
                $assessmentData['has_score'] = true;
                $assessmentData['score'] = $score->score;
                $assessmentData['percentage'] = round(($score->score / $assessment->max_score) * 100, 1);
                $assessmentData['is_late'] = $score->is_late;
                $assessmentData['is_missed'] = false;
            }

            $breakdown['assessments'][] = $assessmentData;
        }

        return $breakdown;
    }

    /**
     * Calculate metrics for multiple students
     */
    public function calculateBulkMetrics($studentIds, $classSectionId, $term = null)
    {
        $results = [];
        
        foreach ($studentIds as $studentId) {
            $metrics = $this->calculateStudentMetrics($studentId, $classSectionId, $term);
            $results[$studentId] = $metrics;
        }

        return $results;
    }
} 