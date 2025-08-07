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
        }

        if ($format === 'pdf') {
            // Render PDF with landscape orientation
            $pdf = Pdf::loadView('teacher.gradebook_pdf', compact(
                'classSection',
                'gradingStructure',
                'midtermAssessmentTypes',
                'finalAssessmentTypes',
                'students',
                'assessments'
            ));
            
            // Set paper size to A4 landscape
            $pdf->setPaper('A4', 'landscape');
            
            return $pdf->download('gradebook.pdf');
        }

        if ($format === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Build header rows (3 rows, like the PDF)
            $col = 1;
            $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '1', 'Student');
            $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . '1:' . Coordinate::stringFromColumnIndex($col) . '3');
            $col++;

            // Midterm section
            $midtermStartCol = $col;
            foreach ($midtermAssessmentTypes as $type) {
                $count = $assessments['midterm'][$type->id]['assessments']->count() ?: 1;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '2', $type->name . ' (Weight: ' . $type->weight . '%)');
                if ($count > 1) {
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . '2:' . Coordinate::stringFromColumnIndex($col + $count - 1) . '2');
                }
                foreach ($assessments['midterm'][$type->id]['assessments'] as $assessment) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '3', $assessment->name . ' (Max: ' . $assessment->max_score . ')');
                    $col++;
                }
                if ($count === 0) {
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
                $count = $assessments['final'][$type->id]['assessments']->count() ?: 1;
                $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '2', $type->name . ' (Weight: ' . $type->weight . '%)');
                if ($count > 1) {
                    $sheet->mergeCells(Coordinate::stringFromColumnIndex($col) . '2:' . Coordinate::stringFromColumnIndex($col + $count - 1) . '2');
                }
                foreach ($assessments['final'][$type->id]['assessments'] as $assessment) {
                    $sheet->setCellValue(Coordinate::stringFromColumnIndex($col) . '3', $assessment->name . ' (Max: ' . $assessment->max_score . ')');
                    $col++;
                }
                if ($count === 0) {
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

            // Data rows
            $row = 4;
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
}