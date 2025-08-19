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
<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-8 gap-4">
  <div>
    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">Gradebook - {{ $classSection->section }}</h2>
    <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400 mt-1">{{ $classSection->subject->code }} - {{ $classSection->subject->title }}</p>
    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
      @if($gradingStructure)
        Weights: Midterm {{ $gradingStructure->midterm_weight }}% | Final {{ $gradingStructure->final_weight }}%
      @else
        Weights: Midterm 50% | Final 50%
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
      
      <!-- Export 2 Button -->
      <button
        id="export2Button"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-green-500 text-white font-medium rounded-lg transition-colors hover:bg-green-600 text-xs sm:text-sm"
        onclick="exportCurrentData()"
      >
        <i data-lucide="file-text" class="w-4 h-4"></i>
        <span>Export 2</span>
      </button>
      
      <!-- Debug Button -->
      <button
        id="debugButton"
        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2 bg-yellow-500 text-white font-medium rounded-lg transition-colors hover:bg-yellow-600 text-xs sm:text-sm"
        onclick="showTableDOM()"
      >
        <i data-lucide="bug" class="w-4 h-4"></i>
        <span>Debug Table</span>
      </button>
    </div>
  </div>
</div>

<!-- Export Modal -->
<div id="exportModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 hidden">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-80">
    <h3 class="text-lg font-bold mb-4">Export Gradebook</h3>
    <form method="GET" action="{{ route('gradebook.export', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id]) }}">
      <input type="hidden" name="format" id="exportFormat" value="">
      <input type="hidden" name="grading_mode" id="exportGradingMode" value="">
      <input type="hidden" name="grading_settings" id="exportGradingSettings" value="">
      
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

