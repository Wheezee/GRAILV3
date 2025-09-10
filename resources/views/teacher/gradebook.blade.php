@extends('layouts.app')

<style>
/* Make entire row red when failing */
tr.failing-row,
tr.failing-row td,
tr.failing-row td div,
tr.failing-row td span,
tr.failing-row * {
  color: #dc2626 !important; /* text-red-600 */
}

.dark tr.failing-row,
.dark tr.failing-row td,
.dark tr.failing-row td div,
.dark tr.failing-row td span,
.dark tr.failing-row * {
  color: #f87171 !important; /* text-red-400 */
}

/* Keep warning icons yellow even in failing rows */
tr.failing-row .text-yellow-600,
.dark tr.failing-row .text-yellow-400 {
  color: #d97706 !important; /* text-yellow-600 */
}

/* Kill Tailwind text color utilities inside failing rows */
tr.failing-row [class*="text-blue-"],
tr.failing-row [class*="text-green-"],
tr.failing-row [class*="text-gray-"],
tr.failing-row [class*="text-slate-"],
tr.failing-row [class*="dark:text-"],
.dark tr.failing-row [class*="text-blue-"],
.dark tr.failing-row [class*="text-green-"],
.dark tr.failing-row [class*="text-gray-"],
.dark tr.failing-row [class*="text-slate-"],
.dark tr.failing-row [class*="dark:text-"] {
  color: inherit !important;
}

/* Also force sticky identifier cells to adopt failing color */
tr.failing-row td.sticky,
tr.failing-row td.sticky * {
  color: inherit !important;
}
</style>

<!-- (Removed debug: global red color) -->

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
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-8 gap-4">
  <div>
    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Gradebook - {{ $classSection->section }}</h2>
    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-1">{{ $classSection->subject->code }} - {{ $classSection->subject->title }}</p>
    @php
      $gs = $classSection->subject->gradingStructure ?? null;
      $hasFinalTypes = $classSection->subject->assessmentTypes()->where('term', 'final')->exists();
      $isAllInOne = $gs && $gs->type === 'custom' && (float)$gs->midterm_weight === 100.0 && (float)$gs->final_weight === 0.0 && !$hasFinalTypes;
    @endphp
    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
      @if($isAllInOne)
        Overall (single-term)
      @else
        @if($gradingStructure)
          Weights: Midterm {{ $gradingStructure->midterm_weight }}% | Final {{ $gradingStructure->final_weight }}%
        @else
          Weights: Midterm 50% | Final 50%
        @endif
      @endif
    </p>
  </div>
  
  <!-- Mobile: Stack buttons vertically -->
  <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4 w-full lg:w-auto">
    <!-- Grading Controls Row -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
        <label for="grading_mode" class="text-xs sm:text-sm font-medium text-gray-700 dark:text-gray-300 whitespace-nowrap">Grading Mode:</label>
        <select id="grading_mode" class="w-full sm:w-auto px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-xs sm:text-sm text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-red-500 focus:border-red-500">
          <option value="percentage">Percentage-Based</option>
          <option value="linear">Linear (1.0–5.0)</option>
          <option value="custom">Custom</option>
        </select>
      </div>
      
      <button id="customize_grading" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white text-xs sm:text-sm font-medium rounded-lg transition-colors hidden">
        <i data-lucide="settings" class="w-4 h-4"></i>
        <span>Customize</span>
      </button>
      
      <div id="current_settings" class="text-xs text-gray-500 dark:text-gray-400 hidden">
        <span id="settings_summary"></span>
      </div>
    </div>
    
    <!-- Divider - hidden on mobile -->
    <div class="hidden sm:block w-px h-6 bg-gray-300 dark:bg-gray-600"></div>
    
    <!-- Action Buttons Row -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 sm:gap-4">
      <a href="{{ route('grading.system', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => 'midterm']) }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors text-xs sm:text-sm">
        <i data-lucide="arrow-left" class="w-4 h-4"></i>
        <span>Back to Grading</span>
      </a>
      
      <!-- Export Button -->
      <button
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-red-500 text-white font-medium rounded-lg transition-colors hover:bg-red-600 text-xs sm:text-sm"
        onclick="openExportModal()"
      >
        <i data-lucide="download" class="w-4 h-4"></i>
        <span>Export</span>
      </button>
      

    </div>
  </div>
</div>

<!-- Grade Calculation Toggle -->
<div class="flex justify-center gap-2 mt-6 mb-6">
  <button id="estimated_grades_btn" class="px-3 py-2 bg-red-500 text-white text-xs sm:text-sm font-medium rounded-l-lg transition-colors">
    Estimated Grades
  </button>
  <button id="projected_final_btn" class="px-3 py-2 bg-gray-300 text-gray-700 text-xs sm:text-sm font-medium rounded-r-lg transition-colors hover:bg-gray-400">
    Projected Final
  </button>
</div>
<div class="text-center text-gray-600 dark:text-gray-400 text-xs mb-6" id="view_summary">
  Current View: Estimated Grades
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 hidden">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-80">
    <h3 class="text-lg font-bold mb-4">Export Gradebook</h3>
    <form method="GET" action="{{ route('gradebook.export', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id]) }}">
      <input type="hidden" name="format" id="exportFormat" value="">
      <input type="hidden" name="grading_mode" id="exportGradingMode" value="">
      <input type="hidden" name="grading_settings" id="exportGradingSettings" value="">
      <input type="hidden" name="export_view" id="exportView" value="estimated">
      
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Grading Mode:</label>
        <div class="text-sm text-gray-600 dark:text-gray-400 p-2 bg-gray-100 dark:bg-gray-700 rounded" id="exportModeDisplay">
          Loading...
        </div>
      </div>
      
      <button type="button" class="w-full mb-2 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
        onclick="prepareExport('pdf');">
        Export as PDF
      </button>
      <button type="button" class="w-full px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
        onclick="prepareExport('excel');">
        Export as Excel
      </button>
      <button type="button" class="w-full mt-4 px-4 py-2 bg-gray-300 text-gray-800 rounded hover:bg-gray-400"
        onclick="document.getElementById('exportModal').classList.add('hidden')">
        Cancel
      </button>
    </form>
  </div>
