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
      <a href="{{ route('grading.system', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => 'midterm']) }}" class="hover:text-red-600 dark:hover:text-red-400 max-w-[120px] sm:max-w-none truncate">
        {{ $classSection->section }}
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">Gradebook</span>
    </li>
  </ol>
</nav>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
  <div>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Gradebook - {{ $classSection->section }}</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $classSection->subject->code }} - {{ $classSection->subject->title }}</p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
      @if($gradingStructure)
        Weights: Midterm {{ $gradingStructure->midterm_weight }}% | Final {{ $gradingStructure->final_weight }}%
      @else
        Weights: Midterm 50% | Final 50%
      @endif
    </p>
  </div>
  <div class="flex items-center gap-4">
    <div class="flex items-center gap-2">
      <label for="grading_mode" class="text-sm font-medium text-gray-700 dark:text-gray-300">Grading Mode:</label>
      <select id="grading_mode" class="px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-red-500">
        <option value="percentage">Percentage-Based</option>
        <option value="linear">Linear (1.0–5.0)</option>
        <option value="custom">Custom</option>
      </select>
    </div>
    
    <button id="customize_grading" class="px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm rounded-lg transition-colors hidden">
      <i data-lucide="settings" class="w-4 h-4"></i>
      Customize
    </button>
    

    
    <div id="current_settings" class="text-xs text-gray-500 dark:text-gray-400 hidden">
      <span id="settings_summary"></span>
    </div>
    
    <div class="w-px h-6 bg-gray-300 dark:bg-gray-600"></div>
    
    <a href="{{ route('grading.system', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => 'midterm']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
      Back to Grading
    </a>
    <!-- Export Button -->
    <button
      class="inline-flex items-center gap-2 px-4 py-2 bg-red-500 text-white font-medium rounded-lg transition-colors hover:bg-red-600"
      onclick="document.getElementById('exportModal').classList.remove('hidden')"
    >
      <i data-lucide="download" class="w-4 h-4"></i>
      Export
    </button>
  </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-80">
    <h3 class="text-lg font-bold mb-4">Export Gradebook</h3>
    <form method="GET" action="{{ route('gradebook.export', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id]) }}">
      <input type="hidden" name="format" id="exportFormat" value="">
      <button type="button" class="w-full mb-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        onclick="document.getElementById('exportFormat').value='pdf'; this.form.submit();">
        PDF
      </button>
      <button type="button" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
        onclick="document.getElementById('exportFormat').value='excel'; this.form.submit();">
        Excel
      </button>
      <button type="button" class="w-full mt-4 px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400"
        onclick="document.getElementById('exportModal').classList.add('hidden')">
        Cancel
      </button>
    </form>
  </div>
</div>

