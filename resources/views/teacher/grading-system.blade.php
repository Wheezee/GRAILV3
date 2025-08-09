@extends('layouts.app')

<style>
.success-checkmark {
  width: 24px;
  height: 24px;
  position: relative;
  display: inline-block;
  vertical-align: top;
}

.success-checkmark .check-icon {
  width: 24px;
  height: 24px;
  position: relative;
  border-radius: 50%;
  border: 2px solid #4ade80;
  background: white;
  animation: scale 0.3s ease-in-out 0.9s both;
}

.success-checkmark .check-icon::before {
  content: '';
  position: absolute;
  top: 3px;
  left: 7px;
  width: 6px;
  height: 10px;
  border: solid #4ade80;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
  animation: check 0.6s ease-in-out 0.9s forwards;
  opacity: 0;
}

@keyframes scale {
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

@keyframes check {
  0% {
    opacity: 0;
    transform: rotate(45deg) scale(0.8);
  }
  50% {
    opacity: 1;
    transform: rotate(45deg) scale(1.2);
  }
  100% {
    opacity: 1;
    transform: rotate(45deg) scale(1);
  }
}

/* Dark mode support */
.dark .success-checkmark .check-icon {
  border-color: #22c55e;
  background: #1f2937;
}

.dark .success-checkmark .check-icon::before {
  border-color: #22c55e;
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  padding: 1rem;
}

.modal-overlay.show {
  opacity: 1;
  visibility: visible;
}

.modal-content {
  background: white;
  border-radius: 0.75rem;
  width: 100%;
  max-width: 64rem;
  max-height: 90vh;
  overflow-y: auto;
  transform: scale(0.95);
  transition: transform 0.3s ease;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.dark .modal-content {
  background: #1f2937;
  border: 1px solid #374151;
}

.modal-overlay.show .modal-content {
  transform: scale(1);
}

/* Multi-range slider styles */
.slider-container {
  position: relative;
  width: 100%;
  height: 20px;
  background: #e5e7eb;
  border-radius: 10px;
  margin-bottom: 20px;
  overflow: visible;
}

.segment {
  position: absolute;
  top: 0;
  height: 100%;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.handle {
  position: absolute;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  cursor: pointer;
  top: 50%;
  transform: translate(-50%, -50%);
  border: 3px solid white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  z-index: 10;
  transition: all 0.2s ease;
}

.handle:hover {
  transform: translate(-50%, -50%) scale(1.1);
}

.handle.active {
  transform: translate(-50%, -50%) scale(1.2);
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

/* Assessment type colors */
.assessment-blue { background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%); }
.assessment-green { background: linear-gradient(90deg, #10b981 0%, #059669 100%); }
.assessment-purple { background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 100%); }
.assessment-orange { background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); }
.assessment-pink { background: linear-gradient(90deg, #ec4899 0%, #db2777 100%); }
.assessment-indigo { background: linear-gradient(90deg, #6366f1 0%, #4f46e5 100%); }
.assessment-teal { background: linear-gradient(90deg, #14b8a6 0%, #0d9488 100%); }
.assessment-red { background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%); }

/* Dark mode support */
.dark .slider-container {
  background: #374151;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .modal-content {
    max-width: 95vw;
    margin: 0.5rem;
  }
  
  .modal-overlay {
    padding: 0.5rem;
  }
}
</style>

@section('content')
<!-- Breadcrumbs -->
<nav class="mb-6" aria-label="Breadcrumb">
  <ol class="flex flex-wrap items-center gap-1 sm:gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
    <li class="flex items-center">
      <a href="{{ route('dashboard') }}" class="hover:text-red-600 dark:hover:text-red-400 transition-colors whitespace-nowrap">
        Home
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <a href="{{ route('subjects.index') }}" class="hover:text-red-600 dark:hover:text-red-400 transition-colors whitespace-nowrap">
        Subjects
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <a href="{{ route('subjects.classes', $classSectionModel->subject->id) }}" class="hover:text-red-600 dark:hover:text-red-400 max-w-[120px] sm:max-w-none truncate">
        {{ $classSectionModel->subject->code }} - {{ $classSectionModel->subject->title }}
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">{{ $classSectionModel->section }}</span>
    </li>
  </ol>
</nav>

<!-- Term Switcher Tabs -->
<div class="mb-6 flex gap-2">
    @php
        $termTabs = [
            'midterm' => 'Midterm',
            'final' => 'Final',
        ];
        $currentRoute = Route::currentRouteName();
        $routeParams = array_merge(request()->route()->parameters(), []);
    @endphp
    @foreach ($termTabs as $slug => $label)
        @php
            $params = array_merge($routeParams, ['term' => $slug]);
        @endphp
        <a href="{{ route($currentRoute, $params) }}"
           class="px-4 py-2 rounded-t-lg font-semibold transition-colors duration-150
                  {{ $term === $slug ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-red-600 hover:text-white' }}">
            {{ $label }}
        </a>
    @endforeach
</div>

@if (session('success'))
  <div id="successMessage" class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg transform transition-all duration-500 ease-out">
    <div class="flex items-center gap-3">
      <div class="success-checkmark">
        <div class="check-icon"></div>
      </div>
      <p class="text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</p>
    </div>
  </div>
@endif

@if (session('error'))
  <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
    <div class="flex items-center gap-3">
      <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>
      <p class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</p>
    </div>
  </div>
@endif

@if ($errors->any())
  <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
    <div class="flex items-start gap-3">
      <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5"></i>
      <div>
        <p class="text-red-800 dark:text-red-200 font-medium mb-1">Please fix the following errors:</p>
        <ul class="text-red-700 dark:text-red-300 text-sm space-y-1">
          @foreach ($errors->all() as $error)
            <li>• {{ $error }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
@endif

<!-- Header Section -->
<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between mb-8 gap-4">
  <div class="flex-shrink-0">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $classSectionModel->section }}</h2>
    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-1">{{ $classSectionModel->subject->code }} - {{ $classSectionModel->subject->title }}</p>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex lg:flex-wrap lg:justify-end gap-2 w-full lg:w-auto">
    <button onclick="openEditSubjectModal()" class="inline-flex items-center justify-center gap-1 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="edit" class="w-4 h-4 sm:w-5 sm:h-5"></i>
      <span class="hidden sm:inline">Edit Subject</span>
      <span class="sm:hidden">Edit</span>
    </button>
    <a href="{{ route('batch-enrollment.form', ['subject' => $classSectionModel->subject->id, 'classSection' => $classSectionModel->id]) }}" class="inline-flex items-center justify-center gap-1 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2.5 bg-green-600 hover:bg-green-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="upload" class="w-4 h-4 sm:w-5 sm:h-5"></i>
      <span class="hidden sm:inline">Batch Enroll</span>
      <span class="sm:hidden">Enroll</span>
    </a>
    <a href="{{ route('gradebook.all', ['subject' => $classSectionModel->subject->id, 'classSection' => $classSectionModel->id]) }}" class="inline-flex items-center justify-center gap-1 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2.5 bg-red-600 hover:bg-red-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="clipboard-list" class="w-4 h-4 sm:w-5 sm:h-5"></i>
      <span class="hidden sm:inline">Gradebook (All)</span>
      <span class="sm:hidden">Grades</span>
    </a>
    @php
      // Find the attendance assessment type for this term
      $attendanceType = $classSectionModel->subject->assessmentTypes()
          ->where('term', $term)
          ->where('name', 'Attendance')
          ->first();
      
      // Get the first attendance assessment if it exists
      $attendanceAssessment = $attendanceType ? $attendanceType->assessments()->where('term', $term)->first() : null;
      
      // Auto-create attendance assessment if it doesn't exist
      if ($attendanceType && !$attendanceAssessment) {
          $attendanceAssessment = $attendanceType->assessments()->create([
              'name' => 'Attendance',
              'max_score' => 100, // Will be calculated based on actual attendance days
              'passing_score' => 75, // 75% attendance is considered passing
              'warning_score' => 85, // 85% attendance triggers warning
              'due_date' => null,
              'description' => 'Attendance tracking for ' . ucfirst($term) . ' term',
              'order' => 1,
              
              'term' => $term,
          ]);
      }
    @endphp
    
    @if($attendanceType && $attendanceAssessment)
      <a href="{{ route('attendance.index', ['subject' => $classSectionModel->subject->id, 'classSection' => $classSectionModel->id, 'term' => $term, 'assessmentType' => $attendanceType->id, 'assessment' => $attendanceAssessment->id]) }}" class="inline-flex items-center justify-center gap-1 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
        <i data-lucide="calendar" class="w-4 h-4 sm:w-5 sm:h-5"></i>
        <span class="hidden sm:inline">Attendance</span>
        <span class="sm:hidden">Attend</span>
      </a>
    @endif
    <button id="showAnalyticsBtn" onclick="openAnalyticsModal()" class="inline-flex items-center justify-center gap-1 sm:gap-2 px-2 sm:px-4 py-2 sm:py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs sm:text-sm font-semibold rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="bar-chart-2" class="w-4 h-4 sm:w-5 sm:h-5"></i>
      <span class="hidden sm:inline">Analytics</span>
      <span class="sm:hidden">Stats</span>
    </button>
  </div>
</div>



<!-- Grading Categories Section -->
<div class="mb-8">
  <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Assessment Categories</h3>
  
  @php
    $assessmentTypes = $classSectionModel->subject->assessmentTypes()->where('term', $term)->orderBy('order')->get();
    $colors = ['#3B82F6', '#10B981', '#8B5CF6', '#F59E0B', '#EF4444', '#06B6D4', '#84CC16', '#F97316'];
  @endphp
  
  @if($assessmentTypes->count() > 0)
    <div class="grid gap-4 w-full" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));">
      @foreach($assessmentTypes as $index => $assessmentType)
        @if($assessmentType->name === 'Attendance')
          @continue
        @endif
        @php
          $colorIndex = $index % count($colors);
          $color = $colors[$colorIndex];
        @endphp
        <div class="group cursor-pointer w-full" onclick="window.location.href='{{ route('assessments.index', ['subject' => $classSectionModel->subject->id, 'classSection' => $classSectionModel->id, 'term' => $term, 'assessmentType' => $assessmentType->id]) }}'">
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 sm:p-6 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200 w-full h-full">
            <div class="text-center">
              <div class="text-2xl sm:text-3xl mb-2 sm:mb-3" style="color: {{ $color }}">📊</div>
              <h4 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1 sm:mb-2 line-clamp-2">{{ $assessmentType->name }}</h4>
              <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">Weight: {{ $assessmentType->weight }}%</p>
              <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">No. of Items: {{ $assessmentType->assessments()->where('term', $term)->count() }}</p>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="text-center py-12">
      <div class="text-gray-400 dark:text-gray-500 mb-4">
        <i data-lucide="clipboard-list" class="w-16 h-16 mx-auto"></i>
      </div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No assessment types found</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6">Assessment types need to be configured for this subject</p>
      <a href="{{ route('subjects.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
        <i data-lucide="settings" class="w-4 h-4"></i>
        Configure Subject
      </a>
    </div>
  @endif
</div>

<!-- Enrolled Students Section -->
<div class="mb-8">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-4">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Enrolled Students</h3>
    <div class="flex items-center gap-3">
      @if($enrolledStudents->count() > 0)
        <button id="bulkUnenrollBtn" class="hidden px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
          <i data-lucide="user-minus" class="w-4 h-4 inline mr-1"></i>
          Unenroll Selected
        </button>
      @endif
      <button onclick="openEnrollStudentModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Enroll Student
      </button>
    </div>
  </div>

<!-- Filter and Sort Controls -->
<div class="flex flex-wrap items-center justify-between mb-4 gap-2">
  <div>
    <label for="riskFilter" class="mr-2 font-medium text-sm text-gray-700 dark:text-gray-300">Filter by Risk:</label>
    <select id="riskFilter" class="border border-gray-300 dark:border-gray-600 rounded px-2 py-1 text-sm bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100">
      <option value="all">Show All</option>
      <option value="high">High Risk</option>
      <option value="low">Low Risk</option>
      <option value="safe">Safe</option>
    </select>
  </div>
</div>
<!-- Students Table -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full" id="studentsTable">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
            <input type="checkbox" id="selectAll" class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500 dark:bg-gray-700">
          </th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student ID</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header" data-sort="name">Name <span class="sort-icon">⇅</span></th>
          @if($hasAttendance)
          <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header" data-sort="absences">Absences <span class="sort-icon">⇅</span></th>
          @endif
          <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header" data-sort="grade">Estimated Grade <span class="sort-icon">⇅</span></th>
          <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer sort-header" data-sort="risk">ML Risk <span class="sort-icon">⇅</span></th>
          <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($enrolledStudents as $student)
             <tr class="hover:bg-gray-50 dark:hover:bg-gray-700" data-student-id="{{ $student->id }}">
               <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                 <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500 dark:bg-gray-700">
               </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                <div class="flex items-center gap-2">
                  {{ $student->student_id }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm student-name-cell text-gray-900 dark:text-gray-100" data-student-id="{{ $student->id }}">
                {{ $student->first_name }} {{ $student->last_name }}
              </td>
              @if($hasAttendance)
              <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                @php
                  $absenceData = $studentAbsences[$student->id] ?? null;
                  $absences = $absenceData['total_absences'] ?? 0;
                  $totalDays = $absenceData['total_days'] ?? 0;
                  $attendancePercentage = $totalDays > 0 ? (100 - (($absences / $totalDays) * 100)) : 0;
                @endphp
                <div class="flex flex-col items-center gap-1">
                  @if($totalDays > 0)
                    <div class="text-sm font-medium text-red-600 dark:text-red-400">{{ $absences }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">out of {{ $totalDays }} days</div>
                    @if($absences > 0)
                      <div class="text-xs text-red-500 font-medium">
                        {{ number_format(($absences / $totalDays) * 100, 1) }}% absent
                      </div>
                    @endif
                    <div class="text-xs text-green-600 dark:text-green-400 font-medium">
                      {{ number_format($attendancePercentage, 1) }}% present
                    </div>
                  @else
                    <span class="text-gray-400 text-sm">No attendance data</span>
                  @endif
                </div>
              </td>
              @endif
              <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                @php
                  $studentGrade = $studentGrades[$student->id] ?? null;
                  $midtermGrade = $studentGrade['midterm'] ?? null;
                  $finalGrade = $studentGrade['final'] ?? null;
                  $overallGrade = $studentGrade['overall'] ?? null;
                @endphp
                <div class="flex flex-col items-center gap-1">
                  @if($midtermGrade !== null || $finalGrade !== null || $overallGrade !== null)
                    <div class="flex items-center gap-2 text-xs">
                      @if($midtermGrade !== null)
                        <span class="text-blue-600 dark:text-blue-400 font-medium">{{ number_format($midtermGrade, 1) }}%</span>
                      @else
                        <span class="text-gray-400">--</span>
                      @endif
                      <span class="text-gray-400">|</span>
                      @if($finalGrade !== null)
                        <span class="text-red-600 dark:text-red-400 font-medium">{{ number_format($finalGrade, 1) }}%</span>
                      @else
                        <span class="text-gray-400">--</span>
                      @endif
                      <span class="text-gray-400">|</span>
                      @if($overallGrade !== null)
                        <span class="text-green-600 dark:text-green-400 font-medium">{{ number_format($overallGrade, 1) }}%</span>
                      @else
                        <span class="text-gray-400">--</span>
                      @endif
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                      <span class="text-blue-600">Midterm</span> | <span class="text-red-600">Final</span> | <span class="text-green-600">Overall</span>
                    </div>
                  @else
                    <span class="text-gray-400">No grades yet</span>
                  @endif
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                <div class="flex items-center justify-center">
                  <div class="ml-risk-indicator" data-student-id="{{ $student->id }}" data-student-data="{{ json_encode([
                    'avg_score_pct' => $studentMetrics[$student->id]['avg_score_pct'] ?? 0,
                    'variation_score_pct' => $studentMetrics[$student->id]['variation_score_pct'] ?? 0,
                    'late_submission_pct' => $studentMetrics[$student->id]['late_submission_pct'] ?? 0,
                    'missed_submission_pct' => $studentMetrics[$student->id]['missed_submission_pct'] ?? 0
                  ]) }}">
                    <div class="ml-loading hidden">
                      <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-gray-400"></i>
                    </div>
                    <div class="ml-risk-display hidden">
                      <div class="risk-badges"></div>
                    </div>
                    <div class="ml-error hidden">
                      <i data-lucide="alert-circle" class="w-4 h-4 text-red-500" title="ML service unavailable"></i>
                    </div>
                  </div>
                  <!-- Debug Button -->
                  <button onclick="showMLDebug('{{ $student->id }}', {{ json_encode([
                    'avg_score_pct' => $studentMetrics[$student->id]['avg_score_pct'] ?? 0,
                    'variation_score_pct' => $studentMetrics[$student->id]['variation_score_pct'] ?? 0,
                    'late_submission_pct' => $studentMetrics[$student->id]['late_submission_pct'] ?? 0,
                    'missed_submission_pct' => $studentMetrics[$student->id]['missed_submission_pct'] ?? 0
                  ]) }})" class="ml-2 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" title="Debug ML Response">
                    <i data-lucide="bug" class="w-3 h-3"></i>
                  </button>
                </div>
              </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                 <div class="flex items-center justify-center gap-2">
                   <a href="/subjects/{{ $classSectionModel->subject->id }}/classes/{{ $classSectionModel->id }}/students/{{ $student->id }}/analysis/{{ $term }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                     <i data-lucide="bar-chart-2" class="w-4 h-4"></i>
                   </a>
                   <button type="button" class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 edit-student-btn" data-student='{{ json_encode($student) }}'>
                     <i data-lucide="edit" class="w-4 h-4"></i>
                   </button>
                   <button type="button" onclick="if(confirm('Are you sure you want to unenroll {{ $student->first_name }} {{ $student->last_name }}? This action cannot be undone.')) { document.getElementById('unenroll-form-{{ $student->id }}').submit(); }" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                     <i data-lucide="user-minus" class="w-4 h-4"></i>
                   </button>
                   <form id="unenroll-form-{{ $student->id }}" action="{{ route('batch-enrollment.unenroll', ['subject' => $classSectionModel->subject->id, 'classSection' => $classSectionModel->id, 'student' => $student->id]) }}" method="POST" class="hidden">
                     @csrf
                     @method('DELETE')
                   </form>
                 </div>
               </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-6 py-12 text-center">
                <div class="text-gray-400 dark:text-gray-500 mb-4">
                  <i data-lucide="users" class="w-16 h-16 mx-auto"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No students enrolled</h3>
                <p class="text-gray-500 dark:text-gray-400 mb-6">Enroll students to start grading</p>
                <button onclick="openEnrollStudentModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                  <i data-lucide="plus" class="w-4 h-4"></i>
                  Enroll First Student
                </button>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Bulk Unenroll Form -->
