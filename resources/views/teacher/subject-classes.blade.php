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
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.1); }
}
@keyframes check {
  0% { opacity: 0; transform: rotate(45deg) scale(0.8); }
  50% { opacity: 1; transform: rotate(45deg) scale(1.2); }
  100% { opacity: 1; transform: rotate(45deg) scale(1); }
}
.dark .success-checkmark .check-icon {
  border-color: #22c55e;
  background: #1f2937;
}
.dark .success-checkmark .check-icon::before {
  border-color: #22c55e;
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
      <span class="text-gray-900 dark:text-gray-100 font-medium max-w-[150px] sm:max-w-none truncate">{{ $subject->code }} - {{ $subject->title }}</span>
    </li>
  </ol>
</nav>

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
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
  <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $subject->title }}</h2>
  <div class="flex items-center gap-3">
    <button onclick="openAddClassModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="plus" class="w-5 h-5"></i>
      Add Class
    </button>
    <button onclick="openCalendarModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 font-semibold rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="calendar" class="w-5 h-5 text-red-600"></i>
      Calendar
    </button>
  </div>
</div>

<div class="block lg:flex lg:gap-8 lg:items-start">
  <div class="w-full lg:flex-none min-w-0" style="flex-basis:calc(73% - 1rem);max-width:calc(73% - 1rem)">
    <div class="bg-white dark:bg-gray-900 rounded-xl p-5 shadow-sm">
@if (count($classes) > 0)
  <div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-3 2xl:grid-cols-3">
    @foreach ($classes as $class)
      <div class="relative group" data-class-id="{{ $class->id }}">
        <a href="{{ route('grading.system', ['subject' => $subject->id, 'classSection' => $class->id, 'term' => 'midterm']) }}" class="block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:shadow-lg hover:scale-105 transition-all duration-200 cursor-pointer">
          <div class="mb-2">
            <span class="text-xl font-extrabold text-red-600 dark:text-red-400 block leading-tight" data-class-section="{{ $class->section }}">{{ $class->section }}</span>
            <div class="flex items-center gap-2 mt-1 text-gray-700 dark:text-gray-200">
              <i data-lucide="calendar" class="w-4 h-4 text-red-600"></i>
              <span data-class-schedule="{{ $class->schedule }}">{{ $class->schedule }}</span>
            </div>
            @if ($class->classroom)
              <div class="flex items-center gap-2 mt-1 text-xs text-gray-500 dark:text-gray-400">
                <i data-lucide="map-pin" class="w-4 h-4 text-red-600"></i>
                <span data-class-classroom="{{ $class->classroom }}">{{ $class->classroom }}</span>
              </div>
            @endif
          </div>
          <div class="mt-2 text-sm text-gray-700 dark:text-gray-200">
            <div class="flex items-center gap-2">
              <i data-lucide="book-open" class="w-4 h-4 text-red-600"></i>
              <span data-class-students="{{ $class->students_count ?? $class->student_count }}">{{ $class->students_count ?? $class->student_count }} students</span>
            </div>
          </div>
        </a>
        <!-- Triple dot menu - outside the card link -->
        <button type="button" onclick="toggleDropdown('dropdown-{{ $class->id }}')"
          class="absolute top-3 right-3 z-10 p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none bg-white dark:bg-gray-800 shadow-sm">
          <i data-lucide="more-vertical" class="w-5 h-5 text-gray-400"></i>
        </button>
        <!-- Dropdown menu - outside the card link -->
        <div id="dropdown-{{ $class->id }}" class="hidden absolute top-10 right-3 z-20 w-32 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg py-1">
          <button type="button" onclick="openEditClassModal({{ $class->id }}, '{{ addslashes($class->section) }}', '{{ addslashes($class->schedule) }}', '{{ addslashes($class->classroom) }}')" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 flex items-center gap-2">
            <i data-lucide="edit-3" class="w-4 h-4"></i> Edit
          </button>
          <form method="POST" action="{{ route('classes.destroy', ['subject' => $subject->id, 'classSection' => $class->id]) }}" onsubmit="return confirm('Are you sure you want to delete this class?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900 flex items-center gap-2">
              <i data-lucide="trash-2" class="w-4 h-4"></i> Delete
            </button>
          </form>
        </div>
      </div>
    @endforeach
  </div>