<!-- Grading Customization Modal -->
<div id="gradingModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50 hidden">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-96 max-h-[90vh] overflow-y-auto">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Customize Grading Parameters</h3>
      <button onclick="closeGradingModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    
    <form id="gradingForm">
      <div class="space-y-4">
        <!-- Max Score -->
        <div>
          <label for="max_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Maximum Score (%)
          </label>
          <input type="number" id="max_score" name="max_score" min="50" max="100" value="95" 
                 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-red-500">
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Scores above this will be treated as this value (e.g., 95% means 100% becomes 95%)</p>
        </div>
        

        
        <!-- Passing Score -->
        <div>
          <label for="passing_score" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Passing Score (%)
          </label>
          <input type="number" id="passing_score" name="passing_score" min="50" max="90" value="75" 
                 class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-red-500">
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Minimum score required to pass</p>
        </div>
        

        
        <!-- Custom Formula (for custom method) -->
        <div id="custom_formula_section" class="hidden">
          <label for="custom_formula" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
            Custom Formula
          </label>
          <select id="custom_formula" name="custom_formula" 
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-red-500">
            <option value="inverse_linear">Inverse Linear (100% = 1.0, 75% = 3.0)</option>
            <option value="exponential">Exponential Curve</option>
            <option value="step">Step-Based Grading</option>
          </select>
          <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Choose the formula for custom grading</p>
        </div>
        
      </div>
      
      <div class="flex gap-3 mt-6">
        <button type="button" onclick="applyGradingSettings()" 
                class="flex-1 px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-colors">
          Apply Settings
        </button>
        <button type="button" onclick="closeGradingModal()" 
                class="flex-1 px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-lg transition-colors">
          Cancel
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Gradebook Table -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-x-auto">
  <table class="w-full min-w-[1400px]">
    <thead>
      <tr>
        <th rowspan="3" class="px-6 py-3 text-left bg-white dark:bg-gray-800 sticky left-0 top-0 z-20 border-b border-gray-200 dark:border-gray-700">
          <div class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Students</div>
        </th>
        
        <!-- Midterm Section -->
        @if($midtermAssessmentTypes->count() > 0)
          @php
            $midtermColspan = 0;
            foreach($midtermAssessmentTypes as $type) {
              $midtermColspan += $assessments['midterm'][$type->id]['assessments']->count() ?: 1;
            }
          @endphp
          <th colspan="{{ $midtermColspan }}" class="px-6 py-3 text-center bg-blue-50 dark:bg-blue-900/20 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">Midterm</div>
            <div class="text-xs text-blue-600 dark:text-blue-400">
              @if($gradingStructure)
                Weight: {{ $gradingStructure->midterm_weight }}%
              @else
                Weight: 50%
              @endif
            </div>
          </th>
        @endif
        
        <!-- Final Section -->
        @if($finalAssessmentTypes->count() > 0)
          @php
            $finalColspan = 0;
            foreach($finalAssessmentTypes as $type) {
              $finalColspan += $assessments['final'][$type->id]['assessments']->count() ?: 1;
            }
          @endphp
          <th colspan="{{ $finalColspan }}" class="px-6 py-3 text-center bg-green-50 dark:bg-green-900/20 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-green-900 dark:text-green-100">Final</div>
            <div class="text-xs text-green-600 dark:text-green-400">
              @if($gradingStructure)
                Weight: {{ $gradingStructure->final_weight }}%
              @else
                Weight: 50%
              @endif
            </div>
          </th>
        @endif
        
        <th rowspan="3" class="px-6 py-3 text-center bg-white dark:bg-gray-800 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Midterm Grade</div>
        </th>
        <th rowspan="3" class="px-6 py-3 text-center bg-white dark:bg-gray-800 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Final Grade</div>
        </th>
        <th rowspan="3" class="px-6 py-3 text-center bg-white dark:bg-gray-800 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Overall Grade</div>
        </th>
      </tr>
      <tr>
        <!-- Midterm Assessment Types -->
        @foreach($midtermAssessmentTypes as $assessmentType)
          @php
            $assessmentCount = $assessments['midterm'][$assessmentType->id]['assessments']->count();
            $colspan = max($assessmentCount, 1);
          @endphp
          <th colspan="{{ $colspan }}" class="px-4 py-2 text-center bg-blue-50 dark:bg-blue-900/20 sticky top-12 z-10 border-b border-gray-200 dark:border-gray-700">
            <div class="text-xs font-medium text-blue-900 dark:text-blue-100">{{ $assessmentType->name }}</div>
            <div class="text-xs text-blue-600 dark:text-blue-400">Weight: {{ $assessmentType->weight }}%</div>
          </th>
        @endforeach
        
        <!-- Final Assessment Types -->
        @foreach($finalAssessmentTypes as $assessmentType)
          @php
            $assessmentCount = $assessments['final'][$assessmentType->id]['assessments']->count();
            $colspan = max($assessmentCount, 1);
          @endphp
          <th colspan="{{ $colspan }}" class="px-4 py-2 text-center bg-green-50 dark:bg-green-900/20 sticky top-12 z-10 border-b border-gray-200 dark:border-gray-700">
            <div class="text-xs font-medium text-green-900 dark:text-green-100">{{ $assessmentType->name }}</div>
            <div class="text-xs text-green-600 dark:text-green-400">Weight: {{ $assessmentType->weight }}%</div>
          </th>
        @endforeach
      </tr>
      <tr>
        <!-- Midterm Assessments -->
        @foreach($midtermAssessmentTypes as $assessmentType)
          @php
            $assessmentList = $assessments['midterm'][$assessmentType->id]['assessments'];
          @endphp
          @if($assessmentList->count() > 0)
            @foreach($assessmentList as $assessment)
              <th class="px-4 py-2 text-center bg-blue-50 dark:bg-blue-900/20 sticky top-20 z-10 border-b border-gray-200 dark:border-gray-700">
                <a href="{{ route('assessments.index', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => 'midterm', 'assessmentType' => $assessmentType->id]) }}" 
                   class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                  {{ $assessment->name }}
                </a>
                <div class="text-xs text-blue-500 dark:text-blue-400">Max: {{ $assessment->max_score }}</div>
              </th>
            @endforeach
          @else
            <th class="px-4 py-2 text-center bg-blue-50 dark:bg-blue-900/20 sticky top-20 z-10 border-b border-gray-200 dark:border-gray-700">
              <div class="text-xs text-blue-500 dark:text-blue-400">No Assessments</div>
            </th>
          @endif
        @endforeach
        
        <!-- Final Assessments -->
        @foreach($finalAssessmentTypes as $assessmentType)
          @php
            $assessmentList = $assessments['final'][$assessmentType->id]['assessments'];
          @endphp
          @if($assessmentList->count() > 0)
            @foreach($assessmentList as $assessment)
              <th class="px-4 py-2 text-center bg-green-50 dark:bg-green-900/20 sticky top-20 z-10 border-b border-gray-200 dark:border-gray-700">
                <a href="{{ route('assessments.index', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => 'final', 'assessmentType' => $assessmentType->id]) }}" 
                   class="text-green-600 dark:text-green-400 hover:underline text-xs">
                  {{ $assessment->name }}
                </a>
                <div class="text-xs text-green-500 dark:text-green-400">Max: {{ $assessment->max_score }}</div>
              </th>
            @endforeach
          @else
            <th class="px-4 py-2 text-center bg-green-50 dark:bg-green-900/20 sticky top-20 z-10 border-b border-gray-200 dark:border-gray-700">
              <div class="text-xs text-green-500 dark:text-green-400">No Assessments</div>
            </th>
          @endif
        @endforeach
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
      @forelse($students as $student)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
          <td class="px-6 py-4 bg-white dark:bg-gray-800 sticky left-0 z-10">
            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $student->last_name }}, {{ $student->first_name }}</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->student_id }}</div>
          </td>
          
          <!-- Midterm Scores -->
          @foreach($midtermAssessmentTypes as $assessmentType)
            @php
              $assessmentList = $assessments['midterm'][$assessmentType->id]['assessments'];
            @endphp
            @if($assessmentList->count() > 0)
              @foreach($assessmentList as $assessment)
                @php
                  $score = $student->assessmentScores()->where('assessment_id', $assessment->id)->first();
                  $displayScore = $score && $score->score !== null ? $score->score : '--';
                  $percentage = $score && $score->percentage_score !== null ? $score->percentage_score : null;
                  $showWarning = $assessment->warning_score !== null && $score && $score->score !== null && $score->score < $assessment->warning_score;
                @endphp
                <td class="px-4 py-3 text-center hover:bg-blue-100 dark:hover:bg-blue-900/20 transition-colors">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $displayScore }}
                    @if($showWarning)
                      <span class="ml-1 text-yellow-600 dark:text-yellow-400" title="At Risk: Below warning score">
                        &#9888;
                      </span>
                    @endif
                  </div>
                  @if($percentage !== null)
                    <div class="text-xs text-blue-600 dark:text-blue-400">
                      {{ $percentage }}%
                    </div>
                  @endif
                </td>
              @endforeach
            @else
              <td class="px-4 py-3 text-center hover:bg-blue-100 dark:hover:bg-blue-900/20 transition-colors">
                <div class="text-sm text-gray-500 dark:text-gray-400">--</div>
              </td>
            @endif
          @endforeach
          
          <!-- Final Scores -->
          @foreach($finalAssessmentTypes as $assessmentType)
            @php
              $assessmentList = $assessments['final'][$assessmentType->id]['assessments'];
            @endphp
            @if($assessmentList->count() > 0)
              @foreach($assessmentList as $assessment)
                @php
                  $score = $student->assessmentScores()->where('assessment_id', $assessment->id)->first();
                  $displayScore = $score && $score->score !== null ? $score->score : '--';
                  $percentage = $score && $score->percentage_score !== null ? $score->percentage_score : null;
                  $showWarning = $assessment->warning_score !== null && $score && $score->score !== null && $score->score < $assessment->warning_score;
                @endphp
                <td class="px-4 py-3 text-center hover:bg-green-100 dark:hover:bg-green-900/20 transition-colors">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    {{ $displayScore }}
                    @if($showWarning)
                      <span class="ml-1 text-yellow-600 dark:text-yellow-400" title="At Risk: Below warning score">
                        &#9888;
                      </span>
                    @endif
                  </div>
                  @if($percentage !== null)
                    <div class="text-xs text-green-600 dark:text-green-400">
                      {{ $percentage }}%
                    </div>
                  @endif
                </td>
              @endforeach
            @else
              <td class="px-4 py-3 text-center hover:bg-green-100 dark:hover:bg-green-900/20 transition-colors">
                <div class="text-sm text-gray-500 dark:text-gray-400">--</div>
              </td>
            @endif
          @endforeach
          
          <!-- Midterm Grade -->
          <td class="px-4 py-3 text-center font-semibold">
            @if($student->midterm_grade !== null)
              <span class="grade-display text-lg text-blue-600 dark:text-blue-400" data-grade="{{ $student->midterm_grade }}" data-type="percentage">
                {{ $student->midterm_grade }}%
              </span>
            @else
              <span class="text-sm text-gray-500 dark:text-gray-400">--</span>
            @endif
          </td>
          
          <!-- Final Grade -->
          <td class="px-4 py-3 text-center font-semibold">
            @if($student->final_grade !== null)
              <span class="grade-display text-lg text-green-600 dark:text-green-400" data-grade="{{ $student->final_grade }}" data-type="percentage">
                {{ $student->final_grade }}%
              </span>
            @else
              <span class="text-sm text-gray-500 dark:text-gray-400">--</span>
            @endif
          </td>
          
          <!-- Overall Grade -->
          <td class="px-4 py-3 text-center font-semibold">
            @if($student->overall_grade !== null)
              <span class="grade-display text-lg font-bold" data-grade="{{ $student->overall_grade }}" data-type="percentage">
                {{ $student->overall_grade }}%
              </span>
            @else
              <span class="text-sm text-gray-500 dark:text-gray-400">--</span>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="{{ ($midtermAssessmentTypes->count() + $finalAssessmentTypes->count() + 4) }}" class="px-6 py-12 text-center">
            <div class="text-gray-400 dark:text-gray-500 mb-4">
              <i data-lucide="users" class="w-16 h-16 mx-auto"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No students enrolled</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-6">Enroll students to view grades</p>
            <a href="{{ route('grading.system', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => 'midterm']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
              <i data-lucide="plus" class="w-4 h-4"></i>
              Enroll Students
            </a>
          </td>
        </tr>
      @endforelse
    </tbody>
  </table>
