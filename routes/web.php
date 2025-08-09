<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Subject;
use App\Models\Student;
use App\Http\Controllers\MLPredictionController;
use App\Http\Controllers\GoogleAuthController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return redirect('/login');
});

// Login page
Route::get('/login', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('welcome');
})->name('login')->middleware('guest');

// Login POST
Route::post('/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required'],
    ]);
    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard');
    }
    return back()->with('error', 'Invalid credentials.');
})->middleware('guest');

// Register page
Route::get('/register', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    return view('register');
})->name('register')->middleware('guest');

// Register POST
Route::post('/register', function (Request $request) {
    $validated = $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
        'user_type' => ['required', 'in:teacher,department_head'],
    ]);
    $user = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'user_type' => $validated['user_type'],
    ]);
    Auth::login($user);
    return redirect('/dashboard');
})->middleware('guest');

// Dashboard (protected)
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->isAdmin()) {
        return view('admin.dashboard');
    }
    if ($user->isTeacher()) {
        $subjects = $user->subjects()->with(['assessmentTypes.assessments'])->get();
        $subjectIds = $subjects->pluck('id');

        // Gather stats
        $totalStudents = \App\Models\Student::whereHas('classSections', function($q) use ($subjectIds) {
            $q->whereIn('subject_id', $subjectIds);
        })->distinct('id')->count('id');
        $totalSubjects = $subjects->count();
        $totalClassSections = \App\Models\ClassSection::whereIn('subject_id', $subjectIds)->count();

        // Group by assessment type
        $assessmentTypeStats = [];
        $assessmentTypes = \App\Models\AssessmentType::whereIn('subject_id', $subjectIds)->get();
        foreach ($assessmentTypes as $type) {
            $assessments = $type->assessments()->orderByDesc('created_at')->get();
            $assessmentTypeStats[$type->name] = [
                'total' => $assessments->count(),
                'latest' => $assessments->take(3),
            ];
        }

        // For dashboard cards, you can sum all assessments
        $totalAssessments = \App\Models\Assessment::whereHas('assessmentType', function($q) use ($subjectIds) {
            $q->whereIn('subject_id', $subjectIds);
        })->count();

        // You can also get the latest overall assessments (limit 3, most recent)
        $latestAssessments = \App\Models\Assessment::whereHas('assessmentType', function($q) use ($subjectIds) {
            $q->whereIn('subject_id', $subjectIds);
        })->orderByDesc('created_at')->limit(3)->get();

        // Get the latest 3 assessment types by most recent assessment
        $latestTypeIds = 
            \App\Models\Assessment::whereHas('assessmentType', function($q) use ($subjectIds) {
                $q->whereIn('subject_id', $subjectIds);
            })
            ->orderByDesc('created_at')
            ->get()
            ->pluck('assessment_type_id')
            ->unique()
            ->take(3)
            ->values();

        $latestTypeStats = [];
        $latestTypes = \App\Models\AssessmentType::whereIn('id', $latestTypeIds)->get();
        foreach ($latestTypes as $type) {
            $assessments = $type->assessments()->orderByDesc('created_at')->take(3)->get();
            $latestTypeStats[] = [
                'type' => $type,
                'assessments' => $assessments,
            ];
        }

        return view('teacher.dashboard', compact(
            'totalStudents',
            'totalSubjects',
            'totalClassSections',
            'assessmentTypeStats',
            'totalAssessments',
            'latestTypeStats',
            'latestAssessments'
        ));
    }
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Google OAuth Routes
Route::get('/auth/google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');

// Logout
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->middleware('auth');

// Subjects routes for teacher (only teachers can access)
Route::get('/subjects', function () {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $subjects = auth()->user()->subjects()->orderBy('code')->get();
    return view('teacher.subjects', compact('subjects'));
})->name('subjects.index')->middleware('auth');

Route::get('/subjects/create', function () {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    return view('teacher.subject-create');
})->name('subjects.create')->middleware('auth');