</div>



<!-- Grading Customization Modal -->
<div id="gradingModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 hidden">
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
  <table id="gradebookTable" class="w-full min-w-[800px] sm:min-w-[1400px]">
    <thead>
      <tr>
        <th rowspan="3" class="px-2 sm:px-6 py-3 text-left bg-white dark:bg-gray-800 sticky left-0 top-0 z-20 border-b border-gray-200 dark:border-gray-700">
          <div class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student ID</div>
        </th>
        <th rowspan="3" class="px-2 sm:px-6 py-3 text-left bg-white dark:bg-gray-800 sticky left-0 top-0 z-20 border-b border-gray-200 dark:border-gray-700">
          <div class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Students</div>
        </th>
        
        <!-- Midterm/Assessments Section -->
        @if($isAllInOne && $midtermAssessmentTypes->count() > 0)
          @php
            $midtermColspan = 0;
            foreach($midtermAssessmentTypes as $type) {
              $midtermColspan += $assessments['midterm'][$type->id]['assessments']->count() ?: 1;
            }
          @endphp
          <th colspan="{{ $midtermColspan }}" class="px-6 py-3 text-center bg-blue-50 dark:bg-blue-900/20 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-blue-900 dark:text-blue-100">Assessments</div>
          </th>
        @elseif(!$isAllInOne && $midtermAssessmentTypes->count() > 0)
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
        @if(!$isAllInOne && $finalAssessmentTypes->count() > 0)
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
        
        @if(!$isAllInOne)
          <th rowspan="3" class="px-6 py-3 text-center bg-white dark:bg-gray-800 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Midterm Grade</div>
          </th>
          <th rowspan="3" class="px-6 py-3 text-center bg-white dark:bg-gray-800 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Final Grade</div>
          </th>
        @endif
        <th rowspan="3" class="px-6 py-3 text-center bg-white dark:bg-gray-800 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
          <div class="text-sm font-medium text-gray-900 dark:text-gray-100">Overall Grade</div>
        </th>
      </tr>
      <tr>
        <!-- Midterm Assessment Types (also used for All-in-one) -->
        @foreach($midtermAssessmentTypes as $assessmentType)
          @php
            $assessmentCount = $assessments['midterm'][$assessmentType->id]['assessments']->count();
            $colspan = max($assessmentCount, 1);
            $hasAssessments = $assessmentCount > 0;
            $statusColor = $hasAssessments ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400';
            $statusIcon = $hasAssessments ? '✓' : '⚠';
          @endphp
          <th colspan="{{ $colspan }}" class="px-4 py-2 text-center bg-blue-50 dark:bg-blue-900/20 sticky top-12 z-10 border-b border-gray-200 dark:border-gray-700">
            <div class="text-xs font-medium text-blue-900 dark:text-blue-100">{{ $assessmentType->name }}</div>
            <div class="text-xs text-blue-600 dark:text-blue-400">Weight: {{ $assessmentType->weight }}%</div>
            <div class="status-indicator text-xs {{ $statusColor }} mt-1 flex items-center justify-center">
              <span class="text-sm" title="{{ $hasAssessments ? 'Complete' : 'No Assessments' }}">{{ $statusIcon }}</span>
            </div>
          </th>
        @endforeach
        
        <!-- Final Assessment Types -->
        @if(!$isAllInOne)
        @foreach($finalAssessmentTypes as $assessmentType)
          @php
            $assessmentCount = $assessments['final'][$assessmentType->id]['assessments']->count();
            $colspan = max($assessmentCount, 1);
            $hasAssessments = $assessmentCount > 0;
            $statusColor = $hasAssessments ? 'text-green-600 dark:text-green-400' : 'text-orange-600 dark:text-orange-400';
            $statusIcon = $hasAssessments ? '✓' : '⚠';
          @endphp
          <th colspan="{{ $colspan }}" class="px-4 py-2 text-center bg-green-50 dark:bg-green-900/20 sticky top-12 z-10 border-b border-gray-200 dark:border-gray-700">
            <div class="text-xs font-medium text-green-900 dark:text-green-100">{{ $assessmentType->name }}</div>
            <div class="text-xs text-green-600 dark:text-green-400">Weight: {{ $assessmentType->weight }}%</div>
            <div class="status-indicator text-xs {{ $statusColor }} mt-1 flex items-center justify-center">
              <span class="text-sm" title="{{ $hasAssessments ? 'Complete' : 'No Assessments' }}">{{ $statusIcon }}</span>
            </div>
          </th>
        @endforeach
        @endif
      </tr>
      <tr>
        <!-- Midterm Assessments (also used for All-in-one) -->
        @foreach($midtermAssessmentTypes as $assessmentType)
          @php
            $assessmentList = $assessments['midterm'][$assessmentType->id]['assessments'];
          @endphp
          @if($assessmentList->count() > 0)
            @foreach($assessmentList as $assessment)
              <th class="px-2 sm:px-4 py-2 text-center bg-blue-50 dark:bg-blue-900/20 sticky top-20 z-10 border-b border-gray-200 dark:border-gray-700 assess-col" data-term="midterm" data-type="{{ trim($assessmentType->name) }}">
                <a href="{{ route('assessments.index', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => 'midterm', 'assessmentType' => $assessmentType->id]) }}" 
                   class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                  <span class="hidden sm:inline">{{ $assessment->name }}</span>
                  <span class="sm:hidden">{{ Str::limit($assessment->name, 8) }}</span>
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
        @if(!$isAllInOne)
        @foreach($finalAssessmentTypes as $assessmentType)
          @php
            $assessmentList = $assessments['final'][$assessmentType->id]['assessments'];
          @endphp
          @if($assessmentList->count() > 0)
            @foreach($assessmentList as $assessment)
              <th class="px-2 sm:px-4 py-2 text-center bg-green-50 dark:bg-green-900/20 sticky top-20 z-10 border-b border-gray-200 dark:border-gray-700 assess-col" data-term="final" data-type="{{ trim($assessmentType->name) }}">
                <a href="{{ route('assessments.index', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => 'final', 'assessmentType' => $assessmentType->id]) }}" 
                   class="text-green-600 dark:text-green-400 hover:underline text-xs">
                  <span class="hidden sm:inline">{{ $assessment->name }}</span>
                  <span class="sm:hidden">{{ Str::limit($assessment->name, 8) }}</span>
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
        @endif
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
      @forelse($students as $student)
        @php
          $isFailing = false; // Disable server-side red styling; handled via per-cell indicators
          $overallGrade = $student->overall_grade;
          $isFailingRow = $overallGrade !== null && $overallGrade <= 75;
        @endphp
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ $isFailingRow ? 'failing-row' : '' }}" data-overall="{{ $overallGrade ?? '' }}">
          <td class="px-2 sm:px-6 py-4 bg-white dark:bg-gray-800 sticky left-0 z-10">
            <div class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100">
              {{ $student->student_id }}
            </div>
          </td>
          <td class="px-2 sm:px-6 py-4 bg-white dark:bg-gray-800 sticky left-0 z-10">
            <div class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100">
              <span class="hidden sm:inline">{{ $student->last_name }}, {{ $student->first_name }}</span>
              <span class="sm:hidden">{{ $student->last_name }}, {{ $student->first_name }}</span>
            </div>
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
                <td class="px-2 sm:px-4 py-3 text-center hover:bg-blue-100 dark:hover:bg-blue-900/20 transition-colors">
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
              <td class="px-2 sm:px-4 py-3 text-center hover:bg-blue-100 dark:hover:bg-blue-900/20 transition-colors">
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
                <td class="px-2 sm:px-4 py-3 text-center hover:bg-green-100 dark:hover:bg-green-900/20 transition-colors">
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
              <td class="px-2 sm:px-4 py-3 text-center hover:bg-green-100 dark:hover:bg-green-900/20 transition-colors">
                <div class="text-sm text-gray-500 dark:text-gray-400">--</div>
              </td>
            @endif
          @endforeach
          
          @if(!$isAllInOne)
          <!-- Midterm Grade -->
          <td class="px-2 sm:px-4 py-3 text-center font-semibold">
            @if($student->midterm_grade !== null)
              @php
                // Calculate midterm breakdown with scaling info (Estimated view)
                $midtermBreakdown = [];
                $midtermActiveWeight = 0;
                foreach($midtermAssessmentTypes as $type) {
                  $assessmentList = $assessments['midterm'][$type->id]['assessments'];
                  if($assessmentList->count() > 0) {
                    $midtermActiveWeight += $type->weight;
                    $totalScore = 0;
                    $totalMax = 0;
                    foreach($assessmentList as $assessment) {
                      $score = $student->assessmentScores()->where('assessment_id', $assessment->id)->first();
                      if($score && $score->score !== null) {
                        $totalScore += $score->score;
                        $totalMax += $assessment->max_score;
                      }
                    }
                    if($totalMax > 0) {
                      $percentage = ($totalScore / $totalMax) * 100;
                      $contribution = ($percentage * $type->weight) / 100;
                      $midtermBreakdown[] = $type->name . ' (' . number_format($contribution, 1) . ' pts)';
                    } else {
                      $midtermBreakdown[] = $type->name . ' (0.0 pts)';
                    }
                  } else {
                    $midtermBreakdown[] = $type->name . ' (0.0 pts)';
                  }
                }
                $midtermScale = $midtermActiveWeight > 0 ? (100 / $midtermActiveWeight) : 0;
                $breakdownText = 'Midterm Breakdown: ' . implode(', ', $midtermBreakdown) . ' | Active weights: ' . number_format($midtermActiveWeight, 1) . '% (×' . number_format($midtermScale, 2) . ')';
              @endphp
              <span class="grade-display text-lg text-blue-600 dark:text-blue-400" data-grade="{{ $student->midterm_grade }}" data-type="percentage" data-term="midterm" title="{{ $breakdownText }}">
                {{ $student->midterm_grade }}%
              </span>
            @else
              <span class="text-sm text-gray-500 dark:text-gray-400">--</span>
            @endif
          </td>
          
          <!-- Final Grade -->
          <td class="px-2 sm:px-4 py-3 text-center font-semibold">
            @if($student->final_grade !== null)
              @php
                // Calculate final breakdown with scaling info (Estimated view)
                $finalBreakdown = [];
                $finalActiveWeight = 0;
                foreach($finalAssessmentTypes as $type) {
                  $assessmentList = $assessments['final'][$type->id]['assessments'];
                  if($assessmentList->count() > 0) {
                    $finalActiveWeight += $type->weight;
                    $totalScore = 0;
                    $totalMax = 0;
                    foreach($assessmentList as $assessment) {
                      $score = $student->assessmentScores()->where('assessment_id', $assessment->id)->first();
                      if($score && $score->score !== null) {
                        $totalScore += $score->score;
                        $totalMax += $assessment->max_score;
                      }
                    }
                    if($totalMax > 0) {
                      $percentage = ($totalScore / $totalMax) * 100;
                      $contribution = ($percentage * $type->weight) / 100;
                      $finalBreakdown[] = $type->name . ' (' . number_format($contribution, 1) . ' pts)';
                    } else {
                      $finalBreakdown[] = $type->name . ' (0.0 pts)';
                    }
                  } else {
                    $finalBreakdown[] = $type->name . ' (0.0 pts)';
                  }
                }
                $finalScale = $finalActiveWeight > 0 ? (100 / $finalActiveWeight) : 0;
                $breakdownText = 'Final Breakdown: ' . implode(', ', $finalBreakdown) . ' | Active weights: ' . number_format($finalActiveWeight, 1) . '% (×' . number_format($finalScale, 2) . ')';
              @endphp
              <span class="grade-display text-lg text-green-600 dark:text-green-400" data-grade="{{ $student->final_grade }}" data-type="percentage" data-term="final" title="{{ $breakdownText }}">
                {{ $student->final_grade }}%
              </span>
            @else
              <span class="text-sm text-gray-500 dark:text-gray-400">--</span>
            @endif
          </td>
          @endif
          
          <!-- Overall Grade -->
          <td class="px-2 sm:px-4 py-3 text-center font-semibold">
            @if($student->overall_grade !== null)
              @php
                // Calculate overall breakdown
                $overallBreakdown = [];
                if($student->midterm_grade !== null) {
                  $midtermWeight = $gradingStructure ? $gradingStructure->midterm_weight : 50;
                  $midtermContribution = ($student->midterm_grade * $midtermWeight) / 100;
                  $overallBreakdown[] = 'Midterm (' . number_format($midtermContribution, 1) . ' pts)';
                }
                if($student->final_grade !== null) {
                  $finalWeight = $gradingStructure ? $gradingStructure->final_weight : 50;
                  $finalContribution = ($student->final_grade * $finalWeight) / 100;
                  $overallBreakdown[] = 'Final (' . number_format($finalContribution, 1) . ' pts)';
                }
                if(empty($overallBreakdown)) {
                  $overallBreakdown[] = 'No grades available';
                }
                $breakdownText = 'Overall Breakdown: ' . implode(', ', $overallBreakdown);
              @endphp
              <span class="grade-display text-lg font-bold" data-grade="{{ $student->overall_grade }}" data-type="percentage" data-term="overall" title="{{ $breakdownText }}">
                {{ $student->overall_grade }}%
              </span>
            @else
              <span class="text-sm text-gray-500 dark:text-gray-400">--</span>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="{{ ($midtermAssessmentTypes->count() + $finalAssessmentTypes->count() + 5) }}" class="px-6 py-12 text-center">
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
        @if($isAllInOne)
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-gray-100 dark:bg-gray-900/20 rounded"></div>
            <span class="text-gray-700 dark:text-gray-300">Overall (100%)</span>
          </div>
        @else
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-blue-100 dark:bg-blue-900/20 rounded"></div>
            <span class="text-gray-700 dark:text-gray-300">Midterm ({{ $gradingStructure->midterm_weight }}%)</span>
          </div>
          <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-green-100 dark:bg-green-900/20 rounded"></div>
            <span class="text-gray-700 dark:text-gray-300">Final ({{ $gradingStructure->final_weight }}%)</span>
          </div>
        @endif
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


