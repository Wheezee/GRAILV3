@extends('layouts.app')

@section('content')
<div class="mb-8">
  <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Dashboard</h2>
  <p class="text-gray-600 dark:text-gray-400 mb-6">Welcome back!</p>

  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
    <!-- Subjects Count -->
    <div class="bg-white dark:bg-gray-800 border border-blue-200 dark:border-blue-700 rounded-lg p-4 shadow-sm flex flex-col items-center">
      <div class="text-2xl mb-1">📚</div>
      <div class="text-xl font-bold text-blue-600 dark:text-blue-400">{{ $totalSubjects ?? '--' }}</div>
      <div class="text-gray-700 dark:text-gray-300 mt-1 text-center text-xs">Subjects</div>
    </div>
    <!-- Student Count -->
    <div class="bg-white dark:bg-gray-800 border border-green-200 dark:border-green-700 rounded-lg p-4 shadow-sm flex flex-col items-center">
      <div class="text-2xl mb-1">👨‍🎓</div>
      <div class="text-xl font-bold text-green-600 dark:text-green-400">{{ $totalStudents ?? '--' }}</div>
      <div class="text-gray-700 dark:text-gray-300 mt-1 text-center text-xs">Enrolled Students</div>
    </div>
    <!-- Assessments Count -->
    <div class="bg-white dark:bg-gray-800 border border-purple-200 dark:border-purple-700 rounded-lg p-4 shadow-sm flex flex-col items-center">
      <div class="text-2xl mb-1">📝</div>
      <div class="text-xl font-bold text-purple-600 dark:text-purple-400">{{ $totalAssessments ?? '--' }}</div>
      <div class="text-gray-700 dark:text-gray-300 mt-1 text-center text-xs">Total Assessments</div>
    </div>
    <!-- Class Sections Count -->
    <div class="bg-white dark:bg-gray-800 border border-yellow-200 dark:border-yellow-700 rounded-lg p-4 shadow-sm flex flex-col items-center">
      <div class="text-2xl mb-1">🏫</div>
      <div class="text-xl font-bold text-yellow-600 dark:text-yellow-400">{{ $totalClassSections ?? '--' }}</div>
      <div class="text-gray-700 dark:text-gray-300 mt-1 text-center text-xs">Class Sections</div>
    </div>
  </div>
</div>
<!-- Latest Assessments by Type Section -->
<div class="mt-10">
  <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Latest Assessments by Type</h3>
  <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 md:grid-cols-3">
    @forelse($latestTypeStats as $typeStat)
      <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm">
        <div class="font-bold text-blue-600 dark:text-blue-400 mb-2">{{ $typeStat['type']->name }}</div>
        @forelse($typeStat['assessments'] as $assessment)
          <div class="mb-2 pb-2 {{ !$loop->last ? 'border-b border-gray-100 dark:border-gray-700' : '' }}">
            <a href="#" class="block rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 px-3 py-2 text-base font-medium text-blue-700 dark:text-blue-300 hover:bg-blue-50 dark:hover:bg-gray-800 hover:border-blue-400 transition-colors duration-150 shadow-sm"
               style="text-decoration: none;"
               onclick="openClassSelectorModal({
                 subjectId: {{ $typeStat['type']->subject_id }},
                 assessmentTypeId: {{ $typeStat['type']->id }},
                 assessmentName: @js($assessment->name),
                 term: @js($assessment->term),
                 assessmentId: {{ $assessment->id }}
               }); return false;">
              <div>{{ $assessment->name }}</div>
              <div class="text-xs text-gray-500 mt-1">Created: {{ $assessment->created_at ? $assessment->created_at->format('M d, Y H:i') : '--' }}</div>
            </a>
          </div>
        @empty
          <div class="text-gray-400 text-sm">No assessments yet.</div>
        @endforelse
      </div>
    @empty
      <div class="text-gray-400 text-sm">No recent assessment types found.</div>
    @endforelse
  </div>
</div>
<!-- Class Selector Modal for Assessments -->
<div id="assessmentClassSelectorModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
  <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
    <div class="mt-3">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100" id="assessmentModalTitle">Select Class Section</h3>
        <button onclick="closeAssessmentClassSelectorModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
      <div id="assessmentClassList" class="space-y-2">
        <!-- Class options will be populated here -->
      </div>
    </div>
  </div>
</div>
<script>
let currentAssessmentData = {};
function openClassSelectorModal(data) {
  currentAssessmentData = data;
  // Fetch class sections for this subject
  fetch(`/api/subjects/${data.subjectId}/classes`)
    .then(response => response.json())
    .then(classes => {
      const modal = document.getElementById('assessmentClassSelectorModal');
      const classList = document.getElementById('assessmentClassList');
      const modalTitle = document.getElementById('assessmentModalTitle');
      modalTitle.textContent = `Select Class for ${data.assessmentName}`;
      classList.innerHTML = '';
      if (classes.length === 0) {
        classList.innerHTML = '<div class="text-gray-400 text-sm">No class sections found for this subject.</div>';
      } else {
        classes.forEach(classSection => {
          const button = document.createElement('button');
          button.className = 'w-full text-left p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors';
          button.innerHTML = `
            <div class=\"font-medium text-gray-900 dark:text-gray-100\">${classSection.section}</div>
            <div class=\"text-sm text-gray-500\">${classSection.schedule} • ${classSection.student_count} students</div>
          `;
          button.onclick = () => navigateToAssessment(data, classSection.id);
          classList.appendChild(button);
        });
      }
      modal.classList.remove('hidden');
    })
    .catch(error => {
      console.error('Error fetching classes:', error);
      alert('Error loading class sections. Please try again.');
    });
}
function closeAssessmentClassSelectorModal() {
  document.getElementById('assessmentClassSelectorModal').classList.add('hidden');
}
function navigateToAssessment(data, classSectionId) {
  const url = `{{ url('/') }}/subjects/${data.subjectId}/classes/${classSectionId}/${data.term}/assessments/${data.assessmentTypeId}`;
  window.location.href = url;
  closeAssessmentClassSelectorModal();
}
document.getElementById('assessmentClassSelectorModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeAssessmentClassSelectorModal();
  }
});
</script>
@endsection