Route::put('/subjects/{id}', function (Request $request, $id) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    
    $subject = auth()->user()->subjects()->findOrFail($id);
    
    $validated = $request->validate([
        'code' => 'required|string|max:20', // Simplified validation rule
        'title' => 'required|string|max:255',
        'units' => 'required|numeric|min:0.5|max:6.0',
        'grading_type' => 'required|in:balanced,custom',
        'midterm_weight' => 'required_if:grading_type,custom|numeric|min:0|max:100',
        'final_weight' => 'required_if:grading_type,custom|numeric|min:0|max:100',
        'assessment_types' => 'required|string',
    ]);
    
    // Update the subject
    $subject->update([
        'code' => $validated['code'],
        'title' => $validated['title'],
        'units' => $validated['units'],
    ]);
    
    // Update grading structure
    $midtermWeight = $validated['grading_type'] === 'balanced' ? 50 : $validated['midterm_weight'];
    $finalWeight = $validated['grading_type'] === 'balanced' ? 50 : $validated['final_weight'];
    
    $subject->gradingStructure()->updateOrCreate(
        ['subject_id' => $subject->id],
        [
            'type' => $validated['grading_type'],
            'midterm_weight' => $midtermWeight,
            'final_weight' => $finalWeight,
        ]
    );
    
    // Delete existing assessment types
    $subject->assessmentTypes()->delete();
    
    // Create new assessment types
    $assessmentTypes = json_decode($validated['assessment_types'], true);
    $order = 0;
    
    foreach (['midterm', 'final'] as $term) {
        if (isset($assessmentTypes[$term])) {
            foreach ($assessmentTypes[$term] as $type) {
                if (!empty($type['name']) && $type['weight'] > 0) {
                    $subject->assessmentTypes()->create([
                        'name' => $type['name'],
                        'term' => $term,
                        'weight' => $type['weight'],
                        'order' => $order++,
                    ]);
                }
            }
        }
    }
    
    return redirect()->route('subjects.index')->with('success', 'Subject updated successfully with assessment types!');
})->name('subjects.update')->middleware('auth');

Route::post('/subjects', function (Request $request) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    
    $validated = $request->validate([
        'code' => [
            'required',
            'string',
            'max:20',
            function ($attribute, $value, $fail) {
                $exists = \App\Models\Subject::where('code', $value)
                    ->where('teacher_id', auth()->id())
                    ->exists();
                if ($exists) {
                    $fail('You already have a subject with this code.');
                }
            }
        ],
        'title' => 'required|string|max:255',
        'units' => 'required|numeric|min:0.5|max:6.0',
        'grading_type' => 'required|in:balanced,custom',
        'midterm_weight' => 'required_if:grading_type,custom|numeric|min:0|max:100',
        'final_weight' => 'required_if:grading_type,custom|numeric|min:0|max:100',
        'assessment_types' => 'required|string',
    ]);
    
    // Create the subject
    $subject = auth()->user()->subjects()->create([
        'code' => $validated['code'],
        'title' => $validated['title'],
        'units' => $validated['units'],
        'teacher_id' => auth()->id(),
    ]);
    
    // Create grading structure
    $midtermWeight = $validated['grading_type'] === 'balanced' ? 50 : $validated['midterm_weight'];
    $finalWeight = $validated['grading_type'] === 'balanced' ? 50 : $validated['final_weight'];
    
    $subject->gradingStructure()->create([
        'type' => $validated['grading_type'],
        'midterm_weight' => $midtermWeight,
        'final_weight' => $finalWeight,
    ]);
    
    // Create assessment types
    $assessmentTypes = json_decode($validated['assessment_types'], true);
    $order = 0;
    
    foreach (['midterm', 'final'] as $term) {
        if (isset($assessmentTypes[$term])) {
            foreach ($assessmentTypes[$term] as $type) {
                if (!empty($type['name']) && $type['weight'] > 0) {
                    $subject->assessmentTypes()->create([
                        'name' => $type['name'],
                        'term' => $term,
                        'weight' => $type['weight'],
                        'order' => $order++,
                    ]);
                }
            }
        }
    }
    
    return redirect()->route('subjects.index')->with('success', 'Subject created successfully with assessment types!');
})->name('subjects.store')->middleware('auth');

Route::get('/subjects/{id}/edit-data', function ($id) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    
    $subject = auth()->user()->subjects()->with(['gradingStructure', 'assessmentTypes'])->findOrFail($id);
    
    $data = [
        'grading_structure' => $subject->gradingStructure ? [
            'type' => $subject->gradingStructure->type,
            'midterm_weight' => $subject->gradingStructure->midterm_weight,
            'final_weight' => $subject->gradingStructure->final_weight,
        ] : null,
        'assessment_types' => [
            'midterm' => $subject->assessmentTypes()->where('term', 'midterm')->get(['name', 'weight'])->toArray(),
            'final' => $subject->assessmentTypes()->where('term', 'final')->get(['name', 'weight'])->toArray(),
        ]
    ];
    
    return response()->json($data);
})->name('subjects.edit-data')->middleware('auth');