// Function to prepare export with grading mode data
function prepareExport(format) {
  // Get current grading mode and settings
  const gradingMode = document.getElementById('grading_mode').value;
  const gradingSettings = getCurrentGradingSettings();
  const view = (typeof currentGradeView !== 'undefined' && currentGradeView === 'projected') ? 'projected' : 'estimated';
  
  // Update hidden inputs
  document.getElementById('exportFormat').value = format;
  document.getElementById('exportGradingMode').value = gradingMode;
  document.getElementById('exportGradingSettings').value = JSON.stringify(gradingSettings);
  const ev = document.getElementById('exportView');
  if (ev) ev.value = view;
  
  // Update display
  document.getElementById('exportModeDisplay').textContent = getGradingModeDisplayName(gradingMode);
  
  // Submit the form
  document.querySelector('#exportModal form').submit();
}

// Function to get current grading settings
function getCurrentGradingSettings() {
  const gradingMode = document.getElementById('grading_mode').value;
  const settings = { mode: gradingMode };
  
  // Always include these parameters for both linear and custom modes
  const maxScore = document.getElementById('max_score')?.value || 95;
  const passingScore = document.getElementById('passing_score')?.value || 75;
  const passingGrade = 3.0; // Hardcoded as per frontend logic
  
  settings.maxScore = maxScore;
  settings.passingScore = passingScore;
  settings.passingGrade = passingGrade;
  
  if (gradingMode === 'custom') {
    // Get custom grading parameters
    const customFormula = document.getElementById('custom_formula')?.value || 'inverse_linear';
    settings.customFormula = customFormula;
  }
  
  return settings;
}

