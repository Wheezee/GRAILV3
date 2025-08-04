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
    <button onclick="openCreateAssessmentModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Add Assessment
    </button>
    <a href="{{ route('grading.system', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
      Back
    </a>
  </div>
</div>

<!-- Assessment Tabs -->
@if($assessments->count() > 0)
<div class="mb-6">
  <div class="border-b border-gray-200 dark:border-gray-700">
    <nav class="-mb-px flex space-x-8 overflow-x-auto">
      @foreach($assessments as $index => $assessment)
        @php
          $hasAtRisk = false;
          if ($assessment->warning_score !== null) {
            foreach ($students as $student) {
              $score = $student->assessmentScores()->where('assessment_id', $assessment->id)->first();
              if ($score && $score->score !== null && $score->score < $assessment->warning_score) {
                $hasAtRisk = true;
                break;
              }
            }
          }
        @endphp
        <div class="flex items-center gap-2 group">
          <button onclick="switchTab({{ $index }})" 
                  class="tab-button whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm transition-colors {{ $index === 0 ? 'border-red-500 text-red-600 dark:text-red-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}"
                  data-assessment-id="{{ $assessment->id }}">
            {{ $assessment->name }}
          </button>
          <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
            <button onclick="openEditAssessmentModal({{ $assessment->id }}, '{{ $assessment->name }}', {{ $assessment->max_score }}, {{ $assessment->passing_score ?? 'null' }}, '{{ $assessment->warning_score ?? 'null' }}', '{{ $assessment->due_date }}', '{{ $assessment->description }}', {{ $assessment->scores()->count() }})" 
                    class="p-1 text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors" 
                    title="Edit Assessment">
              <i data-lucide="edit-3" class="w-3 h-3"></i>
            </button>
            <button onclick="openDeleteAssessmentModal({{ $assessment->id }}, '{{ $assessment->name }}')" 
                    class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors" 
                    title="Delete Assessment">
              <i data-lucide="trash-2" class="w-3 h-3"></i>
            </button>
          </div>
        </div>
      @endforeach
    </nav>
  </div>
</div>
@else
<div class="mb-6 text-center py-8">
  <div class="text-gray-400 dark:text-gray-500 mb-4">
    <i data-lucide="clipboard-list" class="w-16 h-16 mx-auto"></i>
  </div>
  <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No assessments found</h3>
  <p class="text-gray-500 dark:text-gray-400 mb-6">Create assessments to start grading</p>
  <button onclick="openCreateAssessmentModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
    <i data-lucide="plus" class="w-4 h-4"></i>
    Create First Assessment
  </button>
</div>
@endif

<!-- Students Table -->
@if($assessments->count() > 0)
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
                $showRisk = $assessment->warning_score !== null && $score && $score->score !== null && $score->score < $assessment->warning_score;
              @endphp
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-center assessment-column group" data-assessment-id="{{ $assessment->id }}" style="display: {{ $loop->first ? 'table-cell' : 'none' }};">
                 <div class="flex flex-col items-center gap-2">
                  <div class="flex items-center gap-2">
                    <div class="relative">
                      <input type="number" 
                             class="grade-input w-20 px-2 py-1 text-sm border border-gray-300 dark:border-gray-600 rounded focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white transition-colors" 
                             placeholder="Score"
                             min="0" 
                             max="{{ $assessment->max_score }}" 
                             step="0.01"
                             value="{{ $currentScore }}"
                             data-student-id="{{ $student->id }}"
                             data-assessment-id="{{ $assessment->id }}"
                             data-max-score="{{ $assessment->max_score }}"
                             title="Maximum score: {{ $assessment->max_score }}">
                      <div class="absolute -top-6 left-0 text-xs text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity">
                        Max: {{ $assessment->max_score }}
                      </div>
                    </div>
                    <span class="text-xs text-gray-500">/ {{ $assessment->max_score }}</span>
                    @if($showRisk)
                      <span class="ml-1 text-yellow-600 dark:text-yellow-400" title="At risk">&#9888;</span>
                    @endif
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
                    <div class="text-xs text-gray-500 percentage-display">
                      {{ number_format(($currentScore / $assessment->max_score) * 100, 1) }}%
                    </div>
                  @else
                    <div class="text-xs text-gray-500 percentage-display" style="display: none;"></div>
                  @endif
                  <div class="save-status text-xs text-gray-400" style="display: none;">
                    <i data-lucide="check" class="w-3 h-3 inline text-green-500"></i> Saved
                  </div>
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
  
  <!-- Save Button at Bottom -->
  @if($students->count() > 0)
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700">
      <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600 dark:text-gray-400">
          <span class="font-medium">{{ $students->count() }}</span> students
        </div>
        <button onclick="saveAllGrades()" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
          <i data-lucide="save" class="w-4 h-4"></i>
          Save All Grades
        </button>
      </div>
    </div>
  @endif
