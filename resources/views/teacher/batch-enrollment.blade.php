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
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">Batch Enrollment</span>
    </li>
  </ol>
</nav>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
  <div>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Batch Student Enrollment</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $classSection->subject->code }} - {{ $classSection->subject->title }} ({{ $classSection->section }})</p>
  </div>
  <div class="flex gap-2">
    <a href="{{ route('batch-enrollment.template', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id]) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
      <i data-lucide="download" class="w-4 h-4"></i>
      Download Template
    </a>
    <a href="{{ route('grading.system', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'term' => 'midterm']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg transition-colors">
      <i data-lucide="arrow-left" class="w-4 h-4"></i>
      Back to Grading
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

<!-- Upload Section -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
  <!-- Upload Form -->
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
         <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Upload Excel/CSV File</h3>
    
    <form action="{{ route('batch-enrollment.upload', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id]) }}" method="POST" enctype="multipart/form-data">
      @csrf
      
      <div class="space-y-4">
        <div>
                     <label for="excel_file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
             Select File (.xlsx, .xls, .csv)
           </label>
           <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required
                  class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
           <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Maximum file size: 2MB. Supports XLSX, XLS, and CSV formats.</p>
        </div>
        
        <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
          <i data-lucide="upload" class="w-4 h-4 inline mr-2"></i>
          Upload and Enroll Students
        </button>
      </div>
    </form>
  </div>

  <!-- Instructions -->
  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Instructions</h3>
    
    <div class="space-y-4">
      <div>
                 <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">File Format:</h4>
        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3 text-sm">
          <table class="w-full text-xs">
            <thead>
              <tr class="border-b border-gray-200 dark:border-gray-600">
                <th class="text-left py-1">Column A</th>
                <th class="text-left py-1">Column B</th>
                <th class="text-left py-1">Column C</th>
                <th class="text-left py-1">Column D</th>
                <th class="text-left py-1">Column E</th>
                <th class="text-left py-1">Column F</th>
                <th class="text-left py-1">Column G</th>
              </tr>
            </thead>
            <tbody>
              <tr class="border-b border-gray-200 dark:border-gray-600">
                <td class="py-1 font-medium">Student ID</td>
                <td class="py-1 font-medium">Fullname</td>
                <td class="py-1 font-medium">Major</td>
                <td class="py-1 font-medium">Year Level</td>
                <td class="py-1 font-medium">Registered</td>
                <td class="py-1 font-medium">Gender</td>
                <td class="py-1 font-medium">Grade</td>
              </tr>
              <tr>
                <td class="py-1">2019-35557</td>
                <td class="py-1">Carreon, Benjamin N.</td>
                <td class="py-1">BSIT</td>
                <td class="py-1">3</td>
                <td class="py-1">1</td>
                <td class="py-1">M</td>
                <td class="py-1">1.9</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div>
        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Requirements:</h4>
        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
          <li>• Headers should be on row 6 (Student ID, Fullname, Major, Year Level, Registered, Gender, Grade)</li>
          <li>• Student data should start from row 7</li>
          <li>• Student ID must be unique within the class</li>
          <li>• Fullname should be in "LASTNAME, FIRSTNAME MIDDLENAME" format</li>
          <li>• Gender is optional but will be stored if provided</li>
          <li>• File must be in .xlsx, .xls, or .csv format</li>
          <li>• Excel files are automatically converted to CSV for processing</li>
          <li>• Maximum file size: 2MB</li>
        </ul>
      </div>

      <div>
        <h4 class="font-medium text-gray-900 dark:text-gray-100 mb-2">Tips:</h4>
        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
          <li>• Your Excel file format is already supported</li>
          <li>• Make sure Student IDs are unique</li>
          <li>• Remove any empty rows</li>
          <li>• Check for extra spaces in names</li>
          <li>• The system will automatically parse full names into first, last, and middle names</li>
        </ul>
      </div>
    </div>
  </div>
</div>

