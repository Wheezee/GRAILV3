<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;


class BatchEnrollmentController extends Controller
{
    public function showUploadForm($subjectId, $classSectionId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        return view('teacher.batch-enrollment', compact('classSection'));
    }

    public function uploadStudents(Request $request, $subjectId, $classSectionId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {
            $file = $request->file('excel_file');
            $extension = strtolower($file->getClientOriginalExtension());
            
            $students = [];
            $errors = [];
            $successCount = 0;
            $rowNumber = 0;

            // Convert XLSX/XLS to CSV if needed
            if ($extension === 'xlsx' || $extension === 'xls') {
                $rows = $this->convertExcelToCsv($file);
                if ($rows === null) {
                    return back()->withErrors(['excel_file' => 'Error reading Excel file. Please check the file format.'])->withInput();
                }
            } else {
                // Handle CSV file directly
                $handle = fopen($file->getRealPath(), 'r');
                $rows = [];
                while (($data = fgetcsv($handle)) !== false) {
                    $rows[] = $data;
                }
                fclose($handle);
            }

            // Process each row
            foreach ($rows as $row) {
                $rowNumber++;
                
                // Skip header rows (rows 1-6 for your Excel format)
                if ($rowNumber <= 6) {
                    continue;
                }

                // Get data from row array
                $data = $row;
                
                // Check if we have enough columns (at least Student ID and Fullname)
                if (count($data) < 2) {
                    $errors[] = "Row {$rowNumber}: Insufficient data. Need at least Student ID and Fullname.";
                    continue;
                }

                // Parse the full name from "LASTNAME, FIRSTNAME MIDDLENAME" format
                $fullName = trim($data[1]);
                $nameParts = $this->parseFullName($fullName);

                $studentData = [
                    'student_id' => trim($data[0]), // Column A: Student ID
                    'first_name' => $nameParts['first_name'],
                    'last_name' => $nameParts['last_name'],
                    'middle_name' => $nameParts['middle_name'],
                    'gender' => isset($data[5]) ? trim($data[5]) : null, // Column F: Gender
                    'email' => null, // Not available in your Excel format
                ];

                // Validate student data
                $validator = Validator::make($studentData, [
                    'student_id' => 'required|string|max:255',
                    'first_name' => 'required|string|max:255',
                    'last_name' => 'required|string|max:255',
                    'middle_name' => 'nullable|string|max:255',
                    'gender' => 'nullable|string|max:20',
                    'email' => 'nullable|email|max:255',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Row {$rowNumber}: " . implode(', ', $validator->errors()->all());
                    continue;
                }

                // Check if student already exists in this class section
                $existingStudent = $classSection->students()
                    ->where('students.student_id', $studentData['student_id'])
                    ->first();

                if ($existingStudent) {
                    $errors[] = "Row {$rowNumber}: Student ID '{$studentData['student_id']}' already exists in this class.";
                    continue;
                }

                $students[] = $studentData;
            }

            // If no errors, save all students
            if (empty($errors)) {
                foreach ($students as $studentData) {
                    // Check if student exists in the system
                    $existingStudent = Student::where('student_id', $studentData['student_id'])->first();
                    
                    if ($existingStudent) {
                        // Student exists, just enroll them in this class
                        $student = $existingStudent;
                    } else {
                        // Create new student
                        $student = Student::create([
                            'student_id' => $studentData['student_id'],
                            'first_name' => $studentData['first_name'],
                            'last_name' => $studentData['last_name'],
                            'middle_name' => $studentData['middle_name'],
                            'gender' => $studentData['gender'],
                            'email' => $studentData['email'],
                        ]);
                    }
                    
                    // Enroll them in the class section
                    $classSection->students()->attach($student->id, [
                        'enrollment_date' => now(),
                        'status' => 'enrolled'
                    ]);
                    
                    $successCount++;
                }

                // Update student count
                $classSection->update([
                    'student_count' => $classSection->students()->count()
                ]);

                return redirect()->route('grading.system', [
                    'subject' => $subjectId,
                    'classSection' => $classSectionId,
                    'term' => 'midterm'
                ])->with('success', "Successfully enrolled {$successCount} students!");
            } else {
                return back()->withErrors($errors)->withInput();
            }

        } catch (\Exception $e) {
            return back()->withErrors(['excel_file' => 'Error reading file: ' . $e->getMessage()])->withInput();
        }
    }

    public function downloadTemplate($subjectId, $classSectionId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $subject = $classSection->subject;
        $teacher = $classSection->teacher;
        
        // Get current academic year and term
        $currentYear = date('Y');
        $nextYear = $currentYear + 1;
        $academicYear = "{$currentYear}-{$nextYear}";
        
        // Determine current term based on month
        $currentMonth = date('n');
        $term = '';
        if ($currentMonth >= 6 && $currentMonth <= 10) {
            $term = '1';
        } elseif ($currentMonth >= 11 || $currentMonth <= 3) {
            $term = '2';
        } else {
            $term = '3';
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="student_enrollment_template.csv"',
        ];

        $callback = function() use ($subject, $classSection, $teacher, $academicYear, $term) {
            $file = fopen('php://output', 'w');
            
            // Add header information (rows 1-5) - Dynamic based on actual data
            fputcsv($file, ['Eastern Visayas State University', '', '', '', '', '', '']);
            fputcsv($file, ['Tanauan Leyte', '', '', '', '', '', '']);
            fputcsv($file, ["Class List for {$subject->code} Section [{$classSection->section}]", '', '', '', '', '', '']);
            fputcsv($file, ["SY {$academicYear} Term: {$term}", '', '', '', '', '', '']);
            fputcsv($file, ['', '', '', '', '', '', '']);
            
            // Add column headers (row 6)
            fputcsv($file, ['Student ID', 'Fullname', 'Major', 'Year Level', 'Registered', 'Gender', 'Grade']);
            
            // Add example rows (starting from row 7)
            fputcsv($file, ['2019-35557', 'Carreon, Benjamin N.', 'BSIT', '3', '1', 'M', '1.9']);
            fputcsv($file, ['2019-35842', 'Baldoze, Nerissa G.', 'BSTI', '3', '1', 'F', '2.5']);
            fputcsv($file, ['2019-35527', 'Fernandez, Michell A.', 'BSIT', '3', '1', 'F', '1.7']);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function unenrollStudent(Request $request, $subjectId, $classSectionId, $studentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $student = Student::findOrFail($studentId);

        // Remove the student from this class section (unenroll)
        $classSection->students()->detach($student->id);

        // Update student count
        $classSection->update([
            'student_count' => $classSection->students()->count()
        ]);

        return back()->with('success', "Student '{$student->first_name} {$student->last_name}' has been unenrolled successfully.");
    }

    public function bulkUnenrollStudents(Request $request, $subjectId, $classSectionId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $classSection = ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subjectId)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,id'
        ]);

        $studentIds = $request->input('student_ids');
        $students = Student::whereIn('id', $studentIds)->get();

        $unenrolledCount = 0;
        foreach ($students as $student) {
            // Remove the student from this class section (unenroll)
            $classSection->students()->detach($student->id);
            $unenrolledCount++;
        }

        // Update student count
        $classSection->update([
            'student_count' => $classSection->students()->count()
        ]);

        return back()->with('success', "Successfully unenrolled {$unenrolledCount} students.");
    }