</div>
@endif

<!-- Create Assessment Modal -->
<div id="createAssessmentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-2 sm:p-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl shadow-2xl w-full max-w-full sm:max-w-md mx-2 sm:mx-4 transform transition-all overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between p-3 sm:p-6 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">Create Assessment</h3>
      <button onclick="closeCreateAssessmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    <form method="POST" action="{{ route('assessments.store', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id]) }}" class="p-3 sm:p-6">
      @csrf
      <div class="space-y-4">
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assessment Name</label>
          <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., Quiz 1, Lab Report 1">
        </div>
        <div>
          <label for="max_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maximum Score</label>
          <input type="number" id="max_score" name="max_score" required min="0.01" max="999.99" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., 100.00">
        </div>
        <div>
          <label for="passing_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Passing Score (Optional)</label>
          <input type="number" id="passing_score" name="passing_score" min="0.01" max="999.99" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., 25.00 (50% of 50 items)">
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Score that represents 75% for grading purposes</p>
        </div>
        <div>
          <label for="warning_score" class="block text-sm font-medium text-yellow-700 dark:text-yellow-400 mb-2">Warning Score (Optional)</label>
          <input type="number" id="warning_score" name="warning_score" min="0.01" max="999.99" step="0.01" class="w-full px-3 py-2 border border-yellow-300 dark:border-yellow-600 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., 15.00 (at risk)">
          <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Scores below this will show a warning for students at risk</p>
        </div>
        <div>
          <label for="due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Due Date (Optional)</label>
          <input type="date" id="due_date" name="due_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description (Optional)</label>
          <textarea id="description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Brief description of the assessment"></textarea>
        </div>
      </div>
      <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" onclick="closeCreateAssessmentModal()" class="w-full sm:flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">Cancel</button>
        <button type="submit" class="w-full sm:flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">Create Assessment</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Assessment Modal -->
<div id="editAssessmentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-2 sm:p-4">
  <div class="bg-white dark:bg-gray-800 rounded-lg sm:rounded-xl shadow-2xl w-full max-w-full sm:max-w-md mx-2 sm:mx-4 transform transition-all overflow-y-auto max-h-[90vh]">
    <div class="flex items-center justify-between p-3 sm:p-6 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">Edit Assessment</h3>
      <button onclick="closeEditAssessmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
         <form id="editAssessmentForm" method="POST" action="{{ route('assessments.update', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id, 'assessment' => ':id']) }}" class="p-3 sm:p-6">
      @csrf
      @method('PUT')
      <div class="space-y-4">
        <div>
          <label for="edit_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Assessment Name</label>
          <input type="text" id="edit_name" name="name" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label for="edit_max_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Maximum Score</label>
          <input type="number" id="edit_max_score" name="max_score" required min="0.01" max="999.99" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label for="edit_passing_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Passing Score (Optional)</label>
          <input type="number" id="edit_passing_score" name="passing_score" min="0.01" max="999.99" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., 25.00 (50% of 50 items)">
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Score that represents 75% for grading purposes</p>
        </div>
        <div>
          <label for="edit_warning_score" class="block text-sm font-medium text-yellow-700 dark:text-yellow-400 mb-2">Warning Score (Optional)</label>
          <input type="number" id="edit_warning_score" name="warning_score" min="0.01" max="999.99" step="0.01" class="w-full px-3 py-2 border border-yellow-300 dark:border-yellow-600 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., 15.00 (at risk)">
          <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">Scores below this will show a warning for students at risk</p>
        </div>
        <div>
          <label for="edit_due_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Due Date (Optional)</label>
          <input type="date" id="edit_due_date" name="due_date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label for="edit_description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description (Optional)</label>
          <textarea id="edit_description" name="description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white"></textarea>
        </div>
      </div>
      <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" onclick="closeEditAssessmentModal()" class="w-full sm:flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">Cancel</button>
        <button type="submit" class="w-full sm:flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Delete Assessment Modal -->
