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
// Academic Year/Semester setter
Route::post('/set-ays', function (Request $request) {
    $validated = $request->validate([
        'academic_year' => ['required','string','regex:/^\d{4}-\d{4}$/'],
        'semester' => ['required','in:1,2,S'],
    ]);
    session([
        'academic_year' => $validated['academic_year'],
        'semester' => $validated['semester'],
    ]);
    return back();
})->middleware('auth');

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
    // Initialize default AY/Sem on first authenticated load
    if (!session()->has('academic_year') || !session()->has('semester')) {
        $now = now();
        $year = (int) $now->format('Y');
        $month = (int) $now->format('n');
        // Simple rule: AY starts in June. Adjust as needed.
        $ayStart = $month >= 6 ? $year : $year - 1;
        $defaultAy = $ayStart . '-' . ($ayStart + 1);
        session(['academic_year' => session('academic_year', $defaultAy)]);
        session(['semester' => session('semester', '1')]);
    }
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
    $subjects = auth()->user()->subjects()
        ->whereDoesntHave('gradingStructure', function($q) {
            $q->where('type', 'custom')
              ->where('midterm_weight', 100)
              ->where('final_weight', 0);
        })
        ->when(session('academic_year'), fn($q) => $q->where('academic_year', session('academic_year')))
        ->when(session('semester'), fn($q) => $q->where('semester', session('semester')))
        ->orderBy('code')
        ->get();
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
        'academic_year' => session('academic_year'),
        'semester' => session('semester'),
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

// All-in-one Subjects routes (single-term, teacher only)
Route::get('/subjects/all-in-one', function () {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $subjects = auth()->user()->subjects()
        ->whereHas('gradingStructure', function($q) {
            $q->where('type', 'custom')
              ->where('midterm_weight', 100)
              ->where('final_weight', 0);
        })
        ->when(session('academic_year'), fn($q) => $q->where('academic_year', session('academic_year')))
        ->when(session('semester'), fn($q) => $q->where('semester', session('semester')))
        ->orderBy('code')
        ->get();
    return view('teacher.subjects-allinone', compact('subjects'));
})->name('subjects.allinone.index')->middleware('auth');

Route::get('/subjects/all-in-one/create', function () {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    return view('teacher.subject-create-allinone');
})->name('subjects.allinone.create')->middleware('auth');

Route::post('/subjects/all-in-one', function (Request $request) {
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
        'assessment_types' => 'required|string',
    ]);

    // Create the subject
    $subject = auth()->user()->subjects()->create([
        'code' => $validated['code'],
        'title' => $validated['title'],
        'units' => $validated['units'],
        'teacher_id' => auth()->id(),
        'academic_year' => session('academic_year'),
        'semester' => session('semester'),
    ]);

    // Create grading structure as all-in-one (encoded as custom 100/0)
    $subject->gradingStructure()->create([
        'type' => 'custom',
        'midterm_weight' => 100,
        'final_weight' => 0,
    ]);

    // Create assessment types (overall only)
    $assessmentTypes = json_decode($validated['assessment_types'], true);
    $order = 0;
    if (is_array($assessmentTypes)) {
        foreach ($assessmentTypes as $type) {
            if (!empty($type['name']) && (float)($type['weight'] ?? 0) > 0) {
                $subject->assessmentTypes()->create([
                    'name' => $type['name'],
                    // For all-in-one, persist under 'midterm' to satisfy enum constraint
                    'term' => 'midterm',
                    'weight' => $type['weight'],
                    'order' => $order++,
                ]);
            }
        }
    }

    return redirect()->route('subjects.allinone.index')->with('success', 'All-in-one subject created successfully!');
})->name('subjects.allinone.store')->middleware('auth');