// Function to get display name for grading mode
function getGradingModeDisplayName(mode) {
  const modeNames = {
    'percentage': 'Percentage-Based (0-100%)',
    'linear': 'Linear (1.0-5.0)',
    'custom': 'Custom Grading'
  };
  return modeNames[mode] || mode;
}

// Function to open export modal and initialize display
function openExportModal() {
  // Update the grading mode display
  const gradingMode = document.getElementById('grading_mode').value;
  document.getElementById('exportModeDisplay').textContent = getGradingModeDisplayName(gradingMode);
  
  // Show the modal
  document.getElementById('exportModal').classList.remove('hidden');
}



// Dynamic grading system
let currentGradingMode = 'percentage';
let gradingParams = {
  max_score: 95,
  max_grade: 100,
  passing_score: 75,
  passing_grade: 3.0,
  custom_formula: 'inverse_linear'
};

// Weights provided by backend for reliable calculations
const isAllInOne = {{ $isAllInOne ? 'true' : 'false' }};
const midtermTypeWeights = {
  @foreach($midtermAssessmentTypes as $type)
    '{{ trim($type->name) }}': {{ (float)$type->weight }},
  @endforeach
};

const finalTypeWeights = {
  @foreach($finalAssessmentTypes as $type)
    '{{ trim($type->name) }}': {{ (float)$type->weight }},
  @endforeach
};

