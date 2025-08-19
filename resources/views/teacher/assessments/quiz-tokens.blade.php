@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
  /* Font size controls for maximized table */
  .table-header {
    transition: all 0.3s ease;
  }
  
  .table-cell {
    transition: all 0.3s ease;
  }
  
  /* When table is maximized, increase font sizes */
  .maximized .table-header {
    font-size: 1.125rem !important; /* text-lg */
    padding: 1rem 1.5rem !important; /* py-4 px-6 */
  }
  
  .maximized .table-cell {
    font-size: 1.125rem !important; /* text-lg */
    padding: 1.5rem 1.5rem !important; /* py-6 px-6 */
  }
  
  .maximized .table-cell code {
    font-size: 1.125rem !important; /* text-lg */
    padding: 0.5rem 0.75rem !important; /* py-2 px-3 */
  }
  
  .maximized .table-cell button i {
    width: 1.5rem !important; /* w-6 */
    height: 1.5rem !important; /* h-6 */
  }
  
  .maximized .table-cell span.inline-flex {
    font-size: 0.875rem !important; /* text-sm */
    padding: 0.5rem 1rem !important; /* py-2 px-4 */
  }
</style>
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
      <a href="{{ route('assessments.index', [$classSection->subject->id, $classSection->id, $term, $assessment->assessmentType->id]) }}" class="hover:text-red-600 dark:hover:text-red-400 max-w-[120px] sm:max-w-none truncate">
        {{ $assessment->assessmentType->name }}
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">Quiz Tokens</span>
    </li>
  </ol>