Route::delete('/subjects/{id}', function ($id) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    
    $subject = auth()->user()->subjects()->findOrFail($id);
    $subject->delete();
    
    return redirect()->route('subjects.index')->with('success', 'Subject deleted successfully!');
})->name('subjects.destroy')->middleware('auth');

// Class Sections routes
Route::get('/subjects/{subject}/classes', function ($subjectId) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $subject = auth()->user()->subjects()->findOrFail($subjectId);
    $classes = \App\Models\ClassSection::where('subject_id', $subject->id)
        ->where('teacher_id', auth()->id())
        ->orderBy('section')
        ->get();
    return view('teacher.subject-classes', compact('subject', 'classes'));
})->name('subjects.classes')->middleware('auth');

// Class Sections CRUD routes (teacher only)
Route::post('/subjects/{subject}/classes', [\App\Http\Controllers\ClassSectionController::class, 'store'])
    ->name('classes.store')->middleware('auth');
Route::put('/subjects/{subject}/classes/{classSection}', [\App\Http\Controllers\ClassSectionController::class, 'update'])
    ->name('classes.update')->middleware('auth');
Route::delete('/subjects/{subject}/classes/{classSection}', [\App\Http\Controllers\ClassSectionController::class, 'destroy'])
    ->name('classes.destroy')->middleware('auth');

// Grading System routes
Route::get('/subjects/{subject}/classes/{classSection}/{term}/grading', function ($subject, $classSection, $term) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $subjectModel = auth()->user()->subjects()->findOrFail($subject);
    $classSectionModel = \App\Models\ClassSection::where('id', $classSection)
        ->where('subject_id', $subjectModel->id)
        ->where('teacher_id', auth()->id())
        ->firstOrFail();
    
    // Get enrolled students for this class section
    $enrolledStudents = $classSectionModel->students()->orderBy('last_name')->orderBy('first_name')->get();
    
    // Calculate grades and metrics for each student
    $studentGrades = [];
    $studentMetrics = [];
    $studentAbsences = [];
    $metricsService = new \App\Services\StudentMetricsService();
    
    // Check if this subject has attendance assessment
    $hasAttendance = $subjectModel->hasAttendanceAssessment();
    
    foreach ($enrolledStudents as $student) {
        // Get assessment types for this subject
        $midtermAssessmentTypes = $subjectModel->assessmentTypes()
            ->where('term', 'midterm')
            ->orderBy('order')
            ->get();
            
        $finalAssessmentTypes = $subjectModel->assessmentTypes()
            ->where('term', 'final')
            ->orderBy('order')
            ->get();
        
        // Calculate midterm grade using gradebook logic
        $midtermGrade = null;
        $midtermTotalWeight = 0;
        $midtermWeightedSum = 0;
        
        foreach ($midtermAssessmentTypes as $assessmentType) {
            $assessments = $assessmentType->assessments;
            $assessmentScores = collect();
            
            foreach ($assessments as $assessment) {
                $score = $assessment->scores()->where('student_id', $student->id)->first();
                if ($score && $score->score !== null) {
                    // Calculate percentage based on max_score
                    $percentage = ($score->score / $assessment->max_score) * 100;
                    $assessmentScores->push($percentage);
                }
            }
            
            if ($assessmentScores->count() > 0) {
                $assessmentTypeAverage = $assessmentScores->avg();
                $midtermWeightedSum += ($assessmentTypeAverage * $assessmentType->weight);
                $midtermTotalWeight += $assessmentType->weight;
            }
        }
        
        if ($midtermTotalWeight > 0) {
            $midtermGrade = $midtermWeightedSum / $midtermTotalWeight;
        }
        
        // Calculate final grade using gradebook logic
        $finalGrade = null;
        $finalTotalWeight = 0;
        $finalWeightedSum = 0;
        
        foreach ($finalAssessmentTypes as $assessmentType) {
            $assessments = $assessmentType->assessments;
            $assessmentScores = collect();
            
            foreach ($assessments as $assessment) {
                $score = $assessment->scores()->where('student_id', $student->id)->first();
                if ($score && $score->score !== null) {
                    // Calculate percentage based on max_score
                    $percentage = ($score->score / $assessment->max_score) * 100;
                    $assessmentScores->push($percentage);
                }
            }
            
            if ($assessmentScores->count() > 0) {
                $assessmentTypeAverage = $assessmentScores->avg();
                $finalWeightedSum += ($assessmentTypeAverage * $assessmentType->weight);
                $finalTotalWeight += $assessmentType->weight;
            }
        }
        
        if ($finalTotalWeight > 0) {
            $finalGrade = $finalWeightedSum / $finalTotalWeight;
        }
        
        // Calculate overall grade (average of midterm and final)
        $overallGrade = null;
        $gradeCount = 0;
        $gradeSum = 0;
        
        if ($midtermGrade !== null) {
            $gradeSum += $midtermGrade;
            $gradeCount++;
        }
        if ($finalGrade !== null) {
            $gradeSum += $finalGrade;
            $gradeCount++;
        }
        
        if ($gradeCount > 0) {
            $overallGrade = $gradeSum / $gradeCount;
        }
        
        $studentGrades[$student->id] = [
            'midterm' => $midtermGrade,
            'final' => $finalGrade,
            'overall' => $overallGrade
        ];
        
        // Calculate ML metrics for this student (filtered by current term)
        $metrics = $metricsService->calculateStudentMetrics($student->id, $classSectionModel->id, $term);
        $studentMetrics[$student->id] = $metrics;
        
        // Calculate absence data if subject has attendance
        if ($hasAttendance) {
            $absenceData = $subjectModel->getStudentTotalAbsences($student->id, $term);
            $studentAbsences[$student->id] = $absenceData;
        }
    }
    
    return view('teacher.grading-system', compact('classSectionModel', 'enrolledStudents', 'term', 'studentGrades', 'studentMetrics', 'studentAbsences', 'hasAttendance'));
})->name('grading.system')->middleware('auth');

