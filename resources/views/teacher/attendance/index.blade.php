@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3 text-xs sm:text-sm">
                <li class="inline-flex items-center">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                        <svg class="w-3 h-3 me-2.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 20">
                            <path d="m19.707 9.293-2-2-7-7a1 1 0 0 0-1.414 0l-7 7-2 2a1 1 0 0 0 1.414 1.414L2 10.414V18a2 2 0 0 0 2 2h3a1 1 0 0 0 1-1v-4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v4a1 1 0 0 0 1 1h3a2 2 0 0 0 2-2v-7.586l.293.293a1 1 0 0 0 1.414-1.414Z"/>
                        </svg>
                        <span class="hidden sm:inline">Home</span>
                        <span class="sm:hidden">H</span>
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 dark:text-gray-500 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <a href="{{ route('subjects.index') }}" class="ms-1 font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 md:ms-2">
                            <span class="hidden sm:inline">Subjects</span>
                            <span class="sm:hidden">S</span>
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 dark:text-gray-500 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <a href="{{ route('subjects.classes', $subject->id) }}" class="ms-1 font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 md:ms-2 max-w-[80px] sm:max-w-none truncate">
                            {{ $subject->code }} - {{ $subject->name }}
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 dark:text-gray-500 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <a href="{{ route('grading.system', [$subject->id, $classSection->id, $term]) }}" class="ms-1 font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 md:ms-2">
                            {{ $classSection->section }}
                        </a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="rtl:rotate-180 w-3 h-3 text-gray-400 dark:text-gray-500 mx-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                        </svg>
                        <span class="ms-1 font-medium text-gray-500 dark:text-gray-400 md:ms-2">
                            <span class="hidden sm:inline">Attendance</span>
                            <span class="sm:hidden">A</span>
                        </span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-6 gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $assessment->name }}</h1>
                <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">{{ $subject->code }} - {{ $classSection->section }} ({{ ucfirst($term) }})</p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Total Students: {{ $students->count() }}</p>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Total Days: {{ count($attendanceDates) }}</p>
            </div>
        </div>

        <!-- Calendar and Attendance Interface -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
            <!-- Calendar Section -->
            <div class="xl:col-span-2">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-2">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100">Attendance Calendar</h3>
                        <div class="flex gap-1 sm:gap-2">
                            <select id="month-select" class="px-2 sm:px-3 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 shadow-sm text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                @foreach(['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] as $index => $month)
                                    <option value="{{ $index }}" {{ $index == now()->month - 1 ? 'selected' : '' }}>{{ $month }}</option>
                                @endforeach
                            </select>
                            <select id="year-select" class="px-2 sm:px-3 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-800 shadow-sm text-xs sm:text-sm text-gray-900 dark:text-gray-100">
                                @for($year = now()->year - 2; $year <= now()->year + 1; $year++)
                                    <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div id="calendar-grid" class="grid grid-cols-7 gap-0.5 sm:gap-1 text-xs sm:text-sm mb-4"></div>

                    <!-- Calendar Legend -->
                    <div class="flex flex-wrap items-center gap-2 sm:gap-4 text-xs text-gray-600 dark:text-gray-400 mt-2">
                        <div class="flex items-center gap-1">
                            <div class="w-2 h-2 sm:w-3 sm:h-3 bg-green-500 rounded-full"></div>
                            <span class="hidden sm:inline">Attendance recorded</span>
                            <span class="sm:hidden">Recorded</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <div class="w-3 h-3 sm:w-4 sm:h-4 bg-white dark:bg-gray-600 border border-gray-200 dark:border-gray-500 rounded"></div>
                            <span class="hidden sm:inline">No attendance data</span>
                            <span class="sm:hidden">No data</span>
                        </div>
                    </div>

                    <!-- Attendance Form -->
                    <div id="attendance-section" class="mt-4"></div>
                </div>
            </div>

            <!-- Student List Section -->
            <div class="xl:col-span-1">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 sm:p-4">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Students & Attendance</h3>
                    <div id="student-list" class="space-y-2">
                        @foreach($students as $student)
                            @php
                                $studentRecords = $attendanceData[$student->id] ?? collect();
                                $totalDays = count($attendanceDates);
                                $presentDays = $studentRecords->where('status', 'present')->count();
                                $lateDays = $studentRecords->where('status', 'late')->count();
                                $attendancePercentage = $totalDays > 0 ? round((($presentDays + $lateDays) / $totalDays) * 100, 1) : 0;
                            @endphp
                            <div class="flex justify-between items-center p-2 bg-white dark:bg-gray-800 rounded border border-gray-200 dark:border-gray-600">
                                <div class="min-w-0 flex-1">
                                    <div class="font-medium text-xs sm:text-sm truncate text-gray-900 dark:text-gray-100">{{ $student->full_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $student->student_id }}</div>
                                </div>
                                <div class="text-right flex-shrink-0 ml-2">
                                    <div class="text-xs sm:text-sm font-medium text-gray-900 dark:text-gray-100">{{ $attendancePercentage }}%</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $presentDays + $lateDays }}/{{ $totalDays }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const monthNames = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const weekdays = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

let selectedMonth = new Date().getMonth();
let selectedYear = new Date().getFullYear();
let currentAssessmentId = {{ $assessment->id }};
let currentTerm = '{{ $term }}';
let students = @json($students);
let attendanceDates = @json($attendanceDates);
console.log('Attendance dates:', attendanceDates);

const monthSelect = document.getElementById('month-select');
const yearSelect = document.getElementById('year-select');
const grid = document.getElementById('calendar-grid');
const attendanceSection = document.getElementById('attendance-section');

// Initialize calendar
function renderCalendar(month, year) {
    grid.innerHTML = '';
    
    // Add weekday headers
    for (let w of weekdays) {
        const div = document.createElement('div');
        div.textContent = w;
        div.className = 'font-bold text-gray-500 dark:text-gray-400 text-center p-2';
        grid.appendChild(div);
    }

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    
    // Add empty cells for days before the first day of the month
    for (let i = 0; i < firstDay; i++) {
        grid.appendChild(document.createElement('div'));
    }

    // Add day cells
    for (let d = 1; d <= daysInMonth; d++) {
        const date = new Date(year, month, d);
        const dateString = date.toISOString().split('T')[0];
        const hasAttendance = attendanceDates.includes(dateString);
        
        // Debug logging for specific dates
        if (d <= 10) {
            console.log(`Date ${d}: ${dateString}, hasAttendance: ${hasAttendance}`);
        }
        
        const day = document.createElement('div');
        day.className = 'relative p-2 rounded border text-center cursor-pointer transition-all select-none ' +
            (hasAttendance ? 'bg-green-100 dark:bg-green-900/20 border-green-500 dark:border-green-400 font-bold text-green-800 dark:text-green-200' : 'bg-white dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-blue-900/20 border-gray-200 dark:border-gray-600 text-gray-900 dark:text-gray-100');
        
        // Add the day number
        const dayNumber = document.createElement('span');
        dayNumber.textContent = d;
        day.appendChild(dayNumber);
        
        // Add attendance indicator for dates with data
        if (hasAttendance) {
            const indicator = document.createElement('div');
            indicator.className = 'absolute -top-1 -right-1 w-3 h-3 bg-green-500 rounded-full';
            indicator.title = 'Attendance recorded';
            day.appendChild(indicator);
        }

        day.onclick = () => {
            showAttendanceForm(dateString, d);
        };

        grid.appendChild(day);
    }
}

function showAttendanceForm(dateString, day) {
    const monthName = monthNames[selectedMonth];
    const year = selectedYear;
    
    let html = `
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-600 p-3 sm:p-4">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-bold text-base sm:text-lg text-gray-900 dark:text-gray-100">${monthName} ${day}, ${year}</h4>
                <button onclick="closeAttendanceForm()" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <form id="attendance-form" class="space-y-2 sm:space-y-3">
                <input type="hidden" name="date" value="${dateString}">
    `;

    students.forEach(student => {
        html += `
            <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4 p-2 border border-gray-200 dark:border-gray-600 rounded bg-white dark:bg-gray-700">
                <div class="flex-1 min-w-0">
                    <div class="font-medium text-sm sm:text-base truncate text-gray-900 dark:text-gray-100">${student.first_name} ${student.last_name}</div>
                    <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 truncate">${student.student_id}</div>
                </div>
                <div class="flex gap-1 sm:gap-2 flex-wrap">
                    <label class="flex items-center gap-1">
                        <input type="radio" name="status_${student.id}" value="present" checked class="text-blue-600 focus:ring-blue-500">
                        <span class="text-xs sm:text-sm text-gray-900 dark:text-gray-100">Present</span>
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="radio" name="status_${student.id}" value="absent" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-xs sm:text-sm text-gray-900 dark:text-gray-100">Absent</span>
                    </label>
                    <label class="flex items-center gap-1">
                        <input type="radio" name="status_${student.id}" value="late" class="text-blue-600 focus:ring-blue-500">
                        <span class="text-xs sm:text-sm text-gray-900 dark:text-gray-100">Late</span>
                    </label>
                </div>
            </div>
        `;
    });

    html += `
                <div class="flex flex-col sm:flex-row gap-2 justify-end pt-4">
                    <button type="button" onclick="closeAttendanceForm()" class="px-3 sm:px-4 py-2 text-gray-600 dark:text-gray-300 border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 text-sm sm:text-base">
                        Cancel
                    </button>
                    <button type="submit" class="px-3 sm:px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded text-sm sm:text-base">
                        Save Attendance
                    </button>
                </div>
            </form>
        </div>
    `;

    attendanceSection.innerHTML = html;

    // Load existing attendance data for this date
    loadAttendanceData(dateString);

    // Handle form submission
    document.getElementById('attendance-form').addEventListener('submit', function(e) {
        e.preventDefault();
        saveAttendance(dateString);
    });
}

function closeAttendanceForm() {
    attendanceSection.innerHTML = '';
}

async function loadAttendanceData(dateString) {
    try {
        console.log('Loading attendance data for date:', dateString);
        const response = await fetch(`{{ route('attendance.data', [$subject->id, $classSection->id, $term, $assessment->assessmentType->id, $assessment->id]) }}?date=${dateString}`);
        const data = await response.json();
        
        console.log('Received attendance data:', data);
        
        // Pre-fill form with existing data
        Object.keys(data).forEach(studentId => {
            const status = data[studentId].status;
            console.log(`Setting student ${studentId} to ${status}`);
            const radioButton = document.querySelector(`input[name="status_${studentId}"][value="${status}"]`);
            if (radioButton) {
                radioButton.checked = true;
                console.log(`Radio button found and set for student ${studentId}`);
            } else {
                console.log(`Radio button not found for student ${studentId}, status: ${status}`);
            }
        });
    } catch (error) {
        console.error('Error loading attendance data:', error);
    }
}

async function saveAttendance(dateString) {
    const form = document.getElementById('attendance-form');
    const formData = new FormData(form);
    
    const attendance = [];
    students.forEach(student => {
        const status = form.querySelector(`input[name="status_${student.id}"]:checked`).value;
        attendance.push({
            student_id: student.id,
            status: status
        });
    });

    try {
        const response = await fetch('{{ route("attendance.store", [$subject->id, $classSection->id, $term, $assessment->assessmentType->id, $assessment->id]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                date: dateString,
                attendance: attendance
            })
        });

        const result = await response.json();
        
        if (result.success) {
            // Add date to attendance dates if not already present
            if (!attendanceDates.includes(dateString)) {
                attendanceDates.push(dateString);
            }
            
            // Re-render calendar and student list
            renderCalendar(selectedMonth, selectedYear);
            updateStudentList();
            closeAttendanceForm();
            
            // Show success message
            alert('Attendance saved successfully!');
        } else {
            // Show error message from server
            alert('Error: ' + (result.message || 'Unknown error occurred'));
        }
    } catch (error) {
        console.error('Error saving attendance:', error);
        alert('Error saving attendance. Please try again.');
    }
}

function updateStudentList() {
    // This would typically reload the student list with updated attendance percentages
    // For now, we'll just show a simple update
    console.log('Student list updated');
}

// Event listeners
monthSelect.addEventListener('change', () => {
    selectedMonth = parseInt(monthSelect.value);
    renderCalendar(selectedMonth, selectedYear);
});

yearSelect.addEventListener('change', () => {
    selectedYear = parseInt(yearSelect.value);
    renderCalendar(selectedMonth, selectedYear);
});

// Initialize
renderCalendar(selectedMonth, selectedYear);
</script>
@endsection 