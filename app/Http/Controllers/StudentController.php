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
    
    public function showClassAnalytics($subjectId, $classSectionId, $term)
    {
        $subject = Subject::findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)->where('subject_id', $subjectId)->firstOrFail();
        $students = $classSection->students()->orderBy('last_name')->orderBy('first_name')->get();
        $assessmentTypes = $subject->assessmentTypes()->where('term', $term)->with(['assessments' => function($query) use ($term) {
            $query->where('term', $term);
        }, 'assessments.scores'])->orderBy('order')->get();
        
        // Calculate comprehensive class analytics
        $analytics = $this->calculateClassAnalytics($students, $assessmentTypes, $term, $classSection);
        
        return view('teacher.class-analytics', compact('students', 'subject', 'classSection', 'assessmentTypes', 'term', 'analytics'));
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
    
    private function calculateClassAnalytics($students, $assessmentTypes, $term, $classSection)
    {
        $analytics = [
            'student_rankings' => [],
            'grade_distribution' => [],
            'assessment_difficulty' => [],
            'performance_trends' => [],
            'risk_distribution' => [],
            'class_stats' => [],
            'student_metrics' => []
        ];
        
        // Calculate student rankings with gap indicators
        $studentRankings = [];
        $metricsService = app(\App\Services\StudentMetricsService::class);
        
        foreach ($students as $student) {
            $currentGrade = $this->calculateStudentOverallGrade($student, $assessmentTypes, $term);
            
            // Calculate ML risk prediction
            $riskData = $metricsService->calculateStudentRisk($student, $assessmentTypes, $term);
            
            // Get student metrics for debug button
            $studentMetrics = $metricsService->calculateStudentMetrics($student->id, $classSection->id, $term);
            
            $studentRankings[] = [
                'student' => $student,
                'current_grade' => $currentGrade,
                'risk_level' => $riskData['risk_level'],
                'risk_score' => $riskData['risk_score']
            ];
            
            // Store metrics for debug button
            $analytics['student_metrics'][$student->id] = $studentMetrics;
            
            // Store individual assessment scores for correlation analysis
            $analytics['student_assessment_scores'][$student->id] = [];
            
            // Calculate assessment type averages
            $analytics['student_type_averages'][$student->id] = [];
            
            foreach ($assessmentTypes as $type) {
                $typeScores = [];
                foreach ($type->assessments as $assessment) {
                    $score = $assessment->scores->where('student_id', $student->id)->first();
                    if ($score && $score->score !== null && $assessment->max_score > 0) {
                        $percentage = ($score->score / $assessment->max_score) * 100;
                        $analytics['student_assessment_scores'][$student->id][$assessment->name] = $percentage;
                        $typeScores[] = $percentage;
                    } else {
                        $analytics['student_assessment_scores'][$student->id][$assessment->name] = null;
                    }
                }
                
                // Calculate average for this assessment type
                if (!empty($typeScores)) {
                    $analytics['student_type_averages'][$student->id][$type->name] = array_sum($typeScores) / count($typeScores);
                } else {
                    $analytics['student_type_averages'][$student->id][$type->name] = null;
                }
            }
        }
        
        // Sort by current grade (descending) and assign ranks
        usort($studentRankings, function($a, $b) {
            return $b['current_grade'] <=> $a['current_grade'];
        });
        
        // Calculate gaps from the student above them
        foreach ($studentRankings as $index => &$ranking) {
            $ranking['rank'] = $index + 1;
            
            if ($index === 0) {
                // #1 student has no gap
                $ranking['gap'] = 0;
            } else {
                // Calculate gap from the student above them (negative because they need to gain this amount)
                $previousStudentGrade = $studentRankings[$index - 1]['current_grade'];
                $ranking['gap'] = -($previousStudentGrade - $ranking['current_grade']);
            }
        }
        
        $analytics['student_rankings'] = $studentRankings;
        
        // Calculate grade distribution
        $grades = array_column($studentRankings, 'current_grade');
        $analytics['grade_distribution'] = [
            'excellent' => count(array_filter($grades, fn($g) => $g >= 90)),
            'good' => count(array_filter($grades, fn($g) => $g >= 80 && $g < 90)),
            'satisfactory' => count(array_filter($grades, fn($g) => $g >= 70 && $g < 80)),
            'needs_improvement' => count(array_filter($grades, fn($g) => $g >= 60 && $g < 70)),
            'failing' => count(array_filter($grades, fn($g) => $g < 60))
        ];
        
        // Calculate assessment difficulty
        $assessmentDifficulty = [];
        foreach ($assessmentTypes as $type) {
            foreach ($type->assessments as $assessment) {
                $scores = $assessment->scores->map(function($score) use ($assessment) {
                    if ($score->score !== null && $assessment->max_score > 0) {
                        return ($score->score / $assessment->max_score) * 100;
                    }
                    return null;
                })->filter()->values();
                
                if ($scores->count() > 0) {
                    $assessmentDifficulty[] = [
                        'name' => $assessment->name,
                        'type' => $type->name,
                        'average_score' => $scores->avg(),
                        'difficulty_level' => $scores->avg() >= 85 ? 'Easy' : ($scores->avg() >= 70 ? 'Medium' : 'Hard')
                    ];
                }
            }
        }
        
        // Sort by average score (ascending - hardest first)
        usort($assessmentDifficulty, function($a, $b) {
            return $a['average_score'] <=> $b['average_score'];
        });
        
        $analytics['assessment_difficulty'] = $assessmentDifficulty;
        
        // Calculate class statistics
        $analytics['class_stats'] = [
            'total_students' => count($students),
            'average_grade' => count($grades) > 0 ? array_sum($grades) / count($grades) : 0,
            'highest_grade' => count($grades) > 0 ? max($grades) : 0,
            'lowest_grade' => count($grades) > 0 ? min($grades) : 0,
            'passing_rate' => count(array_filter($grades, fn($g) => $g >= 70)) / count($grades) * 100
        ];
        
        return $analytics;
    }
    
    private function calculateStudentOverallGrade($student, $assessmentTypes, $term)
    {
        $totalWeight = 0;
        $weightedSum = 0;
        
        foreach ($assessmentTypes as $type) {
            $typeScores = [];
            foreach ($type->assessments as $assessment) {
                $score = $assessment->scores->where('student_id', $student->id)->first();
                if ($score && $score->score !== null && $assessment->max_score > 0) {
                    $percentage = ($score->score / $assessment->max_score) * 100;
                    $typeScores[] = $percentage;
                }
            }
            
            if (count($typeScores) > 0) {
                $typeAverage = array_sum($typeScores) / count($typeScores);
                $weightedSum += ($typeAverage * $type->weight);
                $totalWeight += $type->weight;
            }
        }
        
        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }
    
    private function calculateStudentPreviousGrade($student, $assessmentTypes, $term)
    {
        // Calculate improvement based on assessment order (first half vs second half)
        $allAssessments = collect();
        foreach ($assessmentTypes as $type) {
            foreach ($type->assessments as $assessment) {
                $allAssessments->push($assessment);
            }
        }
        
        // Sort assessments by date or order
        $sortedAssessments = $allAssessments->sortBy('created_at');
        $totalAssessments = $sortedAssessments->count();
        
        if ($totalAssessments < 2) {
            return 0; // Not enough data for improvement calculation
        }
        
        // Split into first half and second half
        $firstHalf = $sortedAssessments->take(ceil($totalAssessments / 2));
        $secondHalf = $sortedAssessments->slice(ceil($totalAssessments / 2));
        
        // Calculate average for first half
        $firstHalfScores = [];
        foreach ($firstHalf as $assessment) {
            $score = $assessment->scores->where('student_id', $student->id)->first();
            if ($score && $score->score !== null && $assessment->max_score > 0) {
                $percentage = ($score->score / $assessment->max_score) * 100;
                $firstHalfScores[] = $percentage;
            }
        }
        
        // Calculate average for second half
        $secondHalfScores = [];
        foreach ($secondHalf as $assessment) {
            $score = $assessment->scores->where('student_id', $student->id)->first();
            if ($score && $score->score !== null && $assessment->max_score > 0) {
                $percentage = ($score->score / $assessment->max_score) * 100;
                $secondHalfScores[] = $percentage;
            }
        }
        
        $firstHalfAvg = count($firstHalfScores) > 0 ? array_sum($firstHalfScores) / count($firstHalfScores) : 0;
        $secondHalfAvg = count($secondHalfScores) > 0 ? array_sum($secondHalfScores) / count($secondHalfScores) : 0;
        
        // Return the difference (positive means improvement)
        return $secondHalfAvg - $firstHalfAvg;
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