<!-- Debug Modal -->
<div id="debugModal" class="fixed inset-0 flex items-center justify-center bg-black/50 z-50 hidden">
  <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-[95vw] max-w-[1400px] max-h-[90vh] overflow-auto">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100">Debug Table Data</h3>
      <button onclick="closeDebugModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
        <i data-lucide="x" class="w-5 h-5"></i>
      </button>
    </div>
    
    <div id="debugTableContent" class="overflow-x-auto">
      <!-- Table content will be inserted here -->
    </div>
    
    <div class="flex justify-end mt-4">
      <button onclick="closeDebugModal()" 
              class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium rounded-lg transition-colors">
        Close
      </button>
    </div>
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
  <div id="debugCounter" class="text-xs text-gray-500 p-2 text-center">Debug Counter: 0</div>
  <table id="gradebookTable" class="w-full min-w-[800px] sm:min-w-[1400px]">
    <thead>
      <tr>
        <th rowspan="3" class="px-2 sm:px-6 py-3 text-left bg-white dark:bg-gray-800 sticky left-0 top-0 z-20 border-b border-gray-200 dark:border-gray-700">
          <div class="text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student ID</div>
        </th>
        <th rowspan="3" class="px-2 sm:px-6 py-3 text-left bg-white dark:bg-gray-800 sticky left-0 top-0 z-20 border-b border-gray-200 dark:border-gray-700">
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
              <th class="px-2 sm:px-4 py-2 text-center bg-blue-50 dark:bg-blue-900/20 sticky top-20 z-10 border-b border-gray-200 dark:border-gray-700">
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
        @foreach($finalAssessmentTypes as $assessmentType)
          @php
            $assessmentList = $assessments['final'][$assessmentType->id]['assessments'];
          @endphp
          @if($assessmentList->count() > 0)
            @foreach($assessmentList as $assessment)
              <th class="px-2 sm:px-4 py-2 text-center bg-green-50 dark:bg-green-900/20 sticky top-20 z-10 border-b border-gray-200 dark:border-gray-700">
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
      </tr>
    </thead>
    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
      @forelse($students as $student)
        @php
          $isFailing = $student->overall_grade !== null && $student->overall_grade < 75;
        @endphp
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
          <td class="px-2 sm:px-6 py-4 bg-white dark:bg-gray-800 sticky left-0 z-10">
            <div class="text-xs sm:text-sm font-medium {{ $isFailing ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
              {{ $student->student_id }}
            </div>
          </td>
          <td class="px-2 sm:px-6 py-4 bg-white dark:bg-gray-800 sticky left-0 z-10">
            <div class="text-xs sm:text-sm font-medium {{ $isFailing ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-100' }}">
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
          
          <!-- Midterm Grade -->
          <td class="px-2 sm:px-4 py-3 text-center font-semibold">
            @if($student->midterm_grade !== null)
              <span class="grade-display text-lg text-blue-600 dark:text-blue-400" data-grade="{{ $student->midterm_grade }}" data-type="percentage">
                {{ $student->midterm_grade }}%
              </span>
            @else
              <span class="text-sm text-gray-500 dark:text-gray-400">--</span>
            @endif
          </td>
          
          <!-- Final Grade -->
          <td class="px-2 sm:px-4 py-3 text-center font-semibold">
            @if($student->final_grade !== null)
              <span class="grade-display text-lg text-green-600 dark:text-green-400" data-grade="{{ $student->final_grade }}" data-type="percentage">
                {{ $student->final_grade }}%
              </span>
            @else
              <span class="text-sm text-gray-500 dark:text-gray-400">--</span>
            @endif
          </td>
          
          <!-- Overall Grade -->
          <td class="px-2 sm:px-4 py-3 text-center font-semibold">
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
// Debug function to show table data values (defined globally)
function showTableDOM() {
  // Increment debug counter
  const counterElement = document.getElementById('debugCounter');
  const currentCount = parseInt(counterElement.textContent.match(/\d+/)[0]) || 0;
  const newCount = currentCount + 1;
  counterElement.textContent = `Debug Counter: ${newCount}`;
  
  console.log(`Debug: Starting debug function (attempt #${newCount})`);
  
  // Get the specific table by ID
  const table = document.getElementById('gradebookTable');
  if (!table) {
    alert('No gradebook table found in the DOM');
    return;
  }

  console.log('Debug: Found gradebook table with ID');
  
  // Get the current grading mode
  const gradingMode = document.getElementById('grading_mode').value;
  console.log('Debug: Current grading mode:', gradingMode);
  
  // Force update the grade display to ensure we have the latest data
  console.log('Debug: About to call updateGradeDisplay()');
  updateGradeDisplay();
  console.log('Debug: updateGradeDisplay() called');
  
  // Wait a moment for the update to complete, then get the data
  setTimeout(() => {
    // Always read from the specific table by ID
    const originalTable = document.getElementById('gradebookTable');
    
    // Extract clean text content from current table (after grading mode changes)
    const currentCells = originalTable.querySelectorAll('th, td');
    const cleanTextData = [];
    
    console.log('Debug: Found', currentCells.length, 'cells');
    
    // Get the actual grade display elements (these contain the current grades)
    const gradeDisplays = document.querySelectorAll('.grade-display');
    console.log('Debug: Found', gradeDisplays.length, 'grade display elements');
    gradeDisplays.forEach((display, index) => {
      console.log(`Debug: Grade Display ${index}: "${display.textContent}"`);
    });
    
    // Clone the original table to preserve structure (colspan, rowspan, etc.)
    const clonedTable = originalTable.cloneNode(true);
    
    // Clean up the cloned table content but preserve structure
    const allCells = clonedTable.querySelectorAll('th, td');
    allCells.forEach(cell => {
      // Get clean text content
      const textContent = cell.textContent || cell.innerText || '';
      let cleanText = textContent.replace(/\s+/g, ' ').trim();
      
      // Remove duplicate consecutive words/phrases
      const words = cleanText.split(' ');
      const uniqueWords = [];
      
      for (let i = 0; i < words.length; i++) {
        const currentWord = words[i];
        const nextWord = words[i + 1];
        
        // Check if current word is followed by the same word
        if (currentWord === nextWord) {
          continue; // Skip the duplicate
        }
        
        // Check for duplicate phrases (2-word sequences)
        if (i < words.length - 3) {
          const phrase1 = words.slice(i, i + 2).join(' ');
          const phrase2 = words.slice(i + 2, i + 4).join(' ');
          if (phrase1 === phrase2) {
            i += 1; // Skip next word too
            continue;
          }
        }
        
        uniqueWords.push(currentWord);
      }
      
      cleanText = uniqueWords.join(' ');
      
      // Fix specific cases like "Attendance Attendan..." to just "Attendance"
      cleanText = cleanText.replace(/Attendance\s+Attendan\.\.\./g, 'Attendance');
      cleanText = cleanText.replace(/Quiz\s+\d+\s+Quiz\s+\d+/g, (match) => {
        // Extract just "Quiz X" from "Quiz X Quiz X"
        const quizMatch = match.match(/Quiz\s+\d+/);
        return quizMatch ? quizMatch[0] : match;
      });
      
      // Format percentages: convert "100.00 100.00%" to "100.00 (100.00%)"
      cleanText = cleanText.replace(/(\d+\.?\d*)\s+(\d+\.?\d*%)/g, '$1 ($2)');
      
      // Clear the cell and add only the clean text
      cell.innerHTML = cleanText || '--';
    });
    
    // Now update the grade cells with current grade data
    // We need to find the grade cells in the cloned table and update them with current data
    const gradeCells = clonedTable.querySelectorAll('td');
    let gradeIndex = 0;
    
    // Find the last 3 cells of each student row (which are the grade cells)
    const studentRows = clonedTable.querySelectorAll('tbody tr');
    studentRows.forEach((row, studentIndex) => {
      const cells = Array.from(row.querySelectorAll('td'));
      if (cells.length > 0) {
        // The last 3 cells are the grade cells (midterm, final, overall)
        const gradeCellsInRow = cells.slice(-3);
        gradeCellsInRow.forEach((gradeCell, gradePosition) => {
          const currentGrade = gradeDisplays[gradeIndex]?.textContent?.trim() || '--';
          gradeCell.innerHTML = currentGrade;
          gradeIndex++;
        });
      }
    });
    
    // Remove all classes and complex styling but keep basic structure
    const allElements = clonedTable.querySelectorAll('*');
    allElements.forEach(element => {
      // Remove classes but keep essential attributes
      element.removeAttribute('class');
      element.removeAttribute('style');
      
      // Keep colspan, rowspan, and other structural attributes
      const keepAttributes = ['colspan', 'rowspan', 'id', 'data-*'];
      keepAttributes.forEach(attr => {
        if (element.hasAttribute(attr)) {
          // Keep the attribute
        }
      });
    });
    
    // Apply minimal debug styling
    clonedTable.style.cssText = 'border-collapse: collapse; width: 100%; font-family: monospace; font-size: 12px;';
    
    // Style all cells with borders
    const allCellsStyled = clonedTable.querySelectorAll('th, td');
    allCellsStyled.forEach(cell => {
      const isHeader = cell.tagName === 'TH';
      cell.style.cssText = `
        border: 1px solid #ccc; 
        padding: 4px; 
        text-align: center; 
        background-color: ${isHeader ? '#f0f0f0' : 'white'}; 
        min-width: 60px;
        vertical-align: middle;
      `;
    });
    
    // Style rows
    const allRows = clonedTable.querySelectorAll('tr');
    allRows.forEach(row => {
      row.style.cssText = 'border: 1px solid #ccc;';
    });

    // Show modal with the complete table structure
    document.getElementById('debugTableContent').innerHTML = '';
    document.getElementById('debugTableContent').appendChild(clonedTable);
    document.getElementById('debugModal').classList.remove('hidden');
    
    console.log('Debug: Modal displayed with complete table structure');
    console.log('Debug: Table structure preserved with colspan/rowspan');
    console.log('Debug: Current grades updated in the structure');
  }, 100); // Small delay to ensure grade updates are complete
}