const termSectionWeights = {
  midterm: {{ $gradingStructure ? (float)$gradingStructure->midterm_weight : 50 }},
  final: {{ $gradingStructure ? (float)$gradingStructure->final_weight : 50 }}
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
  
  console.log('Debug: updateGradeDisplay called with mode:', gradingMode);
  console.log('Debug: Found', gradeDisplays.length, 'grade display elements');
  
  gradeDisplays.forEach((display, index) => {
    const grade = parseFloat(display.dataset.grade);
    console.log(`Debug: Grade ${index}: original=${grade}, mode=${gradingMode}`);
    
    if (!isNaN(grade)) {
      const convertedGrade = convertGrade(grade, gradingMode, gradingParams);
      console.log(`Debug: Grade ${index}: converted from ${grade} to ${convertedGrade}`);
      
      display.textContent = convertedGrade;
      display.dataset.type = gradingMode;
      
      // Apply color coding (simplified)
      const isFailingRow = display.closest('tr')?.classList.contains('failing-row');
      if (isFailingRow) {
        // Force neutral class so row-level red styling can take over
        display.className = 'grade-display text-lg font-bold';
      } else {
        const colorClass = getGradeColor(convertedGrade, gradingMode);
        if (colorClass) {
          display.className = `grade-display text-lg font-bold ${colorClass}`;
        } else {
          display.className = 'grade-display text-lg';
        }
      }
    }
  });
  
  // Update settings summary
  updateSettingsSummary();
  // Re-evaluate failing rows after visual update
  refreshFailingRowStyles();
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
  if (currentGradeView === 'projected') {
    recalculateGrades();
  } else {
    updateGradeDisplay();
  }
  
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
      
      if (currentGradeView === 'projected') {
        recalculateGrades();
      } else {
        updateGradeDisplay();
      }
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
  
  // Initialize toggle buttons
  const estimatedBtn = document.getElementById('estimated_grades_btn');
  const projectedBtn = document.getElementById('projected_final_btn');
  
  if (estimatedBtn && projectedBtn) {
    estimatedBtn.addEventListener('click', () => switchToEstimatedGrades());
    projectedBtn.addEventListener('click', () => switchToProjectedFinal());
  }
  
  // Initialize status indicators to be visible by default (Estimated Grades view)
  const statusIndicators = document.querySelectorAll('.status-indicator');
  statusIndicators.forEach(indicator => {
    indicator.style.display = 'flex';
  });
  
  // Store original tooltips for grade displays
  const gradeDisplays = document.querySelectorAll('.grade-display');
  gradeDisplays.forEach(display => {
    if (display.title && !display.getAttribute('data-original-title')) {
      display.setAttribute('data-original-title', display.title);
    }
  });

  // Initial failing-row evaluation after content is ready
  refreshFailingRowStyles();
  // Re-run after a short delay to catch async DOM updates
  setTimeout(refreshFailingRowStyles, 0);
  setTimeout(refreshFailingRowStyles, 500);

  // Observe table mutations to keep failing styles in sync
  const table = document.getElementById('gradebookTable');
  if (table && window.MutationObserver) {
    const observer = new MutationObserver(() => {
      refreshFailingRowStyles();
    });
    observer.observe(table, { childList: true, subtree: true, characterData: true });
  }
});

// Simple toggle system
let currentGradeView = 'estimated';

function switchToEstimatedGrades() {
  currentGradeView = 'estimated';
  document.getElementById('estimated_grades_btn').className = 'px-3 py-2 bg-red-500 text-white text-xs sm:text-sm font-medium rounded-l-lg transition-colors';
  document.getElementById('projected_final_btn').className = 'px-3 py-2 bg-gray-300 text-gray-700 text-xs sm:text-sm font-medium rounded-r-lg transition-colors hover:bg-gray-400';
  document.getElementById('view_summary').textContent = 'Current View: Estimated Grades';
  
  // Show status indicators
  const statusIndicators = document.querySelectorAll('.status-indicator');
  statusIndicators.forEach(indicator => {
    indicator.style.display = 'flex';
  });
  
  recalculateGrades();
}

function switchToProjectedFinal() {
  currentGradeView = 'projected';
  document.getElementById('estimated_grades_btn').className = 'px-3 py-2 bg-gray-300 text-gray-700 text-xs sm:text-sm font-medium rounded-l-lg transition-colors hover:bg-gray-400';
  document.getElementById('projected_final_btn').className = 'px-3 py-2 bg-red-500 text-white text-xs sm:text-sm font-medium rounded-r-lg transition-colors';
  document.getElementById('view_summary').textContent = 'Current View: Projected Final';
  
  // Hide status indicators
  const statusIndicators = document.querySelectorAll('.status-indicator');
  statusIndicators.forEach(indicator => {
    indicator.style.display = 'none';
  });
  
  recalculateGrades();
}

