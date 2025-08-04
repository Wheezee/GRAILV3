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
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">{{ $student->first_name }} {{ $student->last_name }} Analysis</span>
    </li>
  </ol>
</nav>

<div class="max-w-7xl mx-auto py-8">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold mb-2">{{ $student->first_name }} {{ $student->last_name }} <span class="text-gray-500">({{ $student->student_id }})</span></h1>
    <p class="mb-4 text-gray-600">Email: <a href="mailto:{{ $student->email }}" class="underline">{{ $student->email }}</a></p>
    <p class="mb-6 text-gray-700 dark:text-gray-300 font-medium">Subject: <span class="font-semibold">{{ $subject->code }} - {{ $subject->title }}</span></p>
    </div>

    <!-- Risk Level Banner -->
    <div class="mb-8">
        @php
            $riskColors = [
                'High Risk' => 'bg-red-100 border-red-300 text-red-800',
                'Medium Risk' => 'bg-yellow-100 border-yellow-300 text-yellow-800',
                'Low Risk' => 'bg-green-100 border-green-300 text-green-800',
                'Safe' => 'bg-blue-100 border-blue-300 text-blue-800'
            ];
            $riskColor = $riskColors[$analytics['risk_level']] ?? 'bg-gray-100 border-gray-300 text-gray-800';
        @endphp
        <div class="p-4 border rounded-lg {{ $riskColor }}">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-lg">Risk Level: {{ $analytics['risk_level'] }}</h3>
                    <p class="text-sm mt-1">Current Grade: {{ $analytics['overall_average'] }}% 
                        @if($analytics['grade_margin'] < 0)
                            ({{ abs($analytics['grade_margin']) }}% below passing)
                        @elseif($analytics['grade_margin'] > 0)
                            ({{ $analytics['grade_margin'] }}% above passing)
                        @else
                            (at passing threshold)
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-2xl font-bold">{{ $analytics['overall_average'] }}%</div>
                    <div class="text-sm">Overall Average</div>
                </div>
            </div>
        </div>
    </div>

    <!-- At-a-Glance Stats -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">At-a-Glance Statistics</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i data-lucide="trending-up" class="w-6 h-6 text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Average</p>
                        <p class="text-2xl font-bold">{{ $analytics['overall_average'] }}%</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 bg-orange-100 rounded-lg">
                        <i data-lucide="activity" class="w-6 h-6 text-orange-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Variation</p>
                        <p class="text-2xl font-bold">{{ $analytics['overall_std_dev'] }}%</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i data-lucide="clock" class="w-6 h-6 text-red-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Late Submissions</p>
                        <p class="text-2xl font-bold">{{ array_sum(array_column($analytics['type_stats'], 'late_count')) }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i data-lucide="x-circle" class="w-6 h-6 text-yellow-600"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Missed</p>
                        <p class="text-2xl font-bold">{{ array_sum(array_column($analytics['type_stats'], 'missed_count')) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Risk Fingerprint Radar Chart -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Risk Fingerprint</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
            <canvas id="radarChart" height="300"></canvas>
        </div>
    </div>

    <!-- Early Warning & Intervention Guide -->
    @if(!empty($analytics['risk_factors']))
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Early Warning & Intervention Guide</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @foreach($analytics['risk_factors'] as $factor)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold">{{ $factor['type'] }}</h3>
                        <span class="px-3 py-1 rounded-full text-xs font-medium 
                            @if($factor['severity'] === 'high') bg-red-100 text-red-800
                            @elseif($factor['severity'] === 'medium') bg-yellow-100 text-yellow-800
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($factor['severity']) }} Risk
                        </span>
                    </div>
                    <p class="text-gray-600 mb-4">{{ $factor['description'] }}</p>
                    <div class="space-y-2">
                        <h4 class="font-medium text-sm">Suggested Actions:</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            @foreach($factor['suggestions'] as $suggestion)
                            <li class="flex items-center">
                                <i data-lucide="check-circle" class="w-4 h-4 text-green-500 mr-2"></i>
                                {{ $suggestion }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Variation Heatmap -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Variation Heatmap</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="text-left py-2">Assessment Type</th>
                            <th class="text-left py-2">Assessment</th>
                            <th class="text-center py-2">Score (%)</th>
                            <th class="text-center py-2">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($analytics['heatmap_data'] as $data)
                        @php
                            $scoreColor = $data['percentage'] >= 90 ? 'bg-green-100 text-green-800' :
                                         ($data['percentage'] >= 80 ? 'bg-blue-100 text-blue-800' :
                                         ($data['percentage'] >= 70 ? 'bg-yellow-100 text-yellow-800' :
                                         ($data['percentage'] >= 60 ? 'bg-orange-100 text-orange-800' :
                                         'bg-red-100 text-red-800')));
                            
                            $status = $data['is_missed'] ? 'Missed' : 
                                     ($data['is_late'] ? 'Late' : 'On Time');
                            $statusColor = $data['is_missed'] ? 'bg-red-100 text-red-800' :
                                         ($data['is_late'] ? 'bg-yellow-100 text-yellow-800' :
                                         'bg-green-100 text-green-800');
                        @endphp
                        <tr class="border-b border-gray-100 dark:border-gray-700">
                            <td class="py-2 font-medium">{{ $data['type'] }}</td>
                            <td class="py-2">{{ $data['assessment'] }}</td>
                            <td class="py-2 text-center">
                                <span class="px-2 py-1 rounded {{ $scoreColor }} font-medium">
                                    {{ $data['percentage'] }}%
                                </span>
                            </td>
                            <td class="py-2 text-center">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $statusColor }}">
                                    {{ $status }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Grade Margin Insight -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Grade Margin Insight</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold">Current Grade: {{ $analytics['overall_average'] }}%</h3>
                    <p class="text-gray-600">
                        @if($analytics['grade_margin'] < 0)
                            {{ abs($analytics['grade_margin']) }}% below passing threshold ({{ $analytics['passing_threshold'] }}%)
                        @elseif($analytics['grade_margin'] > 0)
                            {{ $analytics['grade_margin'] }}% above passing threshold ({{ $analytics['passing_threshold'] }}%)
                        @else
                            At passing threshold ({{ $analytics['passing_threshold'] }}%)
                        @endif
                    </p>
                </div>
                <div class="text-right">
                    @php
                        $marginTag = $analytics['grade_margin'] < -10 ? 'At Risk – Large Gap' :
                                    ($analytics['grade_margin'] < 0 ? 'At Risk – Close' :
                                    ($analytics['overall_average'] >= 80 ? 'Above Average' : 'Safe'));
                        $tagColor = $analytics['grade_margin'] < -10 ? 'bg-red-100 text-red-800' :
                                  ($analytics['grade_margin'] < 0 ? 'bg-yellow-100 text-yellow-800' :
                                  ($analytics['overall_average'] >= 80 ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'));
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-medium {{ $tagColor }}">
                        {{ $marginTag }}
                    </span>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                @php
                    $progress = min(100, max(0, $analytics['overall_average']));
                    $barColor = $analytics['overall_average'] >= 80 ? 'bg-green-500' :
                               ($analytics['overall_average'] >= 70 ? 'bg-yellow-500' :
                               ($analytics['overall_average'] >= 60 ? 'bg-orange-500' : 'bg-red-500'));
                @endphp
                <div class="h-3 rounded-full {{ $barColor }}" style="width: {{ $progress }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-600">
                <span>0%</span>
                <span>{{ $analytics['passing_threshold'] }}% (Passing)</span>
                <span>100%</span>
            </div>
        </div>
    </div>

    <!-- Annotation Support -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Assessment Annotations</h2>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border border-gray-200 dark:border-gray-700">
            <div class="mb-4">
                <button id="addAnnotationBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Add Annotation
                </button>
            </div>
            
            <div id="annotationsList" class="space-y-3">
                <!-- Annotations will be loaded here -->
                <div class="text-gray-500 text-center py-4">
                    <i data-lucide="message-square" class="w-8 h-8 mx-auto mb-2"></i>
                    <p>No annotations yet. Click "Add Annotation" to add notes about this student's performance.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Original Charts -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold mb-4">Scores Over Time</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            @foreach($assessmentTypes as $type)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-6 border border-gray-200 dark:border-gray-700 flex flex-col justify-between min-h-[220px]" style="min-height:220px;">
                <h3 class="font-semibold mb-2">{{ $type->name }}</h3>
                <div class="flex-1 flex items-center justify-center">
                  <canvas id="chart-{{ $type->id }}" height="180" style="max-height: 180px; width: 100%; max-width: 100%;"></canvas>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Annotation Modal -->
<div id="annotationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Add Annotation</h3>
                <form id="annotationForm">
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Assessment</label>
                        <select id="annotationAssessment" class="w-full border border-gray-300 rounded-lg px-3 py-2">
                            <option value="">General Note</option>
                            @foreach($analytics['all_scores'] as $score)
                            <option value="{{ $score['assessment']->id }}">{{ $score['assessment']->name }} ({{ $score['type'] }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium mb-2">Note</label>
                        <textarea id="annotationText" rows="4" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Enter your observation or note about this student's performance..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" id="cancelAnnotation" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancel</button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Radar Chart Data
const radarData = {
    labels: ['Average', 'Variation', 'Late Submissions', 'Missed Assignments', 'Completion Rate'],
    datasets: [{
        label: '{{ $student->first_name }} {{ $student->last_name }}',
        data: [
            {{ $analytics['overall_average'] }},
            {{ $analytics['overall_std_dev'] }},
            {{ array_sum(array_column($analytics['type_stats'], 'late_count')) }},
            {{ array_sum(array_column($analytics['type_stats'], 'missed_count')) }},
            {{ round(array_sum(array_column($analytics['type_stats'], 'completion_rate')) / count($analytics['type_stats']), 1) }}
        ],
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 2,
        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
        pointBorderColor: '#fff',
        pointHoverBackgroundColor: '#fff',
        pointHoverBorderColor: 'rgba(54, 162, 235, 1)'
    }]
};

// Create Radar Chart
new Chart(document.getElementById('radarChart').getContext('2d'), {
    type: 'radar',
    data: radarData,
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            r: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    stepSize: 20
                }
            }
        },
        plugins: {
            legend: {
                display: false
            }
        }
    }
});

// Original Line Charts
@foreach($assessmentTypes as $type)
    const labels{{ $type->id }} = @json($type->assessments->pluck('name'));
    const percents{{ $type->id }} = @json($type->assessments->map(function($a) use ($student) {
        $score = $a->scores->where('student_id', $student->id)->first();
        $max = $a->max_score ?? 0;
        return ($score && $score->score !== null && $max > 0) ? round(($score->score / $max) * 100, 1) : null;
    }));
    new Chart(document.getElementById('chart-{{ $type->id }}').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels{{ $type->id }},
            datasets: [{
                label: '{{ $type->name }} (%)',
                data: percents{{ $type->id }},
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: 'rgba(54, 162, 235, 1)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 2.5,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
@endforeach

// Load existing annotations when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadAnnotations();
});

// Annotation Modal Functionality
document.getElementById('addAnnotationBtn').addEventListener('click', function() {
    document.getElementById('annotationModal').classList.remove('hidden');
});

document.getElementById('cancelAnnotation').addEventListener('click', function() {
    document.getElementById('annotationModal').classList.add('hidden');
});

document.getElementById('annotationForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const assessmentId = document.getElementById('annotationAssessment').value;
    const text = document.getElementById('annotationText').value;
    
    if (!text.trim()) {
        alert('Please enter a note');
        return;
    }
    
    // Send annotation to backend
    fetch('/api/annotations', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            student_id: {{ $student->id }},
            assessment_id: assessmentId || null,
            annotation_text: text,
            annotation_type: assessmentId ? 'assessment_specific' : 'general'
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            addAnnotationToUI(data.annotation);
            document.getElementById('annotationForm').reset();
            document.getElementById('annotationModal').classList.add('hidden');
        } else {
            alert('Error saving annotation');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving annotation');
    });
});