</div>

<!-- Grading Structure Summary -->
@if($gradingStructure)
<div class="mt-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">Grading Structure:</h4>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
        <div class="flex items-center gap-2">
          <div class="w-4 h-4 bg-blue-100 dark:bg-blue-900/20 rounded"></div>
          <span class="text-gray-700 dark:text-gray-300">
            Midterm ({{ $gradingStructure->midterm_weight }}%)
          </span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-4 h-4 bg-green-100 dark:bg-green-900/20 rounded"></div>
          <span class="text-gray-700 dark:text-gray-300">
            Final ({{ $gradingStructure->final_weight }}%)
          </span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-4 h-4 bg-gray-100 dark:bg-gray-900/20 rounded"></div>
          <span class="text-gray-700 dark:text-gray-300">
            Total: 100%
          </span>
        </div>
        <div class="flex items-center gap-2">
          <div class="w-4 h-4 bg-red-100 dark:bg-red-900/20 rounded"></div>
          <span class="text-gray-700 dark:text-gray-300">
            {{ $students->count() }} students
          </span>
        </div>
      </div>
    </div>
    <div class="text-sm text-gray-600 dark:text-gray-400">
      <span class="font-medium">{{ $students->count() }}</span> students enrolled
    </div>
  </div>
</div>
@endif