// Function to close debug modal
function closeDebugModal() {
  document.getElementById('debugModal').classList.add('hidden');
}

// Function to prepare export with grading mode data
function prepareExport(format) {
  // Get current grading mode and settings
  const gradingMode = document.getElementById('grading_mode').value;
  const gradingSettings = getCurrentGradingSettings();
  
  // Update hidden inputs
  document.getElementById('exportFormat').value = format;
  document.getElementById('exportGradingMode').value = gradingMode;
  document.getElementById('exportGradingSettings').value = JSON.stringify(gradingSettings);
  
  // Update display
  document.getElementById('exportModeDisplay').textContent = getGradingModeDisplayName(gradingMode);
  
  // Submit the form
  document.querySelector('#exportModal form').submit();
}

// Function to get current grading settings
function getCurrentGradingSettings() {
  const gradingMode = document.getElementById('grading_mode').value;
  const settings = { mode: gradingMode };
  
  if (gradingMode === 'custom') {
    // Get custom grading parameters
    const maxScore = document.getElementById('max_score')?.value || 95;
    const passingScore = document.getElementById('passing_score')?.value || 75;
    const customFormula = document.getElementById('custom_formula')?.value || 'inverse_linear';
    
    settings.maxScore = maxScore;
    settings.passingScore = passingScore;
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

// Function to export current data (Export 2)
function exportCurrentData() {
  // Force update the grade display to ensure we have the latest data
  updateGradeDisplay();
  
  // Wait a moment for the update to complete, then export
  setTimeout(() => {
    // Get the current grading mode
    const gradingMode = document.getElementById('grading_mode').value;
    
    // Get current grade data
    const gradeDisplays = document.querySelectorAll('.grade-display');
    const currentGrades = [];
    gradeDisplays.forEach(display => {
      currentGrades.push(display.textContent.trim());
    });
    
    // Get student data
    const table = document.getElementById('gradebookTable');
    const studentRows = table.querySelectorAll('tbody tr');
    const students = [];
    
    studentRows.forEach((row, rowIndex) => {
      const cells = Array.from(row.querySelectorAll('td'));
      if (cells.length > 0) {
        const studentId = cells[0]?.textContent?.trim() || 'N/A';
        const studentName = cells[1]?.textContent?.trim() || 'N/A';
        
        // Clean up student name (remove duplicates)
        const cleanName = studentName.replace(/(\w+,\s*\w+)\s+\1/g, '$1');
        
        // Get assessment scores (skip first 2 columns and last 3 columns)
        const scores = [];
        for (let i = 2; i < cells.length - 3; i++) {
          const scoreText = cells[i]?.textContent?.trim() || '--';
          scores.push(scoreText);
        }
        
        // Get grades (last 3 columns)
        const grades = [];
        for (let i = cells.length - 3; i < cells.length; i++) {
          const gradeText = cells[i]?.textContent?.trim() || '--';
          grades.push(gradeText);
        }
        
        students.push({
          id: studentId,
          name: cleanName,
          scores: scores,
          grades: grades
        });
      }
    });
    
    // Prepare export data
    const exportData = {
      gradingMode: gradingMode,
      students: students,
      currentGrades: currentGrades,
      timestamp: new Date().toISOString()
    };
    
    // Send data to backend for export
    fetch(`{{ route('gradebook.export2', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id]) }}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
      },
      body: JSON.stringify(exportData)
    })
    .then(response => {
      if (response.ok) {
        return response.blob();
      }
      throw new Error('Export failed');
    })
    .then(blob => {
      // Create download link
      const url = window.URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = `gradebook_export2_${new Date().toISOString().split('T')[0]}.xlsx`;
      document.body.appendChild(a);
      a.click();
      window.URL.revokeObjectURL(url);
      document.body.removeChild(a);
      
      showNotification('Export 2 completed successfully!', 'success');
    })
    .catch(error => {
      console.error('Export error:', error);
      showNotification('Export failed. Please try again.', 'error');
    });
  }, 100);
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