// Class Sections routes
Route::get('/subjects/{subject}/classes', function ($subjectId) {
    if (!auth()->user()->isTeacher()) {
        abort(403, 'Access denied. Teachers only.');
    }
    $subject = auth()->user()->subjects()->findOrFail($subjectId);
    $classes = \App\Models\ClassSection::where('subject_id', $subject->id)
        ->where('teacher_id', auth()->id())
        ->withCount('students')
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

// Quiz routes for assessments
Route::get('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}/quiz', [\App\Http\Controllers\AssessmentController::class, 'showQuizForm'])
    ->name('assessments.quiz.form')->middleware('auth');
Route::post('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}/quiz', [\App\Http\Controllers\AssessmentController::class, 'storeQuiz'])
    ->name('assessments.quiz.store')->middleware('auth');
Route::get('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}/quiz/tokens', [\App\Http\Controllers\AssessmentController::class, 'showQuizTokens'])
    ->name('assessments.quiz.tokens')->middleware('auth');
Route::get('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}/quiz/tokens/status', [\App\Http\Controllers\AssessmentController::class, 'getQuizTokensStatus'])
    ->name('assessments.quiz.tokens.status')->middleware('auth');
Route::post('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}/quiz/tokens/generate', [\App\Http\Controllers\AssessmentController::class, 'generateTokens'])
    ->name('assessments.quiz.tokens.generate')->middleware('auth');
Route::put('/tokens/{token}/regenerate', [\App\Http\Controllers\AssessmentController::class, 'regenerateToken'])
    ->name('tokens.regenerate')->middleware('auth');
Route::post('/subjects/{subject}/classes/{classSection}/{term}/assessments/{assessmentType}/{assessment}/quiz/reactivate', [\App\Http\Controllers\AssessmentController::class, 'reactivateQuiz'])
    ->name('assessments.quiz.reactivate')->middleware('auth');

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
            'assessments' => $assessmentType->assessments()
                ->where('term', 'midterm')
                ->where(function ($q) use ($classSection) {
                    $q->whereNull('class_section_id')
                      ->orWhere('class_section_id', $classSection->id);
                })
                ->orderBy('order')
                ->get()
        ];
    }
    
    foreach ($finalAssessmentTypes as $assessmentType) {
        $assessments['final'][$assessmentType->id] = [
            'type' => $assessmentType,
            'assessments' => $assessmentType->assessments()
                ->where('term', 'final')
                ->where(function ($q) use ($classSection) {
                    $q->whereNull('class_section_id')
                      ->orWhere('class_section_id', $classSection->id);
                })
                ->orderBy('order')
                ->get()
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
            $availableWeight = 0;
            
            foreach ($midtermAssessmentTypes as $assessmentType) {
                // Get assessments for this class (or shared) for this type
                $allAssessments = $assessmentType->assessments()
                    ->where('term', 'midterm')
                    ->where(function ($q) use ($classSection) {
                        $q->whereNull('class_section_id')
                          ->orWhere('class_section_id', $classSection->id);
                    })
                    ->orderBy('order')
                    ->get();
                
                if ($allAssessments->count() > 0) {
                    $assessmentScores = $student->assessmentScores()
                        ->whereHas('assessment', function($query) use ($assessmentType, $classSection) {
                            $query->where('assessment_type_id', $assessmentType->id)
                                  ->where(function ($q) use ($classSection) {
                                      $q->whereNull('class_section_id')
                                        ->orWhere('class_section_id', $classSection->id);
                                  });
                        })
                        ->with('assessment')
                        ->get();
                    
                    $typeGrades = [];
                    
                    foreach ($allAssessments as $assessment) {
                        $score = $assessmentScores->where('assessment_id', $assessment->id)->first();
                        if ($score && $score->score !== null) {
                            // Prefer scaled percentage_score if available (passing->75% rule)
                            $percent = $score->percentage_score !== null
                                ? (float)$score->percentage_score
                                : ($assessment->max_score > 0 ? ($score->score / $assessment->max_score) * 100 : 0);
                            $typeGrades[] = $percent;
                        }
                        // Skip unscored assessments for Estimated view
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
        
        // Calculate final grade
        if ($finalAssessmentTypes->count() > 0) {
            $finalGrades = [];
            $finalWeights = [];
            $availableWeight = 0;
            
            foreach ($finalAssessmentTypes as $assessmentType) {
                // Get assessments for this class (or shared) for this type
                $allAssessments = $assessmentType->assessments()
                    ->where('term', 'final')
                    ->where(function ($q) use ($classSection) {
                        $q->whereNull('class_section_id')
                          ->orWhere('class_section_id', $classSection->id);
                    })
                    ->orderBy('order')
                    ->get();
                
                if ($allAssessments->count() > 0) {
                    $assessmentScores = $student->assessmentScores()
                        ->whereHas('assessment', function($query) use ($assessmentType, $classSection) {
                            $query->where('assessment_type_id', $assessmentType->id)
                                  ->where(function ($q) use ($classSection) {
                                      $q->whereNull('class_section_id')
                                        ->orWhere('class_section_id', $classSection->id);
                                  });
                        })
                        ->with('assessment')
                        ->get();
                    
                    $typeGrades = [];
                    
                    foreach ($allAssessments as $assessment) {
                        $score = $assessmentScores->where('assessment_id', $assessment->id)->first();
                        if ($score && $score->score !== null) {
                            $percent = $score->percentage_score !== null
                                ? (float)$score->percentage_score
                                : ($assessment->max_score > 0 ? ($score->score / $assessment->max_score) * 100 : 0);
                            $typeGrades[] = $percent;
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
        
        // Calculate overall grade: normalize by active term weights (ignore missing term)
        if ($gradingStructure && ($student->midterm_grade !== null || $student->final_grade !== null)) {
            $midtermWeightPct = (float) $gradingStructure->midterm_weight;
            $finalWeightPct = (float) $gradingStructure->final_weight;

            $mid = $student->midterm_grade;
            $fin = $student->final_grade;

            $weightedSum = 0.0;
            $activeWeight = 0.0;
            if ($mid !== null) { $weightedSum += $mid * $midtermWeightPct; $activeWeight += $midtermWeightPct; }
            if ($fin !== null) { $weightedSum += $fin * $finalWeightPct; $activeWeight += $finalWeightPct; }

            $student->overall_grade = $activeWeight > 0 ? round($weightedSum / $activeWeight, 1) : null;
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

Route::get('/api/generate-qr', function (Illuminate\Http\Request $request) {
    $url = $request->query('url');
    $size = (int) $request->query('size', 180);
    if ($size < 100) { $size = 100; }
    if ($size > 1000) { $size = 1000; }
    return \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')->size($size)->generate($url);
});

Route::get('/subjects/{subject}/classes/{classSection}/analytics/{term}', [\App\Http\Controllers\StudentController::class, 'getAnalytics'])->name('class.analytics');
Route::get('/subjects/{subject}/classes/{classSection}/class-analytics/{term}', function ($subjectId, $classSectionId, $term) {
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
    
    // Calculate grades for each student using gradebook logic
    foreach ($students as $student) {
        $student->midterm_grade = null;
        $student->final_grade = null;
        $student->overall_grade = null;
        
        // Calculate midterm grade
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
        
        // Calculate final grade
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
        
        // Calculate overall grade using simple formula: midterm weight × midterm grade + final weight × final grade
        $midtermWeight = $gradingStructure ? ($gradingStructure->midterm_weight / 100) : 0.5;
        $finalWeight = $gradingStructure ? ($gradingStructure->final_weight / 100) : 0.5;
        
        // Use 0 if grade is null, otherwise use the actual grade
        $midtermGrade = $student->midterm_grade ?? 0;
        $finalGrade = $student->final_grade ?? 0;
        
        $student->overall_grade = round(
            ($midtermGrade * $midtermWeight) + 
            ($finalGrade * $finalWeight), 
            1
        );
        
        // Calculate estimated grade (inflated version) - simpler calculation that inflates scores
        $estimatedMidtermGrade = $student->midterm_grade ?? 0;
        $estimatedFinalGrade = $student->final_grade ?? 0;
        
        // Simple average of midterm and final (inflates the grade by not using weights)
        if ($estimatedMidtermGrade > 0 && $estimatedFinalGrade > 0) {
            $student->estimated_grade = round(($estimatedMidtermGrade + $estimatedFinalGrade) / 2, 1);
        } elseif ($estimatedMidtermGrade > 0) {
            $student->estimated_grade = $estimatedMidtermGrade;
        } elseif ($estimatedFinalGrade > 0) {
            $student->estimated_grade = $estimatedFinalGrade;
        } else {
            $student->estimated_grade = 0;
        }
        
        // Debug: Log both grade calculations
        error_log("Grade calculations for {$student->first_name} {$student->last_name}: Final={$student->overall_grade}, Estimated={$student->estimated_grade}");
    }
    
    // Get assessment types for the specific term (for display purposes)
    $assessmentTypes = $subject->assessmentTypes()->where('term', $term)->with(['assessments' => function($query) use ($term) {
        $query->where('term', $term);
    }, 'assessments.scores'])->orderBy('order')->get();
    
    // Create analytics data structure
    $analytics = [
        'student_rankings' => [],
        'grade_distribution' => [],
        'assessment_difficulty' => [],
        'performance_trends' => [],
        'risk_distribution' => [],
        'class_stats' => [],
        'student_metrics' => [],
        'student_assessment_scores' => [],
        'student_type_averages' => []
    ];
    
            // Calculate student rankings using accurate final grades from gradebook system
        $studentRankings = [];
        foreach ($students as $student) {
            // Use the accurate final grade (with proper weighting) for rankings
            $estimatedGrade = $student->overall_grade ?? 0;
        
        // Calculate real student metrics using StudentMetricsService
        $studentMetricsService = new \App\Services\StudentMetricsService();
        $realMetrics = $studentMetricsService->calculateStudentMetrics($student->id, $classSection->id, $term);
        
        $analytics['student_metrics'][$student->id] = [
            'avg_score_pct' => $estimatedGrade,
            'variation_score_pct' => $realMetrics['variation_score_pct'] ?? 0,
            'late_submission_pct' => $realMetrics['late_submission_pct'] ?? 0,
            'missed_submission_pct' => $realMetrics['missed_submission_pct'] ?? 0
        ];
        
        // Calculate real attendance percentage from attendance records
        $attendancePercentage = 0;
        $attendanceAssessments = $subject->assessmentTypes()
            ->where('name', 'Attendance')
            ->where('term', $term)
            ->with('assessments')
            ->get()
            ->flatMap(function($type) {
                return $type->assessments;
            });
            
        if ($attendanceAssessments->count() > 0) {
            $totalAttendanceDays = 0;
            $presentDays = 0;
            
            foreach ($attendanceAssessments as $assessment) {
                $attendanceRecords = $assessment->attendanceRecords()
                    ->where('student_id', $student->id)
                    ->get();
                    
                foreach ($attendanceRecords as $record) {
                    $totalAttendanceDays++;
                    if ($record->isPresent()) {
                        $presentDays++;
                    }
                }
            }
            
            if ($totalAttendanceDays > 0) {
                $attendancePercentage = round(($presentDays / $totalAttendanceDays) * 100, 1);
            }
        }
        
        // Build dynamic assessment averages for this term (exclude Attendance)
        $analytics['student_assessment_scores'][$student->id] = [
            'Attendance' => $attendancePercentage
        ];

        $termTypes = $subject->assessmentTypes()
            ->where('term', $term)
            ->with('assessments')
            ->orderBy('order')
            ->get();

        $dynamicTypeAverages = [];
        foreach ($termTypes as $assessmentType) {
            if (strtolower($assessmentType->name) === 'attendance') {
                continue;
            }
            $scorePercents = [];
            foreach ($assessmentType->assessments as $assessment) {
                $score = $assessment->scores()->where('student_id', $student->id)->first();
                if ($assessment->max_score > 0) {
                    if ($score && $score->score !== null) {
                        $percent = ($score->score / $assessment->max_score) * 100;
                        // Store per-assessment score for scatter plots (keyed by assessment name)
                        $analytics['student_assessment_scores'][$student->id][$assessment->name] = $percent;
                        $scorePercents[] = $percent;
                    } else {
                        // Explicitly record missing score for this assessment
                        $analytics['student_assessment_scores'][$student->id][$assessment->name] = null;
                    }
                }
            }
            if (count($scorePercents) > 0) {
                $avg = round(array_sum($scorePercents) / count($scorePercents), 1);
                $analytics['student_assessment_scores'][$student->id][$assessmentType->name] = $avg;
                $dynamicTypeAverages[$assessmentType->name] = $avg;
            }
        }

        // Store dynamic type averages
        $analytics['student_type_averages'][$student->id] = $dynamicTypeAverages;
        
        // Debug: Log real data being fetched
        $latePct = isset($realMetrics['late_submission_pct']) ? $realMetrics['late_submission_pct'] : 0;
        $missedPct = isset($realMetrics['missed_submission_pct']) ? $realMetrics['missed_submission_pct'] : 0;
        $typeLogParts = [];
        foreach ($analytics['student_type_averages'][$student->id] as $tName => $tAvg) {
            $typeLogParts[] = $tName . '=' . $tAvg . '%';
        }
        $typeLog = implode(', ', $typeLogParts);
        error_log("Real data for {$student->first_name} {$student->last_name}: Attendance={$attendancePercentage}%, {$typeLog}, Late={$latePct}%, Missed={$missedPct}%");
        
        $studentRankings[] = [
            'student' => $student,
            'estimated_grade' => $estimatedGrade,
            'midterm_grade' => $student->midterm_grade,
                            'final_grade' => $student->final_grade, // This is the actual final grade
            'risk_level' => $estimatedGrade >= 80 ? 'Low' : ($estimatedGrade >= 70 ? 'Medium' : 'High'),
            'risk_score' => $estimatedGrade
        ];
    }
    
    // Sort by estimated grade (descending) and assign ranks
    usort($studentRankings, function($a, $b) {
        return $b['estimated_grade'] <=> $a['estimated_grade'];
    });
    
    // Calculate gaps from the student above them
    foreach ($studentRankings as $index => &$ranking) {
        $ranking['rank'] = $index + 1;
        
        if ($index === 0) {
            // #1 student has no gap
            $ranking['gap'] = 0;
        } else {
            // Calculate gap from the student above them (negative because they need to gain this amount)
            $previousStudentGrade = $studentRankings[$index - 1]['estimated_grade'];
            $ranking['gap'] = -($previousStudentGrade - $ranking['estimated_grade']);
        }
    }
    
    $analytics['student_rankings'] = $studentRankings;
    
    // Calculate grade distribution using estimated grades
    $grades = array_column($studentRankings, 'estimated_grade');
    $analytics['grade_distribution'] = [
        'excellent' => count(array_filter($grades, fn($g) => $g >= 90)),
        'good' => count(array_filter($grades, fn($g) => $g >= 80 && $g < 90)),
        'satisfactory' => count(array_filter($grades, fn($g) => $g >= 70 && $g < 80)),
        'needs_improvement' => count(array_filter($grades, fn($g) => $g >= 60 && $g < 70)),
        'failing' => count(array_filter($grades, fn($g) => $g < 60))
    ];
    
    // Build assessment_difficulty list for the dropdown and scatter plots
    $assessmentDifficulty = [];
    foreach ($termTypes as $type) {
        if (strtolower($type->name) === 'attendance') {
            continue;
        }
        foreach ($type->assessments as $assessment) {
            // Collect percent scores for all students for this assessment
            $percents = [];
            foreach ($students as $stu) {
                $score = $assessment->scores->where('student_id', $stu->id)->first();
                if ($score && $score->score !== null && $assessment->max_score > 0) {
                    $percents[] = ($score->score / $assessment->max_score) * 100;
                }
            }
            if (count($percents) > 0) {
                $avg = array_sum($percents) / count($percents);
                $assessmentDifficulty[] = [
                    'name' => $assessment->name,
                    'type' => $type->name,
                    'average_score' => $avg,
                    'difficulty_level' => $avg >= 85 ? 'Easy' : ($avg >= 70 ? 'Medium' : 'Hard'),
                    'created_at' => optional($assessment->created_at)->toDateTimeString(),
                ];
            }
        }
    }
    // Sort chronologically to make the select stable
    usort($assessmentDifficulty, function($a, $b) {
        return strcmp((string)$a['created_at'], (string)$b['created_at']);
    });
    $analytics['assessment_difficulty'] = $assessmentDifficulty;
    
    // Calculate class statistics using estimated grades
    $analytics['class_stats'] = [
        'total_students' => count($students),
        'average_grade' => count($grades) > 0 ? array_sum($grades) / count($grades) : 0,
        'highest_grade' => count($grades) > 0 ? max($grades) : 0,
        'lowest_grade' => count($grades) > 0 ? min($grades) : 0,
        'passing_rate' => count(array_filter($grades, fn($g) => $g >= 70)) / count($grades) * 100
    ];
    
    return view('teacher.class-analytics', compact('students', 'subject', 'classSection', 'assessmentTypes', 'term', 'analytics'));
})->name('class.analytics.page')->middleware('auth');

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

// Subject Calendar Notes (per-teacher, per-subject)
Route::prefix('subjects/{subject}/calendar-notes')->middleware('auth')->group(function () {
    Route::get('/', [\App\Http\Controllers\SubjectCalendarNoteController::class, 'index'])->name('subject-notes.index');
    Route::get('{date}', [\App\Http\Controllers\SubjectCalendarNoteController::class, 'show'])->name('subject-notes.show');
    Route::post('/', [\App\Http\Controllers\SubjectCalendarNoteController::class, 'upsert'])->name('subject-notes.upsert');
    Route::delete('{date}', [\App\Http\Controllers\SubjectCalendarNoteController::class, 'destroy'])->name('subject-notes.destroy');
});

// Student assessment routes (no authentication required)
Route::prefix('assessment')->name('student.assessment.')->group(function () {
    Route::get('{unique_url}/access', [\App\Http\Controllers\StudentAssessmentController::class, 'showAccessForm'])->name('access');
    Route::post('{unique_url}/validate-token', [\App\Http\Controllers\StudentAssessmentController::class, 'validateToken'])->name('validate-token');
    Route::get('{unique_url}/take', [\App\Http\Controllers\StudentAssessmentController::class, 'takeAssessment'])->name('take');
    Route::post('{unique_url}/submit', [\App\Http\Controllers\StudentAssessmentController::class, 'submitAssessment'])->name('submit');
    Route::get('{unique_url}/result', [\App\Http\Controllers\StudentAssessmentController::class, 'showResult'])->name('result');
});