<form id="bulkUnenrollForm" action="{{ route('batch-enrollment.bulk-unenroll', ['subject' => $classSectionModel->subject->id, 'classSection' => $classSectionModel->id]) }}" method="POST" class="hidden">
  @csrf
  <input type="hidden" name="student_ids" id="selectedStudentIds">
</form>

<!-- Enroll Student Modal -->
<div id="enrollStudentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <i data-lucide="user-check" class="w-6 h-6 text-red-600"></i>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Enroll Student</h3>
      </div>
      <button onclick="closeEnrollStudentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <form method="POST" action="{{ route('grading.enroll-existing-students', ['subject' => $classSectionModel->subject->id, 'classSection' => $classSectionModel->id, 'term' => $term]) }}" class="p-6">
      @csrf
      <div class="space-y-4">
        <!-- Multiple Students Selection -->
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Select Students to Enroll</label>
          <div class="relative">
            <input type="text" id="student_search" placeholder="Search students..." 
                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white mb-2">
          </div>
          
          <!-- Students List with Checkboxes -->
          <div class="border border-gray-300 dark:border-gray-600 rounded-lg max-h-48 overflow-y-auto bg-white dark:bg-gray-700">
            @foreach(\App\Models\Student::whereNotIn('id', $enrolledStudents->pluck('id'))->orderBy('last_name')->orderBy('first_name')->get() as $student)
              <div class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-600 border-b border-gray-200 dark:border-gray-600 last:border-b-0 student-item">
                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" id="student_{{ $student->id }}" 
                       class="w-4 h-4 text-red-600 bg-gray-100 border-gray-300 rounded focus:ring-red-500 dark:focus:ring-red-500 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-600 dark:border-gray-500 student-checkbox">
                <label for="student_{{ $student->id }}" class="flex-1 cursor-pointer">
                  <div class="flex items-center gap-2">
                    <i data-lucide="user" class="w-4 h-4 text-red-600"></i>
                    <span class="text-gray-900 dark:text-gray-100">{{ $student->student_id }} - {{ $student->first_name }} {{ $student->last_name }}</span>
                  </div>
                </label>
              </div>
            @endforeach
          </div>
          
          <!-- Select All / Deselect All -->
          <div class="flex items-center justify-between mt-2">
            <div class="flex gap-2">
              <button type="button" onclick="selectAllStudents()" class="text-sm text-red-600 hover:text-red-700 font-medium">
                Select All
              </button>
              <button type="button" onclick="deselectAllStudents()" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 font-medium">
                Deselect All
              </button>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400">
              <span id="selected-count">0</span> selected
            </div>
          </div>
        </div>

        <!-- Or Divider -->
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
          </div>
          <div class="relative flex justify-center text-sm">
            <span class="px-2 bg-white dark:bg-gray-800 text-gray-500 dark:text-gray-400">or</span>
          </div>
        </div>

        <!-- Create New Student Button -->
        <button type="button" onclick="openCreateStudentModal()" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
          <i data-lucide="user-plus" class="w-4 h-4"></i>
          Create New Student
        </button>
      </div>

      <!-- Modal Footer -->
      <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" onclick="closeEnrollStudentModal()" 
                class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
          Cancel
        </button>
        <button type="submit" 
                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
          Enroll Students
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Create New Student Modal -->
<div id="createStudentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <i data-lucide="user-plus" class="w-6 h-6 text-red-600"></i>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Create New Student</h3>
      </div>
      <button onclick="closeCreateStudentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <form method="POST" action="{{ route('grading.enroll-student', ['subject' => $classSectionModel->subject->id, 'classSection' => $classSectionModel->id, 'term' => $term]) }}" class="p-6">
      @csrf
      <div class="space-y-4">
        <div>
          <label for="new_student_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Student ID</label>
          <input type="text" id="new_student_id" name="student_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., 2021-0001">
        </div>
        <div>
          <label for="new_first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
          <input type="text" id="new_first_name" name="first_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., John">
        </div>
        <div>
          <label for="new_last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
          <input type="text" id="new_last_name" name="last_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., Doe">
        </div>
        <div>
          <label for="new_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email (Optional)</label>
          <input type="email" id="new_email" name="email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., john.doe@email.com">
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" onclick="closeCreateStudentModal()" 
                class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
          Cancel
        </button>
        <button type="submit" 
                class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
          Create & Enroll
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ML Debug Modal -->
<div id="mlDebugModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-2 sm:p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-lg mx-2 sm:mx-4 transform transition-all max-h-[90vh] flex flex-col">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <i data-lucide="bug" class="w-6 h-6 text-blue-600"></i>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">ML Debug Information</h3>
      </div>
      <button onclick="closeMLDebugModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
      <!-- Student Info -->
      <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-2 text-base sm:text-lg">Student Information</h4>
        <div id="debugStudentInfo" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400"></div>
      </div>

      <!-- Input Data -->
      <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-3 sm:p-4">
        <h4 class="font-semibold text-blue-900 dark:text-blue-100 mb-2 text-base sm:text-lg">Input Data Sent to ML API</h4>
        <pre id="debugInputData" class="text-xs bg-white dark:bg-gray-800 p-2 sm:p-3 rounded border overflow-x-auto"></pre>
      </div>

      <!-- API Response -->
      <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 sm:p-4">
        <h4 class="font-semibold text-green-900 dark:text-green-100 mb-2 text-base sm:text-lg">ML API Response</h4>
        <pre id="debugApiResponse" class="text-xs bg-white dark:bg-gray-800 p-2 sm:p-3 rounded border overflow-x-auto"></pre>
      </div>

      <!-- Processing Info -->
      <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-3 sm:p-4">
        <h4 class="font-semibold text-yellow-900 dark:text-yellow-100 mb-2 text-base sm:text-lg">Processing Information</h4>
        <div id="debugProcessingInfo" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400"></div>
      </div>

      <!-- Metrics Breakdown -->
      <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-3 sm:p-4">
        <h4 class="font-semibold text-purple-900 dark:text-purple-100 mb-2 text-base sm:text-lg">Metrics Calculation</h4>
        <div id="debugMetricsBreakdown" class="text-xs sm:text-sm text-gray-600 dark:text-gray-400"></div>
      </div>
    </div>

    <!-- Modal Footer -->
    <div class="flex gap-3 p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700">
      <button onclick="closeMLDebugModal()" 
              class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
        Close
      </button>
      <button onclick="retryMLPrediction()" 
              class="flex-1 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
        <i data-lucide="refresh-cw" class="w-4 h-4 inline mr-2"></i>
        Retry Prediction
      </button>
    </div>
  </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Edit Student</h3>
      <button onclick="closeEditStudentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    <form id="editStudentForm" method="POST">
      @csrf
      @method('PUT')
      <div class="p-6 space-y-4">
        <div>
          <label for="edit_student_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Student ID</label>
          <input type="text" id="edit_student_id" name="student_id" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label for="edit_first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">First Name</label>
          <input type="text" id="edit_first_name" name="first_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label for="edit_last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last Name</label>
          <input type="text" id="edit_last_name" name="last_name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Email (Optional)</label>
          <input type="email" id="edit_email" name="email" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
      </div>
      <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 px-6 pb-6">
        <button type="button" onclick="closeEditStudentModal()" class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">Cancel</button>
        <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Subject Modal -->