    public function convertExcelToCsv($file)
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = [];

            foreach ($sheet->toArray(null, true, true, true) as $row) {
                $rows[] = array_values($row); // reset keys to 0-based
            }

            return $rows;

        } catch (\Exception $e) {
            \Log::error('Error reading Excel file: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update a student's details in the grading system.
     */
    public function updateStudent(Request $request, $subjectId, $classSectionId, $term, $studentId)
    {
        if (!auth()->user()->isTeacher()) {
            abort(403, 'Access denied. Teachers only.');
        }

        $subjectModel = auth()->user()->subjects()->findOrFail($subjectId);
        $classSectionModel = \App\Models\ClassSection::where('id', $classSectionId)
            ->where('subject_id', $subjectModel->id)
            ->where('teacher_id', auth()->id())
            ->firstOrFail();

        $student = $classSectionModel->students()->where('students.id', $studentId)->firstOrFail();

        $validated = $request->validate([
            'student_id' => 'required|string|max:255|unique:students,student_id,' . $student->id,
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
        ]);

        $student->update($validated);

        return back()->with('success', 'Student updated successfully!');
    }

    /**
     * Parse full name from "LASTNAME, FIRSTNAME MIDDLENAME" format
     */
    private function parseFullName($fullName)
    {
        // Remove extra spaces and trim
        $fullName = trim(preg_replace('/\s+/', ' ', $fullName));
        
        // Check if name contains comma (LASTNAME, FIRSTNAME format)
        if (strpos($fullName, ',') !== false) {
            $parts = explode(',', $fullName, 2);
            $lastName = trim($parts[0]);
            $firstMiddle = trim($parts[1]);
            
            // Split first and middle names
            $nameParts = explode(' ', $firstMiddle, 2);
            $firstName = trim($nameParts[0]);
            $middleName = isset($nameParts[1]) ? trim($nameParts[1]) : null;
            
            return [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'middle_name' => $middleName
            ];
        } else {
            // No comma found, assume it's already in FIRSTNAME LASTNAME format
            $nameParts = explode(' ', $fullName, 3);
            
            if (count($nameParts) >= 3) {
                // FIRSTNAME MIDDLENAME LASTNAME
                return [
                    'first_name' => trim($nameParts[0]),
                    'middle_name' => trim($nameParts[1]),
                    'last_name' => trim($nameParts[2])
                ];
            } elseif (count($nameParts) == 2) {
                // FIRSTNAME LASTNAME
                return [
                    'first_name' => trim($nameParts[0]),
                    'last_name' => trim($nameParts[1]),
                    'middle_name' => null
                ];
            } else {
                // Single name
                return [
                    'first_name' => trim($nameParts[0]),
                    'last_name' => '',
                    'middle_name' => null
                ];
            }
        }
    }
} 