function recalculateGrades() {
  const gradeDisplays = document.querySelectorAll('.grade-display');
  
  gradeDisplays.forEach(display => {
    const originalGrade = display.dataset.grade;
    if (originalGrade) {
      if (currentGradeView === 'estimated') {
        // Show original grades converted based on current grading mode
        const gradingMode = document.getElementById('grading_mode').value;
        const converted = convertGrade(parseFloat(originalGrade), gradingMode, gradingParams);
        display.textContent = converted;
        display.dataset.type = gradingMode;
        const isFailingRow = display.closest('tr')?.classList.contains('failing-row');
        if (isFailingRow) {
          display.className = 'grade-display text-lg font-bold';
        } else {
          const colorClass = getGradeColor(converted, gradingMode);
          if (colorClass) {
            display.className = `grade-display text-lg font-bold ${colorClass}`;
          } else {
            display.className = 'grade-display text-lg';
          }
        }
        // Keep the original breakdown tooltip
        const originalTitle = display.getAttribute('data-original-title') || display.title;
        display.title = originalTitle;
      } else {
        // Show projected grades with accurate calculation and convert by grading mode
        const projectedGrade = parseFloat(calculateAccurateProjectedGrade(display));
        const gradingMode = document.getElementById('grading_mode').value;
        const converted = convertGrade(projectedGrade, gradingMode, gradingParams);
        display.textContent = converted;
        display.dataset.type = gradingMode;
        const isFailingRow = display.closest('tr')?.classList.contains('failing-row');
        if (isFailingRow) {
          display.className = 'grade-display text-lg font-bold';
        } else {
          const colorClass = getGradeColor(converted, gradingMode) || '';
          display.className = `grade-display text-lg font-bold ${colorClass}`.trim();
        }
        display.title = getProjectedBreakdownText(display);
      }
    }
  });
  
  // Show status notification
  const statusDiv = document.createElement('div');
  statusDiv.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg z-50';
  statusDiv.textContent = `Switched to: ${currentGradeView === 'estimated' ? 'Estimated Grades' : 'Projected Final'}`;
  document.body.appendChild(statusDiv);
  
  setTimeout(() => {
    if (statusDiv.parentNode) {
      statusDiv.parentNode.removeChild(statusDiv);
    }
  }, 3000);

  // Re-evaluate failing rows after recalculation
  refreshFailingRowStyles();
}

// Calculate accurate projected grade based on actual assessment data
function calculateAccurateProjectedGrade(gradeDisplay) {
  const originalGrade = parseFloat(gradeDisplay.dataset.grade);
  
  // Get the student row
  const studentRow = gradeDisplay.closest('tr');
  if (!studentRow) return originalGrade;
  
  // Find which grade column this is (midterm, final, or overall)
  const gradeCell = gradeDisplay.closest('td');
  const cellIndex = Array.from(gradeCell.parentElement.children).indexOf(gradeCell);
  
  // Count total columns to determine grade type
  const totalColumns = gradeCell.parentElement.children.length;
  
  let gradeType = 'overall';
  if (cellIndex === totalColumns - 3) {
    gradeType = 'midterm';
  } else if (cellIndex === totalColumns - 2) {
    gradeType = 'final';
  } else if (cellIndex === totalColumns - 1) {
    gradeType = 'overall';
  }
  
  if (gradeType === 'midterm') {
    return calculateMidtermProjectedGrade(studentRow);
  } else if (gradeType === 'final') {
    return calculateFinalProjectedGrade(studentRow);
  } else if (gradeType === 'overall') {
    return calculateOverallProjectedGrade(studentRow);
  }
  
  return originalGrade.toFixed(1);
}

// -------- Modular projected grading (no hard-coded slots) --------
let columnTypeMapCache = null; // { midterm: string[], final: string[] }

function buildTypeColumnMap() {
  if (columnTypeMapCache) return columnTypeMapCache;
  const headers = document.querySelectorAll('#gradebookTable thead th.assess-col');
  const midterm = [];
  const fin = [];
  headers.forEach(h => {
    const term = h.getAttribute('data-term');
    const type = h.getAttribute('data-type');
    if (term === 'midterm') midterm.push(type);
    if (term === 'final') fin.push(type);
  });
  columnTypeMapCache = { midterm, final: fin };
  return columnTypeMapCache;
}

function getColumnPercentsByType(studentRow, term) {
  const map = buildTypeColumnMap();
  const types = term === 'final' ? map.final : map.midterm;
  const allCells = Array.from(studentRow.querySelectorAll('td'));
  // data cells only; for All-in-one, only the last column is Overall
  const sliceEnd = isAllInOne ? -1 : -3;
  const scoreCells = allCells.slice(2, sliceEnd);
  const result = {};
  for (let i = 0; i < types.length && i < scoreCells.length; i++) {
    const typeName = types[i];
    const pct = extractPercentFromCell(scoreCells[i]);
    if (!result[typeName]) result[typeName] = [];
    result[typeName].push(isNaN(pct) ? 0 : pct);
  }
  return result; // { TypeName: [pct, pct, ...] }
}

function weightedAverageFromTypeBuckets(buckets, weights) {
  let weightedSum = 0;
  let activeWeight = 0;
  Object.keys(buckets).forEach((typeName) => {
    const w = typeof weights[typeName] === 'number' ? weights[typeName] : 0;
    if (w <= 0) return;
    const values = buckets[typeName];
    if (!values || values.length === 0) return;
    const avg = values.reduce((a, b) => a + b, 0) / values.length;
    weightedSum += avg * (w / 100);
    activeWeight += w;
  });
  // Do not rescale; sum of (avg * weight%) already yields percentage
  return activeWeight > 0 ? weightedSum : 0;
}

function calculateMidtermProjectedGrade(studentRow) {
  const buckets = getColumnPercentsByType(studentRow, 'midterm');
  const grade = weightedAverageFromTypeBuckets(buckets, midtermTypeWeights);
  return Math.max(0, grade).toFixed(1);
}