<div id="editSubjectModal" class="modal-overlay">
  <div class="modal-content">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Edit Subject</h3>
        <button onclick="closeEditSubjectModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>

      <!-- Progress Bar -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Step <span id="editCurrentStep">1</span> of 2</span>
          <span class="text-sm text-gray-500 dark:text-gray-400" id="editStepTitle">Basic Information</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
          <div id="editProgressBar" class="bg-red-600 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
        </div>
      </div>

      <!-- Step 1: Basic Subject Info -->
      <div id="editStep1" class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Step 1: Basic Information</h2>
          
          <div class="space-y-4">
            <div>
              <label for="edit_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject ID</label>
              <input type="text" id="edit_code" name="code" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., MATH101">
            </div>
            
            <div>
              <label for="edit_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject Name</label>
              <input type="text" id="edit_title" name="title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., College Algebra">
            </div>
            
            <div>
              <label for="edit_units" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Units</label>
              <input type="number" id="edit_units" name="units" step="0.5" min="0.5" max="6" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="3.0">
            </div>
          </div>
          
          <div class="flex justify-center mt-6">
            <button type="button" onclick="editNextStep()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
              Next: Assessment Builder
            </button>
          </div>
        </div>
      </div>

      <!-- Step 2: Assessment Type Builder -->
      <div id="editStep2" class="max-w-4xl mx-auto hidden">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 text-center">Step 2: Assessment Type Builder</h2>
          
          <!-- Grading Structure Selection -->
          <div class="mb-8">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">Grading Structure</h3>
            
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                  <input type="radio" name="edit_grading_type" value="balanced" class="mr-3 text-red-600 focus:ring-red-500" checked>
                  <div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">Balanced (50/50)</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Equal weight for Midterm and Final</div>
                  </div>
                </label>
                
                <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                  <input type="radio" name="edit_grading_type" value="custom" class="mr-3 text-red-600 focus:ring-red-500">
                  <div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">Custom Weights</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Define your own Midterm/Final weights</div>
                  </div>
                </label>
              </div>
              
              <div id="editCustomWeights" class="hidden max-w-md mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="edit_midterm_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Midterm Weight (%)</label>
                    <input type="number" id="edit_midterm_weight" name="midterm_weight" min="0" max="100" step="5" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" value="50">
                  </div>
                  <div>
                    <label for="edit_final_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Final Weight (%)</label>
                    <input type="number" id="edit_final_weight" name="final_weight" min="0" max="100" step="5" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" value="50">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Midterm Assessment Types -->
          <div class="mb-8">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">Midterm Assessment Types</h3>
            
            <div id="editMidtermAssessmentTypes" class="space-y-4 max-w-2xl mx-auto">
              <!-- Assessment types will be added here dynamically -->
            </div>
            
            <div class="text-center mt-4">
              <button type="button" onclick="editAddAssessmentType('midterm')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Assessment Type
              </button>
            </div>
          </div>

          <!-- Midterm Progress Bar -->
          <div class="mb-8 max-w-2xl mx-auto">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
              <span>Midterm Weight Distribution</span>
              <span id="editMidtermTotalWeight">0%</span>
            </div>
            <div class="slider-container" id="editMidtermProgressContainer">
              <!-- Progress bar segments will be added here dynamically -->
            </div>
            <div id="editMidtermOutput" class="text-xs text-gray-500 dark:text-gray-400 mt-1"></div>
            <div id="editMidtermError"></div>
          </div>

          <!-- Final Assessment Types -->
          <div class="mb-8">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">Final Assessment Types</h3>
            
            <div id="editFinalAssessmentTypes" class="space-y-4 max-w-2xl mx-auto">
              <!-- Assessment types will be added here dynamically -->
            </div>
            
            <div class="text-center mt-4">
              <button type="button" onclick="editAddAssessmentType('final')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Assessment Type
              </button>
            </div>
          </div>

          <!-- Final Progress Bar -->
          <div class="mb-8 max-w-2xl mx-auto">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
              <span>Final Weight Distribution</span>
              <span id="editFinalTotalWeight">0%</span>
            </div>
            <div class="slider-container" id="editFinalProgressContainer">
              <!-- Progress bar segments will be added here dynamically -->
            </div>
            <div id="editFinalOutput" class="text-xs text-gray-500 dark:text-gray-400 mt-1"></div>
            <div id="editFinalError"></div>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex justify-center gap-4 mt-8">
            <button type="button" onclick="editPrevStep()" class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition-colors font-medium">
              ← Back
            </button>
            <button type="button" onclick="editSaveSubject()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
              Update Subject
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<!-- Analytics Modal (Custom) -->
<div id="analyticsModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-2 sm:p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-4xl mx-2 sm:mx-4 transform transition-all max-h-[90vh] flex flex-col">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <i data-lucide="bar-chart-2" class="w-6 h-6 text-blue-600"></i>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">Student Performance Analytics</h3>
      </div>
      <button onclick="closeAnalyticsModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    <!-- Modal Body -->
    <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
      <!-- Analytics Cards -->
      <div id="analyticsCards" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4"></div>
      
      <!-- Chart Section -->
      <div class="bg-white dark:bg-gray-700 rounded-lg shadow-sm p-3 sm:p-4">
        <div class="flex items-center gap-2 mb-3">
          <i data-lucide="bar-chart-3" class="w-5 h-5 text-blue-600"></i>
          <span class="font-semibold text-gray-800 dark:text-gray-100 text-sm sm:text-base">Risk Level Distribution</span>
        </div>
        <div class="flex justify-center">
          <div class="relative" style="width: 100%; height: 300px;">
            <canvas id="performanceChart"></canvas>
          </div>
        </div>
      </div>
      
      <!-- Summary -->
      <div id="analyticsSummary" class="text-center text-sm sm:text-base"></div>
    </div>
    <!-- Modal Footer -->
    <div class="flex gap-3 p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700">
      <button type="button" onclick="closeAnalyticsModal()" class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">Close</button>
    </div>
  </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function openAnalyticsModal() {
  document.getElementById('analyticsModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
  // Fetch and render chart and cards
  var subjectId = @json($classSectionModel->subject->id);
  var classSectionId = @json($classSectionModel->id);
  var term = @json($term);
  var chartInstance = window.analyticsChartInstance || null;
  fetch(`/subjects/${subjectId}/classes/${classSectionId}/analytics/${term}`)
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Analytics data received:', data); // Debug log
      
      // Count risk badges from the current page for all risk types
      const riskCounts = {
        safe: 0,
        atRisk: 0,
        lowRisk: 0,
        inconsistentPerformer: 0,
        chronicProcrastinator: 0,

      };
      document.querySelectorAll('.ml-risk-indicator .risk-badges span').forEach(badge => {
        const text = badge.textContent.toLowerCase();
        if (text.includes('safe') || text.includes('not at risk')) riskCounts.safe++;
        if (text.includes('at risk')) riskCounts.atRisk++;
        if (text.includes('low risk')) riskCounts.lowRisk++;
        if (text.includes('inconsistent performer')) riskCounts.inconsistentPerformer++;
        if (text.includes('chronic procrastinator')) riskCounts.chronicProcrastinator++;

      });
      // Ensure all values are numbers or 0
      const safeData = {
        total: parseInt(data.total) || 0,
        passing: riskCounts.safe,
        atRisk: riskCounts.lowRisk,
        failing: riskCounts.atRisk,
        avgGrade: parseFloat(data.avgGrade) || null,
        avgRisk: parseFloat(data.avgRisk) || null,
        avgLate: parseFloat(data.avgLate) || null,
        avgMissed: parseFloat(data.avgMissed) || null
      };
      console.log('Risk counts from page:', riskCounts);
      console.log('Safe data:', safeData); // Debug log
      // Cards
      const cards = [
        { label: 'Total Students', value: safeData.total, icon: 'users', color: 'bg-gray-100 dark:bg-gray-700' },
        { label: 'Safe', value: riskCounts.safe, icon: 'check-circle', color: 'bg-green-100 dark:bg-green-900/20' },
        { label: 'At Risk', value: riskCounts.atRisk, icon: 'alert-triangle', color: 'bg-red-100 dark:bg-red-900/20' },
        { label: 'Low Risk', value: riskCounts.lowRisk, icon: 'shield', color: 'bg-yellow-100 dark:bg-yellow-900/20' },
        { label: 'Inconsistent Performer', value: riskCounts.inconsistentPerformer, icon: 'activity', color: 'bg-purple-100 dark:bg-purple-900/20' },
        { label: 'Chronic Procrastinator', value: riskCounts.chronicProcrastinator, icon: 'clock', color: 'bg-yellow-50 dark:bg-yellow-800/20' },

        { label: 'Average Grade', value: safeData.avgGrade ? safeData.avgGrade.toFixed(1) + '%': 'N/A', icon: 'bar-chart-2', color: 'bg-blue-100 dark:bg-blue-900/20' },
        { label: 'Avg. ML Risk', value: safeData.avgRisk ? safeData.avgRisk.toFixed(1) + '%' : 'N/A', icon: 'activity', color: 'bg-purple-100 dark:bg-purple-900/20' },
        { label: 'Avg. Late %', value: safeData.avgLate ? safeData.avgLate.toFixed(1) + '%' : 'N/A', icon: 'clock', color: 'bg-yellow-50 dark:bg-yellow-800/20' },
        { label: 'Avg. Missed %', value: safeData.avgMissed ? safeData.avgMissed.toFixed(1) + '%' : 'N/A', icon: 'slash', color: 'bg-red-50 dark:bg-red-800/20' },
      ];
      document.getElementById('analyticsCards').innerHTML = cards.map(card => `
        <div class="${card.color} rounded-lg p-3 sm:p-4 flex items-center gap-2 sm:gap-3 shadow-sm">
          <div class="flex-shrink-0"><i data-lucide="${card.icon}" class="w-5 h-5 sm:w-6 sm:h-6"></i></div>
          <div class="min-w-0 flex-1">
            <div class="text-lg sm:text-xl font-bold truncate">${card.value}</div>
            <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-300 truncate">${card.label}</div>
          </div>
        </div>
      `).join('');
      if (window.lucide) window.lucide.createIcons();
      // Chart
      var ctx = document.getElementById('performanceChart').getContext('2d');
      if (chartInstance) chartInstance.destroy();
      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: [
            'Safe',
            'At Risk',
            'Low Risk',
            'Inconsistent Performer',
            'Chronic Procrastinator',

          ],
          datasets: [{
            label: 'Number of Students',
            data: [
              riskCounts.safe,
              riskCounts.atRisk,
              riskCounts.lowRisk,
              riskCounts.inconsistentPerformer,
              riskCounts.chronicProcrastinator,

            ],
            backgroundColor: [
              '#22c55e', // Safe
              '#ef4444', // At Risk
              '#fbbf24', // Low Risk
              '#a78bfa', // Inconsistent Performer
              '#facc15', // Chronic Procrastinator

            ],
            borderRadius: 4,
            borderSkipped: false,
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: function(context) {
                  return context.parsed.y + ' students';
                }
              }
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Number of Students',
                font: { size: 14, weight: 'bold' }
              },
              ticks: { stepSize: 1, font: { size: 12 } }
            },
            x: {
              title: {
                display: true,
                text: 'Risk Type',
                font: { size: 14, weight: 'bold' }
              },
              ticks: { font: { size: 12 } }
            }
          }
        }
      });
      window.analyticsChartInstance = chartInstance;
      document.getElementById('analyticsSummary').innerHTML =
        `<div class="text-sm sm:text-base">
          <b>Total Students:</b> ${safeData.total} &nbsp;|&nbsp;
          <span style='color:#22c55e'>Safe:</span> ${riskCounts.safe} &nbsp;|&nbsp;
          <span style='color:#fbbf24'>Low Risk:</span> ${riskCounts.lowRisk} &nbsp;|&nbsp;
          <span style='color:#ef4444'>High Risk:</span> ${riskCounts.atRisk}
        </div>`;
    })
    .catch(error => {
      console.error('Error fetching analytics:', error);
      document.getElementById('analyticsCards').innerHTML = `
        <div class="col-span-full text-center p-4">
          <div class="text-red-600 dark:text-red-400 mb-2">
            <i data-lucide="alert-circle" class="w-8 h-8 mx-auto"></i>
          </div>
          <p class="text-gray-600 dark:text-gray-400">Failed to load analytics data</p>
          <p class="text-sm text-gray-500 dark:text-gray-500">${error.message}</p>
        </div>
      `;
      if (window.lucide) window.lucide.createIcons();
    });
}
function closeAnalyticsModal() {
  document.getElementById('analyticsModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
}
</script>

<script>
// Set context variables from Blade
const subjectId = @json($classSectionModel->subject->id);
const classSectionId = @json($classSectionModel->id);
const term = @json($term);

function openEnrollStudentModal() {
  document.getElementById('enrollStudentModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeEnrollStudentModal() {
  document.getElementById('enrollStudentModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
}

function openCreateStudentModal() {
  document.getElementById('createStudentModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeCreateStudentModal() {
  document.getElementById('createStudentModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  document.getElementById('createStudentModal').querySelector('form').reset();
}

function selectAllStudents() {
  document.querySelectorAll('.student-checkbox').forEach(checkbox => {
    checkbox.checked = true;
  });
  updateSelectedCount();
}

function deselectAllStudents() {
  document.querySelectorAll('.student-checkbox').forEach(checkbox => {
    checkbox.checked = false;
  });
  updateSelectedCount();
}

function updateSelectedCount() {
  const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
  document.getElementById('selected-count').textContent = checkedBoxes.length;
}

// Student search functionality
document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('student_search');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const searchTerm = this.value.toLowerCase();
      const studentItems = document.querySelectorAll('.student-item');
      
      studentItems.forEach(item => {
        const studentName = item.querySelector('span').textContent.toLowerCase();
        if (studentName.includes(searchTerm)) {
          item.style.display = 'flex';
        } else {
          item.style.display = 'none';
        }
      });
    });
  }
  
  // Update selected count when checkboxes change
  document.querySelectorAll('.student-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', updateSelectedCount);
  });
  
  // Initialize selected count
  updateSelectedCount();
});