<div id="deleteAssessmentModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Delete Assessment</h3>
      <button onclick="closeDeleteAssessmentModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    <div class="p-6 text-center">
      <i data-lucide="alert-triangle" class="w-16 h-16 text-red-500 dark:text-red-400 mx-auto mb-4"></i>
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">Are you sure you want to delete this assessment?</h3>
             <p class="text-gray-500 dark:text-gray-400 mb-6">This action cannot be undone. All scores for this assessment will also be deleted.</p>
             <form id="deleteAssessmentForm" method="POST" action="{{ route('assessments.destroy', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id, 'assessment' => ':id']) }}" class="flex gap-3">
        @csrf
        @method('DELETE')
        <button type="button" onclick="closeDeleteAssessmentModal()" class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">Cancel</button>
        <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">Delete Assessment</button>
      </form>
    </div>
  </div>
</div>

<!-- Save Form for Manual Save -->
@if($assessments->count() > 0)
<form id="saveGradesForm" method="POST" action="{{ route('assessments.scores.save', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id, 'assessment' => $assessments->first()->id]) }}" style="display: none;">
  @csrf
  <input type="hidden" id="gradesData" name="grades_data">
</form>
@endif

<script>
let currentTab = 0;
const assessments = @json($assessments);
let saveTimeout = null;

// Function to get storage key for this page
function getStorageKey() {
  return `assessment_tab_${@json($classSection->subject->id)}_${@json($classSection->id)}_${@json($term)}_${@json($assessmentType->id)}`;
}

// Function to save current tab to localStorage
function saveCurrentTab(index) {
  localStorage.setItem(getStorageKey(), index.toString());
}

// Function to get saved tab from localStorage
function getSavedTab() {
  const saved = localStorage.getItem(getStorageKey());
  return saved !== null ? parseInt(saved) : 0;
}

function switchTab(index) {
  // Check if there are assessments
  if (assessments.length === 0) {
    return;
  }

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
  // Save the current tab to localStorage
  saveCurrentTab(index);
}

function saveGrade(studentId, assessmentId, score, isLate) {
  const data = {
    student_id: studentId,
    assessment_id: assessmentId,
    score: score,
    is_late: isLate
  };

  fetch(`{{ route('assessments.scores.save', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id, 'assessment' => ':id']) }}`.replace(':id', assessmentId), {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ grades_data: JSON.stringify([data]) })
  })
  .then(response => {
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showSaveStatus(studentId, assessmentId, true);
    }
    // Silently ignore errors since grades are being saved
  })
  .catch(error => {
    console.error('Error saving grade:', error);
    // Silently ignore network errors since grades are being saved
  });
}

