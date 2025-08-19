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

        // Calculate grades for each student (same as in gradebook view)
        foreach ($students as $student) {
            $student->midterm_grade = null;
            $student->final_grade = null;
            $student->overall_grade = null;
            // Midterm
            if ($midtermAssessmentTypes->count() > 0) {
                $midtermGrades = [];
                $midtermWeights = [];
                foreach ($midtermAssessmentTypes as $assessmentType) {
                    $assessmentScores = $student->assessmentScores()
                        ->whereHas('assessment', function($query) use ($assessmentType) {
                            $query->where('assessment_type_id', $assessmentType->id);
                        })
                        ->with('assessment')
                        ->get();
                    if ($assessmentScores->count() > 0) {
                        $typeGrades = $assessmentScores->map(function($score) {
                            return ($score->score / $score->assessment->max_score) * 100;
                        })->toArray();
                        $midtermGrades[$assessmentType->id] = $typeGrades;
                        $midtermWeights[$assessmentType->id] = $assessmentType->weight;
                    }
                }
                if (!empty($midtermGrades)) {
                    $totalWeight = array_sum($midtermWeights);
                    $weightedSum = 0;
                    foreach ($midtermGrades as $typeId => $typeGrades) {
                        if (!empty($typeGrades)) {
                            $averageGrade = array_sum($typeGrades) / count($typeGrades);
                            $weightedSum += ($averageGrade * $midtermWeights[$typeId]);
                        }
                    }
                    if ($totalWeight > 0) {
                        $student->midterm_grade = round($weightedSum / $totalWeight, 1);
                    }
                }
            }
            // Final
            if ($finalAssessmentTypes->count() > 0) {
                $finalGrades = [];
                $finalWeights = [];
                foreach ($finalAssessmentTypes as $assessmentType) {
                    $assessmentScores = $student->assessmentScores()
                        ->whereHas('assessment', function($query) use ($assessmentType) {
                            $query->where('assessment_type_id', $assessmentType->id);
                        })
                        ->with('assessment')
                        ->get();
                    if ($assessmentScores->count() > 0) {
                        $typeGrades = $assessmentScores->map(function($score) {
                            return ($score->score / $score->assessment->max_score) * 100;
                        })->toArray();
                        $finalGrades[$assessmentType->id] = $typeGrades;
                        $finalWeights[$assessmentType->id] = $assessmentType->weight;
                    }
                }
                if (!empty($finalGrades)) {
                    $totalWeight = array_sum($finalWeights);
                    $weightedSum = 0;
                    foreach ($finalGrades as $typeId => $typeGrades) {
                        if (!empty($typeGrades)) {
                            $averageGrade = array_sum($typeGrades) / count($typeGrades);
                            $weightedSum += ($averageGrade * $finalWeights[$typeId]);
                        }
                    }
                    if ($totalWeight > 0) {
                        $student->final_grade = round($weightedSum / $totalWeight, 1);
                    }
                }
            }
            // Overall
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

    public function export2(Request $request, $subjectId, $classSectionId)
    {
        $subject = auth()->user()->subjects()->findOrFail($subjectId);
        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subject->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $exportData = $request->json()->all();
        $gradingMode = $exportData['gradingMode'] ?? 'percentage';
        $students = $exportData['students'] ?? [];
        $currentGrades = $exportData['currentGrades'] ?? [];

        // Get the same data structure as the original export
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

        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Build header rows (3 rows, like the PDF) - EXACTLY like original export
        $col = 1;
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '1', 'Student');
        $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . '1:' . Coordinate::stringFromColumnIndex($col) . '3');
        $col++;

        // Midterm section
        $midtermStartCol = $col;
        foreach ($midtermAssessmentTypes as $type) {
            $count = $assessments['midterm'][$type->id]['assessments']->count();
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '2', $type->name . ' (Weight: ' . $type->weight . '%)');
            
            if ($count > 1) {
                $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . '2:' . Coordinate::stringFromColumnIndex($col + $count - 1) . '2');
            }
            
            if ($count > 0) {
                foreach ($assessments['midterm'][$type->id]['assessments'] as $assessment) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '3', $assessment->name . ' (Max: ' . $assessment->max_score . ')');
                    $col++;
                }
            } else {
                // For assessment types with 0 assessments, set the header in row 2 and "No Assessments" in row 3
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '2', $type->name . ' (Weight: ' . $type->weight . '%)');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '3', 'No Assessments');
                $col++;
            }
        }
        $midtermEndCol = $col - 1;
        if ($midtermEndCol >= $midtermStartCol && $midtermEndCol > $midtermStartCol) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($midtermStartCol) . '1', 'Midterm');
            $sheet->mergeCells(Coordinate::stringFromColumnIndex($midtermStartCol) . '1:' . Coordinate::stringFromColumnIndex($midtermEndCol) . '1');
        } elseif ($midtermEndCol === $midtermStartCol) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($midtermStartCol) . '1', 'Midterm');
        }

        // Final section
        $finalStartCol = $col;
        foreach ($finalAssessmentTypes as $type) {
            $count = $assessments['final'][$type->id]['assessments']->count();
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '2', $type->name . ' (Weight: ' . $type->weight . '%)');
            
            if ($count > 1) {
                $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . '2:' . Coordinate::stringFromColumnIndex($col + $count - 1) . '2');
            }
            
            if ($count > 0) {
                foreach ($assessments['final'][$type->id]['assessments'] as $assessment) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '3', $assessment->name . ' (Max: ' . $assessment->max_score . ')');
                    $col++;
                }
            } else {
                // For assessment types with 0 assessments, set the header in row 2 and "No Assessments" in row 3
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '2', $type->name . ' (Weight: ' . $type->weight . '%)');
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '3', 'No Assessments');
                $col++;
            }
        }
        $finalEndCol = $col - 1;
        if ($finalEndCol >= $finalStartCol && $finalEndCol > $finalStartCol) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($finalStartCol) . '1', 'Final');
            $sheet->mergeCells(Coordinate::stringFromColumnIndex($finalStartCol) . '1:' . Coordinate::stringFromColumnIndex($finalEndCol) . '1');
        } elseif ($finalEndCol === $finalStartCol) {
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($finalStartCol) . '1', 'Final');
        }

        // Grade columns
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '1', 'Midterm Grade');
        $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . '1:' . Coordinate::stringFromColumnIndex($col) . '3');
        $col++;
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '1', 'Final Grade');
        $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . '1:' . Coordinate::stringFromColumnIndex($col) . '3');
        $col++;
        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '1', 'Overall Grade');
        $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . '1:' . Coordinate::stringFromColumnIndex($col) . '3');

        // Data rows - EXACTLY like original export but with JS data
        $row = 4;
        $gradeIndex = 0; // Index for currentGrades array
        foreach ($students as $student) {
            $col = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $student['name']);

            // Midterm scores - use scores from JS data
            $scoreIndex = 0;
            foreach ($midtermAssessmentTypes as $type) {
                $assessmentList = $assessments['midterm'][$type->id]['assessments'];
                if ($assessmentList->count() > 0) {
                    foreach ($assessmentList as $assessment) {
                        $displayScore = $student['scores'][$scoreIndex] ?? '--';
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $displayScore);
                        $scoreIndex++;
                    }
                } else {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, '--');
                }
            }
            
            // Final scores - use scores from JS data
            foreach ($finalAssessmentTypes as $type) {
                $assessmentList = $assessments['final'][$type->id]['assessments'];
                if ($assessmentList->count() > 0) {
                    foreach ($assessmentList as $assessment) {
                        $displayScore = $student['scores'][$scoreIndex] ?? '--';
                        $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $displayScore);
                        $scoreIndex++;
                    }
                } else {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, '--');
                }
            }
            
            // GRADES - REPLACE WITH JS-CALCULATED GRADES
            $midtermGrade = $currentGrades[$gradeIndex] ?? '--';
            $finalGrade = $currentGrades[$gradeIndex + 1] ?? '--';
            $overallGrade = $currentGrades[$gradeIndex + 2] ?? '--';
            
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $midtermGrade);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $finalGrade);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col++) . $row, $overallGrade);
            
            $gradeIndex += 3; // Move to next student's grades
            $row++;
        }

        // Auto-size columns
        foreach (range(1, $col - 1) as $colIndex) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIndex))->setAutoSize(true);
        }

        // Add borders to all cells
        $allBordersStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];
        $sheet->getStyle('A1:' . Coordinate::stringFromColumnIndex($col - 1) . ($row - 1))->applyFromArray($allBordersStyle);

        // Create filename
        $filename = 'gradebook_export2_' . $classSection->subject->code . '_' . $classSection->section . '_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Save to temporary file
        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path('app/temp/' . $filename);
        
        // Ensure temp directory exists
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        $writer->save($filePath);

        return response()->download($filePath, $filename)->deleteFileAfterSend();
    }
    
    /**
     * Apply grading mode transformation to a grade
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
                // Convert to 1.0-5.0 scale
                if ($grade >= 97) return 1.0;
                if ($grade >= 94) return 1.25;
                if ($grade >= 91) return 1.5;
                if ($grade >= 88) return 1.75;
                if ($grade >= 85) return 2.0;
                if ($grade >= 82) return 2.25;
                if ($grade >= 79) return 2.5;
                if ($grade >= 76) return 2.75;
                if ($grade >= 73) return 3.0;
                if ($grade >= 70) return 3.25;
                if ($grade >= 67) return 3.5;
                if ($grade >= 64) return 3.75;
                if ($grade >= 60) return 4.0;
                if ($grade >= 55) return 4.25;
                if ($grade >= 50) return 4.5;
                return 5.0;
                
            case 'custom':
                $maxScore = $settings['maxScore'] ?? 95;
                $passingScore = $settings['passingScore'] ?? 75;
                $customFormula = $settings['customFormula'] ?? 'inverse_linear';
                
                // Cap the grade at max score
                $cappedGrade = min($grade, $maxScore);
                
                switch ($customFormula) {
                    case 'inverse_linear':
                        // 100% = 1.0, 75% = 3.0, 50% = 5.0
                        if ($cappedGrade >= $passingScore) {
                            $range = $maxScore - $passingScore;
                            $gradeRange = 3.0 - 1.0;
                            $position = ($cappedGrade - $passingScore) / $range;
                            return round(1.0 + ($position * $gradeRange), 2);
                        } else {
                            $range = $passingScore - 50;
                            $gradeRange = 5.0 - 3.0;
                            $position = ($cappedGrade - 50) / $range;
                            return round(3.0 + ($position * $gradeRange), 2);
                        }
                        
                    case 'exponential':
                        // Exponential curve: higher grades get better scores
                        $normalized = ($cappedGrade - 50) / 50; // 0 to 1
                        $exponential = pow($normalized, 1.5);
                        return round(1.0 + (4.0 * $exponential), 2);
                        
                    case 'step':
                        // Step-based grading
                        if ($cappedGrade >= 90) return 1.0;
                        if ($cappedGrade >= 80) return 1.5;
                        if ($cappedGrade >= 70) return 2.0;
                        if ($cappedGrade >= 60) return 2.5;
                        if ($cappedGrade >= 50) return 3.0;
                        return 4.0;
                        
                    default:
                        return $cappedGrade;
                }
                
            default:
                return $grade;
        }
    }
}