lucide.createIcons();

// Bulk unenroll functionality
document.addEventListener('DOMContentLoaded', function() {
  const selectAllCheckbox = document.getElementById('selectAll');
  const studentCheckboxes = document.querySelectorAll('.student-checkbox');
  const bulkUnenrollBtn = document.getElementById('bulkUnenrollBtn');
  const bulkUnenrollForm = document.getElementById('bulkUnenrollForm');
  const selectedStudentIdsInput = document.getElementById('selectedStudentIds');

  // Select all functionality
  if (selectAllCheckbox) {
    selectAllCheckbox.addEventListener('change', function() {
      studentCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });
      updateBulkUnenrollButton();
    });
  }

  // Individual checkbox functionality
  studentCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      updateBulkUnenrollButton();
      updateSelectAllCheckbox();
    });
  });

  // Update bulk unenroll button visibility
  function updateBulkUnenrollButton() {
    const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
    if (checkedBoxes.length > 0) {
      bulkUnenrollBtn.classList.remove('hidden');
      bulkUnenrollBtn.textContent = `Unenroll Selected (${checkedBoxes.length})`;
    } else {
      bulkUnenrollBtn.classList.add('hidden');
    }
  }

  // Update select all checkbox state
  function updateSelectAllCheckbox() {
    const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
    const totalBoxes = studentCheckboxes.length;
    
    if (selectAllCheckbox) {
      if (checkedBoxes.length === 0) {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = false;
      } else if (checkedBoxes.length === totalBoxes) {
        selectAllCheckbox.checked = true;
        selectAllCheckbox.indeterminate = false;
      } else {
        selectAllCheckbox.checked = false;
        selectAllCheckbox.indeterminate = true;
      }
    }
  }

  // Bulk unenroll button click
  if (bulkUnenrollBtn) {
    bulkUnenrollBtn.addEventListener('click', function() {
      const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
      const studentIds = Array.from(checkedBoxes).map(cb => cb.value);
      
      if (studentIds.length > 0) {
        const confirmMessage = `Are you sure you want to unenroll ${studentIds.length} student(s)? This action cannot be undone.`;
        if (confirm(confirmMessage)) {
          // Remove any previous hidden inputs
          document.querySelectorAll('#bulkUnenrollForm input[name="student_ids[]"]').forEach(e => e.remove());
          // Add a hidden input for each selected student
          studentIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            bulkUnenrollForm.appendChild(input);
          });
          bulkUnenrollForm.submit();
        }
      }
    });
  }
});