@else
  <div class="flex flex-col items-center justify-center py-24">
    <i data-lucide="book" class="w-16 h-16 text-gray-300 mb-4"></i>
    <p class="text-lg text-gray-500 mb-2">No classes found. Click 'Add Class' to get started.</p>
    <button onclick="openAddClassModal()" class="inline-flex items-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
      <i data-lucide="plus" class="w-5 h-5"></i>
      Add Class
    </button>
  </div>
@endif
    </div>
  </div>

  <aside class="w-full lg:flex-none mt-8 lg:mt-0 lg:self-start" style="flex-basis:25%;max-width:25%">
    <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-5 shadow-sm lg:sticky lg:top-24">
      <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-2">
          <i data-lucide="calendar-range" class="w-5 h-5 text-red-600"></i>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Upcoming This Month</h3>
        </div>
      </div>
      <div id="upcoming-notes-container" class="space-y-3">
        <div class="text-sm text-gray-500 dark:text-gray-400">Loading notes…</div>
      </div>
    </div>
  </aside>
</div>

<!-- Add Class Modal -->
<div id="addClassModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Add New Class</h3>
      <button onclick="closeAddClassModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    <form id="addClassForm" method="POST" action="{{ route('classes.store', ['subject' => $subject->id]) }}" class="p-6">
      @csrf
      <div class="space-y-4">
        <div>
          <label for="section" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Section Name</label>
          <input type="text" id="section" name="section" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., IT-3A">
        </div>
        <div>
          <label for="schedule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Schedule</label>
          <input type="text" id="schedule" name="schedule" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., MWF 10:00–11:00 AM">
        </div>
        <div>
          <label for="classroom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Classroom</label>
          <input type="text" id="classroom" name="classroom" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="e.g., Room 201">
        </div>
      </div>
      <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" onclick="closeAddClassModal()" class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">Cancel</button>
        <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">Add Class</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Class Modal -->
<div id="editClassModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-md mx-4 transform transition-all">
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Edit Class</h3>
      <button onclick="closeEditClassModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    <form id="editClassForm" method="POST" class="p-6">
      @csrf
      @method('PUT')
      <div class="space-y-4">
        <div>
          <label for="edit_section" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Section Name</label>
          <input type="text" id="edit_section" name="section" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label for="edit_schedule" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Schedule</label>
          <input type="text" id="edit_schedule" name="schedule" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
        <div>
          <label for="edit_classroom" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Classroom</label>
          <input type="text" id="edit_classroom" name="classroom" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
        </div>
      </div>
      <div class="flex gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
        <button type="button" onclick="closeEditClassModal()" class="flex-1 px-4 py-2 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg font-medium transition-colors">Cancel</button>
        <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- Subject Calendar Modal -->