</nav>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
  <div>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Quiz Tokens: {{ $assessment->name }}</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $classSection->section }} - {{ $classSection->subject->code }} - {{ $classSection->subject->title }}</p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Term: {{ ucfirst($term) }}</p>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('assessments.index', [$classSection->subject->id, $classSection->id, $term, $assessment->assessmentType->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
      Back
    </a>
  </div>
</div>

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

<!-- QR Code Section -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-6 mb-6">
  <div class="flex flex-col lg:flex-row items-start gap-6">
    <div class="flex-shrink-0">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">QR Code</h3>
      <div class="bg-white p-4 rounded-lg border border-gray-200 flex flex-col items-center cursor-pointer hover:scale-105 transition-transform" onclick="openQRModal()" title="Click to enlarge QR code">
        {!! $qrSvg !!}
      </div>
      <p class="text-sm text-gray-600 dark:text-gray-400 mt-2 text-center">Click to enlarge for scanning</p>
    </div>
    
    <div class="flex-1">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quiz Access Information</h3>
      <div class="space-y-3">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quiz URL</label>
                     <div class="flex items-center gap-2 mt-1">
             <input type="text" value="http://{{ \App\Helpers\NetworkHelper::getServerIP() }}:8000/assessment/{{ $assessment->unique_url }}/access" readonly class="flex-1 px-3 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm">
             <button onclick="copyToClipboard('http://{{ \App\Helpers\NetworkHelper::getServerIP() }}:8000/assessment/{{ $assessment->unique_url }}/access')" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm">
              <i data-lucide="copy" class="w-4 h-4"></i>
            </button>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Questions</label>
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $assessment->questions->count() }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Total Points</label>
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $assessment->questions->sum('points') }}</p>
          </div>
        </div>

        <!-- Quiz Expiration Status -->
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
          <div class="flex items-center justify-between">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Quiz Status</label>
              @if($assessment->isExpired())
                <div class="flex items-center gap-2 mt-1">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                    <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                    Expired
                  </span>
                  <span class="text-sm text-gray-600 dark:text-gray-400">
                    Expired on {{ $assessment->expires_at->format('M d, Y H:i') }}
                  </span>
                </div>
              @else
                @php
                  $timeRemainingHours = $assessment->expires_at ? $assessment->expires_at->diffInHours(now()) : null;
                  $isExpiringSoon = $assessment->expires_at && $timeRemainingHours < 2;
                  $timeRemaining = $assessment->getTimeUntilExpiration();
                @endphp
                <div class="flex items-center gap-2 mt-1">
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isExpiringSoon ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' }}">
                    <i data-lucide="{{ $isExpiringSoon ? 'alert-triangle' : 'check-circle' }}" class="w-3 h-3 mr-1"></i>
                    {{ $isExpiringSoon ? 'Expiring Soon' : 'Active' }}
                  </span>
                  <span class="text-sm {{ $isExpiringSoon ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400' }}">
                    {{ $timeRemaining }}
                  </span>
                </div>
              @endif
            </div>
            
            @if($assessment->isExpired())
              <form action="{{ route('assessments.quiz.reactivate', [$classSection->subject->id, $classSection->id, $term, $assessment->assessmentType->id, $assessment->id]) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition-colors">
                  <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                  Reactivate Quiz
                </button>
              </form>
            @elseif($isExpiringSoon)
              <form action="{{ route('assessments.quiz.reactivate', [$classSection->subject->id, $classSection->id, $term, $assessment->assessmentType->id, $assessment->id]) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors">
                  <i data-lucide="clock" class="w-4 h-4"></i>
                  Extend Time
                </button>
              </form>
            @endif
          </div>
        </div>
        
        <div class="flex gap-2">
          <form action="{{ route('assessments.quiz.tokens.generate', [$classSection->subject->id, $classSection->id, $term, $assessment->assessmentType->id, $assessment->id]) }}" method="POST" class="inline">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
              <i data-lucide="refresh-cw" class="w-4 h-4"></i>
              Generate Tokens
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Student Tokens Table -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden transition-all duration-300" id="student-table-container">
  <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
    <div class="flex items-center justify-between">
      <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Student Tokens</h3>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Share these tokens with students to access the quiz</p>
      </div>
      <div class="flex items-center gap-2">
        <button onclick="toggleTableMaximize()" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
          <i data-lucide="maximize-2" class="w-4 h-4" id="maximize-icon"></i>
          <span id="maximize-text">Maximize</span>
        </button>
        <button onclick="closeMaximizedTable()" class="hidden inline-flex items-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors" id="close-maximize-btn">
          <i data-lucide="x" class="w-4 h-4"></i>
          <span>Close</span>
        </button>
      </div>
    </div>
  </div>
  
  <div class="overflow-x-auto">
    <table class="w-full">
      <thead class="bg-gray-50 dark:bg-gray-700">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider table-header">Student ID</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider table-header">Name</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider table-header">Token</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider table-header">Status</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider table-header">Used At</th>
        </tr>
      </thead>
      <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
        @forelse($students as $student)
          @php
            $token = $tokens->where('student_id', $student->id)->first();
          @endphp
          <tr class="hover:bg-gray-50 dark:hover:bg-gray-700" @if($token) data-token-id="{{ $token->id }}" @endif>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 table-cell">
              {{ $student->student_id }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 table-cell">
              {{ $student->first_name }} {{ $student->last_name }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm table-cell">
              @if($token)
                <div class="flex items-center gap-2 token-code">
                  <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm font-mono">{{ $token->token }}</code>
                  <button onclick="copyToClipboard('{{ $token->token }}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                  </button>
                </div>
              @else
                <span class="text-gray-400 dark:text-gray-500">No token</span>
              @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm table-cell">
              @if($token)
                @if($token->isUsed())
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                    Used
                  </span>
                @elseif($token->isExpired())
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                    Expired
                  </span>
                @else
                  <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                    Active
                  </span>
                @endif
              @else
                <span class="text-gray-400 dark:text-gray-500">-</span>
              @endif
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 table-cell">
              @if($token && $token->used_at)
                {{ $token->used_at->format('M d, Y H:i') }}
              @else
                <span class="text-gray-400 dark:text-gray-500">-</span>
              @endif
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
              No students found
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<!-- QR Code Modal -->
<div id="qrModal" class="fixed inset-0 bg-black/80 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4">
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">QR Code - {{ $assessment->name }}</h3>
      <button onclick="closeQRModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    
    <div class="p-8 text-center">
      <div class="bg-white p-6 rounded-lg border border-gray-200 inline-block">
        {!! $qrSvgLarge !!}
      </div>
      
      <div class="mt-6 space-y-3">
        <p class="text-lg font-medium text-gray-900 dark:text-gray-100">
          Scan this QR code to access the quiz
        </p>
        <p class="text-sm text-gray-600 dark:text-gray-400">
          "The bigger the better" -Buscopan
        </p>
        <div class="flex items-center justify-center gap-4 mt-4">
          <button onclick="copyQuizURL()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
            <i data-lucide="copy" class="w-4 h-4"></i>
            Copy URL
          </button>
          <button onclick="downloadQR()" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
            <i data-lucide="download" class="w-4 h-4"></i>
            Download QR
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
let refreshInterval;
let lastUpdateTime = new Date();

// QR Code Modal Functions
function openQRModal() {
  document.getElementById('qrModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function closeQRModal() {
  document.getElementById('qrModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
}

function copyQuizURL() {
  const quizURL = 'http://{{ \App\Helpers\NetworkHelper::getServerIP() }}:8000/assessment/{{ $assessment->unique_url }}/access';
  navigator.clipboard.writeText(quizURL).then(function() {
    // Show success feedback
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i> Copied!';
    button.classList.add('bg-green-600', 'hover:bg-green-700');
    
    setTimeout(() => {
      button.innerHTML = originalText;
      button.classList.remove('bg-green-600', 'hover:bg-green-700');
    }, 2000);
  });
}

function downloadQR() {
  const quizURL = 'http://{{ \App\Helpers\NetworkHelper::getServerIP() }}:8000/assessment/{{ $assessment->unique_url }}/access';
  const qrImageUrl = `https://api.qrserver.com/v1/create-qr-code/?size=800x800&data=${encodeURIComponent(quizURL)}`;
  
  // Create a temporary link to download the image
  const link = document.createElement('a');
  link.href = qrImageUrl;
  link.download = 'quiz-qr-code.png';
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}

// Table maximize functionality
function toggleTableMaximize() {
  const tableContainer = document.getElementById('student-table-container');
  const maximizeIcon = document.getElementById('maximize-icon');
  const maximizeText = document.getElementById('maximize-text');
  const closeBtn = document.getElementById('close-maximize-btn');
  
  if (tableContainer.classList.contains('maximized')) {
    // Restore normal size
    tableContainer.classList.remove('maximized', 'fixed', 'inset-0', 'z-40', 'max-w-none', 'rounded-none');
    tableContainer.style.width = '';
    tableContainer.style.height = '';
    tableContainer.style.maxWidth = '';
    tableContainer.style.maxHeight = '';
    
    // Update buttons
    maximizeIcon.setAttribute('data-lucide', 'maximize-2');
    maximizeText.textContent = 'Maximize';
    closeBtn.classList.add('hidden');
    
    // Restore body scroll
    document.body.style.overflow = 'auto';
  } else {
    // Maximize table
    tableContainer.classList.add('maximized', 'fixed', 'inset-0', 'z-40', 'max-w-none', 'rounded-none');
    tableContainer.style.width = '100vw';
    tableContainer.style.height = '100vh';
    tableContainer.style.maxWidth = '100vw';
    tableContainer.style.maxHeight = '100vh';
    
    // Update buttons
    maximizeIcon.setAttribute('data-lucide', 'minimize-2');
    maximizeText.textContent = 'Minimize';
    closeBtn.classList.remove('hidden');
    
    // Prevent body scroll
    document.body.style.overflow = 'hidden';
  }
  
  // Reinitialize Lucide icons
  lucide.createIcons();
}

function closeMaximizedTable() {
  const tableContainer = document.getElementById('student-table-container');
  const maximizeIcon = document.getElementById('maximize-icon');
  const maximizeText = document.getElementById('maximize-text');
  const closeBtn = document.getElementById('close-maximize-btn');
  
  // Restore normal size
  tableContainer.classList.remove('maximized', 'fixed', 'inset-0', 'z-40', 'max-w-none', 'rounded-none');
  tableContainer.style.width = '';
  tableContainer.style.height = '';
  tableContainer.style.maxWidth = '';
  tableContainer.style.maxHeight = '';
  
  // Update buttons
  maximizeIcon.setAttribute('data-lucide', 'maximize-2');
  maximizeText.textContent = 'Maximize';
  closeBtn.classList.add('hidden');
  
  // Restore body scroll
  document.body.style.overflow = 'auto';
  
  // Reinitialize Lucide icons
  lucide.createIcons();
}

// Function to update token status in real-time
function updateTokenStatus() {
  fetch(`{{ route('assessments.quiz.tokens.status', [$classSection->subject->id, $classSection->id, $term, $assessment->assessmentType->id, $assessment->id]) }}`, {
    method: 'GET',
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
      'Content-Type': 'application/json',
    },
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      updateTokenTable(data.tokens);
      updateQuizStats(data.stats);
      lastUpdateTime = new Date();
    }
  })
  .catch(error => {
    console.error('Error updating token status:', error);
  });
}

// Function to update the token table with new data
function updateTokenTable(tokens) {
  tokens.forEach(tokenData => {
    const row = document.querySelector(`tr[data-student-id="${tokenData.student_id}"]`);
    if (row) {
      // Update status badge
      const statusCell = row.querySelector('.token-status');
      if (statusCell) {
        let statusHtml = '';
        if (tokenData.is_used) {
          statusHtml = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">Used</span>';
        } else if (tokenData.is_expired) {
          statusHtml = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Expired</span>';
        } else {
          statusHtml = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Active</span>';
        }
        statusCell.innerHTML = statusHtml;
      }

      // Update used at time
      const usedAtCell = row.querySelector('.token-used-at');
      if (usedAtCell) {
        if (tokenData.used_at) {
          usedAtCell.textContent = new Date(tokenData.used_at).toLocaleString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          });
        } else {
          usedAtCell.innerHTML = '<span class="text-gray-400 dark:text-gray-500">-</span>';
        }
      }

      // Add visual feedback for new completions
      if (tokenData.just_completed) {
        row.classList.add('bg-green-50', 'dark:bg-green-900/20');
        setTimeout(() => {
          row.classList.remove('bg-green-50', 'dark:bg-green-900/20');
        }, 3000);
      }
    }
  });
}