Route::post('/subjects/{subject}/classes/{classSection}/{term}/grading', function ($subject, $classSection, $term, Request $request) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    
    $request->validate([
        'student_id' => 'required|string|max:255',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'nullable|email|max:255',
    ]);
    
    $subjectModel = auth()->user()->subjects()->findOrFail($subject);
    $classSectionModel = \App\Models\ClassSection::where('id', $classSection)
        ->where('subject_id', $subjectModel->id)
        ->where('teacher_id', auth()->id())
        ->firstOrFail();
    
    // Check if student already exists in this class section
    $existingEnrollment = $classSectionModel->students()
        ->where('students.student_id', $request->student_id)
        ->first();
    
    if ($existingEnrollment) {
        return back()->with('error', 'Student is already enrolled in this class section.');
    }
    
    // Check if student exists in the system
    $existingStudent = Student::where('student_id', $request->student_id)->first();
    
    if ($existingStudent) {
        // Student exists, just enroll them in this class
        $student = $existingStudent;
    } else {
        // Create new student
        $student = Student::create([
            'student_id' => $request->student_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ]);
    }
    
    // Enroll them in the class section using the pivot table
    $classSectionModel->students()->attach($student->id, [
        'enrollment_date' => now(),
        'status' => 'enrolled'
    ]);
    
    return back()->with('success', 'Student enrolled successfully!');
})->name('grading.enroll-student')->middleware('auth');

Route::put('/subjects/{subject}/classes/{classSection}/{term}/grading/{student}', [\App\Http\Controllers\BatchEnrollmentController::class, 'updateStudent'])
    ->name('grading.update-student')->middleware('auth');