<script>
// Dynamic grading system
let currentGradingMode = 'percentage';
let gradingParams = {
  max_score: 95,
  max_grade: 100,
  passing_score: 75,
  passing_grade: 3.0,
  custom_formula: 'inverse_linear'
};

// Grade conversion functions using dynamic parameters
function convertGrade(percentage, mode, params = gradingParams) {
  if (mode === 'percentage') {
    return percentage + '%';
  }

  if (mode === 'linear') {
    return calculateLinearGrade(percentage, params);
  }

  if (mode === 'curved') {
    return calculateCurvedGrade(percentage, params);
  }

  if (mode === 'pass_fail') {
    return calculatePassFailGrade(percentage, params);
  }

  if (mode === 'custom') {
    return calculateCustomGrade(percentage, params);
  }

  return percentage + '%';
}

function calculateLinearGrade(percentage, params) {
  const maxScore = params.max_score || 95;
  const passingScore = params.passing_score || 75;
  const passingGrade = params.passing_grade || 3.0;

  // Scale percentage to max score
  if (percentage > maxScore) {
    percentage = maxScore;
  }

  if (percentage >= passingScore) {
    const grade = passingGrade - ((percentage - passingScore) / (maxScore - passingScore)) * (passingGrade - 1.0);
    return grade.toFixed(2);
  } else {
    const grade = passingGrade + ((passingScore - percentage) / passingScore) * (5.0 - passingGrade);
    return grade.toFixed(2);
  }
}