// Function to update quiz statistics
function updateQuizStats(stats) {
  const totalQuestionsEl = document.querySelector('.total-questions');
  const totalPointsEl = document.querySelector('.total-points');
  const completedCountEl = document.querySelector('.completed-count');
  
  if (totalQuestionsEl) totalQuestionsEl.textContent = stats.total_questions;
  if (totalPointsEl) totalPointsEl.textContent = stats.total_points;
  if (completedCountEl) completedCountEl.textContent = stats.completed_count;
}

// Function to show real-time notification
function showNotification(message, type = 'info') {
  const notification = document.createElement('div');
  notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all transform translate-x-full ${
    type === 'success' ? 'bg-green-500 text-white' : 
    type === 'error' ? 'bg-red-500 text-white' : 
    'bg-blue-500 text-white'
  }`;
  notification.innerHTML = `
    <div class="flex items-center gap-2">
      <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info'}" class="w-5 h-5"></i>
      <span>${message}</span>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  // Animate in
  setTimeout(() => {
    notification.classList.remove('translate-x-full');
  }, 100);
  
  // Animate out and remove
  setTimeout(() => {
    notification.classList.add('translate-x-full');
    setTimeout(() => {
      document.body.removeChild(notification);
    }, 300);
  }, 3000);
}

function copyToClipboard(text) {
  navigator.clipboard.writeText(text).then(function() {
    // Show success message
    const button = event.target.closest('button');
    const originalIcon = button.innerHTML;
    button.innerHTML = '<i data-lucide="check" class="w-4 h-4"></i>';
    button.classList.add('text-green-600');
    
    setTimeout(() => {
      button.innerHTML = originalIcon;
      button.classList.remove('text-green-600');
    }, 2000);
  });
}