<!-- Current Students -->
<div class="mt-8">
     <div class="flex items-center justify-between mb-4">
     <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Currently Enrolled Students</h3>
     <div class="flex items-center gap-4">
       <span class="text-sm text-gray-500 dark:text-gray-400">{{ $classSection->students()->count() }} students</span>
       @if($classSection->students()->count() > 0)
         <button id="bulkUnenrollBtn" class="hidden px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
           <i data-lucide="user-minus" class="w-4 h-4 inline mr-1"></i>
           Unenroll Selected
         </button>
       @endif
     </div>
   </div>

  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full">
                 <thead class="bg-gray-50 dark:bg-gray-700">
           <tr>
             <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
               <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-red-600 focus:ring-red-500">
             </th>
             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student ID</th>
             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Enrolled Date</th>
             <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
           </tr>
         </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                     @forelse($classSection->students()->orderBy('last_name')->orderBy('first_name')->get() as $student)
             <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
               <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                 <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" class="student-checkbox rounded border-gray-300 text-red-600 focus:ring-red-500">
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                 {{ $student->student_id }}
               </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                {{ $student->first_name }} {{ $student->last_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                {{ $student->email ?: '--' }}
              </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                 {{ $student->created_at->format('M d, Y') }}
               </td>
               <td class="px-6 py-4 whitespace-nowrap text-sm">
                 <button type="button" onclick="if(confirm('Are you sure you want to unenroll {{ $student->first_name }} {{ $student->last_name }}? This action cannot be undone.')) { document.getElementById('unenroll-form-{{ $student->id }}').submit(); }" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">
                   <i data-lucide="user-minus" class="w-4 h-4 inline mr-1"></i>
                   Unenroll
                 </button>
                 <form id="unenroll-form-{{ $student->id }}" action="{{ route('batch-enrollment.unenroll', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id, 'student' => $student->id]) }}" method="POST" class="hidden">
                   @csrf
                   @method('DELETE')
                 </form>
               </td>
             </tr>
          @empty
                         <tr>
               <td colspan="6" class="px-6 py-12 text-center">
                <div class="text-gray-400 dark:text-gray-500 mb-4">
                  <i data-lucide="users" class="w-16 h-16 mx-auto"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No students enrolled</h3>
                                 <p class="text-gray-500 dark:text-gray-400">Upload an Excel (.xlsx/.xls) or CSV file to enroll students</p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
     </div>
 </div>

 <!-- Bulk Unenroll Form -->
 <form id="bulkUnenrollForm" action="{{ route('batch-enrollment.bulk-unenroll', ['subject' => $classSection->subject->id, 'classSection' => $classSection->id]) }}" method="POST" class="hidden">
   @csrf
   <input type="hidden" name="student_ids" id="selectedStudentIds">
 </form>

 <script>
 lucide.createIcons();

 // Bulk unenroll functionality
 document.addEventListener('DOMContentLoaded', function() {
   const selectAllCheckbox = document.getElementById('selectAll');
   const studentCheckboxes = document.querySelectorAll('.student-checkbox');
   const bulkUnenrollBtn = document.getElementById('bulkUnenrollBtn');
   const bulkUnenrollForm = document.getElementById('bulkUnenrollForm');
   const selectedStudentIdsInput = document.getElementById('selectedStudentIds');

   // Select all functionality
   if (selectAllCheckbox) {
     selectAllCheckbox.addEventListener('change', function() {
       studentCheckboxes.forEach(checkbox => {
         checkbox.checked = this.checked;
       });
       updateBulkUnenrollButton();
     });
   }

   // Individual checkbox functionality
   studentCheckboxes.forEach(checkbox => {
     checkbox.addEventListener('change', function() {
       updateBulkUnenrollButton();
       updateSelectAllCheckbox();
     });
   });

   // Update bulk unenroll button visibility
   function updateBulkUnenrollButton() {
     const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
     if (checkedBoxes.length > 0) {
       bulkUnenrollBtn.classList.remove('hidden');
       bulkUnenrollBtn.textContent = `Unenroll Selected (${checkedBoxes.length})`;
     } else {
       bulkUnenrollBtn.classList.add('hidden');
     }
   }

   // Update select all checkbox state
   function updateSelectAllCheckbox() {
     const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
     const totalBoxes = studentCheckboxes.length;
     
     if (selectAllCheckbox) {
       if (checkedBoxes.length === 0) {
         selectAllCheckbox.checked = false;
         selectAllCheckbox.indeterminate = false;
       } else if (checkedBoxes.length === totalBoxes) {
         selectAllCheckbox.checked = true;
         selectAllCheckbox.indeterminate = false;
       } else {
         selectAllCheckbox.checked = false;
         selectAllCheckbox.indeterminate = true;
       }
     }
   }

   // Bulk unenroll button click
   if (bulkUnenrollBtn) {
     bulkUnenrollBtn.addEventListener('click', function() {
       const checkedBoxes = document.querySelectorAll('.student-checkbox:checked');
       const studentIds = Array.from(checkedBoxes).map(cb => cb.value);
       
       if (studentIds.length > 0) {
         const confirmMessage = `Are you sure you want to unenroll ${studentIds.length} student(s)? This action cannot be undone.`;
         if (confirm(confirmMessage)) {
           // Remove any previous hidden inputs
           document.querySelectorAll('#bulkUnenrollForm input[name="student_ids[]"]').forEach(e => e.remove());
           // Add a hidden input for each selected student
           studentIds.forEach(id => {
             const input = document.createElement('input');
             input.type = 'hidden';
             input.name = 'student_ids[]';
             input.value = id;
             bulkUnenrollForm.appendChild(input);
           });
           bulkUnenrollForm.submit();
         }
       }
     });
   }
 });
 </script>
@endsection 