function calculateCurvedGrade(percentage, params) {
  const maxScore = params.max_score || 95;
  const passingScore = params.passing_score || 75;
  const passingGrade = params.passing_grade || 3.0;

  const scaledPercentage = (percentage / 100) * maxScore;

  if (scaledPercentage >= passingScore) {
    const grade = 1.0 + ((maxScore - scaledPercentage) / (maxScore - passingScore)) * (passingGrade - 1.0);
    return grade.toFixed(2);
  } else {
    const grade = passingGrade + ((passingScore - scaledPercentage) / passingScore) * (5.0 - passingGrade);
    return grade.toFixed(2);
  }
}

function calculatePassFailGrade(percentage, params) {
  const passingScore = params.passing_score || 75;
  const passingGrade = params.passing_grade || 3.0;

  if (percentage >= passingScore) {
    const grade = 1.0 + ((percentage - passingScore) / (100 - passingScore)) * (passingGrade - 1.0);
    return grade.toFixed(2);
  } else {
    return '5.00';
  }
}

function getBestGrade(maxScore) {
  // 100% → 1.0, 95% → 1.1, 90% → 1.2, etc.
  // Adjust the formula as needed for your scale
  return 2.0 - (maxScore / 100);
}

function calculateCustomGrade(percentage, params) {
  const formula = params.custom_formula || 'inverse_linear';
  
  switch (formula) {
    case 'inverse_linear': {
      // Linear scale: max_score% = best grade, passing_score = passing grade
      const maxScore = params.max_score || 95;
      const passingScore = params.passing_score || 75;
      const passingGrade = params.passing_grade || 3.0;
      const bestGrade = getBestGrade(maxScore);
      // Cap percentage at max_score
      const effectivePercentage = Math.min(percentage, maxScore);
      if (effectivePercentage >= passingScore) {
        const grade = passingGrade - ((effectivePercentage - passingScore) / (maxScore - passingScore)) * (passingGrade - bestGrade);
        return grade.toFixed(2);
      } else {
        // Below passing: linear scale to 5.0
        const grade = passingGrade + ((passingScore - effectivePercentage) / passingScore) * (5.0 - passingGrade);
        return grade.toFixed(2);
      }
    }
    case 'exponential':
      const passingScore = params.passing_score || 75;
      const passingGrade = params.passing_grade || 3.0;
      const normalized = (percentage - passingScore) / (100 - passingScore);
      const grade = passingGrade - (normalized * (passingGrade - 1.0));
      return Math.max(1.0, grade).toFixed(2);
    case 'step':
      if (percentage >= 97) return '1.00';
      if (percentage >= 94) return '1.25';
      if (percentage >= 91) return '1.50';
      if (percentage >= 88) return '1.75';
      if (percentage >= 85) return '2.00';
      if (percentage >= 82) return '2.25';
      if (percentage >= 79) return '2.50';
      if (percentage >= 76) return '2.75';
      if (percentage >= (params.passing_score || 75)) return (params.passing_grade || 3.0).toFixed(2);
      return '5.00';
    default:
      return calculateLinearGrade(percentage, params);
  }
}

