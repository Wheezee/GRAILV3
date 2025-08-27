<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSection;
use App\Models\Subject;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class GradebookExportController extends Controller
{
    public function export(Request $request, $subjectId, $classSectionId)
    {
        $format = $request->input('format');
        $gradingMode = $request->input('grading_mode', 'percentage');
        $gradingSettings = json_decode($request->input('grading_settings', '{}'), true);
        
        $subject = auth()->user()->subjects()->findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $gradingStructure = $subject->gradingStructure;
        $midtermAssessmentTypes = $subject->assessmentTypes()->where('term', 'midterm')->orderBy('order')->get();
        $finalAssessmentTypes = $subject->assessmentTypes()->where('term', 'final')->orderBy('order')->get();

        $assessments = [
            'midterm' => [],
            'final' => []
        ];
        foreach ($midtermAssessmentTypes as $assessmentType) {
            $assessments['midterm'][$assessmentType->id] = [
                'type' => $assessmentType,
                'assessments' => $assessmentType->assessments()->where('term', 'midterm')->orderBy('order')->get()
            ];
        }
        foreach ($finalAssessmentTypes as $assessmentType) {
            $assessments['final'][$assessmentType->id] = [
                'type' => $assessmentType,
                'assessments' => $assessmentType->assessments()->where('term', 'final')->orderBy('order')->get()
            ];
        }
        $students = $classSection->students()->orderBy('last_name')->orderBy('first_name')->get();

        // Calculate grades for each student (EXACTLY same as gradebook table route)
        foreach ($students as $student) {
            $student->midterm_grade = null;
            $student->final_grade = null;
            $student->overall_grade = null;
            
            // Calculate midterm grade (same logic as gradebook table)
            if ($midtermAssessmentTypes->count() > 0) {
                $midtermGrades = [];
                $midtermWeights = [];
                $availableWeight = 0;
                
                foreach ($midtermAssessmentTypes as $assessmentType) {
                    // Get all assessments for this type, regardless of whether student has scores
                    $allAssessments = $assessmentType->assessments;
                    
                    if ($allAssessments->count() > 0) {
                        $assessmentScores = $student->assessmentScores()
                            ->whereHas('assessment', function($query) use ($assessmentType) {
                                $query->where('assessment_type_id', $assessmentType->id);
                            })
                            ->with('assessment')
                            ->get();
                        
                        $typeGrades = [];
                        
                        foreach ($allAssessments as $assessment) {
                            $score = $assessmentScores->where('assessment_id', $assessment->id)->first();
                            if ($score && $score->score !== null) {
                                $typeGrades[] = ($score->score / $assessment->max_score) * 100;
                            } else {
                                $typeGrades[] = 0; // missing counts as 0%
                            }
                        }
                        
                        $midtermGrades[$assessmentType->id] = $typeGrades;
                        $midtermWeights[$assessmentType->id] = $assessmentType->weight;
                        $availableWeight += $assessmentType->weight;
                    }
                }
                
                // Compute weighted average for midterm
                if (!empty($midtermGrades)) {
                    $weightedSum = 0;
                    
                    foreach ($midtermGrades as $typeId => $typeGrades) {
                        if (!empty($typeGrades)) {
                            $averageGrade = array_sum($typeGrades) / count($typeGrades);
                            $weightedSum += ($averageGrade * $midtermWeights[$typeId]);
                        }
                    }
                    
                    if ($availableWeight > 0) {
                        // Midterm grade = weighted sum ÷ sum of active weights (no scaling needed)
                        $student->midterm_grade = round($weightedSum / $availableWeight, 1);
                    }
                }
            }
            
            // Calculate final grade (same logic as gradebook table)
            if ($finalAssessmentTypes->count() > 0) {
                $finalGrades = [];
                $finalWeights = [];
                $availableWeight = 0;
                
                foreach ($finalAssessmentTypes as $assessmentType) {
                    // Get all assessments for this type, regardless of whether student has scores
                    $allAssessments = $assessmentType->assessments;
                    
                    if ($allAssessments->count() > 0) {
                        $assessmentScores = $student->assessmentScores()
                            ->whereHas('assessment', function($query) use ($assessmentType) {
                                $query->where('assessment_type_id', $assessmentType->id);
                            })
                            ->with('assessment')
                            ->get();
                        
                        $typeGrades = [];
                        
                        foreach ($allAssessments as $assessment) {
                            $score = $assessmentScores->where('assessment_id', $assessment->id)->first();
                            if ($score && $score->score !== null) {
                                $typeGrades[] = ($score->score / $assessment->max_score) * 100;
                            } else {
                                $typeGrades[] = 0; // missing counts as 0%
                            }
                        }
                        
                        $finalGrades[$assessmentType->id] = $typeGrades;
                        $finalWeights[$assessmentType->id] = $assessmentType->weight;
                        $availableWeight += $assessmentType->weight;
                    }
                }
                
                // Compute weighted average for final
                if (!empty($finalGrades)) {
                    $weightedSum = 0;
                    
                    foreach ($finalGrades as $typeId => $typeGrades) {
                        if (!empty($typeGrades)) {
                            $averageGrade = array_sum($typeGrades) / count($typeGrades);
                            $weightedSum += ($averageGrade * $finalWeights[$typeId]);
                        }
                    }
                    
                    if ($availableWeight > 0) {
                        // Final grade = weighted sum ÷ sum of active weights (no scaling needed)
                        $student->final_grade = round($weightedSum / $availableWeight, 1);
                    }
                }
            }
            
            // Calculate overall grade using subject weights (same logic as gradebook table)
            if ($student->midterm_grade !== null && $student->final_grade !== null && $gradingStructure) {
                $midtermWeight = $gradingStructure->midterm_weight / 100;
                $finalWeight = $gradingStructure->final_weight / 100;
                
                $student->overall_grade = round(
                    ($student->midterm_grade * $midtermWeight) + 
                    ($student->final_grade * $finalWeight), 
                    1
                );
            }
            
            // Apply grading mode transformations
            $student->midterm_grade = $this->applyGradingMode($student->midterm_grade, $gradingMode, $gradingSettings);
            $student->final_grade = $this->applyGradingMode($student->final_grade, $gradingMode, $gradingSettings);
            $student->overall_grade = $this->applyGradingMode($student->overall_grade, $gradingMode, $gradingSettings);
        }

        if ($format === 'pdf') {
            // Render PDF with landscape orientation
            $pdf = Pdf::loadView('teacher.gradebook_pdf', compact(
                'classSection',
                'gradingStructure',
                'midtermAssessmentTypes',
                'finalAssessmentTypes',
                'students',
                'assessments',
                'gradingMode'
            ));
            
            // Set paper size to A4 landscape
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('gradebook.pdf');
        }

        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Add grading mode info
            if ($gradingMode !== 'percentage') {
                $gradingModeText = match($gradingMode) {
                    'linear' => 'Linear Scale (1.0-5.0)',
                    'custom' => 'Custom Grading',
                    default => ucfirst($gradingMode)
                };
                $sheet->setCellValue('A1', 'Grading Mode: ' . $gradingModeText);
                $sheet->mergeCells('A1:D1');
                $sheet->getStyle('A1')->getFont()->setBold(true);
                
                // Adjust row offset for the rest of the content
                $rowOffset = 2;
            } else {
                $rowOffset = 0;
            }

            // Build header rows (3 rows, like the PDF)
            $col = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (1 + $rowOffset), 'Student');
            $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . (1 + $rowOffset) . ':' . Coordinate::stringFromColumnIndex($col) . (3 + $rowOffset));
            $col++;

            // Midterm section
            $midtermStartCol = $col;
            foreach ($midtermAssessmentTypes as $type) {
                $count = $assessments['midterm'][$type->id]['assessments']->count();
                $colspan = max($count, 1); // Ensure at least 1 column
                
                // Row 2: Assessment type name and weight
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (2 + $rowOffset), $type->name . ' (Weight: ' . $type->weight . '%)');
                if ($colspan > 1) {
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . (2 + $rowOffset) . ':' . Coordinate::stringFromColumnIndex($col + $colspan - 1) . (2 + $rowOffset));
                }
                
                // Row 3: Individual assessments or "No Assessments"
                if ($count > 0) {
                foreach ($assessments['midterm'][$type->id]['assessments'] as $assessment) {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (3 + $rowOffset), $assessment->name . ' (Max: ' . $assessment->max_score . ')');
                    $col++;
                }
                } else {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (3 + $rowOffset), 'No Assessments');
                    $col++;
                }
            }
            $midtermEndCol = $col - 1;
            
            // Row 1: Midterm header
            if ($midtermEndCol >= $midtermStartCol) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($midtermStartCol) . (1 + $rowOffset), 'Midterm');
                if ($midtermEndCol > $midtermStartCol) {
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($midtermStartCol) . (1 + $rowOffset) . ':' . Coordinate::stringFromColumnIndex($midtermEndCol) . (1 + $rowOffset));
                }
            }

            // Final section
            $finalStartCol = $col;
            foreach ($finalAssessmentTypes as $type) {
                $count = $assessments['final'][$type->id]['assessments']->count();
                $colspan = max($count, 1); // Ensure at least 1 column
                
                // Row 2: Assessment type name and weight
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (2 + $rowOffset), $type->name . ' (Weight: ' . $type->weight . '%)');
                if ($colspan > 1) {
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . (2 + $rowOffset) . ':' . Coordinate::stringFromColumnIndex($col + $colspan - 1) . (2 + $rowOffset));
                }
                
                // Row 3: Individual assessments or "No Assessments"
                if ($count > 0) {
                foreach ($assessments['final'][$type->id]['assessments'] as $assessment) {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (3 + $rowOffset), $assessment->name . ' (Max: ' . $assessment->max_score . ')');
                    $col++;
                }
                } else {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (3 + $rowOffset), 'No Assessments');
                    $col++;
                }
            }
            $finalEndCol = $col - 1;
            
            // Row 1: Final header
            if ($finalEndCol >= $finalStartCol) {
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($finalStartCol) . (1 + $rowOffset), 'Final');
                if ($finalEndCol > $finalStartCol) {
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($finalStartCol) . (1 + $rowOffset) . ':' . Coordinate::stringFromColumnIndex($finalEndCol) . (1 + $rowOffset));
                }
            }

            // Grade columns
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (1 + $rowOffset), 'Midterm Grade');
            $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . (1 + $rowOffset) . ':' . Coordinate::stringFromColumnIndex($col) . (3 + $rowOffset));
            $col++;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (1 + $rowOffset), 'Final Grade');
            $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . (1 + $rowOffset) . ':' . Coordinate::stringFromColumnIndex($col) . (3 + $rowOffset));
            $col++;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . (1 + $rowOffset), 'Overall Grade');
            $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . (1 + $rowOffset) . ':' . Coordinate::stringFromColumnIndex($col) . (3 + $rowOffset));

            // Data rows
            $row = 4 + $rowOffset;
            foreach ($students as $student) {
                $col = 1;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student->last_name . ', ' . $student->first_name);

                // Midterm scores
                foreach ($midtermAssessmentTypes as $type) {
                    $assessmentList = $assessments['midterm'][$type->id]['assessments'];
                    if ($assessmentList->count() > 0) {
                        foreach ($assessmentList as $assessment) {
                            $score = $student->assessmentScores->where('assessment_id', $assessment->id)->first();
                            $displayScore = $score && $score->score !== null ? $score->score : '--';
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $displayScore);
                        }
                    } else {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, '--');
                    }
                }
                // Final scores
                foreach ($finalAssessmentTypes as $type) {
                    $assessmentList = $assessments['final'][$type->id]['assessments'];
                    if ($assessmentList->count() > 0) {
                        foreach ($assessmentList as $assessment) {
                            $score = $student->assessmentScores->where('assessment_id', $assessment->id)->first();
                            $displayScore = $score && $score->score !== null ? $score->score : '--';
                            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $displayScore);
                        }
                    } else {
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, '--');
                    }
                }
                // Grades
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student->midterm_grade !== null ? $student->midterm_grade : '-');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student->final_grade !== null ? $student->final_grade : '-');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student->overall_grade !== null ? $student->overall_grade : '-');
                $row++;
            }

            // Output
            $filename = 'gradebook.xlsx';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;
        }

        return back()->with('error', 'Invalid export format.');
    }


    
    /**
     * Apply grading mode transformation to a grade (matches frontend logic exactly)
     */
    private function applyGradingMode($grade, $mode, $settings = [])
    {
        if ($grade === null) {
            return null;
        }
        
        switch ($mode) {
            case 'percentage':
                // Keep as percentage (0-100)
                return $grade;
                
            case 'linear':
                return $this->calculateLinearGrade($grade, $settings);
                
            case 'custom':
                return $this->calculateCustomGrade($grade, $settings);
                
            default:
                return $grade;
        }
    }
    
    /**
     * Calculate linear grade (matches frontend calculateLinearGrade function)
     */
    private function calculateLinearGrade($percentage, $params = [])
    {
        $maxScore = $params['maxScore'] ?? 95;
        $passingScore = $params['passingScore'] ?? 75;
        $passingGrade = $params['passingGrade'] ?? 3.0;

        // Scale percentage to max score
        if ($percentage > $maxScore) {
            $percentage = $maxScore;
        }

        if ($percentage >= $passingScore) {
            $grade = $passingGrade - (($percentage - $passingScore) / ($maxScore - $passingScore)) * ($passingGrade - 1.0);
            return round($grade, 2);
        } else {
            $grade = $passingGrade + (($passingScore - $percentage) / $passingScore) * (5.0 - $passingGrade);
            return round($grade, 2);
        }
    }
    
    /**
     * Get best grade for custom calculations
     */
    private function getBestGrade($maxScore)
    {
        // 100% → 1.0, 95% → 1.1, 90% → 1.2, etc.
        return 2.0 - ($maxScore / 100);
    }
    
    /**
     * Calculate custom grade (matches frontend calculateCustomGrade function)
     */
    private function calculateCustomGrade($percentage, $params = [])
    {
        $formula = $params['customFormula'] ?? 'inverse_linear';
        
        switch ($formula) {
            case 'inverse_linear': {
                // Linear scale: max_score% = best grade, passing_score = passing grade
                $maxScore = $params['maxScore'] ?? 95;
                $passingScore = $params['passingScore'] ?? 75;
                $passingGrade = $params['passingGrade'] ?? 3.0;
                $bestGrade = $this->getBestGrade($maxScore);
                
                // Cap percentage at max_score
                $effectivePercentage = min($percentage, $maxScore);
                
                if ($effectivePercentage >= $passingScore) {
                    $grade = $passingGrade - (($effectivePercentage - $passingScore) / ($maxScore - $passingScore)) * ($passingGrade - $bestGrade);
                    return round($grade, 2);
                } else {
                    // Below passing: linear scale to 5.0
                    $grade = $passingGrade + (($passingScore - $effectivePercentage) / $passingScore) * (5.0 - $passingGrade);
                    return round($grade, 2);
                }
            }
            
            case 'exponential':
                $passingScore = $params['passingScore'] ?? 75;
                $passingGrade = $params['passingGrade'] ?? 3.0;
                $normalized = ($percentage - $passingScore) / (100 - $passingScore);
                $grade = $passingGrade - ($normalized * ($passingGrade - 1.0));
                return round(max(1.0, $grade), 2);
                
            case 'step':
                if ($percentage >= 97) return 1.00;
                if ($percentage >= 94) return 1.25;
                if ($percentage >= 91) return 1.50;
                if ($percentage >= 88) return 1.75;
                if ($percentage >= 85) return 2.00;
                if ($percentage >= 82) return 2.25;
                if ($percentage >= 79) return 2.50;
                if ($percentage >= 76) return 2.75;
                if ($percentage >= ($params['passingScore'] ?? 75)) return ($params['passingGrade'] ?? 3.0);
                return 5.00;
                
            default:
                return $this->calculateLinearGrade($percentage, $params);
        }
    }
}