function regenerateToken(tokenId) {
  if (confirm('Are you sure you want to regenerate this token? The old token will no longer work.')) {
    fetch(`/tokens/${tokenId}/regenerate`, {
      method: 'PUT',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification('Token regenerated successfully!', 'success');
        // Update the token display immediately
        const row = document.querySelector(`tr[data-token-id="${tokenId}"]`);
        if (row) {
          const tokenCell = row.querySelector('.token-code');
          if (tokenCell) {
            tokenCell.innerHTML = `
              <div class="flex items-center gap-2">
                <code class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded text-sm font-mono">${data.token}</code>
                <button onclick="copyToClipboard('${data.token}')" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                  <i data-lucide="copy" class="w-4 h-4"></i>
                </button>
              </div>
            `;
          }
        }
      }
    });
  }
}

// Start real-time updates when page loads
document.addEventListener('DOMContentLoaded', function() {
  // Start periodic updates every 3 seconds
  refreshInterval = setInterval(updateTokenStatus, 3000);
  
  // Close QR modal when clicking outside
  document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeQRModal();
    }
  });
  
  // Close QR modal with Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeQRModal();
      // Also close maximized table if it's open
      if (document.getElementById('student-table-container').classList.contains('maximized')) {
        closeMaximizedTable();
      }
    }
  });
  
  // Add data attributes to table rows for easier updates
  document.querySelectorAll('tbody tr').forEach(row => {
    const studentId = row.querySelector('td:first-child').textContent.trim();
    row.setAttribute('data-student-id', studentId);
    
    // Add data attributes for status and used-at cells
    const statusCell = row.querySelector('td:nth-child(4)');
    const usedAtCell = row.querySelector('td:nth-child(5)');
    
    if (statusCell) statusCell.classList.add('token-status');
    if (usedAtCell) usedAtCell.classList.add('token-used-at');
  });
  
  // Add data attributes for quiz stats
  const totalQuestionsEl = document.querySelector('.total-questions');
  const totalPointsEl = document.querySelector('.total-points');
  
  if (totalQuestionsEl) totalQuestionsEl.classList.add('total-questions');
  if (totalPointsEl) totalPointsEl.classList.add('total-points');
  
  // Add completed count display
  const statsSection = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.gap-4');
  if (statsSection) {
    const completedDiv = document.createElement('div');
    completedDiv.innerHTML = `
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Completed</label>
        <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 completed-count">{{ $tokens->where('status', 'used')->count() }}</p>
      </div>
    `;
    statsSection.appendChild(completedDiv);
  }
  
  // Show initial notification
  showNotification('Real-time updates enabled. Quiz completions will appear automatically!', 'info');
});

// Clean up interval when page is unloaded
window.addEventListener('beforeunload', function() {
  if (refreshInterval) {
    clearInterval(refreshInterval);
  }
});
</script>
@endsection