// Enroll existing students (multiple selection)
Route::post('/subjects/{subject}/classes/{classSection}/{term}/grading/enroll-existing', function ($subject, $classSection, $term, Request $request) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    
    $request->validate([
        'student_ids' => 'required|array',
        'student_ids.*' => 'exists:students,id'
    ]);
    
    $subjectModel = auth()->user()->subjects()->findOrFail($subject);
    $classSectionModel = \App\Models\ClassSection::where('id', $classSection)
        ->where('subject_id', $subjectModel->id)
        ->where('teacher_id', auth()->id())
        ->firstOrFail();
    
    $enrolledCount = 0;
    $errors = [];
    
    foreach ($request->student_ids as $studentId) {
        // Check if student is already enrolled
        $existingEnrollment = $classSectionModel->students()
            ->where('students.id', $studentId)
            ->first();
        
        if ($existingEnrollment) {
            $errors[] = "Student is already enrolled in this class.";
            continue;
        }
        
        // Enroll the student
        $classSectionModel->students()->attach($studentId, [
            'enrollment_date' => now(),
            'status' => 'enrolled'
        ]);
        
        $enrolledCount++;
    }
    
    if ($enrolledCount > 0) {
        $message = "Successfully enrolled {$enrolledCount} student(s)!";
        if (count($errors) > 0) {
            $message .= " Some students were already enrolled.";
        }
        return back()->with('success', $message);
    } else {
        return back()->with('error', 'No students were enrolled. ' . implode(' ', $errors));
    }
})->name('grading.enroll-existing-students')->middleware('auth');

// Assessment routes
Route::get('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}', [\App\Http\Controllers\AssessmentController::class, 'index'])
    ->name('assessments.index')->middleware('auth');
Route::post('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}', [\App\Http\Controllers\AssessmentController::class, 'store'])
    ->name('assessments.store')->middleware('auth');
Route::put('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}', [\App\Http\Controllers\AssessmentController::class, 'update'])
    ->name('assessments.update')->middleware('auth');
Route::delete('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}', [\App\Http\Controllers\AssessmentController::class, 'destroy'])
    ->name('assessments.destroy')->middleware('auth');
Route::get('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}/scores', [\App\Http\Controllers\AssessmentController::class, 'scores'])
    ->name('assessments.scores')->middleware('auth');
Route::post('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}/scores', [\App\Http\Controllers\AssessmentController::class, 'saveScores'])
    ->name('assessments.scores.save')->middleware('auth');

// Batch Enrollment routes
Route::get('/subjects/{subject}/classes/{classSection}/batch-enrollment', [App\Http\Controllers\BatchEnrollmentController::class, 'showUploadForm'])
    ->name('batch-enrollment.form')
    ->middleware('auth');

Route::post('/subjects/{subject}/classes/{classSection}/batch-enrollment', [App\Http\Controllers\BatchEnrollmentController::class, 'uploadStudents'])
    ->name('batch-enrollment.upload')
    ->middleware('auth');

Route::get('/subjects/{subject}/classes/{classSection}/batch-enrollment/template', [App\Http\Controllers\BatchEnrollmentController::class, 'downloadTemplate'])
    ->name('batch-enrollment.template')
    ->middleware('auth');

Route::delete('/subjects/{subject}/classes/{classSection}/students/{student}/unenroll', [App\Http\Controllers\BatchEnrollmentController::class, 'unenrollStudent'])
    ->name('batch-enrollment.unenroll')
    ->middleware('auth');

Route::post('/subjects/{subject}/classes/{classSection}/bulk-unenroll', [App\Http\Controllers\BatchEnrollmentController::class, 'bulkUnenrollStudents'])
    ->name('batch-enrollment.bulk-unenroll')
    ->middleware('auth');

// Gradebook routes
Route::get('/subjects/{subject}/classes/{classSection}/gradebook', function ($subjectId, $classSectionId) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    
    $subject = auth()->user()->subjects()->findOrFail($subjectId);
    $classSection = \App\Models\ClassSection::where('id', $classSectionId)
        ->where('subject_id', $subject->id)
        ->where('teacher_id', auth()->id())
        ->firstOrFail();
    
    // Get grading structure (weights for midterm and final)
    $gradingStructure = $subject->gradingStructure;
    
    // Fetch all assessment types for both terms
    $midtermAssessmentTypes = $subject->assessmentTypes()->where('term', 'midterm')->orderBy('order')->get();
    $finalAssessmentTypes = $subject->assessmentTypes()->where('term', 'final')->orderBy('order')->get();
    
    // Fetch assessments for both terms
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
    
    // Fetch students
    $students = $classSection->students()->orderBy('last_name')->orderBy('first_name')->get();
    
    // Calculate grades for each student
    foreach ($students as $student) {
        $student->midterm_grade = null;
        $student->final_grade = null;
        $student->overall_grade = null;
        
        // Calculate midterm grade
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
            
            // Compute weighted average for midterm
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
        
        // Calculate final grade
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
            
            // Compute weighted average for final
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
        
        // Calculate overall grade using subject weights
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
    
    return view('teacher.gradebook', compact(
        'classSection', 
        'gradingStructure', 
        'midtermAssessmentTypes', 
        'finalAssessmentTypes', 
        'students', 
        'assessments'
    ));
})->name('gradebook.all')->middleware('auth');