// Success message animation and auto-hide
@if (session('success'))
  document.addEventListener('DOMContentLoaded', function() {
    const successMessage = document.getElementById('successMessage');
    if (successMessage) {
      successMessage.style.transform = 'translateY(-20px)';
      successMessage.style.opacity = '0';
      setTimeout(() => {
        successMessage.style.transform = 'translateY(0)';
        successMessage.style.opacity = '1';
      }, 100);
      setTimeout(() => {
        successMessage.style.transform = 'translateY(-20px)';
        successMessage.style.opacity = '0';
        setTimeout(() => {
          successMessage.remove();
        }, 300);
      }, 5000);
    }
  });
@endif

// Edit Student Modal logic
function openEditStudentModal(student) {
  // Set form action
  const form = document.getElementById('editStudentForm');
  form.action = `/subjects/${subjectId}/classes/${classSectionId}/${term}/grading/${student.id}`;
  // Fill fields
  document.getElementById('edit_student_id').value = student.student_id;
  document.getElementById('edit_first_name').value = student.first_name;
  document.getElementById('edit_last_name').value = student.last_name;
  document.getElementById('edit_email').value = student.email || '';
  // Show modal
  document.getElementById('editStudentModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
function closeEditStudentModal() {
  document.getElementById('editStudentModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  document.getElementById('editStudentForm').reset();
}
// Attach click event to edit icons after DOM is loaded
window.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.edit-student-btn').forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      const student = JSON.parse(this.getAttribute('data-student'));
      openEditStudentModal(student);
    });
  });

  // Initialize ML risk predictions
  initializeMLRiskPredictions();
  
});

// ML Risk Prediction Functions
function initializeMLRiskPredictions() {
  const riskIndicators = document.querySelectorAll('.ml-risk-indicator');
  let delay = 0;
  riskIndicators.forEach((indicator, idx) => {
    const studentData = JSON.parse(indicator.getAttribute('data-student-data'));
    setTimeout(() => {
      loadRiskPrediction(indicator, studentData);
    }, delay);
    delay += 100; // 100ms between each call, adjust as needed
  });
}

// Alternative initialization function that uses term-specific endpoint
function initializeMLRiskPredictionsByTerm() {
  const riskIndicators = document.querySelectorAll('.ml-risk-indicator');
  const classSectionId = '{{ $classSectionModel->id }}';
  const term = '{{ $term }}';
  let delay = 0;
  
  riskIndicators.forEach((indicator, idx) => {
    const studentId = indicator.getAttribute('data-student-id');
    setTimeout(() => {
      loadRiskPredictionByTerm(indicator, studentId, classSectionId, term);
    }, delay);
    delay += 100; // 100ms between each call, adjust as needed
  });
}