// Color coding for different grading modes
function getGradeColor(grade, mode) {
  if (mode === 'percentage') {
    return ''; // No color for percentage
  }
  
  const numGrade = parseFloat(grade);
  if (numGrade <= 1.0) return 'text-green-600'; // Excellent
  if (numGrade <= 1.5) return 'text-blue-600'; // Very Good
  if (numGrade <= 1.75) return 'text-yellow-600'; // Good
  if (numGrade <= 2.5) return 'text-orange-600'; // Fair
  if (numGrade <= 2.75) return 'text-orange-600'; // Passing
  if (numGrade <= 3.0) return 'text-red-500'; // Lowest Passing
  return 'text-red-700'; // Failed
}

function updateGradeDisplay() {
  const gradeDisplays = document.querySelectorAll('.grade-display');
  const gradingMode = document.getElementById('grading_mode').value;
  
  gradeDisplays.forEach(display => {
    const grade = parseFloat(display.dataset.grade);
    if (!isNaN(grade)) {
      const convertedGrade = convertGrade(grade, gradingMode, gradingParams);
      display.textContent = convertedGrade;
      display.dataset.type = gradingMode;
      
      // Apply color coding (simplified)
      const colorClass = getGradeColor(convertedGrade, gradingMode);
      if (colorClass) {
        display.className = `grade-display text-lg font-bold ${colorClass}`;
      } else {
        display.className = 'grade-display text-lg';
      }
    }
  });
  
  // Update settings summary
  updateSettingsSummary();
}

function updateSettingsSummary() {
  const gradingMode = document.getElementById('grading_mode').value;
  const settingsDiv = document.getElementById('current_settings');
  const summarySpan = document.getElementById('settings_summary');
  
  if (gradingMode === 'percentage') {
    settingsDiv.classList.add('hidden');
    return;
  }
  
  settingsDiv.classList.remove('hidden');
  
  let summary = '';
  if (gradingMode === 'custom') {
    summary = `Custom (${gradingParams.custom_formula})`;
  } else {
    summary = `${gradingMode.charAt(0).toUpperCase() + gradingMode.slice(1)}`;
  }
  
  summary += ` | Max: ${gradingParams.max_score}% | Pass: ${gradingParams.passing_score}%`;
  summarySpan.textContent = summary;
}

// Modal functions
function openGradingModal() {
  document.getElementById('gradingModal').classList.remove('hidden');
  updateGradeDisplay(); // No longer need updatePreview
}

function closeGradingModal() {
  document.getElementById('gradingModal').classList.add('hidden');
}

function applyGradingSettings() {
  // Get form values and update local params directly
  gradingParams = {
    max_score: parseFloat(document.getElementById('max_score').value),
    passing_score: parseFloat(document.getElementById('passing_score').value),
    passing_grade: 3.0, // Hardcoded passing grade
    custom_formula: document.getElementById('custom_formula').value
  };
  
  // Update grade display immediately
  updateGradeDisplay();
  
  // Close modal
  closeGradingModal();
  
  // Show success message
  showNotification('Grading settings applied successfully!', 'success');
  
  // Optionally save to backend in background (non-blocking)
  saveSettingsToBackend();
}