function showSaveStatus(studentId, assessmentId, success, message = '') {
  const cell = document.querySelector(`[data-student-id="${studentId}"][data-assessment-id="${assessmentId}"]`).closest('td');
  const statusElement = cell.querySelector('.save-status');
  
  if (success) {
    statusElement.innerHTML = '<i data-lucide="check" class="w-3 h-3 inline text-green-500"></i> Saved';
    statusElement.style.color = '#10B981';
  } else {
    const errorMessage = message || 'Error';
    statusElement.innerHTML = `<i data-lucide="alert-circle" class="w-3 h-3 inline text-red-500"></i> ${errorMessage}`;
    statusElement.style.color = '#EF4444';
  }
  
  statusElement.style.display = 'block';
  
  setTimeout(() => {
    statusElement.style.display = 'none';
  }, 3000);
}

function debounceSave(studentId, assessmentId, score, isLate) {
  // Clear existing timeout
  if (saveTimeout) {
    clearTimeout(saveTimeout);
  }
  
  // Set new timeout for auto-save
  saveTimeout = setTimeout(() => {
    saveGrade(studentId, assessmentId, score, isLate);
  }, 1000); // Save after 1 second of no typing
}

function saveAllGrades() {
  // Check if there are assessments
  if (assessments.length === 0) {
    alert('No assessments to save grades for.');
    return;
  }

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
  if (form) {
    const currentAssessmentId = assessments[currentTab].id;
    form.action = `{{ route('assessments.scores.save', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id, 'assessment' => ':id']) }}`.replace(':id', currentAssessmentId);
    
    document.getElementById('gradesData').value = JSON.stringify(gradesData);
    form.submit();
  }
}

