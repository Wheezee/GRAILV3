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
      <a href="{{ route('subjects.classes', $subject->id) }}" class="hover:text-red-600 dark:hover:text-red-400 max-w-[120px] sm:max-w-none truncate">
        {{ $subject->code }} - {{ $subject->title }}
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <a href="{{ route('grading.system', ['subject' => $subject->id, 'classSection' => $classSection->id, 'term' => $term]) }}" class="hover:text-red-600 dark:hover:text-red-400 transition-colors whitespace-nowrap">
        {{ $classSection->section }}
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">Class Analytics</span>
    </li>
  </ol>
</nav>

<div class="max-w-7xl mx-auto py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">{{ $classSection->section }} Class Analytics</h1>
        <p class="text-gray-600 dark:text-gray-400">{{ $subject->code }} - {{ $subject->title }} ({{ ucfirst($term) }} Term)</p>
    </div>

    <!-- Class Stats Overview -->
    <div class="mb-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i data-lucide="users" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Students</p>
                        <p class="text-2xl font-bold">{{ $analytics['class_stats']['total_students'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Class Average</p>
                        <p class="text-2xl font-bold">{{ number_format($analytics['class_stats']['average_grade'], 1) }}%</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i data-lucide="target" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Passing Rate</p>
                        <p class="text-2xl font-bold">{{ number_format($analytics['class_stats']['passing_rate'], 1) }}%</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i data-lucide="bar-chart-2" class="w-6 h-6 text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Grade Range</p>
                        <p class="text-2xl font-bold">{{ number_format($analytics['class_stats']['highest_grade'], 1) }} - {{ number_format($analytics['class_stats']['lowest_grade'], 1) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Leaderboard Section -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
            <i data-lucide="trophy" class="w-8 h-8 text-yellow-500"></i>
            Student Leaderboard
        </h2>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Rank</th>
                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Current Grade</th>
                                                                                      <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Gap</th>
                             <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Risk Level</th>
                             <th class="px-6 py-4 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Performance</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($analytics['student_rankings'] as $index => $ranking)
                                                 @php
                             $student = $ranking['student'];
                             $currentGrade = $ranking['current_grade'];
                             $gap = $ranking['gap'];
                             $riskLevel = $ranking['risk_level'];
                             $riskScore = $ranking['risk_score'];
                             $rank = $index + 1;
                            
                            // Performance tier colors
                            $tierColor = $currentGrade >= 90 ? 'text-green-600 bg-green-100 dark:bg-green-900/20' :
                                        ($currentGrade >= 80 ? 'text-blue-600 bg-blue-100 dark:bg-blue-900/20' :
                                        ($currentGrade >= 70 ? 'text-yellow-600 bg-yellow-100 dark:bg-yellow-900/20' :
                                        ($currentGrade >= 60 ? 'text-orange-600 bg-orange-100 dark:bg-orange-900/20' :
                                        'text-red-600 bg-red-100 dark:bg-red-900/20')));
                            
                            $tierLabel = $currentGrade >= 90 ? 'Excellent' :
                                        ($currentGrade >= 80 ? 'Good' :
                                        ($currentGrade >= 70 ? 'Satisfactory' :
                                        ($currentGrade >= 60 ? 'Needs Improvement' : 'Failing')));
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors" data-student-id="{{ $student->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($rank <= 3)
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-white
                                            {{ $rank === 1 ? 'bg-yellow-500' : ($rank === 2 ? 'bg-gray-400' : 'bg-orange-500') }}">
                                            {{ $rank }}
                                        </div>
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-600 flex items-center justify-center font-bold text-gray-700 dark:text-gray-300">
                                            {{ $rank }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-gray-300 dark:bg-gray-600 flex items-center justify-center">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ $student->first_name }} {{ $student->last_name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ $student->student_id }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="text-2xl font-bold {{ $currentGrade >= 90 ? 'text-green-600' : ($currentGrade >= 80 ? 'text-blue-600' : ($currentGrade >= 70 ? 'text-yellow-600' : ($currentGrade >= 60 ? 'text-orange-600' : 'text-red-600'))) }}">
                                    {{ number_format($currentGrade, 1) }}%
                                </div>
                            </td>
                                                         <td class="px-6 py-4 whitespace-nowrap text-center">
                                 @if($rank === 1)
                                     <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                         <i data-lucide="crown" class="w-3 h-3 mr-1"></i>
                                         #1
                                     </div>
                                                                   @else
                                      <div class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                          {{ number_format($gap, 1) }}%
                                      </div>
                                                                    @endif
                              </td>
                                                     <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center">
                                <div class="ml-risk-indicator" data-student-id="{{ $ranking['student']->id }}" data-student-data="{{ json_encode([
                                    'avg_score_pct' => $analytics['student_metrics'][$ranking['student']->id]['avg_score_pct'] ?? 0,
                                    'variation_score_pct' => $analytics['student_metrics'][$ranking['student']->id]['variation_score_pct'] ?? 0,
                                    'late_submission_pct' => $analytics['student_metrics'][$ranking['student']->id]['late_submission_pct'] ?? 0,
                                    'missed_submission_pct' => $analytics['student_metrics'][$ranking['student']->id]['missed_submission_pct'] ?? 0
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
                        </td>
                             <td class="px-6 py-4 whitespace-nowrap text-center">
                                 <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $tierColor }}">
                                     {{ $tierLabel }}
                                 </span>
                             </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Risk Distribution Histogram -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
            <i data-lucide="bar-chart-3" class="w-8 h-8 text-purple-500"></i>
            Risk Distribution
        </h2>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center justify-center h-64">
                <div id="riskHistogramLoading" class="text-center">
                    <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-purple-500 mx-auto mb-2"></i>
                    <p class="text-gray-600 dark:text-gray-400">Loading risk predictions...</p>
                </div>
                <canvas id="riskHistogramChart" class="hidden" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Correlation Analysis Tool -->
    <div class="mb-8">
        <h2 class="text-2xl font-bold mb-6 flex items-center gap-3">
            <i data-lucide="activity" class="w-8 h-8 text-indigo-500"></i>
            Analysis Tools
        </h2>
        
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Correlation Tool Card -->
                <div class="flex flex-col justify-between h-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Correlation Analysis</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Analyze relationships between different variables in your class data</p>
                </div>
                    <button onclick="openCorrelationModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors mt-2">
                    <i data-lucide="bar-chart-3" class="w-4 h-4"></i>
                    Open Correlation Tool
                </button>
            </div>
                <!-- Regression Tool Card -->
                <div class="flex flex-col justify-between h-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Regression Analysis</h3>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">Model and predict outcomes based on student performance data</p>
                    </div>
                    <button onclick="openRegressionModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors mt-2">
                        <i data-lucide="trending-up" class="w-4 h-4"></i>
                        Open Regression Tool
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Dashboard -->
    <div class="space-y-8">
        <!-- Grade Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <i data-lucide="bar-chart-3" class="w-5 h-5 text-blue-600"></i>
                Grade Distribution
            </h3>
            <div class="space-y-4">
                @php
                    $totalStudents = $analytics['class_stats']['total_students'];
                    $distribution = $analytics['grade_distribution'];
                @endphp
                
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Excellent (90%+)</span>
                        <div class="flex items-center gap-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full" style="width: {{ $totalStudents > 0 ? ($distribution['excellent'] / $totalStudents) * 100 : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $distribution['excellent'] }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Good (80-89%)</span>
                        <div class="flex items-center gap-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $totalStudents > 0 ? ($distribution['good'] / $totalStudents) * 100 : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $distribution['good'] }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Satisfactory (70-79%)</span>
                        <div class="flex items-center gap-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-yellow-500 h-2 rounded-full" style="width: {{ $totalStudents > 0 ? ($distribution['satisfactory'] / $totalStudents) * 100 : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $distribution['satisfactory'] }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Needs Improvement (60-69%)</span>
                        <div class="flex items-center gap-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-orange-500 h-2 rounded-full" style="width: {{ $totalStudents > 0 ? ($distribution['needs_improvement'] / $totalStudents) * 100 : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $distribution['needs_improvement'] }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Failing (<60%)</span>
                        <div class="flex items-center gap-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-red-500 h-2 rounded-full" style="width: {{ $totalStudents > 0 ? ($distribution['failing'] / $totalStudents) * 100 : 0 }}%"></div>
                            </div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $distribution['failing'] }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessment Difficulty -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                <i data-lucide="target" class="w-5 h-5 text-purple-600"></i>
                Assessment Difficulty
            </h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach(array_slice($analytics['assessment_difficulty'], 0, 5) as $assessment)
                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex-1">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $assessment['name'] }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $assessment['type'] }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                {{ number_format($assessment['average_score'], 1) }}%
                            </span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                {{ $assessment['difficulty_level'] === 'Easy' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' :
                                   ($assessment['difficulty_level'] === 'Medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' :
                                   'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200') }}">
                                {{ $assessment['difficulty_level'] }}
                            </span>
                        </div>
                    </div>
                    
                    <!-- Scatter Plot for this assessment -->
                    <div class="h-48 w-full">
                        <canvas id="assessmentScatterChart_{{ $loop->index }}" class="w-full"></canvas>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Performance Trends Chart -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
            <i data-lucide="trending-up" class="w-5 h-5 text-green-600"></i>
            Performance Trends
        </h3>
        <div class="h-64">
            <canvas id="performanceTrendsChart"></canvas>
        </div>
    </div>
</div>

<!-- ML Debug Modal -->
<div id="mlDebugModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-2 sm:p-4">
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

<!-- Correlation Analysis Modal -->
<div id="correlationModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-2 sm:p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-2xl mx-2 sm:mx-4 transform transition-all max-h-[90vh] flex flex-col">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <i data-lucide="activity" class="w-6 h-6 text-indigo-600"></i>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">Correlation Analysis</h3>
      </div>
      <button onclick="closeCorrelationModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>

    <!-- Modal Body -->
    <div class="p-4 sm:p-6 space-y-6 overflow-y-auto flex-1">
      <!-- Variable Selection -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Variable X</label>
          <select id="correlationX" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="">Select X Variable</option>
            <optgroup label="Student Demographics">
              <option value="sex">Gender (Categorical)</option>
            </optgroup>
            <optgroup label="Performance Metrics">
              <option value="current_grade">Current Grade (%)</option>
              <option value="average_score">Average Score (%)</option>
              <option value="missed_submission_pct">Missed Submissions (%)</option>
            </optgroup>
            <optgroup label="Assessment Scores">
              <option value="attendance">Attendance (%)</option>
              <option value="quiz1">Quiz 1 (%)</option>
              <option value="quiz2">Quiz 2 (%)</option>
              <option value="quiz3">Quiz 3 (%)</option>
              <option value="midterm">Midterm (%)</option>
              <option value="final">Final (%)</option>
            </optgroup>
            <optgroup label="Assessment Averages">
              <option value="quiz_avg">Quiz Average (%)</option>
              <option value="midterm_avg">Midterm Average (%)</option>
              <option value="final_avg">Final Average (%)</option>
            </optgroup>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Variable Y</label>
          <select id="correlationY" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="">Select Y Variable</option>
            <optgroup label="Student Demographics">
              <option value="sex">Gender (Categorical)</option>
            </optgroup>
            <optgroup label="Performance Metrics">
              <option value="current_grade">Current Grade (%)</option>
              <option value="average_score">Average Score (%)</option>
              <option value="missed_submission_pct">Missed Submissions (%)</option>
            </optgroup>
            <optgroup label="Assessment Scores">
              <option value="attendance">Attendance (%)</option>
              <option value="quiz1">Quiz 1 (%)</option>
              <option value="quiz2">Quiz 2 (%)</option>
              <option value="quiz3">Quiz 3 (%)</option>
              <option value="midterm">Midterm (%)</option>
              <option value="final">Final (%)</option>
            </optgroup>
            <optgroup label="Assessment Averages">
              <option value="quiz_avg">Quiz Average (%)</option>
              <option value="midterm_avg">Midterm Average (%)</option>
              <option value="final_avg">Final Average (%)</option>
            </optgroup>
          </select>
        </div>
        
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Correlation Method</label>
          <select id="correlationMethod" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="pearson">Pearson Correlation</option>
            <option value="spearman">Spearman Rank Correlation</option>
            <option value="point_biserial">Point-Biserial Correlation</option>
            <option value="independent_t_test">Independent T-Test</option>
          </select>
        </div>
      </div>

      <!-- Calculate Button -->
      <div class="flex justify-center">
        <button onclick="calculateCorrelation()" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition-colors">
          <i data-lucide="calculator" class="w-4 h-4"></i>
          Calculate Correlation
        </button>
      </div>

      <!-- Results Section -->
      <div id="correlationResults" class="hidden space-y-4">
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Correlation Results</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Test Statistic</p>
              <p id="correlationCoefficient" class="text-2xl font-bold text-indigo-600"></p>
            </div>
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Result</p>
              <p id="correlationStrength" class="text-lg font-semibold"></p>
            </div>
          </div>
          <div class="mt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">Interpretation</p>
            <p id="correlationInterpretation" class="text-sm text-gray-700 dark:text-gray-300"></p>
          </div>
        </div>

        <!-- Variable Distributions -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- X Variable Histogram -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">X Variable Distribution</h4>
            <div class="h-48">
              <canvas id="xVariableHistogram"></canvas>
            </div>
          </div>
          
          <!-- Y Variable Histogram -->
          <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
            <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Y Variable Distribution</h4>
            <div class="h-48">
              <canvas id="yVariableHistogram"></canvas>
            </div>
          </div>
        </div>

        <!-- Enhanced Scatter Plot with Regression Line -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Data Visualization</h4>
          <div class="h-64">
            <canvas id="scatterPlotChart"></canvas>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div id="correlationLoading" class="hidden text-center">
        <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-indigo-500 mx-auto mb-2"></i>
        <p class="text-gray-600 dark:text-gray-400">Calculating correlation...</p>
      </div>
    </div>

    <!-- Modal Footer -->
    <div class="flex justify-center p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700">
      <button onclick="closeCorrelationModal()" 
              class="px-6 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
        Close
      </button>
    </div>
  </div>
</div>

<!-- Regression Analysis Modal -->
<div id="regressionModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-2 sm:p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-2xl mx-2 sm:mx-4 transform transition-all max-h-[90vh] flex flex-col">
    <!-- Modal Header -->
    <div class="flex items-center justify-between p-4 sm:p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
        <h3 class="text-lg sm:text-xl font-bold text-gray-900 dark:text-gray-100">Regression Analysis</h3>
      </div>
      <button onclick="closeRegressionModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    <!-- Modal Body -->
    <div class="p-4 sm:p-6 space-y-6 overflow-y-auto flex-1">
      <!-- Variable Selection -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Variable X</label>
          <select id="regressionX" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="">Select X Variable</option>
            <optgroup label="Student Demographics">
              <option value="sex">Gender (Categorical)</option>
            </optgroup>
            <optgroup label="Performance Metrics">
              <option value="current_grade">Current Grade (%)</option>
              <option value="average_score">Average Score (%)</option>
              <option value="missed_submission_pct">Missed Submissions (%)</option>
            </optgroup>
            <optgroup label="Assessment Scores">
              <option value="attendance">Attendance (%)</option>
              <option value="quiz1">Quiz 1 (%)</option>
              <option value="quiz2">Quiz 2 (%)</option>
              <option value="quiz3">Quiz 3 (%)</option>
              <option value="midterm">Midterm (%)</option>
              <option value="final">Final (%)</option>
            </optgroup>
            <optgroup label="Assessment Averages">
              <option value="quiz_avg">Quiz Average (%)</option>
              <option value="midterm_avg">Midterm Average (%)</option>
              <option value="final_avg">Final Average (%)</option>
            </optgroup>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Variable Y</label>
          <select id="regressionY" class="w-full p-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
            <option value="">Select Y Variable</option>
            <optgroup label="Student Demographics">
              <option value="sex">Gender (Categorical)</option>
            </optgroup>
            <optgroup label="Performance Metrics">
              <option value="current_grade">Current Grade (%)</option>
              <option value="average_score">Average Score (%)</option>
              <option value="missed_submission_pct">Missed Submissions (%)</option>
            </optgroup>
            <optgroup label="Assessment Scores">
              <option value="attendance">Attendance (%)</option>
              <option value="quiz1">Quiz 1 (%)</option>
              <option value="quiz2">Quiz 2 (%)</option>
              <option value="quiz3">Quiz 3 (%)</option>
              <option value="midterm">Midterm (%)</option>
              <option value="final">Final (%)</option>
            </optgroup>
            <optgroup label="Assessment Averages">
              <option value="quiz_avg">Quiz Average (%)</option>
              <option value="midterm_avg">Midterm Average (%)</option>
              <option value="final_avg">Final Average (%)</option>
            </optgroup>
          </select>
        </div>
      </div>
      <!-- Run Regression Button -->
      <div class="flex justify-center">
        <button onclick="runRegression()" class="inline-flex items-center gap-2 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
          <i data-lucide="trending-up" class="w-4 h-4"></i>
          Run Regression
        </button>
      </div>
      <!-- Results Section -->
      <div id="regressionResults" class="hidden space-y-4">
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Regression Results</h4>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">Regression Equation</p>
              <p id="regressionEquation" class="text-lg font-bold text-green-600"></p>
            </div>
            <div>
              <p class="text-sm text-gray-600 dark:text-gray-400">R² Value</p>
              <p id="regressionR2" class="text-lg font-semibold"></p>
            </div>
          </div>
        </div>
        <!-- Scatter Plot with Regression Line -->
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
          <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">Data Visualization</h4>
          <div class="h-64">
            <canvas id="regressionScatterPlot"></canvas>
          </div>
        </div>
      </div>
      <!-- Loading State -->
      <div id="regressionLoading" class="hidden text-center">
        <i data-lucide="loader-2" class="w-8 h-8 animate-spin text-green-500 mx-auto mb-2"></i>
        <p class="text-gray-600 dark:text-gray-400">Running regression analysis...</p>
      </div>
    </div>
    <!-- Modal Footer -->
    <div class="flex justify-center p-4 sm:p-6 border-t border-gray-200 dark:border-gray-700">
      <button onclick="closeRegressionModal()" class="px-6 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">
        Close
      </button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Performance Trends Chart
const ctx = document.getElementById('performanceTrendsChart').getContext('2d');

// Get assessment data from PHP
const assessmentData = @json($analytics['assessment_difficulty']);

// Prepare chart data
const assessmentLabels = assessmentData.map(assessment => assessment.name);
const assessmentScores = assessmentData.map(assessment => assessment.average_score);

new Chart(ctx, {
    type: 'line',
    data: {
        labels: assessmentLabels,
        datasets: [{
            label: 'Class Average',
            data: assessmentScores,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            pointRadius: 6,
            pointHoverRadius: 8
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Class Performance Across Assessments'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Class Average: ' + context.parsed.y.toFixed(1) + '%';
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                title: {
                    display: true,
                    text: 'Average Score (%)'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Assessment'
                }
            }
        }
    }
});

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

// Global variables for risk tracking
let riskDataCollection = [];
let totalStudents = 0;
let completedPredictions = 0;

// Risk Badge Rendering Function
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

// Dynamic ML Risk Loading Functions
function loadMLRisks() {
  const riskIndicators = document.querySelectorAll('.ml-risk-indicator');
  totalStudents = riskIndicators.length;
  completedPredictions = 0;
  riskDataCollection = [];
  
  riskIndicators.forEach(indicator => {
    const studentId = indicator.dataset.studentId;
    const studentData = JSON.parse(indicator.dataset.studentData);
    
    // Show loading state
    indicator.querySelector('.ml-loading').classList.remove('hidden');
    indicator.querySelector('.ml-risk-display').classList.add('hidden');
    indicator.querySelector('.ml-error').classList.add('hidden');
    
    // Make API call
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
      indicator.querySelector('.ml-loading').classList.add('hidden');
      
      // Collect risk data for histogram
      if (data.success && data.has_risks) {
        data.risks.forEach(risk => {
          riskDataCollection.push({
            code: risk.code,
            label: risk.label,
            description: risk.description
          });
        });
      } else {
        // No risks = Low Risk
        riskDataCollection.push({
          code: 'no_risk',
          label: 'Low Risk',
          description: 'No risks detected'
        });
      }
      
      if (data.success && data.has_risks) {
        const riskBadges = indicator.querySelector('.risk-badges');
        renderRiskBadges(riskBadges, data.risks);
        
        indicator.querySelector('.ml-risk-display').classList.remove('hidden');
      } else {
        // No risks detected
        const riskBadges = indicator.querySelector('.risk-badges');
        riskBadges.innerHTML = `
          <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
            <i data-lucide="shield-check" class="w-3 h-3"></i>
            Low Risk
          </span>
        `;
        indicator.querySelector('.ml-risk-display').classList.remove('hidden');
      }
      
      // Check if all predictions are complete
      completedPredictions++;
      if (completedPredictions === totalStudents) {
        createRiskHistogram();
      }
    })
    .catch(error => {
      indicator.querySelector('.ml-loading').classList.add('hidden');
      indicator.querySelector('.ml-error').classList.remove('hidden');
      
      // Still count as completed for histogram
      completedPredictions++;
      if (completedPredictions === totalStudents) {
        createRiskHistogram();
      }
    });
  });
}

// Create Risk Histogram Function
function createRiskHistogram() {
  // Count risk frequencies
  const riskCounts = {};
  riskDataCollection.forEach(risk => {
    const key = risk.label;
    riskCounts[key] = (riskCounts[key] || 0) + 1;
  });
  
  // Prepare chart data
  const labels = Object.keys(riskCounts);
  const data = Object.values(riskCounts);
  
  // Color mapping for different risk types
  const backgroundColors = labels.map(label => {
    if (label === 'Low Risk' || label === 'Not At Risk') return 'rgba(34, 197, 94, 0.8)'; // Green
    if (label === 'At Risk') return 'rgba(239, 68, 68, 0.8)'; // Red
    if (label === 'Chronic Procrastinator') return 'rgba(245, 158, 11, 0.8)'; // Yellow
    if (label === 'Incomplete') return 'rgba(249, 115, 22, 0.8)'; // Orange
    if (label === 'Inconsistent Performer') return 'rgba(147, 51, 234, 0.8)'; // Purple
    return 'rgba(107, 114, 128, 0.8)'; // Gray default
  });
  
  const borderColors = labels.map(label => {
    if (label === 'Low Risk' || label === 'Not At Risk') return 'rgba(34, 197, 94, 1)';
    if (label === 'At Risk') return 'rgba(239, 68, 68, 1)';
    if (label === 'Chronic Procrastinator') return 'rgba(245, 158, 11, 1)';
    if (label === 'Incomplete') return 'rgba(249, 115, 22, 1)';
    if (label === 'Inconsistent Performer') return 'rgba(147, 51, 234, 1)';
    return 'rgba(107, 114, 128, 1)';
  });
  
  // Hide loading, show chart
  document.getElementById('riskHistogramLoading').classList.add('hidden');
  document.getElementById('riskHistogramChart').classList.remove('hidden');
  
  // Create the chart
  const ctx = document.getElementById('riskHistogramChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [{
        label: 'Number of Students',
        data: data,
        backgroundColor: backgroundColors,
        borderColor: borderColors,
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        title: {
          display: true,
          text: 'Risk Distribution Across Class',
          color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151',
          font: {
            size: 16,
            weight: 'bold'
          }
        },
        tooltip: {
          callbacks: {
            label: function(context) {
              const total = context.dataset.data.reduce((a, b) => a + b, 0);
              const percentage = ((context.parsed.y / total) * 100).toFixed(1);
              return `${context.parsed.y} students (${percentage}%)`;
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
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            stepSize: 1,
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        },
        x: {
          title: {
            display: true,
            text: 'Risk Level',
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        }
      }
    }
  });
}

// Load ML risks when page loads
document.addEventListener('DOMContentLoaded', function() {
  loadMLRisks();
});

// Correlation Analysis Functions
let scatterChart = null;
let xHistogramChart = null;
let yHistogramChart = null;

function openCorrelationModal() {
  document.getElementById('correlationModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeCorrelationModal() {
  document.getElementById('correlationModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  // Reset form
  document.getElementById('correlationX').value = '';
  document.getElementById('correlationY').value = '';
  document.getElementById('correlationMethod').value = 'pearson';
  document.getElementById('correlationResults').classList.add('hidden');
  document.getElementById('correlationLoading').classList.add('hidden');
  // Destroy scatter chart if exists
  if (scatterChart) {
    scatterChart.destroy();
    scatterChart = null;
  }
}

function calculateCorrelation() {
  const variableX = document.getElementById('correlationX').value;
  const variableY = document.getElementById('correlationY').value;
  const method = document.getElementById('correlationMethod').value;
  
  if (!variableX || !variableY) {
    alert('Please select both variables');
    return;
  }
  
  if (variableX === variableY) {
    alert('Please select different variables');
    return;
  }
  
  // Show loading
  document.getElementById('correlationLoading').classList.remove('hidden');
  document.getElementById('correlationResults').classList.add('hidden');
  
  // Get student data
  const studentData = @json($analytics['student_rankings']);
  const studentMetrics = @json($analytics['student_metrics']);
  const studentAssessmentScores = @json($analytics['student_assessment_scores']);
  const studentTypeAverages = @json($analytics['student_type_averages']);
  
  // Extract variables
  const xValues = [];
  const yValues = [];
  const xValuesForHistogram = [];
  const yValuesForHistogram = [];
  const labels = [];
  
  studentData.forEach(ranking => {
    const studentId = ranking.student.id;
    const metrics = studentMetrics[studentId] || {};
    
    let xValue, yValue;
    
    // Extract X variable value
    if (variableX === 'sex') {
      if (ranking.student.gender) {
        xValue = {
          categorical: ranking.student.gender.toLowerCase(), // 'male' or 'female'
          binary: ranking.student.gender.toLowerCase() === 'male' ? 1 : 0 // 1 for male, 0 for female
        };
      } else {
        xValue = null; // Skip this student
      }
    } else {
      switch(variableX) {
        case 'current_grade': xValue = ranking.current_grade; break;
        case 'average_score': xValue = metrics.avg_score_pct || 0; break;
        case 'missed_submission_pct': xValue = metrics.missed_submission_pct || 0; break;
        case 'quiz1': xValue = studentAssessmentScores[studentId]?.['Quiz 1'] || null; break;
        case 'quiz2': xValue = studentAssessmentScores[studentId]?.['Quiz 2'] || null; break;
        case 'quiz3': xValue = studentAssessmentScores[studentId]?.['Quiz 3'] || null; break;
        case 'midterm': xValue = studentAssessmentScores[studentId]?.['Midterm'] || null; break;
        case 'final': xValue = studentAssessmentScores[studentId]?.['Final'] || null; break;
        case 'attendance': xValue = studentAssessmentScores[studentId]?.['Attendance'] || null; break;
        case 'quiz_avg': xValue = studentTypeAverages[studentId]?.['Quiz'] || null; break;
        case 'midterm_avg': xValue = studentTypeAverages[studentId]?.['Midterm'] || null; break;
        case 'final_avg': xValue = studentTypeAverages[studentId]?.['Final'] || null; break;
      }
    }
    
    // Extract Y variable value
    if (variableY === 'sex') {
      if (ranking.student.gender) {
        yValue = {
          categorical: ranking.student.gender.toLowerCase(), // 'male' or 'female'
          binary: ranking.student.gender.toLowerCase() === 'male' ? 1 : 0 // 1 for male, 0 for female
        };
      } else {
        yValue = null; // Skip this student
      }
    } else {
      switch(variableY) {
        case 'current_grade': yValue = ranking.current_grade; break;
        case 'average_score': yValue = metrics.avg_score_pct || 0; break;
        case 'missed_submission_pct': yValue = metrics.missed_submission_pct || 0; break;
        case 'quiz1': yValue = studentAssessmentScores[studentId]?.['Quiz 1'] || null; break;
        case 'quiz2': yValue = studentAssessmentScores[studentId]?.['Quiz 2'] || null; break;
        case 'quiz3': yValue = studentAssessmentScores[studentId]?.['Quiz 3'] || null; break;
        case 'midterm': yValue = studentAssessmentScores[studentId]?.['Midterm'] || null; break;
        case 'final': yValue = studentAssessmentScores[studentId]?.['Final'] || null; break;
        case 'attendance': yValue = studentAssessmentScores[studentId]?.['Attendance'] || null; break;
        case 'quiz_avg': yValue = studentTypeAverages[studentId]?.['Quiz'] || null; break;
        case 'midterm_avg': yValue = studentTypeAverages[studentId]?.['Midterm'] || null; break;
        case 'final_avg': yValue = studentTypeAverages[studentId]?.['Final'] || null; break;
      }
    }
    
    // Only add if both values are valid
    // For categorical variables, check if they're not null/undefined
    // For numeric variables, check if they're not null/undefined and not NaN
    const xIsValid = xValue !== null && xValue !== undefined && 
                     (typeof xValue === 'string' || typeof xValue === 'object' || !isNaN(xValue));
    const yIsValid = yValue !== null && yValue !== undefined && 
                     (typeof yValue === 'string' || typeof yValue === 'object' || !isNaN(yValue));
    
    // Debug: Log the extracted values and validation
    console.log(`Student ${ranking.student.first_name}: xValue=${JSON.stringify(xValue)} (${typeof xValue}), yValue=${JSON.stringify(yValue)} (${typeof yValue})`);
    console.log(`Validation: xIsValid=${xIsValid}, yIsValid=${yIsValid}`);
    
    if (xIsValid && yIsValid) {
      // Prepare values based on data type and method
      let finalXValue, finalYValue;
      let finalXValueForHistogram, finalYValueForHistogram;
      
      if (typeof xValue === 'object' && xValue.categorical !== undefined) {
        // This is a gender variable, choose appropriate version based on method
        if (method === 'point_biserial' || method === 'independent_t_test') {
          finalXValue = xValue.binary; // Use binary (0/1) for these methods
          finalXValueForHistogram = xValue.categorical; // Use categorical for histogram
        } else {
          finalXValue = xValue.categorical; // Use categorical for others
          finalXValueForHistogram = xValue.categorical; // Use categorical for histogram
        }
      } else {
        finalXValue = xValue;
        finalXValueForHistogram = xValue;
      }
      
      if (typeof yValue === 'object' && yValue.categorical !== undefined) {
        // This is a gender variable, choose appropriate version based on method
        if (method === 'point_biserial' || method === 'independent_t_test') {
          finalYValue = yValue.binary; // Use binary (0/1) for these methods
          finalYValueForHistogram = yValue.categorical; // Use categorical for histogram
        } else {
          finalYValue = yValue.categorical; // Use categorical for others
          finalYValueForHistogram = yValue.categorical; // Use categorical for histogram
        }
      } else {
        finalYValue = yValue;
        finalYValueForHistogram = yValue;
      }
      
      xValues.push(finalXValue);
      yValues.push(finalYValue);
      xValuesForHistogram.push(finalXValueForHistogram);
      yValuesForHistogram.push(finalYValueForHistogram);
      labels.push(`${ranking.student.first_name} ${ranking.student.last_name}`);
      console.log(`Added student ${ranking.student.first_name} to arrays: x=${finalXValue}, y=${finalYValue}`);
    } else {
      console.log(`Skipped student ${ranking.student.first_name} due to invalid values`);
    }
  });
  
  // Debug: Log the final arrays
  console.log('Final xValues:', xValues);
  console.log('Final yValues:', yValues);
  console.log('xValues.length:', xValues.length);
  console.log('yValues.length:', yValues.length);
  
  if (xValues.length < 2) {
    // Check if the issue is with demographic variables
    if (variableX === 'age' || variableX === 'sex' || variableY === 'age' || variableY === 'sex') {
      alert('Demographic data (age/sex) is not available in the current dataset. Please use performance metrics like Current Grade, Average Score, or Assessment scores instead.');
    } else {
    alert('Not enough valid data points for correlation analysis');
    }
    document.getElementById('correlationLoading').classList.add('hidden');
    return;
  }
  
  // Check if the selected method is appropriate for the data types
  const hasCategorical = xValues.some(val => typeof val === 'string') || yValues.some(val => typeof val === 'string');
  const hasContinuous = xValues.some(val => typeof val === 'number') || yValues.some(val => typeof val === 'number');
  
  if (hasCategorical && method === 'pearson') {
    alert('Pearson correlation requires both variables to be continuous. For categorical variables, use Spearman, Kendall, or Point-Biserial.');
    document.getElementById('correlationLoading').classList.add('hidden');
    return;
  }
  
  // Warning for nominal categorical data with rank-based methods
  if (hasCategorical && (method === 'spearman' || method === 'kendall')) {
    const hasGender = variableX === 'sex' || variableY === 'sex';
    if (hasGender) {
      const proceed = confirm('⚠️ WARNING: You are using rank-based correlation (Spearman/Kendall) with nominal categorical data (Gender).\n\nWhile this will technically work, the interpretation may not be statistically sound since gender is not ordinal.\n\nFor Gender vs Quiz Scores, consider using:\n• Point-Biserial Correlation (correlation coefficient)\n• Independent T-Test (group comparison)\n\nDo you want to proceed anyway?');
      if (!proceed) {
        document.getElementById('correlationLoading').classList.add('hidden');
        return;
      }
    }
  }
  
  if (hasCategorical && hasContinuous && method !== 'spearman' && method !== 'kendall') {
    alert('When mixing categorical and continuous variables, use Spearman or Kendall correlation methods.');
    document.getElementById('correlationLoading').classList.add('hidden');
    return;
  }
  
  // Calculate correlation
  const correlation = calculateCorrelationCoefficient(xValues, yValues, method);
  
  // Display results
  displayCorrelationResults(correlation, xValues, yValues, xValuesForHistogram, yValuesForHistogram, labels, variableX, variableY, method);
  
  // Hide loading
  document.getElementById('correlationLoading').classList.add('hidden');
}

function calculateCorrelationCoefficient(x, y, method) {
  const n = x.length;
  
  if (method === 'pearson') {
    // Pearson correlation
    const sumX = x.reduce((a, b) => a + b, 0);
    const sumY = y.reduce((a, b) => a + b, 0);
    const sumXY = x.reduce((sum, xi, i) => sum + xi * y[i], 0);
    const sumX2 = x.reduce((sum, xi) => sum + xi * xi, 0);
    const sumY2 = y.reduce((sum, yi) => sum + yi * yi, 0);
    
    const numerator = n * sumXY - sumX * sumY;
    const denominator = Math.sqrt((n * sumX2 - sumX * sumX) * (n * sumY2 - sumY * sumY));
    
    return denominator === 0 ? 0 : numerator / denominator;
  } else if (method === 'spearman') {
    // Spearman rank correlation
    const rankX = getRanks(x);
    const rankY = getRanks(y);
    
    const sumD2 = rankX.reduce((sum, rx, i) => sum + Math.pow(rx - rankY[i], 2), 0);
    return 1 - (6 * sumD2) / (n * (n * n - 1));
  } else if (method === 'kendall') {
    // Kendall's tau
    let concordant = 0;
    let discordant = 0;
    
    for (let i = 0; i < n; i++) {
      for (let j = i + 1; j < n; j++) {
        const xDiff = x[i] - x[j];
        const yDiff = y[i] - y[j];
        
        if (xDiff * yDiff > 0) {
          concordant++;
        } else if (xDiff * yDiff < 0) {
          discordant++;
        }
      }
    }
    
    return (concordant - discordant) / (concordant + discordant);
  } else if (method === 'point_biserial') {
    // Point-Biserial Correlation
    // This is used when one variable is binary (0/1) and the other is continuous
    const binaryValues = x.every(val => val === 0 || val === 1) ? x : y;
    const continuousValues = x.every(val => val === 0 || val === 1) ? y : x;
    
    if (!binaryValues.every(val => val === 0 || val === 1)) {
      alert('Point-Biserial correlation requires one binary variable (0/1) and one continuous variable');
      return 0;
    }
    
    const n = binaryValues.length;
    const p = binaryValues.reduce((sum, val) => sum + val, 0) / n; // proportion of 1s
    const q = 1 - p; // proportion of 0s
    
    if (p === 0 || q === 0) {
      alert('Point-Biserial correlation requires both 0s and 1s in the binary variable');
  return 0;
    }
    
    const mean1 = continuousValues.reduce((sum, val, i) => sum + (binaryValues[i] === 1 ? val : 0), 0) / (n * p);
    const mean0 = continuousValues.reduce((sum, val, i) => sum + (binaryValues[i] === 0 ? val : 0), 0) / (n * q);
    
    const variance = continuousValues.reduce((sum, val) => sum + Math.pow(val - (mean1 * p + mean0 * q), 2), 0) / n;
    const stdDev = Math.sqrt(variance);
    
    if (stdDev === 0) {
      return 0;
    }
    
    return ((mean1 - mean0) * Math.sqrt(p * q)) / stdDev;
    
  } else if (method === 'independent_t_test') {
    // Independent T-Test
    // This is used when comparing means between two groups
    const binaryValues = x.every(val => val === 0 || val === 1) ? x : y;
    const continuousValues = x.every(val => val === 0 || val === 1) ? y : x;
    
    if (!binaryValues.every(val => val === 0 || val === 1)) {
      alert('Independent T-Test requires one binary variable (0/1) and one continuous variable');
      return { error: 'Invalid data for t-test' };
    }
    
    const group1 = continuousValues.filter((_, i) => binaryValues[i] === 1);
    const group0 = continuousValues.filter((_, i) => binaryValues[i] === 0);
    
    if (group1.length === 0 || group0.length === 0) {
      alert('Independent T-Test requires both groups to have data');
      return { error: 'Insufficient data for t-test' };
    }
    
    // Warn about small sample sizes
    if (group1.length < 2 || group0.length < 2) {
      alert('⚠️ WARNING: Small sample sizes detected. T-Test results may not be reliable with less than 2 observations per group.');
    }
    
    const mean1 = group1.reduce((sum, val) => sum + val, 0) / group1.length;
    const mean0 = group0.reduce((sum, val) => sum + val, 0) / group0.length;
    
    // Handle case where one group has only 1 observation (no variance)
    if (group1.length === 1 && group0.length === 1) {
      return { error: 'Cannot perform t-test with only 1 observation per group' };
    }
    
    // Calculate variances, handling single-observation groups
    const var1 = group1.length > 1 ? 
      group1.reduce((sum, val) => sum + Math.pow(val - mean1, 2), 0) / (group1.length - 1) : 0;
    const var0 = group0.length > 1 ? 
      group0.reduce((sum, val) => sum + Math.pow(val - mean0, 2), 0) / (group0.length - 1) : 0;
    
    const pooledVar = ((group1.length - 1) * var1 + (group0.length - 1) * var0) / (group1.length + group0.length - 2);
    const pooledStd = Math.sqrt(pooledVar);
    
    if (pooledStd === 0 || isNaN(pooledStd)) {
      return { error: 'Insufficient variance in data for t-test' };
    }
    
    const tStat = (mean1 - mean0) / (pooledStd * Math.sqrt(1/group1.length + 1/group0.length));
    const df = group1.length + group0.length - 2;
    
    // Debug logging
    console.log('T-Test Debug:', {
      group1: { values: group1, mean: mean1, n: group1.length },
      group0: { values: group0, mean: mean0, n: group0.length },
      pooledStd: pooledStd,
      tStat: tStat,
      df: df
    });
    
    // Calculate p-value (approximate using t-distribution)
    const pValue = calculatePValue(tStat, df);
    
    return {
      type: 't_test',
      t_statistic: tStat,
      p_value: pValue,
      degrees_of_freedom: df,
      group_means: {
        group1: { mean: mean1, n: group1.length, label: 'Group 1' },
        group0: { mean: mean0, n: group0.length, label: 'Group 0' }
      },
      pooled_std: pooledStd,
      significant: pValue < 0.05
    };
  }
  
  return 0; // Default return for unknown methods
}

// Helper function to calculate approximate p-value for t-test
function calculatePValue(tStat, df) {
  // Better approximation using t-distribution properties
  // This is still simplified but more accurate than the previous version
  
  const absT = Math.abs(tStat);
  
  // For very small degrees of freedom (df < 5), t-distribution is more spread out
  if (df <= 2) {
    if (absT > 4.0) return 0.05;
    if (absT > 2.0) return 0.20;
    if (absT > 1.0) return 0.40;
    return 0.60;
  } else if (df <= 5) {
    if (absT > 3.0) return 0.05;
    if (absT > 2.0) return 0.10;
    if (absT > 1.5) return 0.20;
    if (absT > 1.0) return 0.35;
    return 0.50;
  } else {
    // For larger df, closer to normal distribution
    if (absT > 2.5) return 0.02;
    if (absT > 2.0) return 0.05;
    if (absT > 1.5) return 0.15;
    if (absT > 1.0) return 0.30;
    return 0.50;
  }
}

function getRanks(values) {
  const sorted = [...values].sort((a, b) => a - b);
  return values.map(v => sorted.indexOf(v) + 1);
}

function displayCorrelationResults(result, xValues, yValues, xValuesForHistogram, yValuesForHistogram, labels, varX, varY, method) {
  // Check if this is a t-test result
  if (result.type === 't_test') {
    displayTTestResults(result, xValues, yValues, xValuesForHistogram, yValuesForHistogram, labels, varX, varY);
    return;
  }
  
  // Original correlation display logic
  const correlation = result;
  
  // Format correlation coefficient
  const formattedCorrelation = correlation.toFixed(4);
  
  // Determine strength
  const absCorr = Math.abs(correlation);
  let strength, color;
  
  if (absCorr >= 0.8) {
    strength = 'Very Strong';
    color = 'text-green-600';
  } else if (absCorr >= 0.6) {
    strength = 'Strong';
    color = 'text-blue-600';
  } else if (absCorr >= 0.4) {
    strength = 'Moderate';
    color = 'text-yellow-600';
  } else if (absCorr >= 0.2) {
    strength = 'Weak';
    color = 'text-orange-600';
  } else {
    strength = 'Very Weak';
    color = 'text-red-600';
  }
  
  // Determine direction
  const direction = correlation > 0 ? 'Positive' : correlation < 0 ? 'Negative' : 'No';
  
  // Update UI
  document.getElementById('correlationCoefficient').textContent = formattedCorrelation;
  document.getElementById('correlationStrength').textContent = strength;
  document.getElementById('correlationStrength').className = `text-lg font-semibold ${color}`;
  
  const interpretation = `${direction} ${strength.toLowerCase()} correlation (r = ${formattedCorrelation}). `;
  const interpretation2 = correlation > 0 
    ? 'As one variable increases, the other tends to increase as well.'
    : correlation < 0 
    ? 'As one variable decreases, the other tends to increase.'
    : 'There is no linear relationship between the variables.';
  
  document.getElementById('correlationInterpretation').textContent = interpretation + interpretation2;
  
  // Create histograms
  createVariableHistogram('xVariableHistogram', xValuesForHistogram, getVariableLabel(varX), 'indigo');
  createVariableHistogram('yVariableHistogram', yValuesForHistogram, getVariableLabel(varY), 'green');
  
  // Create enhanced scatter plot with regression line
  createEnhancedScatterPlot(xValues, yValues, labels, varX, varY, method, correlation);
  
  // Show results
  document.getElementById('correlationResults').classList.remove('hidden');
}

function displayTTestResults(tTestResult, xValues, yValues, xValuesForHistogram, yValuesForHistogram, labels, varX, varY) {
  // Update UI for t-test results
  document.getElementById('correlationCoefficient').textContent = tTestResult.t_statistic.toFixed(4);
  document.getElementById('correlationStrength').textContent = tTestResult.significant ? 'Significant' : 'Not Significant';
  document.getElementById('correlationStrength').className = `text-lg font-semibold ${tTestResult.significant ? 'text-green-600' : 'text-red-600'}`;
  
  // Create t-test interpretation
  const group1Label = varX === 'sex' ? 'Males' : 'Group 1';
  const group0Label = varX === 'sex' ? 'Females' : 'Group 0';
  
  const interpretation = `Independent T-Test Results: ${group1Label} vs ${group0Label}. `;
  const interpretation2 = `The difference in means (${(tTestResult.group_means.group1.mean - tTestResult.group_means.group0.mean).toFixed(2)}) is ${tTestResult.significant ? 'statistically significant' : 'not statistically significant'} (p = ${tTestResult.p_value.toFixed(3)}).`;
  
  document.getElementById('correlationInterpretation').textContent = interpretation + interpretation2;
  
  // Create histograms
  createVariableHistogram('xVariableHistogram', xValuesForHistogram, getVariableLabel(varX), 'indigo');
  createVariableHistogram('yVariableHistogram', yValuesForHistogram, getVariableLabel(varY), 'green');
  
  // Create t-test visualization (boxplot or bar chart)
  createTTestVisualization(tTestResult, varX, varY);
  
  // Show results
  document.getElementById('correlationResults').classList.remove('hidden');
}

function createVariableHistogram(canvasId, values, label, color) {
  // Destroy existing chart if it exists
  let existingChart = null;
  if (canvasId === 'xVariableHistogram' && xHistogramChart) {
    xHistogramChart.destroy();
    xHistogramChart = null;
  } else if (canvasId === 'yVariableHistogram' && yHistogramChart) {
    yHistogramChart.destroy();
    yHistogramChart = null;
  }
  
  const ctx = document.getElementById(canvasId).getContext('2d');
  
  // Check if values are categorical (strings) or continuous (numbers)
  const isCategorical = values.some(val => typeof val === 'string');
  
  let chartData, chartOptions;
  
  if (isCategorical) {
    // Create categorical bar chart (counts)
    const categories = [...new Set(values)];
    const counts = categories.map(cat => values.filter(val => val === cat).length);
    
    chartData = {
      labels: categories,
      datasets: [{
        label: `Count of ${label}`,
        data: counts,
        backgroundColor: color === 'indigo' ? 'rgba(99, 102, 241, 0.6)' : 'rgba(34, 197, 94, 0.6)',
        borderColor: color === 'indigo' ? 'rgba(99, 102, 241, 1)' : 'rgba(34, 197, 94, 1)',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false
      }]
    };
    
    chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: `Distribution of ${label}`,
          color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151',
          font: {
            size: 14,
            weight: 'bold'
          }
        },
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Count',
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        },
        x: {
          title: {
            display: true,
            text: label,
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        }
      }
    };
  } else {
    // Create histogram for continuous data
    const min = Math.min(...values);
    const max = Math.max(...values);
    const binCount = Math.min(10, Math.ceil(Math.sqrt(values.length)));
    const binSize = (max - min) / binCount;
    
    const bins = [];
    const binLabels = [];
    
    for (let i = 0; i < binCount; i++) {
      const binStart = min + i * binSize;
      const binEnd = min + (i + 1) * binSize;
      const count = values.filter(val => val >= binStart && val < binEnd).length;
      bins.push(count);
      binLabels.push(`${binStart.toFixed(1)}-${binEnd.toFixed(1)}`);
    }
    
    chartData = {
      labels: binLabels,
      datasets: [{
        label: `Frequency of ${label}`,
        data: bins,
        backgroundColor: color === 'indigo' ? 'rgba(99, 102, 241, 0.6)' : 'rgba(34, 197, 94, 0.6)',
        borderColor: color === 'indigo' ? 'rgba(99, 102, 241, 1)' : 'rgba(34, 197, 94, 1)',
        borderWidth: 2,
        borderRadius: 4,
        borderSkipped: false
      }]
    };
    
    chartOptions = {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        title: {
          display: true,
          text: `Distribution of ${label}`,
          color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151',
          font: {
            size: 14,
            weight: 'bold'
          }
        },
        legend: {
          display: false
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: 'Frequency',
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        },
        x: {
          title: {
            display: true,
            text: label,
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        }
      }
    };
  }
  
  // Create and store the chart
  const newChart = new Chart(ctx, {
    type: 'bar',
    data: chartData,
    options: chartOptions
  });
  
  if (canvasId === 'xVariableHistogram') {
    xHistogramChart = newChart;
  } else if (canvasId === 'yVariableHistogram') {
    yHistogramChart = newChart;
  }
}

function createEnhancedScatterPlot(xValues, yValues, labels, varX, varY, method, correlation) {
  // Destroy existing chart
  if (scatterChart) {
    scatterChart.destroy();
  }
  
  const ctx = document.getElementById('scatterPlotChart').getContext('2d');
  
  // Check if this is Point-Biserial (binary vs continuous)
  const isPointBiserial = method === 'point_biserial';
  let processedXValues = xValues;
  
  if (isPointBiserial) {
    // Add jitter to binary values for better visualization
    processedXValues = xValues.map(x => {
      if (x === 0 || x === 1) {
        // Add small random jitter to separate overlapping points
        return x + (Math.random() - 0.5) * 0.1;
      }
      return x;
    });
  }
  
  // Calculate regression line for trend line overlay
  const { slope, intercept } = linearRegression(xValues, yValues);
  
  // Create trend line data
  const minX = Math.min(...xValues);
  const maxX = Math.max(...xValues);
  const trendLine = [
    { x: minX, y: slope * minX + intercept },
    { x: maxX, y: slope * maxX + intercept }
  ];
  
  // Determine trend line style based on method
  let trendLineStyle = {
    borderColor: 'rgba(239, 68, 68, 1)',
    backgroundColor: 'rgba(239, 68, 68, 0.2)',
    borderWidth: 3,
    borderDash: []
  };
  
  if (method === 'spearman') {
    // Smoothed line for Spearman
    trendLineStyle.borderDash = [5, 5];
    trendLineStyle.borderColor = 'rgba(147, 51, 234, 1)';
    trendLineStyle.backgroundColor = 'rgba(147, 51, 234, 0.2)';
  } else if (method === 'point_biserial') {
    // Special styling for Point-Biserial
    trendLineStyle.borderColor = 'rgba(16, 185, 129, 1)';
    trendLineStyle.backgroundColor = 'rgba(16, 185, 129, 0.2)';
    trendLineStyle.borderWidth = 2;
  }
  
  scatterChart = new Chart(ctx, {
    type: 'scatter',
    data: {
      datasets: [
        {
        label: 'Students',
          data: processedXValues.map((x, i) => ({
          x: x,
          y: yValues[i]
        })),
          backgroundColor: isPointBiserial ? 'rgba(16, 185, 129, 0.6)' : 'rgba(99, 102, 241, 0.6)',
          borderColor: isPointBiserial ? 'rgba(16, 185, 129, 1)' : 'rgba(99, 102, 241, 1)',
          borderWidth: 2,
          pointRadius: isPointBiserial ? 8 : 6,
          pointHoverRadius: isPointBiserial ? 10 : 8
        },
        {
          label: 'Trend Line',
          type: 'line',
          data: trendLine,
          fill: false,
          ...trendLineStyle,
          pointRadius: 0,
          pointHoverRadius: 0,
          order: 1
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        tooltip: {
          callbacks: {
            title: function(context) {
              const index = context[0].dataIndex;
              if (context[0].datasetIndex === 0) {
                return labels[index] || `Point ${index + 1}`;
              }
              return 'Trend Line';
            },
            label: function(context) {
              if (context.dataset.label === 'Trend Line') {
                return `Y = ${slope.toFixed(3)}X + ${intercept.toFixed(3)}`;
              }
              // For Point-Biserial, show the actual binary value
              const actualX = xValues[context[0].dataIndex];
              const displayX = isPointBiserial && (actualX === 0 || actualX === 1) 
                ? (actualX === 1 ? 'Group 1' : 'Group 0') 
                : context.parsed.x;
              return [
                `${getVariableLabel(varX)}: ${displayX}`,
                `${getVariableLabel(varY)}: ${context.parsed.y}`
              ];
            }
          }
        },
        title: {
          display: true,
          text: isPointBiserial 
            ? `Point-Biserial Correlation: r = ${correlation.toFixed(4)}`
            : `${method.charAt(0).toUpperCase() + method.slice(1)} Correlation: r = ${correlation.toFixed(4)}`,
          color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151',
          font: {
            size: 16,
            weight: 'bold'
          }
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: getVariableLabel(varX),
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        },
        y: {
          title: {
            display: true,
            text: getVariableLabel(varY),
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        }
      }
    }
  });
}

// Regression Modal Functions
let regressionChart = null;

function openRegressionModal() {
  document.getElementById('regressionModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeRegressionModal() {
  document.getElementById('regressionModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  // Reset form
  document.getElementById('regressionX').value = '';
  document.getElementById('regressionY').value = '';
  document.getElementById('regressionResults').classList.add('hidden');
  document.getElementById('regressionLoading').classList.add('hidden');
  if (regressionChart) {
    regressionChart.destroy();
    regressionChart = null;
  }
}

function runRegression() {
  const xVar = document.getElementById('regressionX').value;
  const yVar = document.getElementById('regressionY').value;
  if (!xVar || !yVar) {
    alert('Please select both variables');
    return;
  }
  if (xVar === yVar) {
    alert('Please select different variables');
    return;
  }
  document.getElementById('regressionLoading').classList.remove('hidden');
  document.getElementById('regressionResults').classList.add('hidden');

  // Get student data (reuse from correlation)
  const studentData = @json($analytics['student_rankings']);
  const studentMetrics = @json($analytics['student_metrics']);
  const studentAssessmentScores = @json($analytics['student_assessment_scores']);
  const studentTypeAverages = @json($analytics['student_type_averages']);

  const xValues = [];
  const yValues = [];
  const labels = [];

  studentData.forEach(ranking => {
    const studentId = ranking.student.id;
    const metrics = studentMetrics[studentId] || {};
    
    let xValue, yValue;
    
    // Extract X variable value
    if (xVar === 'sex') {
      if (ranking.student.gender) {
        xValue = {
          categorical: ranking.student.gender.toLowerCase(), // 'male' or 'female'
          binary: ranking.student.gender.toLowerCase() === 'male' ? 1 : 0 // 1 for male, 0 for female
        };
      } else {
        xValue = null; // Skip this student
      }
    } else {
      switch(xVar) {
        case 'current_grade': xValue = ranking.current_grade; break;
        case 'average_score': xValue = metrics.avg_score_pct || 0; break;
        case 'missed_submission_pct': xValue = metrics.missed_submission_pct || 0; break;
        case 'quiz1': xValue = studentAssessmentScores[studentId]?.['Quiz 1'] || null; break;
        case 'quiz2': xValue = studentAssessmentScores[studentId]?.['Quiz 2'] || null; break;
        case 'quiz3': xValue = studentAssessmentScores[studentId]?.['Quiz 3'] || null; break;
        case 'midterm': xValue = studentAssessmentScores[studentId]?.['Midterm'] || null; break;
        case 'final': xValue = studentAssessmentScores[studentId]?.['Final'] || null; break;
        case 'attendance': xValue = studentAssessmentScores[studentId]?.['Attendance'] || null; break;
        case 'quiz_avg': xValue = studentTypeAverages[studentId]?.['Quiz'] || null; break;
        case 'midterm_avg': xValue = studentTypeAverages[studentId]?.['Midterm'] || null; break;
        case 'final_avg': xValue = studentTypeAverages[studentId]?.['Final'] || null; break;
      }
    }
    
    // Extract Y variable value
    if (yVar === 'sex') {
      if (ranking.student.gender) {
        yValue = {
          categorical: ranking.student.gender.toLowerCase(), // 'male' or 'female'
          binary: ranking.student.gender.toLowerCase() === 'male' ? 1 : 0 // 1 for male, 0 for female
        };
      } else {
        yValue = null; // Skip this student
      }
    } else {
      switch(yVar) {
        case 'current_grade': yValue = ranking.current_grade; break;
        case 'average_score': yValue = metrics.avg_score_pct || 0; break;
        case 'missed_submission_pct': yValue = metrics.missed_submission_pct || 0; break;
        case 'quiz1': yValue = studentAssessmentScores[studentId]?.['Quiz 1'] || null; break;
        case 'quiz2': yValue = studentAssessmentScores[studentId]?.['Quiz 2'] || null; break;
        case 'quiz3': yValue = studentAssessmentScores[studentId]?.['Quiz 3'] || null; break;
        case 'midterm': yValue = studentAssessmentScores[studentId]?.['Midterm'] || null; break;
        case 'final': yValue = studentAssessmentScores[studentId]?.['Final'] || null; break;
        case 'attendance': yValue = studentAssessmentScores[studentId]?.['Attendance'] || null; break;
        case 'quiz_avg': yValue = studentTypeAverages[studentId]?.['Quiz'] || null; break;
        case 'midterm_avg': yValue = studentTypeAverages[studentId]?.['Midterm'] || null; break;
        case 'final_avg': yValue = studentTypeAverages[studentId]?.['Final'] || null; break;
      }
    }
    
    // Only add if both values are valid
    // For categorical variables, check if they're not null/undefined
    // For numeric variables, check if they're not null/undefined and not NaN
    const xIsValid = xValue !== null && xValue !== undefined && 
                     (typeof xValue === 'string' || typeof xValue === 'object' || !isNaN(xValue));
    const yIsValid = yValue !== null && yValue !== undefined && 
                     (typeof yValue === 'string' || typeof yValue === 'object' || !isNaN(yValue));
    
    if (xIsValid && yIsValid) {
      // Prepare values based on data type
      let finalXValue, finalYValue;
      
      // Handle gender variables (which now have both categorical and binary versions)
      if (typeof xValue === 'object' && xValue.categorical !== undefined) {
        // For regression, we'll use the binary version (0/1) for gender
        finalXValue = xValue.binary;
      } else {
        finalXValue = xValue;
      }
      
      if (typeof yValue === 'object' && yValue.categorical !== undefined) {
        // For regression, we'll use the binary version (0/1) for gender
        finalYValue = yValue.binary;
      } else {
        finalYValue = yValue;
      }
      
      xValues.push(finalXValue);
      yValues.push(finalYValue);
      labels.push(`${ranking.student.first_name} ${ranking.student.last_name}`);
    }
  });
  if (xValues.length < 2) {
    // Check if the issue is with demographic variables
    if (xVar === 'sex' || yVar === 'sex') {
      alert('Gender data is available but may not have enough valid data points. Please try different variable combinations.');
    } else {
      alert('Not enough valid data points for regression analysis');
    }
    document.getElementById('regressionLoading').classList.add('hidden');
    return;
  }
  // Calculate regression
  const { slope, intercept, r2 } = linearRegression(xValues, yValues);
  // Display results
  document.getElementById('regressionEquation').textContent = `Y = ${slope.toFixed(3)}X + ${intercept.toFixed(3)}`;
  document.getElementById('regressionR2').textContent = r2.toFixed(4);
  // Plot
  createRegressionScatterPlot(xValues, yValues, labels, slope, intercept, xVar, yVar);
  document.getElementById('regressionResults').classList.remove('hidden');
  document.getElementById('regressionLoading').classList.add('hidden');
}

function linearRegression(x, y) {
  const n = x.length;
  const sumX = x.reduce((a, b) => a + b, 0);
  const sumY = y.reduce((a, b) => a + b, 0);
  const sumXY = x.reduce((sum, xi, i) => sum + xi * y[i], 0);
  const sumX2 = x.reduce((sum, xi) => sum + xi * xi, 0);
  const slope = (n * sumXY - sumX * sumY) / (n * sumX2 - sumX * sumX);
  const intercept = (sumY - slope * sumX) / n;
  const meanY = sumY / n;
  const ssTot = y.reduce((sum, yi) => sum + Math.pow(yi - meanY, 2), 0);
  const ssRes = y.reduce((sum, yi, i) => sum + Math.pow(yi - (slope * x[i] + intercept), 2), 0);
  const r2 = 1 - ssRes / ssTot;
  return { slope, intercept, r2 };
}

function createRegressionScatterPlot(xValues, yValues, labels, slope, intercept, varX, varY) {
  if (regressionChart) {
    regressionChart.destroy();
  }
  const ctx = document.getElementById('regressionScatterPlot').getContext('2d');
  // Scatter data
  const scatterData = xValues.map((x, i) => ({ x: x, y: yValues[i] }));
  // Regression line (min to max x)
  const minX = Math.min(...xValues);
  const maxX = Math.max(...xValues);
  const regLine = [
    { x: minX, y: slope * minX + intercept },
    { x: maxX, y: slope * maxX + intercept }
  ];
  regressionChart = new Chart(ctx, {
    type: 'scatter',
    data: {
      datasets: [
        {
          label: 'Students',
          data: scatterData,
          backgroundColor: 'rgba(16, 185, 129, 0.6)',
          borderColor: 'rgba(16, 185, 129, 1)',
        borderWidth: 2,
        pointRadius: 6,
        pointHoverRadius: 8
        },
        {
          label: 'Regression Line',
          type: 'line',
          data: regLine,
          fill: false,
          borderColor: 'rgba(34,197,94,1)',
          backgroundColor: 'rgba(34,197,94,0.2)',
          borderWidth: 3,
          pointRadius: 0,
          pointHoverRadius: 0,
          order: 1
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        tooltip: {
          callbacks: {
            title: function(context) {
              const index = context[0].dataIndex;
              if (context[0].datasetIndex === 0) {
              return labels[index] || `Point ${index + 1}`;
              }
              return 'Regression Line';
            },
            label: function(context) {
              if (context.dataset.label === 'Regression Line') {
                return `Y = ${slope.toFixed(3)}X + ${intercept.toFixed(3)}`;
              }
              return [
                `${getVariableLabel(varX)}: ${context.parsed.x}`,
                `${getVariableLabel(varY)}: ${context.parsed.y}`
              ];
            }
          }
        }
      },
      scales: {
        x: {
          title: {
            display: true,
            text: getVariableLabel(varX),
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        },
        y: {
          title: {
            display: true,
            text: getVariableLabel(varY),
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        }
      }
    }
  });
}

function getVariableLabel(variable) {
  if (variable === 'age') {
    return 'Age (years)';
  }
  
  if (variable === 'sex') {
    return 'Gender (Categorical)';
  }
  
  if (variable.startsWith('assessment_')) {
    const assessmentName = variable.replace('assessment_', '');
    return `${assessmentName} (%)`;
  }
  
  if (variable.startsWith('type_avg_')) {
    const typeName = variable.replace('type_avg_', '');
    return `${typeName} Average (%)`;
  }
  
  const labels = {
    'current_grade': 'Current Grade (%)',
    'avg_score_pct': 'Average Score (%)',
    'variation_score_pct': 'Score Variation (%)',
    'late_submission_pct': 'Late Submissions (%)',
    'missed_submission_pct': 'Missed Submissions (%)',
    'risk_score': 'Risk Score (%)',
    'completed_assessments': 'Completed Assessments',
    'total_assessments': 'Total Assessments'
  };
  return labels[variable] || variable;
}

// Create Assessment Scatter Plots
function createAssessmentScatterPlots() {
  const assessmentData = @json($analytics['assessment_difficulty']);
  const studentData = @json($analytics['student_rankings']);
  const studentAssessmentScores = @json($analytics['student_assessment_scores']);
  
  assessmentData.forEach((assessment, index) => {
    const canvas = document.getElementById(`assessmentScatterChart_${index}`);
    if (!canvas) return;
    
    const ctx = canvas.getContext('2d');
    
    // Get scores for this assessment
    const scores = [];
    const labels = [];
    
    studentData.forEach(ranking => {
      const studentId = ranking.student.id;
      const score = studentAssessmentScores[studentId]?.[assessment.name];
      
      if (score !== null && score !== undefined) {
        scores.push(score);
        labels.push(`${ranking.student.first_name} ${ranking.student.last_name}`);
      }
    });
    
    if (scores.length === 0) return;
    
    // Create scatter plot data
    const scatterData = scores.map((score, i) => ({
      x: i + 1, // Student position
      y: score
    }));
    
    // Determine color based on difficulty
    let pointColor;
    switch(assessment.difficulty_level) {
      case 'Easy':
        pointColor = 'rgba(34, 197, 94, 0.6)'; // Green
        break;
      case 'Medium':
        pointColor = 'rgba(245, 158, 11, 0.6)'; // Yellow
        break;
      case 'Hard':
        pointColor = 'rgba(239, 68, 68, 0.6)'; // Red
        break;
      default:
        pointColor = 'rgba(107, 114, 128, 0.6)'; // Gray
    }
    
    new Chart(ctx, {
      type: 'scatter',
      data: {
        datasets: [{
          label: 'Student Scores',
          data: scatterData,
          backgroundColor: pointColor,
          borderColor: pointColor.replace('0.6', '1'),
          borderWidth: 2,
          pointRadius: 6,
          pointHoverRadius: 8
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              title: function(context) {
                const index = context[0].dataIndex;
                return labels[index] || `Student ${index + 1}`;
              },
              label: function(context) {
                return `Score: ${context.parsed.y.toFixed(1)}%`;
              }
            }
          }
        },
        scales: {
          x: {
            title: {
              display: true,
              text: 'Students',
              color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
            },
            ticks: {
              stepSize: 1,
              color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
            },
            grid: {
              color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
            }
          },
          y: {
            title: {
              display: true,
              text: 'Score (%)',
              color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
            },
            min: 0,
            max: 100,
            ticks: {
              color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
            },
            grid: {
              color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
            }
          }
        }
      }
    });
  });
}

// Initialize assessment scatter plots when page loads
document.addEventListener('DOMContentLoaded', function() {
  createAssessmentScatterPlots();
});

function createTTestVisualization(tTestResult, varX, varY) {
  // Destroy existing chart
  if (scatterChart) {
    scatterChart.destroy();
  }
  
  const ctx = document.getElementById('scatterPlotChart').getContext('2d');
  
  // Create bar chart for group means
  const groupLabels = varX === 'sex' ? ['Females', 'Males'] : ['Group 0', 'Group 1'];
  const groupMeans = [tTestResult.group_means.group0.mean, tTestResult.group_means.group1.mean];
  const groupSizes = [tTestResult.group_means.group0.n, tTestResult.group_means.group1.n];
  
  // Calculate standard errors for error bars
  const standardErrors = [
    tTestResult.pooled_std / Math.sqrt(groupSizes[0]),
    tTestResult.pooled_std / Math.sqrt(groupSizes[1])
  ];
  
  scatterChart = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: groupLabels,
      datasets: [{
        label: 'Mean Score',
        data: groupMeans,
        backgroundColor: ['rgba(99, 102, 241, 0.6)', 'rgba(239, 68, 68, 0.6)'],
        borderColor: ['rgba(99, 102, 241, 1)', 'rgba(239, 68, 68, 1)'],
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        tooltip: {
          callbacks: {
            title: function(context) {
              return groupLabels[context[0].dataIndex];
            },
            label: function(context) {
              const index = context[0].dataIndex;
              return [
                `Mean: ${context.parsed.y.toFixed(2)}`,
                `n: ${groupSizes[index]}`,
                `SE: ±${standardErrors[index].toFixed(2)}`,
                `t = ${tTestResult.t_statistic.toFixed(3)}`,
                `p = ${tTestResult.p_value.toFixed(3)}`,
                `df = ${tTestResult.degrees_of_freedom}`
              ];
            }
          }
        },
        title: {
          display: true,
          text: `Independent T-Test: ${getVariableLabel(varX)} vs ${getVariableLabel(varY)}`,
          color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151',
          font: {
            size: 16,
            weight: 'bold'
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          title: {
            display: true,
            text: getVariableLabel(varY),
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        },
        x: {
          title: {
            display: true,
            text: getVariableLabel(varX),
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          ticks: {
            color: document.documentElement.classList.contains('dark') ? '#f3f4f6' : '#374151'
          },
          grid: {
            color: document.documentElement.classList.contains('dark') ? '#374151' : '#e5e7eb'
          }
        }
      }
    }
  });
}
</script>
@endsection 