function loadRiskPrediction(indicator, studentData) {
  const loadingDiv = indicator.querySelector('.ml-loading');
  const displayDiv = indicator.querySelector('.ml-risk-display');
  const errorDiv = indicator.querySelector('.ml-error');
  const badgesDiv = indicator.querySelector('.risk-badges');

  // Show loading
  loadingDiv.classList.remove('hidden');
  displayDiv.classList.add('hidden');
  errorDiv.classList.add('hidden');

  // Make API call with term-filtered data (data is already filtered by current term)
  fetch('/api/ml/predict/student', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(studentData)
  })
  .then(response => response.json())
  .then(data => {
    loadingDiv.classList.add('hidden');
    const studentId = indicator.getAttribute('data-student-id');
    const nameCell = document.querySelector(`.student-name-cell[data-student-id="${studentId}"]`);
    if (data.success && data.has_risks) {
      displayDiv.classList.remove('hidden');
      renderRiskBadges(badgesDiv, data.risks);
      // Color logic
      const hasAtRisk = data.risks.some(risk => risk.code === 'risk_at_risk');
      if (hasAtRisk) {
        nameCell.classList.remove('text-green-600', 'text-orange-500');
        nameCell.classList.add('text-red-600');
      } else {
        nameCell.classList.remove('text-red-600', 'text-green-600');
        nameCell.classList.add('text-orange-500');
      }
    } else if (data.success && !data.has_risks) {
      displayDiv.classList.remove('hidden');
      badgesDiv.innerHTML = '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Safe</span>';
      // Safe: green
      nameCell.classList.remove('text-red-600', 'text-orange-500');
      nameCell.classList.add('text-green-600');
    } else {
      errorDiv.classList.remove('hidden');
      // Optionally, remove color if error
      nameCell.classList.remove('text-red-600', 'text-orange-500', 'text-green-600');
    }
  })
  .catch(error => {
    console.error('ML Prediction error:', error);
    loadingDiv.classList.add('hidden');
    errorDiv.classList.remove('hidden');
    // Optionally, remove color if error
    const studentId = indicator.getAttribute('data-student-id');
    const nameCell = document.querySelector(`.student-name-cell[data-student-id="${studentId}"]`);
    if (nameCell) {
      nameCell.classList.remove('text-red-600', 'text-orange-500', 'text-green-600');
    }
  });
}

// Alternative function that uses the term-specific endpoint
function loadRiskPredictionByTerm(indicator, studentId, classSectionId, term) {
  const loadingDiv = indicator.querySelector('.ml-loading');
  const displayDiv = indicator.querySelector('.ml-risk-display');
  const errorDiv = indicator.querySelector('.ml-error');
  const badgesDiv = indicator.querySelector('.risk-badges');

  // Show loading
  loadingDiv.classList.remove('hidden');
  displayDiv.classList.add('hidden');
  errorDiv.classList.add('hidden');

  // Make API call using term-specific endpoint
  fetch(`/api/ml/predict/student/${studentId}/${classSectionId}/${term}`, {
    method: 'GET',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    loadingDiv.classList.add('hidden');
    const nameCell = document.querySelector(`.student-name-cell[data-student-id="${studentId}"]`);
    
    if (data.success && data.predictions && data.predictions.has_risks) {
      displayDiv.classList.remove('hidden');
      renderRiskBadges(badgesDiv, data.predictions.risks);
      // Color logic
      const hasAtRisk = data.predictions.risks.some(risk => risk.code === 'risk_at_risk');
      if (hasAtRisk) {
        nameCell.classList.remove('text-green-600', 'text-orange-500');
        nameCell.classList.add('text-red-600');
      } else {
        nameCell.classList.remove('text-red-600', 'text-green-600');
        nameCell.classList.add('text-orange-500');
      }
    } else if (data.success && data.predictions && !data.predictions.has_risks) {
      displayDiv.classList.remove('hidden');
      badgesDiv.innerHTML = '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Safe</span>';
      // Safe: green
      nameCell.classList.remove('text-red-600', 'text-orange-500');
      nameCell.classList.add('text-green-600');
    } else {
      errorDiv.classList.remove('hidden');
      // Optionally, remove color if error
      nameCell.classList.remove('text-red-600', 'text-orange-500', 'text-green-600');
    }
  })
  .catch(error => {
    console.error('ML Prediction error:', error);
    loadingDiv.classList.add('hidden');
    errorDiv.classList.remove('hidden');
    // Optionally, remove color if error
    const nameCell = document.querySelector(`.student-name-cell[data-student-id="${studentId}"]`);
    if (nameCell) {
      nameCell.classList.remove('text-red-600', 'text-orange-500', 'text-green-600');
    }
  });
}

function renderRiskBadges(badgesDiv, risks) {
  // Debug: Log the risks to see what we're getting
  console.log('Rendering risks:', risks);
  
  const riskColors = {
    'risk_at_risk': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
    'risk_chronic_procrastinator': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    'risk_incomplete': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    'risk_inconsistent_performer': 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200'
  };

  const riskIcons = {
    'risk_at_risk': 'alert-triangle',
    'risk_chronic_procrastinator': 'clock',
    'risk_incomplete': 'file-x',
    'risk_inconsistent_performer': 'activity'
  };

  // Post-processing logic
  const hasAtRisk = risks.some(risk => risk.code === 'risk_at_risk');
  const otherRisks = risks.filter(risk => risk.code !== 'risk_at_risk');

  let html = '';
  if (hasAtRisk) {
    // At Risk: show main badge, then all comments
    html += `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 mr-1">
      <i data-lucide="alert-triangle" class="w-3 h-3"></i> At Risk
    </span>`;
    otherRisks.forEach(risk => {
      const colorClass = riskColors[risk.code] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
      const icon = riskIcons[risk.code] || 'alert-circle';
      html += `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium ${colorClass} mr-1" title="${risk.description}">
        <i data-lucide="${icon}" class="w-3 h-3"></i> ${risk.label}
      </span>`;
    });
  } else if (otherRisks.length > 0) {
    // Low Risk: show main badge, then comments
    html += `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 mr-1">
      <i data-lucide="shield" class="w-3 h-3"></i> Low Risk
    </span>`;
    otherRisks.forEach(risk => {
      const colorClass = riskColors[risk.code] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200';
      const icon = riskIcons[risk.code] || 'alert-circle';
      html += `<span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium ${colorClass} mr-1" title="${risk.description}">
        <i data-lucide="${icon}" class="w-3 h-3"></i> ${risk.label}
      </span>`;
    });
  } else {
    // Not At Risk
    html = '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Not At Risk</span>';
  }

  badgesDiv.innerHTML = html;
  lucide.createIcons();
}

// ML Debug Functions
let currentDebugData = null;

function showMLDebug(studentId, studentData) {
  currentDebugData = { studentId, studentData };
  
  // Find student info from the table
  const studentRow = document.querySelector(`tr[data-student-id="${studentId}"]`);
  
  let studentInfo = 'Student ID: ' + studentId;
  if (studentRow) {
    const studentIdCell = studentRow.querySelector('td:nth-child(2)');
    const nameCell = studentRow.querySelector('td:nth-child(3)');
    
    if (studentIdCell) {
      studentInfo = 'Student ID: ' + studentIdCell.textContent.trim();
    }
    if (nameCell) {
      studentInfo += '\nName: ' + nameCell.textContent.trim();
    }
  }
  
  // Update modal content
  document.getElementById('debugStudentInfo').textContent = studentInfo;
  document.getElementById('debugInputData').textContent = JSON.stringify(studentData, null, 2);
  document.getElementById('debugApiResponse').textContent = 'Loading...';
  document.getElementById('debugProcessingInfo').textContent = 'Making API call...';
  document.getElementById('debugMetricsBreakdown').textContent = 'Loading metrics...';
  
  // Show modal
  document.getElementById('mlDebugModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
  
  // Make API call and show response
  makeDebugApiCall(studentData);
  
  // Fetch detailed metrics breakdown
  fetchMetricsBreakdown(studentId);
}

function makeDebugApiCall(studentData) {
  const startTime = Date.now();
  
  fetch('/api/ml/predict/student', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(studentData)
  })
  .then(response => {
    const responseTime = Date.now() - startTime;
    document.getElementById('debugProcessingInfo').textContent = 
      `Response time: ${responseTime}ms\nStatus: ${response.status} ${response.statusText}`;
    
    return response.json();
  })
  .then(data => {
    document.getElementById('debugApiResponse').textContent = JSON.stringify(data, null, 2);
  })
  .catch(error => {
    const responseTime = Date.now() - startTime;
    document.getElementById('debugProcessingInfo').textContent = 
      `Error after ${responseTime}ms: ${error.message}`;
    document.getElementById('debugApiResponse').textContent = `Error: ${error.message}`;
  });
}