<div id="calendarModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center p-4">
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-5xl mx-4 transform transition-all">
    <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <i data-lucide="calendar" class="w-6 h-6 text-red-600"></i>
        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Subject Calendar & Notes</h3>
      </div>
      <button onclick="closeCalendarModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
        <i data-lucide="x" class="w-6 h-6"></i>
      </button>
    </div>
    <div class="p-6 grid grid-cols-1 xl:grid-cols-3 gap-6">
      <div class="xl:col-span-2">
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
          <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-2">
            <div class="flex items-center gap-2">
              <label for="calendar-month-select" class="text-sm text-gray-700 dark:text-gray-300">Month</label>
              <select id="calendar-month-select" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 shadow-sm text-sm text-gray-900 dark:text-gray-100"></select>
              <label for="calendar-year-select" class="text-sm text-gray-700 dark:text-gray-300">Year</label>
              <select id="calendar-year-select" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 shadow-sm text-sm text-gray-900 dark:text-gray-100"></select>
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-300">Click a date to add/view notes.</div>
          </div>
          <div id="class-calendar-grid" class="grid grid-cols-7 gap-1 text-sm"></div>
          <div class="flex items-center gap-4 text-xs text-gray-600 dark:text-gray-400 mt-3">
            <div class="flex items-center gap-1"><div class="w-3 h-3 bg-red-500 rounded"></div><span>Has note</span></div>
          </div>
        </div>
      </div>
      <div>
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-lg p-4 h-full">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="text-xs text-gray-500 dark:text-gray-400">Selected date</p>
              <p id="selected-date-label" class="text-lg font-semibold text-gray-900 dark:text-gray-100">—</p>
            </div>
            <button id="delete-note-btn" title="Delete note for this date" class="hidden px-3 py-1.5 text-sm rounded border text-red-600 dark:text-red-300 border-red-300 dark:border-red-600 hover:bg-red-600 hover:text-white dark:hover:bg-red-700 transition-colors">Delete</button>
          </div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Notes</label>
          <textarea id="date-note-input" rows="8" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-800 dark:text-white" placeholder="Add notes, plans, reminders for this date..."></textarea>
          <div class="flex gap-3 mt-4">
            <button id="save-note-btn" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">Save Note</button>
            <button onclick="clearNoteForm()" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 rounded-lg">Clear</button>
          </div>
          <p id="note-hint" class="mt-3 text-xs text-gray-500 dark:text-gray-400">Notes are saved to the database and sync across devices.</p>
        </div>
      </div>
    </div>
  </div>
  
</div>

