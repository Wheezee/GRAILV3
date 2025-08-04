@extends('layouts.app')

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
      <a href="{{ route('subjects.classes', $classSection->subject->id) }}" class="hover:text-red-600 dark:hover:text-red-400 max-w-[120px] sm:max-w-none truncate">
        {{ $classSection->subject->code }} - {{ $classSection->subject->title }}
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <a href="{{ route('grading.system', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term]) }}" class="hover:text-red-600 dark:hover:text-red-400 max-w-[120px] sm:max-w-none truncate">
        {{ $classSection->section }}
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">{{ $assessmentType->name }}</span>
    </li>
  </ol>
</nav>

@if (session('success'))
  <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
    <div class="flex items-center gap-3">
      <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
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

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
  <div>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $assessmentType->name }} - Grading</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $classSection->section }} - {{ $classSection->subject->code }} - {{ $classSection->subject->title }}</p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Weight: {{ $assessmentType->weight }}% | Term: {{ ucfirst($term) }}</p>
  </div>
  <div class="flex gap-2">
    <button onclick="saveAllGrades()" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="save" class="w-4 h-4"></i>
      Save All Grades
    </button>
    <a href="{{ route('assessments.index', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
      Back
    </a>
  </div>
</div>

<!-- Assessment Tabs -->
<div class="mb-6">
  <div class="border-b border-gray-200 dark:border-gray-700">
    <nav class="-mb-px flex space-x-8 overflow-x-auto">
      @foreach($assessments as $index => $assessment)
        <button onclick="switchTab({{ $index }})" 
                class="tab-button whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors {{ $index === 0 ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                data-assessment-id="{{ $assessment->id }}">
          {{ $assessment->name }}
        </button>
      @endforeach
    </nav>
  </div>
</div>

<!-- Students Table -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student ID</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
          @foreach($assessments as $assessment)
            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider assessment-column" data-assessment-id="{{ $assessment->id }}" style="display: {{ $loop->first ? 'table-cell' : 'none' }};">
              {{ $assessment->name }}
              <div class="text-xs text-gray-400 mt-1">Max: {{ $assessment->max_score }}</div>
            </th>
          @endforeach
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($students as $student)
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
              {{ $student->student_id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
              {{ $student->first_name }} {{ $student->last_name }}
            </td>
            @foreach($assessments as $assessment)
              @php
                $score = $student->assessmentScores()->where('assessment_id', $assessment->id)->first();
                $currentScore = $score ? $score->score : '';
                $isLate = $score ? $score->is_late : false;
              @endphp
              <td class="px-6 py-4 whitespace-nowrap text-sm text-center assessment-column" data-assessment-id="{{ $assessment->id }}" style="display: {{ $loop->first ? 'table-cell' : 'none' }};">
                <div class="flex flex-col items-center gap-2">
                  <div class="flex items-center gap-2">
                    <input type="number" 
                           class="grade-input w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" 
                           placeholder="Score"
                           min="0" 
                           max="{{ $assessment->max_score }}" 
                           step="0.01"
                           value="{{ $currentScore }}"
                           data-student-id="{{ $student->id }}"
                           data-assessment-id="{{ $assessment->id }}"
                           data-warning-score="{{ $assessment->warning_score }}">
                    <span class="text-xs text-gray-500">/ {{ $assessment->max_score }}</span>
                    <span class="risk-indicator ml-1 text-yellow-600 dark:text-yellow-400 hidden" title="At Risk: Below warning score">&#9888;</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <label class="flex items-center gap-1 text-xs">
                      <input type="checkbox" 
                             class="late-checkbox rounded border-gray-300 dark:border-gray-600 focus:ring-red-500"
                             {{ $isLate ? 'checked' : '' }}
                             data-student-id="{{ $student->id }}"
                             data-assessment-id="{{ $assessment->id }}">
                      <span class="text-gray-600 dark:text-gray-400">Late</span>
                    </label>
                  </div>
                  @if($currentScore)
                    <div class="text-xs text-gray-500">
                      {{ number_format(($currentScore / $assessment->max_score) * 100, 1) }}%
                    </div>
                  @endif
                </div>
              </td>
            @endforeach
          </tr>
        @empty
          <tr>
            <td colspan="{{ 2 + $assessments->count() }}" class="px-6 py-12 text-center">
              <div class="text-gray-400 dark:text-gray-500 mb-4">
                <i data-lucide="users" class="w-16 h-16 mx-auto"></i>
              </div>
              <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No students enrolled</h3>
              <p class="text-gray-500 dark:text-gray-400">Enroll students to start grading</p>
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- Save Form -->
<form id="saveGradesForm" method="POST" action="{{ route('assessments.scores.save', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id, 'assessment' => $assessments->first()->id]) }}" style="display: none;">
  @csrf
  <input type="hidden" id="gradesData" name="grades_data">
</form>

<script>
let currentTab = 0;
const assessments = @json($assessments);

function switchTab(index) {
  // Update tab buttons
  document.querySelectorAll('.tab-button').forEach((button, i) => {
    if (i === index) {
      button.classList.add('border-red-500', 'text-red-600', 'dark:text-red-400');
      button.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'dark:text-gray-400', 'dark:hover:text-gray-300');
    } else {
      button.classList.remove('border-red-500', 'text-red-600', 'dark:text-red-400');
      button.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300', 'dark:text-gray-400', 'dark:hover:text-gray-300');
    }
  });

  // Update table columns
  const assessmentId = assessments[index].id;
  document.querySelectorAll('.assessment-column').forEach(column => {
    if (column.dataset.assessmentId == assessmentId) {
      column.style.display = 'table-cell';
    } else {
      column.style.display = 'none';
    }
  });

  currentTab = index;
}

function saveAllGrades() {
  const gradesData = [];
  
  // Collect all grade data
  document.querySelectorAll('.grade-input').forEach(input => {
    const studentId = input.dataset.studentId;
    const assessmentId = input.dataset.assessmentId;
    const score = input.value;
    
    if (score !== '') {
      const lateCheckbox = document.querySelector(`.late-checkbox[data-student-id="${studentId}"][data-assessment-id="${assessmentId}"]`);
      const isLate = lateCheckbox ? lateCheckbox.checked : false;
      
      gradesData.push({
        student_id: studentId,
        assessment_id: assessmentId,
        score: parseFloat(score),
        is_late: isLate
      });
    }
  });

  // Update form action and submit
  const form = document.getElementById('saveGradesForm');
  const currentAssessmentId = assessments[currentTab].id;
  form.action = `{{ route('assessments.scores.save', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id, 'assessment' => ':id']) }}`.replace(':id', currentAssessmentId);
  
  document.getElementById('gradesData').value = JSON.stringify(gradesData);
  form.submit();
}

// Auto-calculate percentage when score changes
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.grade-input').forEach(input => {
    input.addEventListener('input', function() {
      const score = parseFloat(this.value) || 0;
      const maxScore = parseFloat(this.getAttribute('max'));
      const percentage = maxScore > 0 ? (score / maxScore) * 100 : 0;
      
      // Update percentage display if it exists
      const percentageElement = this.closest('td').querySelector('.text-xs.text-gray-500');
      if (percentageElement && this.value !== '') {
        percentageElement.textContent = percentage.toFixed(1) + '%';
      }
    });
  });
});

lucide.createIcons();
</script>
@endsection 