function closeMLDebugModal() {
  document.getElementById('mlDebugModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  currentDebugData = null;
}

function retryMLPrediction() {
  if (currentDebugData) {
    document.getElementById('debugApiResponse').textContent = 'Loading...';
    document.getElementById('debugProcessingInfo').textContent = 'Retrying API call...';
    makeDebugApiCall(currentDebugData.studentData);
  }
}

function fetchMetricsBreakdown(studentId) {
  // Get class section ID from the current page URL
  const urlParts = window.location.pathname.split('/');
  const classSectionId = urlParts[4]; // /subjects/{subject}/classes/{classSection}/...
  
  fetch(`/api/ml/metrics/${studentId}/${classSectionId}`)
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        const breakdown = data.data;
        let metricsText = '';
        // Metrics summary as table
        metricsText += `<table class='w-full text-xs sm:text-sm mb-4 border border-gray-200 dark:border-gray-700 rounded'>
          <tbody>
            <tr><td class='font-semibold py-1 px-2'>Average Score</td><td class='py-1 px-2'>${breakdown.metrics.avg_score_pct}%</td></tr>
            <tr><td class='font-semibold py-1 px-2'>Score Variation</td><td class='py-1 px-2'>${breakdown.metrics.variation_score_pct}%</td></tr>
            <tr><td class='font-semibold py-1 px-2'>Late Submissions</td><td class='py-1 px-2'>${breakdown.metrics.late_submission_pct}%</td></tr>
            <tr><td class='font-semibold py-1 px-2'>Missed Submissions</td><td class='py-1 px-2'>${breakdown.metrics.missed_submission_pct}%</td></tr>
            <tr><td class='font-semibold py-1 px-2'>Total Assessments</td><td class='py-1 px-2'>${breakdown.metrics.total_assessments}</td></tr>
            <tr><td class='font-semibold py-1 px-2'>Completed</td><td class='py-1 px-2'>${breakdown.metrics.completed_assessments}</td></tr>
            <tr><td class='font-semibold py-1 px-2'>Late Count</td><td class='py-1 px-2'>${breakdown.metrics.late_submissions}</td></tr>
            <tr><td class='font-semibold py-1 px-2'>Missed Count</td><td class='py-1 px-2'>${breakdown.metrics.missed_submissions}</td></tr>
          </tbody>
        </table>`;
        // Assessment breakdown as table
        if (breakdown.assessments.length > 0) {
          metricsText += `<div class='overflow-x-auto'><table class='w-full text-xs sm:text-sm border border-gray-200 dark:border-gray-700 rounded'>
            <thead>
              <tr class='bg-gray-100 dark:bg-gray-700'>
                <th class='py-1 px-2 text-left'>#</th>
                <th class='py-1 px-2 text-left'>Assessment</th>
                <th class='py-1 px-2 text-left'>Type</th>
                <th class='py-1 px-2 text-left'>Score</th>
                <th class='py-1 px-2 text-left'>Status</th>
              </tr>
            </thead>
            <tbody>`;
          breakdown.assessments.forEach((assessment, index) => {
            const status = assessment.is_missed ? 'Missed' : assessment.is_late ? 'Late' : 'Completed';
            const score = assessment.has_score ? `${assessment.percentage}%` : 'N/A';
            metricsText += `<tr>
              <td class='py-1 px-2'>${index + 1}</td>
              <td class='py-1 px-2'>${assessment.name}</td>
              <td class='py-1 px-2'>${assessment.type}</td>
              <td class='py-1 px-2'>${score}</td>
              <td class='py-1 px-2'>${status}</td>
            </tr>`;
          });
          metricsText += `</tbody></table></div>`;
        }
        document.getElementById('debugMetricsBreakdown').innerHTML = metricsText;
      } else {
        document.getElementById('debugMetricsBreakdown').textContent = `Error: ${data.error}`;
      }
    })
    .catch(error => {
      document.getElementById('debugMetricsBreakdown').textContent = `Error fetching metrics: ${error.message}`;
    });
}

// Edit Subject Modal Variables
let editCurrentStep = 1;
let editAssessmentTypeCounter = { midterm: 0, final: 0 };
let editAssessmentTypes = { midterm: [], final: [] };
const editColors = ['blue', 'green', 'purple', 'orange', 'pink', 'indigo', 'teal', 'red'];

// Edit Subject Modal Functions
function openEditSubjectModal() {
  // Get subject data from the current page context
  const subjectCode = @json($classSectionModel->subject->code);
  const subjectTitle = @json($classSectionModel->subject->title);
  const subjectUnits = @json($classSectionModel->subject->units);
  const subjectId = @json($classSectionModel->subject->id);
  
  // Fill the edit form
  document.getElementById('edit_code').value = subjectCode;
  document.getElementById('edit_title').value = subjectTitle;
  document.getElementById('edit_units').value = subjectUnits;
  
  // Reset to step 1
  editCurrentStep = 1;
  editUpdateProgress();
  document.getElementById('editStep1').classList.remove('hidden');
  document.getElementById('editStep2').classList.add('hidden');
  
  // Reset assessment types
  editAssessmentTypes = { midterm: [], final: [] };
  editAssessmentTypeCounter = { midterm: 0, final: 0 };
  
  // Clear existing assessment types
  document.getElementById('editMidtermAssessmentTypes').innerHTML = '';
  document.getElementById('editFinalAssessmentTypes').innerHTML = '';
  
  // Load existing subject data
  editLoadSubjectData(subjectId);
  
  // Show modal
  const modal = document.getElementById('editSubjectModal');
  modal.classList.add('show');
  
  // Focus on first input
  setTimeout(() => document.getElementById('edit_code').focus(), 100);
}

function closeEditSubjectModal() {
  const modal = document.getElementById('editSubjectModal');
  modal.classList.remove('show');
  document.body.style.overflow = 'auto';
}

function editUpdateProgress() {
  document.getElementById('editCurrentStep').textContent = editCurrentStep;
  document.getElementById('editStepTitle').textContent = editCurrentStep === 1 ? 'Basic Information' : 'Assessment Builder';
  document.getElementById('editProgressBar').style.width = editCurrentStep === 1 ? '50%' : '100%';
}

function editNextStep() {
  if (editCurrentStep < 2) {
    editCurrentStep++;
    editUpdateProgress();
    document.getElementById('editStep1').classList.add('hidden');
    document.getElementById('editStep2').classList.remove('hidden');
  }
}

function editPrevStep() {
  if (editCurrentStep > 1) {
    editCurrentStep--;
    editUpdateProgress();
    document.getElementById('editStep2').classList.add('hidden');
    document.getElementById('editStep1').classList.remove('hidden');
  }
}

// Load existing subject data
async function editLoadSubjectData(subjectId) {
  try {
    const response = await fetch(`/subjects/${subjectId}/edit-data`);
    if (response.ok) {
      const data = await response.json();
      
      // Set grading structure
      if (data.grading_structure) {
        const gradingType = data.grading_structure.type;
        document.querySelector(`input[name="edit_grading_type"][value="${gradingType}"]`).checked = true;
        
        if (gradingType === 'custom') {
          document.getElementById('editCustomWeights').classList.remove('hidden');
          document.getElementById('edit_midterm_weight').value = data.grading_structure.midterm_weight;
          document.getElementById('edit_final_weight').value = data.grading_structure.final_weight;
        }
      }
      
      // Load assessment types
      if (data.assessment_types) {
        editAssessmentTypes = { midterm: [], final: [] };
        editAssessmentTypeCounter = { midterm: 0, final: 0 };
        
        // Clear existing
        document.getElementById('editMidtermAssessmentTypes').innerHTML = '';
        document.getElementById('editFinalAssessmentTypes').innerHTML = '';
        
        // Load midterm assessment types
        if (data.assessment_types.midterm) {
          data.assessment_types.midterm.forEach(type => {
            editAddAssessmentType('midterm', type);
          });
        }
        
        // Load final assessment types
        if (data.assessment_types.final) {
          data.assessment_types.final.forEach(type => {
            editAddAssessmentType('final', type);
          });
        }
      }
    }
  } catch (error) {
    console.error('Error loading subject data:', error);
  }
}

function editAddAssessmentType(term, existingData = null) {
  const counter = ++editAssessmentTypeCounter[term];
  const typeId = `${term}_${counter}`;
  const colorIndex = (counter - 1) % editColors.length;
  const color = editColors[colorIndex];
  
  const assessmentType = {
    id: existingData ? existingData.id : typeId,
    name: existingData ? existingData.name : '',
    weight: existingData ? existingData.weight : 0,
    order: existingData ? existingData.order : counter,
    color: color
  };
  
  editAssessmentTypes[term].push(assessmentType);
  
  const container = document.getElementById(`edit${term.charAt(0).toUpperCase() + term.slice(1)}AssessmentTypes`);
  const typeElement = document.createElement('div');
  typeElement.className = 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4';
  typeElement.innerHTML = `
    <div class="flex items-center justify-between">
      <div class="flex-1 mr-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Type Name</label>
        <input type="text" 
               id="edit_name_${typeId}"
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
               placeholder="e.g., Quiz, Lab, Project"
               value="${assessmentType.name}"
               oninput="editUpdateAssessmentType('${term}', ${counter - 1}, 'name', this.value)">
      </div>
      <div class="w-24">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (%)</label>
        <input type="number" 
               id="edit_weight_${typeId}"
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
               min="0" max="100" step="1" value="${Math.round(parseFloat(assessmentType.weight) || 0)}"
               oninput="editUpdateAssessmentType('${term}', ${counter - 1}, 'weight', this.value)"
               onkeypress="return event.charCode >= 48 && event.charCode <= 57">
      </div>
      <button type="button" onclick="editRemoveAssessmentType('${term}', ${counter - 1})" class="ml-2 text-red-600 hover:text-red-700 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    </div>
  `;
  
  container.appendChild(typeElement);
  lucide.createIcons();
  editUpdateProgressBars();
}

function editRemoveAssessmentType(term, index) {
  editAssessmentTypes[term].splice(index, 1);
  editAssessmentTypeCounter[term]--;
  editRenderAssessmentTypes(term);
  editUpdateProgressBars();
}

function editUpdateAssessmentType(term, index, field, value) {
  editAssessmentTypes[term][index][field] = value;
  editUpdateProgressBars();
}