Route::get('/subjects/{subject}/classes/{classSection}/gradebook/export', [\App\Http\Controllers\GradebookExportController::class, 'export'])
    ->name('gradebook.export')
    ->middleware('auth');

// Grading system routes
Route::post('/grading/test', [\App\Http\Controllers\GradingController::class, 'test'])
    ->name('grading.test')
    ->middleware('auth');

Route::post('/grading/calculate', [\App\Http\Controllers\GradingController::class, 'calculateGrade'])
    ->name('grading.calculate')
    ->middleware('auth');

Route::get('/grading/params', [\App\Http\Controllers\GradingController::class, 'getDefaultParams'])
    ->name('grading.params')
    ->middleware('auth');

Route::post('/subjects/{subject}/classes/{classSection}/grading/settings', [\App\Http\Controllers\GradingController::class, 'saveSettings'])
    ->name('grading.settings.save')
    ->middleware('auth');

Route::get('/subjects/{subject}/classes/{classSection}/grading/settings', [\App\Http\Controllers\GradingController::class, 'getSettings'])
    ->name('grading.settings.get')
    ->middleware('auth');

Route::post('/subjects/{subject}/classes/{classSection}/grading/calculate', [\App\Http\Controllers\GradingController::class, 'calculateClassGrades'])
    ->name('grading.class.calculate')
    ->middleware('auth');

Route::get('/subjects/{subject}/classes/{classSection}/students/{student}/analysis/{term}', [\App\Http\Controllers\StudentController::class, 'showAnalysis'])->name('students.analysis');

// Students index for teachers
Route::get('/students', function () {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $students = \App\Models\Student::orderBy('last_name')->paginate(15);
    return view('teacher.students.index', compact('students'));
})->name('students.index')->middleware('auth');

// Add student (for teachers)
Route::post('/students', function (\Illuminate\Http\Request $request) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $validated = $request->validate([
        'student_id' => 'required|string|max:255|unique:students,student_id',
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'birth_date' => 'nullable|date',
        'gender' => 'nullable|string|max:20',
        'contact_number' => 'nullable|string|max:50',
        'address' => 'nullable|string|max:255',
    ]);
    \App\Models\Student::create($validated);
    return redirect()->route('students.index')->with('success', 'Student added successfully!');
})->name('students.store')->middleware('auth');

// Update student (for teachers)
Route::put('/students/{student}', function (\Illuminate\Http\Request $request, $studentId) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $student = \App\Models\Student::findOrFail($studentId);
    $validated = $request->validate([
        'student_id' => 'required|string|max:255|unique:students,student_id,' . $student->id,
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'middle_name' => 'nullable|string|max:255',
        'email' => 'nullable|email|max:255',
        'birth_date' => 'nullable|date',
        'gender' => 'nullable|string|max:20',
        'contact_number' => 'nullable|string|max:50',
        'address' => 'nullable|string|max:255',
    ]);
    $student->update($validated);
    return redirect()->route('students.index')->with('success', 'Student updated successfully!');
})->name('students.update')->middleware('auth');