// Calculate projected final grade
function calculateFinalProjectedGrade(studentRow) {
  const buckets = getColumnPercentsByType(studentRow, 'final');
  const grade = weightedAverageFromTypeBuckets(buckets, finalTypeWeights);
  return Math.max(0, grade).toFixed(1);
}

// Calculate projected overall grade
function calculateOverallProjectedGrade(studentRow) {
  // Get midterm and final projected grades
  const midtermProjected = parseFloat(calculateMidtermProjectedGrade(studentRow));
  const finalProjected = parseFloat(calculateFinalProjectedGrade(studentRow));
  
  // Use backend-provided term section weights (fallback 100/0)
  const midtermWeight = typeof termSectionWeights.midterm === 'number' ? termSectionWeights.midterm : 100;
  const finalWeight = typeof termSectionWeights.final === 'number' ? termSectionWeights.final : 0;
  
  const projectedOverall = (midtermProjected * midtermWeight / 100) + 
                          (finalProjected * finalWeight / 100);
  
  return projectedOverall.toFixed(1);
}


// Extract first percentage value from a cell's inner content
function extractPercentFromCell(cell) {
  if (!cell) return NaN;
  // Search specifically for a percentage text node
  const percentNode = Array.from(cell.querySelectorAll('div, span'))
    .map(el => (el.textContent || '').trim())
    .find(t => /\d+\.?\d*%/.test(t));
  const source = percentNode || (cell.textContent || '');
  const match = source.match(/(\d+\.?\d*)%/);
  if (match) return parseFloat(match[1]);
  // Fallback: try to parse number and treat as percentage
  const num = parseFloat(source);
  return isNaN(num) ? NaN : num;
}

// Helpers for projected breakdown tooltips
function parseProjectedScoreCells(studentRow) {
  const cells = studentRow.querySelectorAll('td');
  const scoreCells = Array.from(cells).slice(2, -3);

  let attendanceScore = 0;
  let quiz1Score = 0;
  let quiz2Score = 0;
  let examScore = 0;

  if (scoreCells.length >= 1) {
    const t = scoreCells[0].textContent.trim();
    if (t !== '--' && t !== '') attendanceScore = parseFloat(t) || 0;
  }
  if (scoreCells.length >= 2) {
    const t = scoreCells[1].textContent.trim();
    if (t !== '--' && t !== '') quiz1Score = parseFloat(t) || 0;
  }
  if (scoreCells.length >= 3) {
    const t = scoreCells[2].textContent.trim();
    if (t !== '--' && t !== '') quiz2Score = parseFloat(t) || 0;
  }
  if (scoreCells.length >= 4) {
    const t = scoreCells[3].textContent.trim();
    if (t !== '--' && t !== '') examScore = parseFloat(t) || 0;
  }

  const attendancePercent = attendanceScore; // 0-100 already
  const quiz1Percent = (quiz1Score / 15) * 100; // max 15
  const quiz2Percent = (quiz2Score / 15) * 100; // max 15
  const examPercent = examScore; // 0-100 already

  return { attendancePercent, quiz1Percent, quiz2Percent, examPercent };
}

function getGradeCellType(gradeDisplay) {
  const gradeCell = gradeDisplay.closest('td');
  const cellIndex = Array.from(gradeCell.parentElement.children).indexOf(gradeCell);
  const totalColumns = gradeCell.parentElement.children.length;
  if (isAllInOne) {
    return 'overall';
  }
  if (cellIndex === totalColumns - 3) return 'midterm';
  if (cellIndex === totalColumns - 2) return 'final';
  return 'overall';
}

function getProjectedBreakdownText(gradeDisplay) {
  const studentRow = gradeDisplay.closest('tr');
  if (!studentRow) return 'Projected Grade';

  const type = getGradeCellType(gradeDisplay);

  if (type === 'midterm') {
    const buckets = getColumnPercentsByType(studentRow, 'midterm');
    const parts = [];
    Object.keys(buckets).forEach((name) => {
      const avg = buckets[name].length > 0 ? (buckets[name].reduce((a,b)=>a+b,0) / buckets[name].length) : 0;
      const w = typeof midtermTypeWeights[name] === 'number' ? midtermTypeWeights[name] : 0;
      const pts = (avg * w) / 100;
      parts.push(`${name} ${avg.toFixed(1)}% (${pts.toFixed(1)} pts)`);
    });
    return `Projected Midterm: ${parts.join(', ')}`;
  }

  if (type === 'final') {
    const buckets = getColumnPercentsByType(studentRow, 'final');
    const parts = [];
    Object.keys(buckets).forEach((name) => {
      const avg = buckets[name].length > 0 ? (buckets[name].reduce((a,b)=>a+b,0) / buckets[name].length) : 0;
      const w = typeof finalTypeWeights[name] === 'number' ? finalTypeWeights[name] : 0;
      const pts = (avg * w) / 100;
      parts.push(`${name} ${avg.toFixed(1)}% (${pts.toFixed(1)} pts)`);
    });
    if (parts.length === 0) {
      return 'Projected Final: No assessments yet';
    }
    return `Projected Final: ${parts.join(', ')}`;
  }

  // overall
  const midtermProjected = parseFloat(calculateMidtermProjectedGrade(studentRow));
  const finalProjected = parseFloat(calculateFinalProjectedGrade(studentRow));
  const mw = termSectionWeights.midterm ?? 50;
  const fw = termSectionWeights.final ?? 50;

  // Only use weights for terms that actually have columns
  const map = buildTypeColumnMap();
  const hasMid = (map.midterm?.length || 0) > 0;
  const hasFin = (map.final?.length || 0) > 0;
  const midW = hasMid ? mw : 0;
  const finW = hasFin ? fw : 0;
  const activeW = midW + finW;

  let midPts = 0, finPts = 0;
  if (activeW > 0) {
    midPts = hasMid ? (midtermProjected * midW) / activeW : 0;
    finPts = hasFin ? (finalProjected * finW) / activeW : 0;
  }
  return `Projected Overall: Midterm ${midtermProjected.toFixed(1)}% (${midPts.toFixed(1)} pts, ${midW || 0}%), Final ${finalProjected.toFixed(1)}% (${finPts.toFixed(1)} pts, ${finW || 0}%)`;
}