function openCreateAssessmentModal() {
  document.getElementById('createAssessmentModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeCreateAssessmentModal() {
  document.getElementById('createAssessmentModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  document.getElementById('createAssessmentModal').querySelector('form').reset();
}

function openEditAssessmentModal(id, name, max_score, passing_score, warning_score, due_date, description, scoresCount = 0) {
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_max_score').value = max_score;
  document.getElementById('edit_passing_score').value = passing_score || '';
  document.getElementById('edit_warning_score').value = warning_score || '';
  document.getElementById('edit_due_date').value = due_date;
  document.getElementById('edit_description').value = description;
  
  // Show warning if assessment has scores
  const maxScoreField = document.getElementById('edit_max_score');
  const warningDiv = document.getElementById('edit_max_score_warning');
  
  if (scoresCount > 0) {
    if (!warningDiv) {
      const warning = document.createElement('div');
      warning.id = 'edit_max_score_warning';
      warning.className = 'text-sm text-amber-600 dark:text-amber-400 mt-1';
      warning.innerHTML = '<i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i> Changing max score may affect existing grades';
      maxScoreField.parentNode.appendChild(warning);
    } else {
      warningDiv.style.display = 'block';
    }
  } else if (warningDiv) {
    warningDiv.style.display = 'none';
  }
  
  // Update form action with the correct assessment ID
  const form = document.getElementById('editAssessmentForm');
  form.action = `{{ route('assessments.update', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id, 'assessment' => ':id']) }}`.replace(':id', id);
  
  document.getElementById('editAssessmentModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeEditAssessmentModal() {
  document.getElementById('editAssessmentModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  document.getElementById('editAssessmentModal').querySelector('form').reset();
}

function openDeleteAssessmentModal(id, name) {
  document.getElementById('deleteAssessmentModal').dataset.assessmentId = id;
  document.getElementById('deleteAssessmentModal').querySelector('h3').textContent = `Are you sure you want to delete "${name}"?`;
  
  // Update form action with the correct assessment ID
  const form = document.getElementById('deleteAssessmentForm');
  form.action = `{{ route('assessments.destroy', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => $term, 'assessmentType' => $assessmentType->id, 'assessment' => ':id']) }}`.replace(':id', id);
  
  document.getElementById('deleteAssessmentModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeDeleteAssessmentModal() {
  document.getElementById('deleteAssessmentModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  document.getElementById('deleteAssessmentModal').dataset.assessmentId = '';
  document.getElementById('deleteAssessmentModal').querySelector('form').reset();
}

// Auto-calculate percentage and auto-save when score changes
document.addEventListener('DOMContentLoaded', function() {
  // Close modals when clicking outside
  document.querySelectorAll('#createAssessmentModal, #editAssessmentModal, #deleteAssessmentModal').forEach(modal => {
    modal.addEventListener('click', function(e) {
      if (e.target === this) {
        if (this.id === 'createAssessmentModal') {
          closeCreateAssessmentModal();
        } else if (this.id === 'editAssessmentModal') {
          closeEditAssessmentModal();
        } else if (this.id === 'deleteAssessmentModal') {
          closeDeleteAssessmentModal();
        }
      }
    });
  });
  
  // Close modals with Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (!document.getElementById('createAssessmentModal').classList.contains('hidden')) {
        closeCreateAssessmentModal();
      } else if (!document.getElementById('editAssessmentModal').classList.contains('hidden')) {
        closeEditAssessmentModal();
      } else if (!document.getElementById('deleteAssessmentModal').classList.contains('hidden')) {
        closeDeleteAssessmentModal();
      }
    }
  });
  document.querySelectorAll('.grade-input').forEach(input => {
    input.addEventListener('input', function() {
      const studentId = this.dataset.studentId;
      const assessmentId = this.dataset.assessmentId;
      const maxScore = parseFloat(this.getAttribute('max'));
      let score = parseFloat(this.value) || 0;
      
      // Validate score against maximum
      if (score > maxScore) {
        score = maxScore;
        this.value = maxScore;
        this.classList.add('border-red-500', 'bg-red-50', 'dark:bg-red-900/20');
        setTimeout(() => {
          this.classList.remove('border-red-500', 'bg-red-50', 'dark:bg-red-900/20');
        }, 1000);
      }
      
      const percentage = maxScore > 0 ? (score / maxScore) * 100 : 0;
      
      // Update percentage display
      const percentageElement = this.closest('td').querySelector('.percentage-display');
      if (percentageElement) {
        if (this.value !== '') {
          percentageElement.textContent = percentage.toFixed(1) + '%';
          percentageElement.style.display = 'block';
        } else {
          percentageElement.style.display = 'none';
        }
      }
      
      // Auto-save after debounce
      if (this.value !== '') {
        const lateCheckbox = document.querySelector(`.late-checkbox[data-student-id="${studentId}"][data-assessment-id="${assessmentId}"]`);
        const isLate = lateCheckbox ? lateCheckbox.checked : false;
        debounceSave(studentId, assessmentId, score, isLate);
      }
    });
    
    // Add blur event to validate on focus out
    input.addEventListener('blur', function() {
      const maxScore = parseFloat(this.getAttribute('max'));
      const currentValue = parseFloat(this.value) || 0;
      
      if (currentValue > maxScore) {
        this.value = maxScore;
        this.classList.add('border-red-500', 'bg-red-50', 'dark:bg-red-900/20');
        setTimeout(() => {
          this.classList.remove('border-red-500', 'bg-red-50', 'dark:bg-red-900/20');
        }, 1000);
      }
    });
  });
  
  // Auto-save when late checkbox changes
  document.querySelectorAll('.late-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function() {
      const studentId = this.dataset.studentId;
      const assessmentId = this.dataset.assessmentId;
      const scoreInput = document.querySelector(`.grade-input[data-student-id="${studentId}"][data-assessment-id="${assessmentId}"]`);
      const score = parseFloat(scoreInput.value) || 0;
      const isLate = this.checked;
      
      if (scoreInput.value !== '') {
        debounceSave(studentId, assessmentId, score, isLate);
      }
    });
  });

  // Restore last active tab on page load
  const savedTabIndex = getSavedTab();
  if (savedTabIndex >= 0 && savedTabIndex < assessments.length) {
    switchTab(savedTabIndex);
  } else {
    // Fallback to default if saved tab is invalid or not found
    switchTab(0);
  }
});

lucide.createIcons();
</script>
@endsection 