function saveSettingsToBackend() {
  const settings = {
    grading_method: document.getElementById('grading_mode').value,
    max_score: gradingParams.max_score,
    passing_score: gradingParams.passing_score,
    passing_grade: 3.0, // Hardcoded passing grade
    custom_formula: gradingParams.custom_formula
  };
  
  // Save settings to backend without blocking the UI
  fetch(`{{ route('grading.settings.save', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id]) }}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(settings)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      console.log('Settings saved to backend successfully');
    } else {
      console.warn('Failed to save settings to backend:', data.message);
    }
  })
  .catch(error => {
    console.warn('Error saving to backend:', error.message);
  });
}

// Remove updatePreview function and all calls to it

function showNotification(message, type = 'info') {
  // Create notification element
  const notification = document.createElement('div');
  notification.className = `fixed top-4 right-4 px-4 py-2 rounded-lg text-white z-50 ${
    type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500'
  }`;
  notification.textContent = message;
  
  document.body.appendChild(notification);
  
  // Remove after 3 seconds
  setTimeout(() => {
    notification.remove();
  }, 3000);
}



// Load saved grading settings
function loadGradingSettings() {
  fetch(`{{ route('grading.settings.get', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id]) }}`)
  .then(response => response.json())
  .then(data => {
    if (data.success && data.settings) {
      const settings = data.settings;
      
      // Set grading mode
      const gradingModeSelect = document.getElementById('grading_mode');
      if (gradingModeSelect && settings.grading_method) {
        gradingModeSelect.value = settings.grading_method;
        currentGradingMode = settings.grading_method;
        
        // Show/hide customize button
        const customizeButton = document.getElementById('customize_grading');
        if (settings.grading_method === 'custom') {
          customizeButton.classList.remove('hidden');
          document.getElementById('custom_formula_section').classList.remove('hidden');
        }
      }
      
      // Set form values
      if (settings.max_score) document.getElementById('max_score').value = settings.max_score;
      if (settings.passing_score) document.getElementById('passing_score').value = settings.passing_score;
      if (settings.custom_formula) document.getElementById('custom_formula').value = settings.custom_formula;
      
      // Update local params
      gradingParams = {
        max_score: settings.max_score || 95,
        passing_score: settings.passing_score || 75,
        passing_grade: 3.0, // Hardcoded passing grade
        custom_formula: settings.custom_formula || 'inverse_linear'
      };
      
      // Update grade display
      updateGradeDisplay();
    }
  })
  .catch(error => {
    console.error('Error loading grading settings:', error);
  });
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  const gradingModeSelect = document.getElementById('grading_mode');
  const customizeButton = document.getElementById('customize_grading');
  
  if (gradingModeSelect) {
    gradingModeSelect.addEventListener('change', function() {
      currentGradingMode = this.value;
      
      // Show/hide customize button and custom formula section
      if (this.value === 'custom') {
        customizeButton.classList.remove('hidden');
        document.getElementById('custom_formula_section').classList.remove('hidden');
      } else {
        customizeButton.classList.add('hidden');
        document.getElementById('custom_formula_section').classList.add('hidden');
      }
      
      updateGradeDisplay();
    });
  }
  
  if (customizeButton) {
    customizeButton.addEventListener('click', openGradingModal);
  }
  

  
  // Add event listeners for form inputs to update preview
  const formInputs = ['max_score', 'passing_score'];
  formInputs.forEach(inputId => {
    const input = document.getElementById(inputId);
    if (input) {
      // input.addEventListener('input', updatePreview); // Removed updatePreview
    }
  });
  
  // Add event listener for custom formula dropdown
  const customFormulaSelect = document.getElementById('custom_formula');
  if (customFormulaSelect) {
    // customFormulaSelect.addEventListener('change', updatePreview); // Removed updatePreview
  }
  
  // Load saved settings
  loadGradingSettings();
  
  // Initialize Lucide icons
  if (window.lucide) {
    lucide.createIcons();
  }
});
</script>
@endsection 