// ----- Failing row handling (dynamic) -----
function parsePercentFromText(text) {
  if (!text) return NaN;
  const m = String(text).match(/(\d+\.?\d*)%/);
  return m ? parseFloat(m[1]) : NaN;
}

function isRowFailing(row) {
  // Single source of truth: backend-provided original overall percentage
  const dataOverall = row.getAttribute('data-overall');
  const pct = parseFloat(dataOverall);
  if (!isNaN(pct)) return pct <= 75;
  // Fallbacks (should rarely occur)
  const overallSpan = row.querySelector('.grade-display[data-term="overall"]');
  if (overallSpan) {
    const pct2 = parseFloat(overallSpan.dataset.grade);
    if (!isNaN(pct2)) return pct2 <= 75;
    const visiblePct = parsePercentFromText(overallSpan.textContent);
    if (!isNaN(visiblePct)) return visiblePct <= 75;
  }
  return false;
}

function refreshFailingRowStyles() {
  const rows = document.querySelectorAll('#gradebookTable tbody tr');
  rows.forEach(row => {
    if (isRowFailing(row)) {
      row.classList.add('failing-row');
      // Also neutralize any per-cell grade-display color classes in this row
      row.querySelectorAll('.grade-display').forEach(el => {
        el.className = 'grade-display text-lg font-bold';
      });
    } else {
      row.classList.remove('failing-row');
    }
  });
}
</script>
<style>
.tooltip-card{position:absolute;z-index:50;max-width:260px;background:#111827;color:#e5e7eb;border:1px solid #374151;border-radius:.5rem;box-shadow:0 10px 15px -3px rgba(0,0,0,.1),0 4px 6px -4px rgba(0,0,0,.1);padding:.5rem .75rem;display:none;font-size:.75rem}
.tooltip-card .heading{font-weight:600;color:#f9fafb;margin-bottom:.25rem}
.tooltip-card .muted{color:#9ca3af}
</style>
<div id="grade-tooltip" class="tooltip-card"></div>
<script>
const tooltipEl=document.getElementById('grade-tooltip');
function showTooltip(target,html){tooltipEl.innerHTML=html;tooltipEl.style.display='block';const rect=target.getBoundingClientRect();const top=window.scrollY+rect.top- tooltipEl.offsetHeight - 8;const left=Math.min(window.scrollX+rect.left, window.scrollX+window.innerWidth-tooltipEl.offsetWidth-8);tooltipEl.style.top=top+'px';tooltipEl.style.left=left+'px';}
function hideTooltip(){tooltipEl.style.display='none';}

function formatEstimatedTooltip(studentRow, term){
  // Use only types that have visible assessment columns for this term
  const map = buildTypeColumnMap();
  const presentTypes = term==='final' ? map.final : map.midterm;
  const weights = term==='final' ? @json($finalAssessmentTypes->pluck('weight','name')) : @json($midtermAssessmentTypes->pluck('weight','name'));
  // Unique type names
  const typeSet = Array.from(new Set(presentTypes));
  let activeW = 0; typeSet.forEach(n=>{activeW += Number(weights[n]||0)});
  const S = activeW>0 ? (100/activeW) : 0;
  const items = typeSet.map(n=>`${n}: ${(Number(weights[n]||0)*S).toFixed(4)}%`).join('<br>');
  return `<div class="heading">Estimated (Scaled to 100%)</div>
  <div>Active weights: <strong>${activeW.toFixed(0)}</strong></div>
  <div>Scaling: <strong>×${S.toFixed(2)}</strong></div>
  <div class="muted">Scaled weights</div>${items || '<span class=\"muted\">No active types</span>'}`;
}

function formatProjectedTooltip(studentRow, term){
  const buckets = getColumnPercentsByType(studentRow, term);
  const weights = term==='final' ? finalTypeWeights : midtermTypeWeights;
  const rows = Object.keys(buckets).map(n=>{
    const avg = buckets[n].length? (buckets[n].reduce((a,b)=>a+b,0)/buckets[n].length):0;
    const w = typeof weights[n]==='number'?weights[n]:0;
    const pts = (avg*w)/100;
    return `${n}: avg ${avg.toFixed(2)}% × ${w.toFixed(2)}% = ${pts.toFixed(2)} pts`;
  }).join('<br>');
  return `<div class="heading">Projected (No scaling)</div>${rows||'<span class="muted">No assessments yet</span>'}`;
}

document.addEventListener('mouseover', (e)=>{
  const el = e.target.closest('.grade-display');
  if(!el) return;
  const term = el.getAttribute('data-term');
  const tr = el.closest('tr');
  let html='';
  if(currentGradeView==='estimated'){
    if(term==='midterm' || term==='final') html = formatEstimatedTooltip(tr, term);
  } else {
    if(term==='midterm' || term==='final') html = formatProjectedTooltip(tr, term);
  }
  if(html){ showTooltip(el, html); }
});
document.addEventListener('mouseout',(e)=>{ if(e.relatedTarget && e.relatedTarget.closest && e.relatedTarget.closest('#grade-tooltip')) return; hideTooltip(); });
</script>
@endsection 