function editRenderAssessmentTypes(term) {
  const container = document.getElementById(`edit${term.charAt(0).toUpperCase() + term.slice(1)}AssessmentTypes`);
  container.innerHTML = '';
  
  editAssessmentTypes[term].forEach((type, index) => {
    const counter = index + 1;
    const typeId = `${term}_${counter}`;
    
    const typeElement = document.createElement('div');
    typeElement.className = 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4';
    typeElement.innerHTML = `
      <div class="flex items-center justify-between">
        <div class="flex-1 mr-4">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Type Name</label>
          <input type="text" 
                 id="edit_name_${typeId}"
                 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
                 placeholder="e.g., Quiz, Lab, Project"
                 value="${type.name}"
                 oninput="editUpdateAssessmentType('${term}', ${index}, 'name', this.value)">
        </div>
        <div class="w-24">
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (%)</label>
          <input type="number" 
                 id="edit_weight_${typeId}"
                 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
                 min="0" max="100" step="1" value="${Math.round(parseFloat(type.weight) || 0)}"
                 oninput="editUpdateAssessmentType('${term}', ${index}, 'weight', this.value)"
                 onkeypress="return event.charCode >= 48 && event.charCode <= 57">
        </div>
        <button type="button" onclick="editRemoveAssessmentType('${term}', ${index})" class="ml-2 text-red-600 hover:text-red-700 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
          <i data-lucide="trash-2" class="w-4 h-4"></i>
        </button>
      </div>
    `;
    
    container.appendChild(typeElement);
  });
  
  lucide.createIcons();
}

function editUpdateProgressBars() {
  // Update midterm progress bar
  const midtermTotal = editAssessmentTypes.midterm.reduce((sum, type) => sum + parseFloat(type.weight || 0), 0);
  document.getElementById('editMidtermTotalWeight').textContent = `${Math.round(midtermTotal)}%`;
  editRenderProgressBar('editMidtermProgressContainer', editAssessmentTypes.midterm, 'editMidtermOutput', 'editMidtermError');
  
  // Update final progress bar
  const finalTotal = editAssessmentTypes.final.reduce((sum, type) => sum + parseFloat(type.weight || 0), 0);
  document.getElementById('editFinalTotalWeight').textContent = `${Math.round(finalTotal)}%`;
  editRenderProgressBar('editFinalProgressContainer', editAssessmentTypes.final, 'editFinalOutput', 'editFinalError');
}

function editRenderProgressBar(containerId, assessmentTypes, outputId, errorId) {
  const container = document.getElementById(containerId);
  const output = document.getElementById(outputId);
  const error = document.getElementById(errorId);
  
  container.innerHTML = '';
  output.textContent = '';
  error.textContent = '';
  
  const total = assessmentTypes.reduce((sum, type) => sum + parseFloat(type.weight || 0), 0);
  
  if (total > 100) {
    error.innerHTML = '<span class="text-red-600 font-medium">Total weight exceeds 100%</span>';
    return;
  }
  
  if (total < 100) {
    error.innerHTML = '<span class="text-yellow-600 font-medium">Total weight is less than 100%</span>';
  }
  
  let currentPosition = 0;
  assessmentTypes.forEach((type, index) => {
    const width = (parseFloat(type.weight || 0) / 100) * 100;
    const colorIndex = index % editColors.length;
    const color = editColors[colorIndex];
    
    const segment = document.createElement('div');
    segment.className = `segment assessment-${color}`;
    segment.style.left = `${currentPosition}%`;
    segment.style.width = `${width}%`;
    
    container.appendChild(segment);
    
    currentPosition += width;
  });
  
  output.textContent = assessmentTypes.map(type => `${type.name}: ${type.weight}%`).join(' | ');
}

function editSaveSubject() {
  // Validate weights
  const midtermTotal = editAssessmentTypes.midterm.reduce((sum, type) => sum + parseFloat(type.weight || 0), 0);
  const finalTotal = editAssessmentTypes.final.reduce((sum, type) => sum + parseFloat(type.weight || 0), 0);
  
  if (Math.round(midtermTotal) !== 100) {
    alert(`Midterm total must equal 100%. Current total: ${Math.round(midtermTotal)}%`);
    return;
  }
  
  if (Math.round(finalTotal) !== 100) {
    alert(`Final total must equal 100%. Current total: ${Math.round(finalTotal)}%`);
    return;
  }
  
  // Create form dynamically
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `/subjects/${@json($classSectionModel->subject->id)}`;
  form.style.display = 'none';
  
  // Add CSRF token
  const csrfToken = document.createElement('input');
  csrfToken.type = 'hidden';
  csrfToken.name = '_token';
  csrfToken.value = '{{ csrf_token() }}';
  form.appendChild(csrfToken);
  
  // Add method override
  const methodOverride = document.createElement('input');
  methodOverride.type = 'hidden';
  methodOverride.name = '_method';
  methodOverride.value = 'PUT';
  form.appendChild(methodOverride);
  
  // Add form data
  const formData = {
    'code': document.getElementById('edit_code').value,
    'title': document.getElementById('edit_title').value,
    'units': document.getElementById('edit_units').value,
    'grading_type': document.querySelector('input[name="edit_grading_type"]:checked').value,
    'midterm_weight': document.querySelector('input[name="edit_grading_type"]:checked').value === 'custom' ? document.getElementById('edit_midterm_weight').value : 50,
    'final_weight': document.querySelector('input[name="edit_grading_type"]:checked').value === 'custom' ? document.getElementById('edit_final_weight').value : 50,
    'assessment_types': JSON.stringify(editAssessmentTypes)
  };
  
  // Add each form field
  Object.keys(formData).forEach(key => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = key;
    input.value = formData[key];
    form.appendChild(input);
  });
  
  // Submit form
  document.body.appendChild(form);
  form.submit();
}

// Grading type change handler
document.addEventListener('DOMContentLoaded', function() {
  const gradingTypeRadios = document.querySelectorAll('input[name="edit_grading_type"]');
  gradingTypeRadios.forEach(radio => {
    radio.addEventListener('change', function() {
      const customWeights = document.getElementById('editCustomWeights');
      if (this.value === 'custom') {
        customWeights.classList.remove('hidden');
      } else {
        customWeights.classList.add('hidden');
      }
    });
  });
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // --- FILTER ---
  const riskFilter = document.getElementById('riskFilter');
  if (riskFilter) {
    riskFilter.addEventListener('change', function() {
      const value = this.value;
      document.querySelectorAll('#studentsTable tbody tr').forEach(row => {
        let riskCell = row.querySelector('.ml-risk-indicator .risk-badges');
        let riskText = riskCell ? riskCell.textContent.toLowerCase() : '';
        if (value === 'all') {
          row.style.display = '';
        } else if (value === 'high') {
          row.style.display = riskText.includes('at risk') ? '' : 'none';
        } else if (value === 'low') {
          row.style.display = riskText.includes('low risk') ? '' : 'none';
        } else if (value === 'safe') {
          row.style.display = (riskText.includes('safe') || riskText.includes('not at risk')) ? '' : 'none';
        }
      });
    });
  }
  // --- SORT ---
  let sortDirection = { name: 1, grade: 1, risk: 1, absences: 1 };
  document.querySelectorAll('.sort-header').forEach(header => {
    header.addEventListener('click', function() {
      const sortKey = this.getAttribute('data-sort');
      const tbody = document.querySelector('#studentsTable tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      rows.sort((a, b) => {
        if (sortKey === 'name') {
          const nameA = a.querySelector('.student-name-cell').textContent.trim().toLowerCase();
          const nameB = b.querySelector('.student-name-cell').textContent.trim().toLowerCase();
          return nameA.localeCompare(nameB) * sortDirection.name;
        } else if (sortKey === 'grade') {
          // Use overall grade if available, else 0
          const gradeA = parseFloat((a.querySelector('.flex .text-green-600, .flex .text-green-400')||{}).textContent)||0;
          const gradeB = parseFloat((b.querySelector('.flex .text-green-600, .flex .text-green-400')||{}).textContent)||0;
          return (gradeA - gradeB) * sortDirection.grade;
        } else if (sortKey === 'risk') {
          // High risk = 2, low risk = 1, safe = 0
          function riskScore(row) {
            let riskCell = row.querySelector('.ml-risk-indicator .risk-badges');
            let riskText = riskCell ? riskCell.textContent.toLowerCase() : '';
            if (riskText.includes('at risk')) return 2;
            if (riskText.includes('low risk')) return 1;
            return 0;
          }
          return (riskScore(a) - riskScore(b)) * sortDirection.risk;
        } else if (sortKey === 'absences') {
          // Extract absence count from the absence cell
          function absenceCount(row) {
            // Find the absence cell by looking for the red absence number
            const absenceCell = row.querySelector('td .text-red-600, td .text-red-400');
            if (!absenceCell) return 0;
            const text = absenceCell.textContent.trim();
            const match = text.match(/(\d+)/);
            return match ? parseInt(match[1]) : 0;
          }
          return (absenceCount(a) - absenceCount(b)) * sortDirection.absences;
        }
        return 0;
      });
      // Toggle direction
      sortDirection[sortKey] *= -1;
      // Re-append rows
      rows.forEach(row => tbody.appendChild(row));
    });
  });
});
</script>
@endsection 