// Student profile page for teachers
Route::get('/students/{student}', function ($studentId) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $student = \App\Models\Student::findOrFail($studentId);
    
    // Get enrolled classes for this student
    $enrolledClasses = $student->classSections()->with(['subject', 'teacher'])->get();
    
    // Calculate academic data for each class
    $academicData = [];
    foreach ($enrolledClasses as $classSection) {
        $midtermAssessments = $classSection->subject->assessmentTypes()
            ->where('term', 'midterm')
            ->with(['assessments.scores' => function($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->get();
            
        $finalAssessments = $classSection->subject->assessmentTypes()
            ->where('term', 'final')
            ->with(['assessments.scores' => function($query) use ($student) {
                $query->where('student_id', $student->id);
            }])
            ->get();
        
        // Calculate midterm performance
        $midtermScores = collect();
        $midtermCount = 0;
        foreach ($midtermAssessments as $assessmentType) {
            $midtermCount += $assessmentType->assessments->count();
            foreach ($assessmentType->assessments as $assessment) {
                $score = $assessment->scores->where('student_id', $student->id)->first();
                if ($score && $score->score !== null) {
                    // Calculate percentage based on max_score
                    $percentage = ($score->score / $assessment->max_score) * 100;
                    $midtermScores->push($percentage);
                }
            }
        }
        
        // Calculate final performance
        $finalScores = collect();
        $finalCount = 0;
        foreach ($finalAssessments as $assessmentType) {
            $finalCount += $assessmentType->assessments->count();
            foreach ($assessmentType->assessments as $assessment) {
                $score = $assessment->scores->where('student_id', $student->id)->first();
                if ($score && $score->score !== null) {
                    // Calculate percentage based on max_score
                    $percentage = ($score->score / $assessment->max_score) * 100;
                    $finalScores->push($percentage);
                }
            }
        }
        
        // Calculate totals
        $allScores = $midtermScores->merge($finalScores);
        $totalCount = $midtermCount + $finalCount;
        $completedCount = $allScores->count();
        
        $academicData[$classSection->id] = [
            'midterm' => [
                'average' => $midtermScores->count() > 0 ? $midtermScores->avg() : null,
                'count' => $midtermCount,
                'completed' => $midtermScores->count(),
                'scores' => $midtermScores
            ],
            'final' => [
                'average' => $finalScores->count() > 0 ? $finalScores->avg() : null,
                'count' => $finalCount,
                'completed' => $finalScores->count(),
                'scores' => $finalScores
            ],
            'total' => [
                'average' => $allScores->count() > 0 ? $allScores->avg() : null,
                'count' => $totalCount,
                'scores' => $allScores
            ],
            'completed' => $completedCount
        ];
    }
    
    return view('teacher.students.show', compact('student', 'enrolledClasses', 'academicData'));
})->name('students.show')->middleware('auth');

// Delete student (for teachers)
Route::delete('/students/{student}', function ($studentId) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $student = \App\Models\Student::findOrFail($studentId);
    $student->delete();
    return redirect()->route('students.index')->with('success', 'Student deleted successfully!');
})->name('students.destroy')->middleware('auth');

// API: Get class sections for a subject and current teacher
Route::get('/api/subjects/{subject}/classes', function ($subjectId) {
    $user = auth()->user();
    if (!$user->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $classSections = \App\Models\ClassSection::where('subject_id', $subjectId)
        ->where('teacher_id', $user->id)
        ->get(['id', 'section', 'schedule', 'student_count']);
    return response()->json($classSections);
})->middleware('auth');

// ML Prediction Routes
Route::prefix('api/ml')->middleware('auth')->group(function () {
    Route::post('/predict/student', [MLPredictionController::class, 'getStudentRiskPredictions'])->name('ml.predict.student');
    Route::post('/predict/bulk', [MLPredictionController::class, 'getBulkRiskPredictions'])->name('ml.predict.bulk');
    Route::get('/health', [MLPredictionController::class, 'healthCheck'])->name('ml.health');
    Route::get('/info', [MLPredictionController::class, 'getApiInfo'])->name('ml.info');
    Route::get('/metrics/{studentId}/{classSectionId}/{term?}', [MLPredictionController::class, 'getStudentMetrics'])->name('ml.metrics');
    Route::get('/predict/student/{studentId}/{classSectionId}/{term}', [MLPredictionController::class, 'getStudentRiskPredictionsByTerm'])->name('ml.predict.student.term');
});

Route::get('/subjects/{subject}/classes/{classSection}/analytics/{term}', [\App\Http\Controllers\StudentController::class, 'getAnalytics'])->name('class.analytics');

// Annotation Routes
Route::prefix('api/annotations')->middleware('auth')->group(function () {
    Route::post('/', [\App\Http\Controllers\AnnotationController::class, 'store'])->name('annotations.store');
    Route::get('/student/{studentId}', [\App\Http\Controllers\AnnotationController::class, 'index'])->name('annotations.index');
    Route::delete('/{id}', [\App\Http\Controllers\AnnotationController::class, 'destroy'])->name('annotations.destroy');
});

// Attendance Routes
Route::prefix('subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}/attendance')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/', [\App\Http\Controllers\AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/data', [\App\Http\Controllers\AttendanceController::class, 'getAttendanceData'])->name('attendance.data');
    Route::delete('/date', [\App\Http\Controllers\AttendanceController::class, 'deleteDate'])->name('attendance.delete-date');
});