<script>
function openAddClassModal() {
  document.getElementById('addClassModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}
function closeAddClassModal() {
  document.getElementById('addClassModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  document.getElementById('addClassForm').reset();
}
function openEditClassModal(id, section, schedule, classroom) {
  document.getElementById('editClassModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
  document.getElementById('edit_section').value = section;
  document.getElementById('edit_schedule').value = schedule;
  document.getElementById('edit_classroom').value = classroom;
  const form = document.getElementById('editClassForm');
  form.action = `/subjects/{{ $subject->id }}/classes/${id}`;
}
function closeEditClassModal() {
  document.getElementById('editClassModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
  document.getElementById('editClassForm').reset();
}
function toggleDropdown(id) {
  document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
  const el = document.getElementById(id);
  if (el) el.classList.toggle('hidden');
}
window.addEventListener('click', function(e) {
  document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
    if (!el.contains(e.target) && !el.previousElementSibling.contains(e.target)) {
      el.classList.add('hidden');
    }
  });
});
lucide.createIcons();
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

// ===== Subject Calendar & Notes (Database-backed) =====
const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const weekdays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
let calSelectedMonth = new Date().getMonth();
let calSelectedYear = new Date().getFullYear();
let calSelectedDateISO = null;

const subjectId = {{ $subject->id }};
let monthNotesIndex = {}; // { 'YYYY-MM-DD': 'note text' }

function openCalendarModal() {
  document.getElementById('calendarModal').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
  buildMonthYearSelectors();
  loadMonthNotes(calSelectedYear, calSelectedMonth).then(() => {
    renderSubjectCalendar(calSelectedMonth, calSelectedYear);
    updateSelectedDateLabel(null);
  });
}

function closeCalendarModal() {
  document.getElementById('calendarModal').classList.add('hidden');
  document.body.style.overflow = 'auto';
}

function buildMonthYearSelectors() {
  const monthSelect = document.getElementById('calendar-month-select');
  const yearSelect = document.getElementById('calendar-year-select');
  if (monthSelect.options.length === 0) {
    monthNames.forEach((m, idx) => {
      const opt = document.createElement('option');
      opt.value = idx;
      opt.textContent = m;
      if (idx === calSelectedMonth) opt.selected = true;
      monthSelect.appendChild(opt);
    });
  } else {
    monthSelect.value = calSelectedMonth;
  }
  if (yearSelect.options.length === 0) {
    const currentYear = new Date().getFullYear();
    for (let y = currentYear - 5; y <= currentYear + 5; y++) {
      const opt = document.createElement('option');
      opt.value = y;
      opt.textContent = y;
      if (y === calSelectedYear) opt.selected = true;
      yearSelect.appendChild(opt);
    }
  } else {
    yearSelect.value = calSelectedYear;
  }
  monthSelect.onchange = () => {
    calSelectedMonth = parseInt(monthSelect.value);
    loadMonthNotes(calSelectedYear, calSelectedMonth).then(() => {
      renderSubjectCalendar(calSelectedMonth, calSelectedYear);
    });
  };
  yearSelect.onchange = () => {
    calSelectedYear = parseInt(yearSelect.value);
    loadMonthNotes(calSelectedYear, calSelectedMonth).then(() => {
      renderSubjectCalendar(calSelectedMonth, calSelectedYear);
    });
  };
}

function renderSubjectCalendar(month, year) {
  const grid = document.getElementById('class-calendar-grid');
  grid.innerHTML = '';
  // Weekday headers
  for (let i = 0; i < 7; i++) {
    const div = document.createElement('div');
    div.className = 'text-center font-semibold text-gray-700 dark:text-gray-200 py-2';
    div.textContent = weekdays[i];
    grid.appendChild(div);
  }

  const firstDay = new Date(year, month, 1).getDay();
  const daysInMonth = new Date(year, month + 1, 0).getDate();

  for (let i = 0; i < firstDay; i++) {
    const empty = document.createElement('div');
    empty.className = 'p-3 rounded-lg';
    grid.appendChild(empty);
  }

  for (let d = 1; d <= daysInMonth; d++) {
    const date = new Date(year, month, d);
    const iso = formatDateLocal(date);
    const hasNote = !!monthNotesIndex[iso];

    const day = document.createElement('div');
    const isSelected = calSelectedDateISO === iso;
    day.className = 'relative border rounded-lg p-3 hover:shadow cursor-pointer min-h-[64px] ' +
      (isSelected
        ? 'bg-red-50 dark:bg-red-900/20 border-red-300 dark:border-red-700 ring-2 ring-red-500'
        : 'bg-white dark:bg-gray-800 border-gray-200 dark:border-gray-700');

    const dayNumber = document.createElement('div');
    dayNumber.className = 'text-sm font-semibold text-gray-800 dark:text-gray-100';
    dayNumber.textContent = d;
    day.appendChild(dayNumber);

    if (hasNote) {
      const indicator = document.createElement('div');
      indicator.className = 'absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full';
      day.appendChild(indicator);
    }

    day.onclick = () => selectDateForNotes(iso);
    grid.appendChild(day);
  }
}

function updateSelectedDateLabel(iso) {
  const label = document.getElementById('selected-date-label');
  const delBtn = document.getElementById('delete-note-btn');
  if (!iso) {
    label.textContent = '—';
    delBtn.classList.add('hidden');
    document.getElementById('date-note-input').value = '';
    calSelectedDateISO = null;
    return;
  }
  const d = parseDateLocal(iso);
  label.textContent = `${monthNames[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
  delBtn.classList.remove('hidden');
}

function selectDateForNotes(iso) {
  calSelectedDateISO = iso;
  updateSelectedDateLabel(iso);
  document.getElementById('date-note-input').value = monthNotesIndex[iso] || '';
  // Re-render to apply selection highlight
  renderSubjectCalendar(calSelectedMonth, calSelectedYear);
}

function clearNoteForm() {
  document.getElementById('date-note-input').value = '';
}

document.addEventListener('DOMContentLoaded', function() {
  const saveBtn = document.getElementById('save-note-btn');
  const delBtn = document.getElementById('delete-note-btn');
  if (saveBtn) {
    saveBtn.addEventListener('click', async function() {
      if (!calSelectedDateISO) {
        alert('Please select a date on the calendar first.');
        return;
      }
      const text = document.getElementById('date-note-input').value || '';
      await upsertNote(calSelectedDateISO, text);
      monthNotesIndex[calSelectedDateISO] = text;
      if (!text) delete monthNotesIndex[calSelectedDateISO];
      renderSubjectCalendar(calSelectedMonth, calSelectedYear);
    });
  }
  if (delBtn) {
    delBtn.addEventListener('click', async function() {
      if (!calSelectedDateISO) return;
      if (!confirm('Delete the note for ' + calSelectedDateISO + '?')) return;
      await deleteNote(calSelectedDateISO);
      delete monthNotesIndex[calSelectedDateISO];
      document.getElementById('date-note-input').value = '';
      renderSubjectCalendar(calSelectedMonth, calSelectedYear);
      updateSelectedDateLabel(calSelectedDateISO);
    });
  }
});

// Date helpers to avoid UTC off-by-one
function pad2(n){ return String(n).padStart(2,'0'); }
function formatDateLocal(date){
  // Build YYYY-MM-DD in local time rather than UTC
  return `${date.getFullYear()}-${pad2(date.getMonth()+1)}-${pad2(date.getDate())}`;
}
function parseDateLocal(iso){
  const [y,m,d] = iso.split('-').map(v=>parseInt(v,10));
  return new Date(y, (m-1), d);
}

// Sidebar: load and render upcoming notes for the current month
document.addEventListener('DOMContentLoaded', async function() {
  try {
    const container = document.getElementById('upcoming-notes-container');
    if (!container) return;
    const now = new Date();
    const ym = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2,'0')}`;
    const res = await fetch(`/subjects/${subjectId}/calendar-notes?month=${ym}`, { headers: { 'Accept': 'application/json' } });
    const all = await res.json();
    // Show all notes in the current month (not just future)
    const items = all
      .filter(n => (n.text||'').trim() !== '')
      .sort((a,b) => a.date.localeCompare(b.date))
      .slice(0, 8);
    container.innerHTML = '';
    if (items.length === 0) {
      container.innerHTML = '<div class="text-sm text-gray-500 dark:text-gray-400">No notes for this month.</div>';
      return;
    }
    items.forEach(n => {
      const d = parseDateLocal(n.date);
      const item = document.createElement('div');
      item.className = 'flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800';
      item.innerHTML = `
        <div class=\"shrink-0 mt-0.5\"><i data-lucide=\"sticky-note\" class=\"w-4 h-4 text-red-600\"></i></div>
        <div class=\"min-w-0\">\n          <div class=\"text-xs text-gray-500 dark:text-gray-400\">${monthNames[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}</div>\n          <div class=\"text-sm text-gray-900 dark:text-gray-100 truncate\">${(n.text||'').replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>\n        </div>`;
      container.appendChild(item);
    });
    if (window.lucide) lucide.createIcons();
  } catch (e) {
    const container = document.getElementById('upcoming-notes-container');
    if (container) container.innerHTML = '<div class="text-sm text-gray-500 dark:text-gray-400">Failed to load notes.</div>';
  }
});

async function loadMonthNotes(year, month) {
  const ym = `${year}-${String(month + 1).padStart(2,'0')}`;
  const res = await fetch(`/subjects/${subjectId}/calendar-notes?month=${ym}`, {
    headers: { 'Accept': 'application/json' }
  });
  const data = await res.json();
  monthNotesIndex = {};
  data.forEach(n => { monthNotesIndex[n.date] = n.text || ''; });
}

async function upsertNote(dateISO, text) {
  await fetch(`/subjects/${subjectId}/calendar-notes`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ note_date: dateISO, note_text: text })
  });
}

async function deleteNote(dateISO) {
  await fetch(`/subjects/${subjectId}/calendar-notes/${dateISO}`, {
    method: 'DELETE',
    headers: {
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  });
}
</script>
@endsection 