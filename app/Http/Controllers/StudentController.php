<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassSection;
use App\Models\Subject;
use App\Models\AssessmentType;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function showAnalysis($subjectId, $classSectionId, $studentId, $term)
    {
        $subject = Subject::findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)->where('subject_id', $subjectId)->firstOrFail();
        $student = $classSection->students()->where('students.id', $studentId)->firstOrFail();
        $assessmentTypes = $subject->assessmentTypes()->where('term', $term)->with(['assessments' => function($query) use ($term) {
            $query->where('term', $term);
        }, 'assessments.scores'])->orderBy('order')->get();
        
        // Calculate comprehensive analytics
        $analytics = $this->calculateStudentAnalytics($student, $assessmentTypes, $term);
        
        return view('teacher.student-analysis', compact('student', 'subject', 'classSection', 'assessmentTypes', 'term', 'analytics'));
    }
    
    public function getAnalytics($subjectId, $classSectionId, $term)
    {
        $classSection = \App\Models\ClassSection::where('id', $classSectionId)->where('subject_id', $subjectId)->firstOrFail();
        $students = $classSection->students;
        $total = $students->count();
        $gradeSum = 0;
        $gradeCount = 0;
        $riskSum = 0;
        $lateSum = 0;
        $missedSum = 0;
        $metricCount = 0;
        
        // Get metrics service for averages
        $metricsService = app(\App\Services\StudentMetricsService::class);
        
        foreach ($students as $student) {
            // Calculate grade averages for display
            $grade = $student->assessmentScores()
                ->whereHas('assessment', function($query) use ($term) {
                    $query->where('term', $term);
                })
                ->get()
                ->map(function($score) {
                    if ($score->assessment && $score->assessment->max_score > 0 && $score->score !== null) {
                        return ($score->score / $score->assessment->max_score) * 100;
                    }
                    return null;
                })
                ->filter()
                ->avg() ?? 0;
            
            if ($grade > 0) {
                $gradeSum += $grade;
                $gradeCount++;
            }
            
            // Get metrics for averages
            $metrics = $metricsService->calculateStudentMetrics($student->id, $classSectionId, $term);
            if ($metrics) {
                $riskSum += $metrics['avg_score_pct'] ?? 0;
                $lateSum += $metrics['late_submission_pct'] ?? 0;
                $missedSum += $metrics['missed_submission_pct'] ?? 0;
                $metricCount++;
            }
        }
        
        $avgGrade = $gradeCount > 0 ? $gradeSum / $gradeCount : null;
        $avgRisk = $metricCount > 0 ? $riskSum / $metricCount : null;
        $avgLate = $metricCount > 0 ? $lateSum / $metricCount : null;
        $avgMissed = $metricCount > 0 ? $missedSum / $metricCount : null;
        
        // Return data without risk counts - will be calculated client-side
        return response()->json([
            'passing' => 0,      // Will be calculated client-side
            'failing' => 0,      // Will be calculated client-side  
            'atRisk' => 0,       // Will be calculated client-side
            'total' => $total,
            'avgGrade' => $avgGrade,
            'avgRisk' => $avgRisk,
            'avgLate' => $avgLate,
            'avgMissed' => $avgMissed,
        ]);
    }
    
    private function calculateStudentAnalytics($student, $assessmentTypes, $term)
    {
        $allScores = [];
        $typeStats = [];
        $heatmapData = [];
        $riskFactors = [];
        
        foreach ($assessmentTypes as $type) {
            $scores = [];
            $percentages = [];
            $lateCount = 0;
            $missedCount = 0;
            
            foreach ($type->assessments as $assessment) {
                $score = $assessment->getStudentScore($student->id);
                
                if ($score && $score->score !== null) {
                    $percentage = ($assessment->max_score > 0) ? ($score->score / $assessment->max_score) * 100 : 0;
                    $scores[] = $score->score;
                    $percentages[] = $percentage;
                    
                    if ($score->is_late) $lateCount++;
                    
                    $allScores[] = [
                        'assessment' => $assessment,
                        'score' => $score,
                        'percentage' => $percentage,
                        'type' => $type->name
                    ];
                } else {
                    $missedCount++;
                    $percentages[] = null;
                }
                
                // Heatmap data
                $heatmapData[] = [
                    'type' => $type->name,
                    'assessment' => $assessment->name,
                    'percentage' => $percentage ?? 0,
                    'is_late' => $score ? $score->is_late : false,
                    'is_missed' => !$score || $score->score === null
                ];
            }
            
            // Calculate statistics for this type
            $validScores = array_filter($scores);
            $validPercentages = array_filter($percentages, function($p) { return $p !== null; });
            
            $typeStats[$type->name] = [
                'average' => count($validScores) > 0 ? round(array_sum($validScores) / count($validScores), 2) : 0,
                'average_percentage' => count($validPercentages) > 0 ? round(array_sum($validPercentages) / count($validPercentages), 2) : 0,
                'std_deviation' => count($validPercentages) > 1 ? $this->calculateStdDeviation($validPercentages) : 0,
                'late_count' => $lateCount,
                'missed_count' => $missedCount,
                'total_assessments' => count($type->assessments),
                'completion_rate' => count($type->assessments) > 0 ? (count($validScores) / count($type->assessments) * 100) : 0
            ];
        }
        
        // Calculate overall statistics
        $allPercentages = array_column($allScores, 'percentage');
        $overallAverage = count($allPercentages) > 0 ? round(array_sum($allPercentages) / count($allPercentages), 2) : 0;
        $overallStdDev = count($allPercentages) > 1 ? $this->calculateStdDeviation($allPercentages) : 0;
        
        // Risk assessment
        $riskFactors = $this->assessRiskFactors($typeStats, $overallAverage, $overallStdDev);
        
        // Grade margin analysis
        $passingThreshold = 75;
        $gradeMargin = $overallAverage - $passingThreshold;
        $riskLevel = $this->determineRiskLevel($gradeMargin, $overallStdDev, $riskFactors);
        
        return [
            'overall_average' => $overallAverage,
            'overall_std_dev' => $overallStdDev,
            'type_stats' => $typeStats,
            'heatmap_data' => $heatmapData,
            'risk_factors' => $riskFactors,
            'grade_margin' => $gradeMargin,
            'passing_threshold' => $passingThreshold,
            'risk_level' => $riskLevel,
            'all_scores' => $allScores
        ];
    }
    
    private function calculateStdDeviation($values)
    {
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(function($x) use ($mean) {
            return pow($x - $mean, 2);
        }, $values)) / count($values);
        return round(sqrt($variance), 2);
    }
    
    private function assessRiskFactors($typeStats, $overallAverage, $overallStdDev)
    {
        $factors = [];
        
        // High variation risk
        if ($overallStdDev > 20) {
            $factors[] = [
                'type' => 'High Variation',
                'description' => 'Inconsistent performance across assessments',
                'severity' => 'high',
                'suggestions' => ['Provide study guides', 'Schedule regular check-ins', 'Offer practice materials']
            ];
        }
        
        // Low average risk
        if ($overallAverage < 75) {
            $factors[] = [
                'type' => 'Below Passing',
                'description' => 'Current average is below passing threshold',
                'severity' => 'high',
                'suggestions' => ['Schedule tutoring sessions', 'Provide extra practice', 'Contact guardian']
            ];
        }
        
        // Late submission risk
        $totalLate = array_sum(array_column($typeStats, 'late_count'));
        if ($totalLate > 2) {
            $factors[] = [
                'type' => 'Chronic Procrastinator',
                'description' => 'Frequent late submissions',
                'severity' => 'medium',
                'suggestions' => ['Offer pacing guide', 'Set earlier deadlines', 'Initiate regular check-ins']
            ];
        }
        
        // Missing assignments risk
        $totalMissed = array_sum(array_column($typeStats, 'missed_count'));
        if ($totalMissed > 1) {
            $factors[] = [
                'type' => 'Missing Assignments',
                'description' => 'Several assignments not submitted',
                'severity' => 'high',
                'suggestions' => ['Contact student immediately', 'Notify guardian', 'Provide makeup opportunities']
            ];
        }
        
        // Low performance for any assessment type (modular) - only for types with actual data
        foreach ($typeStats as $typeName => $stats) {
            // Only create risk factors for assessment types that have assessments in this term
            if (isset($stats['total_assessments']) && $stats['total_assessments'] > 0 && 
                isset($stats['average_percentage']) && $stats['average_percentage'] < 60) {
                $factors[] = [
                    'type' => 'Low ' . $typeName . ' Performance',
                    'description' => $typeName . ' scores are consistently low.',
                    'severity' => 'high',
                    'suggestions' => [
                        'Offer extra help or review sessions for ' . strtolower($typeName),
                        'Provide additional feedback',
                        'Encourage practice and improvement in ' . strtolower($typeName)
                    ]
                ];
            }
        }
        
        return $factors;
    }
    
    private function determineRiskLevel($gradeMargin, $stdDev, $riskFactors)
    {
        if (empty($riskFactors)) {
            return 'Safe';
        }
        
        $highRiskCount = count(array_filter($riskFactors, function($f) { return $f['severity'] === 'high'; }));
        
        if ($highRiskCount >= 2) {
            return 'High Risk';
        } elseif ($highRiskCount >= 1 || $gradeMargin < -10) {
            return 'Medium Risk';
        } else {
            return 'Low Risk';
        }
    }
}