// Close modal when clicking outside
document.getElementById('annotationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

// Function to load annotations from backend
function loadAnnotations() {
    fetch('/api/annotations/student/{{ $student->id }}')
    .then(response => response.json())
    .then(annotations => {
        const annotationsList = document.getElementById('annotationsList');
        
        if (annotations.length === 0) {
            annotationsList.innerHTML = `
                <div class="text-gray-500 text-center py-4">
                    <i data-lucide="message-square" class="w-8 h-8 mx-auto mb-2"></i>
                    <p>No annotations yet. Click "Add Annotation" to add notes about this student's performance.</p>
                </div>
            `;
        } else {
            annotationsList.innerHTML = '';
            annotations.forEach(annotation => {
                addAnnotationToUI(annotation);
            });
        }
    })
    .catch(error => {
        console.error('Error loading annotations:', error);
    });
}

// Function to add annotation to UI
function addAnnotationToUI(annotation) {
    const annotationsList = document.getElementById('annotationsList');
    
    // Remove the "no annotations" message if it exists
    const noAnnotationsMsg = annotationsList.querySelector('.text-gray-500');
    if (noAnnotationsMsg) {
        noAnnotationsMsg.remove();
    }
    
    const annotationHtml = `
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4" data-annotation-id="${annotation.id}">
            <div class="flex justify-between items-start mb-2">
                <div class="flex items-center">
                    <i data-lucide="message-square" class="w-4 h-4 text-blue-500 mr-2"></i>
                    <span class="text-sm font-medium">${annotation.assessment_name ? 'Assessment Note: ' + annotation.assessment_name : 'General Note'}</span>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="text-xs text-gray-500">${annotation.created_at}</span>
                    <button onclick="deleteAnnotation(${annotation.id})" class="text-red-500 hover:text-red-700 text-xs">
                        <i data-lucide="trash-2" class="w-3 h-3"></i>
                    </button>
                </div>
            </div>
            <p class="text-sm text-gray-700 dark:text-gray-300">${annotation.text}</p>
        </div>
    `;
    
    annotationsList.insertAdjacentHTML('afterbegin', annotationHtml);
    lucide.createIcons();
}

// Function to delete annotation
function deleteAnnotation(annotationId) {
    if (!confirm('Are you sure you want to delete this annotation?')) {
        return;
    }
    
    fetch(`/api/annotations/${annotationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const annotationElement = document.querySelector(`[data-annotation-id="${annotationId}"]`);
            if (annotationElement) {
                annotationElement.remove();
                
                // Check if there are no more annotations
                const annotationsList = document.getElementById('annotationsList');
                if (annotationsList.children.length === 0) {
                    annotationsList.innerHTML = `
                        <div class="text-gray-500 text-center py-4">
                            <i data-lucide="message-square" class="w-8 h-8 mx-auto mb-2"></i>
                            <p>No annotations yet. Click "Add Annotation" to add notes about this student's performance.</p>
                        </div>
                    `;
                }
            }
        } else {
            alert('Error deleting annotation');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting annotation');
    });
}
</script>
@endsection