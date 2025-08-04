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
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.1);
  }
}

@keyframes check {
  0% {
    opacity: 0;
    transform: rotate(45deg) scale(0.8);
  }
  50% {
    opacity: 1;
    transform: rotate(45deg) scale(1.2);
  }
  100% {
    opacity: 1;
    transform: rotate(45deg) scale(1);
  }
}

/* Dark mode support */
.dark .success-checkmark .check-icon {
  border-color: #22c55e;
  background: #1f2937;
}

.dark .success-checkmark .check-icon::before {
  border-color: #22c55e;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s ease;
  padding: 1rem;
}

.modal-overlay.show {
  opacity: 1;
  visibility: visible;
}

.modal-content {
  background: white;
  border-radius: 0.75rem;
  width: 100%;
  max-width: 64rem;
  max-height: 90vh;
  overflow-y: auto;
  transform: scale(0.95);
  transition: transform 0.3s ease;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.dark .modal-content {
  background: #1f2937;
  border: 1px solid #374151;
}

.modal-overlay.show .modal-content {
  transform: scale(1);
}

/* Multi-range slider styles */
.slider-container {
  position: relative;
  width: 100%;
  height: 20px;
  background: #e5e7eb;
  border-radius: 10px;
  margin-bottom: 20px;
  overflow: visible;
}

.segment {
  position: absolute;
  top: 0;
  height: 100%;
  border-radius: 10px;
  transition: all 0.2s ease;
}

.handle {
  position: absolute;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  cursor: pointer;
  top: 50%;
  transform: translate(-50%, -50%);
  border: 3px solid white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  z-index: 10;
  transition: all 0.2s ease;
}

.handle:hover {
  transform: translate(-50%, -50%) scale(1.1);
}

.handle.active {
  transform: translate(-50%, -50%) scale(1.2);
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

/* Assessment type colors */
.assessment-blue { background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%); }
.assessment-green { background: linear-gradient(90deg, #10b981 0%, #059669 100%); }
.assessment-purple { background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 100%); }
.assessment-orange { background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); }
.assessment-pink { background: linear-gradient(90deg, #ec4899 0%, #db2777 100%); }
.assessment-indigo { background: linear-gradient(90deg, #6366f1 0%, #4f46e5 100%); }
.assessment-teal { background: linear-gradient(90deg, #14b8a6 0%, #0d9488 100%); }
.assessment-red { background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%); }

/* Dark mode support */
.dark .slider-container {
  background: #374151;
}

/* Responsive improvements */
@media (max-width: 768px) {
  .modal-content {
    max-width: 95vw;
    margin: 0.5rem;
  }
  
  .modal-overlay {
    padding: 0.5rem;
  }
}
</style>

@section('content')
<!-- Breadcrumbs -->
<nav class="mb-6" aria-label="Breadcrumb">
  <ol class="flex flex-wrap items-center gap-1 sm:gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
    <li class="flex items-center">
      <a href="{{ route('dashboard') }}" class="hover:text-evsu dark:hover:text-evsu transition-colors whitespace-nowrap">
        Home
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">Subjects</span>
    </li>
  </ol>
</nav>

@if (session('success'))
  <div id="successMessage" class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg transform transition-all duration-500 ease-out">
    <div class="flex items-center gap-3">
      <div class="success-checkmark">
        <div class="check-icon">
          <span class="icon-line line-tip"></span>
          <span class="icon-line line-long"></span>
          <div class="icon-circle"></div>
          <div class="icon-fix"></div>
        </div>
      </div>
      <p class="text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</p>
    </div>
  </div>
@endif

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Subjects</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Manage your subjects and courses</p>
  </div>
  <div class="flex gap-2">
    <button onclick="createSubject()" class="mt-4 sm:mt-0 inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Add Subject
    </button>
  </div>
</div>

<!-- Subjects Grid -->
<div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
  @forelse($subjects ?? [] as $subject)
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow">
      <div class="flex items-start justify-between mb-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $subject->code }}</h3>
          <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $subject->title }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button onclick="editSubject({{ $subject->id }}, '{{ $subject->code }}', '{{ $subject->title }}', {{ $subject->units }})" class="text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
            <i data-lucide="edit" class="w-4 h-4"></i>
          </button>
          <button onclick="deleteSubject({{ $subject->id }})" class="text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">
            <i data-lucide="trash-2" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
      
      <div class="space-y-2">
        <div class="flex items-center justify-between text-sm">
          <span class="text-gray-500 dark:text-gray-400">Units:</span>
          <span class="font-medium text-gray-900 dark:text-gray-100">{{ $subject->units }}</span>
        </div>
        <div class="flex items-center justify-between text-sm">
          <span class="text-gray-500 dark:text-gray-400">Classes:</span>
          <span class="font-medium text-gray-900 dark:text-gray-100">{{ $subject->classSections->count() }}</span>
        </div>
      </div>
      
      <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <a href="{{ route('subjects.classes', $subject->id) }}" class="inline-flex items-center gap-2 px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm rounded-lg transition-colors">
          <i data-lucide="users" class="w-4 h-4"></i>
          View Classes
        </a>
      </div>
    </div>
  @empty
    <div class="col-span-full text-center py-12">
      <div class="text-gray-400 dark:text-gray-500 mb-4">
        <i data-lucide="book-open" class="w-16 h-16 mx-auto"></i>
      </div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No subjects yet</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-6">Get started by adding your first subject</p>
      <button onclick="createSubject()" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add Your First Subject
      </button>
    </div>
  @endforelse
</div>

<!-- Create Modal -->
<div id="createModal" class="modal-overlay">
  <div class="modal-content">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Create New Subject</h3>
        <button onclick="closeCreateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>

      <!-- Progress Bar -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Step <span id="createCurrentStep">1</span> of 2</span>
          <span class="text-sm text-gray-500 dark:text-gray-400" id="createStepTitle">Basic Information</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
          <div id="createProgressBar" class="bg-red-600 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
        </div>
      </div>

      <!-- Step 1: Basic Subject Info -->
      <div id="createStep1" class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Step 1: Basic Information</h2>
          
          <div class="space-y-4">
            <div>
              <label for="create_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject ID</label>
              <input type="text" id="create_code" name="code" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., MATH101">
            </div>
            
            <div>
              <label for="create_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject Name</label>
              <input type="text" id="create_title" name="title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., College Algebra">
            </div>
            
            <div>
              <label for="create_units" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Units</label>
              <input type="number" id="create_units" name="units" step="0.5" min="0.5" max="6" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="3.0">
            </div>
          </div>
          
          <div class="flex justify-center mt-6">
            <button type="button" onclick="createNextStep()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
              Next: Assessment Builder
            </button>
          </div>
        </div>
      </div>

      <!-- Step 2: Assessment Type Builder -->
      <div id="createStep2" class="max-w-4xl mx-auto hidden">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 text-center">Step 2: Assessment Type Builder</h2>
          
          <!-- Grading Structure Selection -->
          <div class="mb-8">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">Grading Structure</h3>
            
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                  <input type="radio" name="create_grading_type" value="balanced" class="mr-3" checked>
                  <div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">Balanced (50/50)</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Equal weight for Midterm and Final</div>
                  </div>
                </label>
                
                <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                  <input type="radio" name="create_grading_type" value="custom" class="mr-3">
                  <div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">Custom Weights</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Define your own Midterm/Final weights</div>
                  </div>
                </label>
              </div>
              
              <div id="createCustomWeights" class="hidden max-w-md mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="create_midterm_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Midterm Weight (%)</label>
                    <input type="number" id="create_midterm_weight" name="midterm_weight" min="0" max="100" step="5" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" value="50">
                  </div>
                  <div>
                    <label for="create_final_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Final Weight (%)</label>
                    <input type="number" id="create_final_weight" name="final_weight" min="0" max="100" step="5" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" value="50">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Midterm Assessment Types -->
          <div class="mb-8">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">Midterm Assessment Types</h3>
            
            <div id="createMidtermAssessmentTypes" class="space-y-4 max-w-2xl mx-auto">
              <!-- Assessment types will be added here dynamically -->
            </div>
            
            <div class="text-center mt-4">
              <button type="button" onclick="createAddAssessmentType('midterm')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Assessment Type
              </button>
            </div>
          </div>

          <!-- Midterm Progress Bar -->
          <div class="mb-8 max-w-2xl mx-auto">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
              <span>Midterm Weight Distribution</span>
              <span id="createMidtermTotalWeight">0%</span>
            </div>
            <div class="slider-container" id="createMidtermProgressContainer">
              <!-- Progress bar segments will be added here dynamically -->
            </div>
            <div id="createMidtermOutput" class="text-xs text-gray-500 dark:text-gray-400 mt-1"></div>
            <div id="createMidtermError"></div>
          </div>

          <!-- Final Assessment Types -->
          <div class="mb-8">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">Final Assessment Types</h3>
            
            <div id="createFinalAssessmentTypes" class="space-y-4 max-w-2xl mx-auto">
              <!-- Assessment types will be added here dynamically -->
            </div>
            
            <div class="text-center mt-4">
              <button type="button" onclick="createAddAssessmentType('final')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Assessment Type
              </button>
            </div>
          </div>

          <!-- Final Progress Bar -->
          <div class="mb-8 max-w-2xl mx-auto">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
              <span>Final Weight Distribution</span>
              <span id="createFinalTotalWeight">0%</span>
            </div>
            <div class="slider-container" id="createFinalProgressContainer">
              <!-- Progress bar segments will be added here dynamically -->
            </div>
            <div id="createFinalOutput" class="text-xs text-gray-500 dark:text-gray-400 mt-1"></div>
            <div id="createFinalError"></div>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex justify-center gap-4 mt-8">
            <button type="button" onclick="createPrevStep()" class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition-colors font-medium">
              ← Back
            </button>
            <button type="button" onclick="createSaveSubject()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
              Create Subject
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="modal-overlay">
  <div class="modal-content">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Edit Subject</h3>
        <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>

      <!-- Progress Bar -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Step <span id="currentStep">1</span> of 2</span>
          <span class="text-sm text-gray-500 dark:text-gray-400" id="stepTitle">Basic Information</span>
        </div>
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
          <div id="progressBar" class="bg-red-600 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
        </div>
      </div>

      <!-- Step 1: Basic Subject Info -->
      <div id="step1" class="max-w-2xl mx-auto">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Step 1: Basic Information</h2>
          
          <div class="space-y-4">
            <div>
              <label for="edit_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject ID</label>
              <input type="text" id="edit_code" name="code" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., MATH101">
            </div>
            
            <div>
              <label for="edit_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject Name</label>
              <input type="text" id="edit_title" name="title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., College Algebra">
            </div>
            
            <div>
              <label for="edit_units" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Units</label>
              <input type="number" id="edit_units" name="units" step="0.5" min="0.5" max="6" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="3.0">
            </div>
          </div>
          
          <div class="flex justify-center mt-6">
            <button type="button" onclick="nextStep()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
              Next: Assessment Builder
            </button>
          </div>
        </div>
      </div>

      <!-- Step 2: Assessment Type Builder -->
      <div id="step2" class="max-w-4xl mx-auto hidden">
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 text-center">Step 2: Assessment Type Builder</h2>
          
          <!-- Grading Structure Selection -->
          <div class="mb-8">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">Grading Structure</h3>
            
            <div class="space-y-4">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto">
                <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                  <input type="radio" name="grading_type" value="balanced" class="mr-3" checked>
                  <div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">Balanced (50/50)</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Equal weight for Midterm and Final</div>
                  </div>
                </label>
                
                <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                  <input type="radio" name="grading_type" value="custom" class="mr-3">
                  <div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">Custom Weights</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">Define your own Midterm/Final weights</div>
                  </div>
                </label>
              </div>
              
              <div id="customWeights" class="hidden max-w-md mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label for="midterm_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Midterm Weight (%)</label>
                    <input type="number" id="midterm_weight" name="midterm_weight" min="0" max="100" step="5" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" value="50">
                  </div>
                  <div>
                    <label for="final_weight" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Final Weight (%)</label>
                    <input type="number" id="final_weight" name="final_weight" min="0" max="100" step="5" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" value="50">
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Midterm Assessment Types -->
          <div class="mb-8">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">Midterm Assessment Types</h3>
            
            <div id="midtermAssessmentTypes" class="space-y-4 max-w-2xl mx-auto">
              <!-- Assessment types will be added here dynamically -->
            </div>
            
            <div class="text-center mt-4">
              <button type="button" onclick="addAssessmentType('midterm')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Assessment Type
              </button>
            </div>
          </div>

          <!-- Midterm Progress Bar -->
          <div class="mb-8 max-w-2xl mx-auto">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
              <span>Midterm Weight Distribution</span>
              <span id="midtermTotalWeight">0%</span>
            </div>
            <div class="slider-container" id="midtermProgressContainer">
              <!-- Progress bar segments will be added here dynamically -->
            </div>
            <div id="midtermOutput" class="text-xs text-gray-500 dark:text-gray-400 mt-1"></div>
            <div id="midtermError"></div>
          </div>

          <!-- Final Assessment Types -->
          <div class="mb-8">
            <h3 class="text-md font-medium text-gray-900 dark:text-gray-100 mb-4 text-center">Final Assessment Types</h3>
            
            <div id="finalAssessmentTypes" class="space-y-4 max-w-2xl mx-auto">
              <!-- Assessment types will be added here dynamically -->
            </div>
            
            <div class="text-center mt-4">
              <button type="button" onclick="addAssessmentType('final')" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
                <i data-lucide="plus" class="w-4 h-4"></i>
                Add Assessment Type
              </button>
            </div>
          </div>

          <!-- Final Progress Bar -->
          <div class="mb-8 max-w-2xl mx-auto">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
              <span>Final Weight Distribution</span>
              <span id="finalTotalWeight">0%</span>
            </div>
            <div class="slider-container" id="finalProgressContainer">
              <!-- Progress bar segments will be added here dynamically -->
            </div>
            <div id="finalOutput" class="text-xs text-gray-500 dark:text-gray-400 mt-1"></div>
            <div id="finalError"></div>
          </div>

          <!-- Navigation Buttons -->
          <div class="flex justify-center gap-4 mt-8">
            <button type="button" onclick="prevStep()" class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition-colors font-medium">
              ← Back
            </button>
            <button type="button" onclick="saveSubject()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">
              Update Subject
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<script>
let currentStep = 1;
let assessmentTypeCounter = { midterm: 0, final: 0 };
let assessmentTypes = { midterm: [], final: [] };
const colors = ['blue', 'green', 'purple', 'orange', 'pink', 'indigo', 'teal', 'red'];
let currentSubjectId = null;

// Create modal variables
let createCurrentStep = 1;
let createAssessmentTypeCounter = { midterm: 0, final: 0 };
let createAssessmentTypes = { midterm: [], final: [] };

// Auto-hide success message
setTimeout(function() {
  const successMessage = document.getElementById('successMessage');
  if (successMessage) {
    successMessage.style.transform = 'translateY(-100%)';
    successMessage.style.opacity = '0';
    setTimeout(() => successMessage.remove(), 500);
  }
}, 3000);

// Edit subject function
function editSubject(id, code, title, units) {
  currentSubjectId = id;
  const modal = document.getElementById('editModal');
  
  // Reset form
  document.getElementById('edit_code').value = code;
  document.getElementById('edit_title').value = title;
  document.getElementById('edit_units').value = units;
  
  // Reset to step 1
  currentStep = 1;
  updateProgress();
  document.getElementById('step1').classList.remove('hidden');
  document.getElementById('step2').classList.add('hidden');
  
  // Reset assessment types
  assessmentTypes = { midterm: [], final: [] };
  assessmentTypeCounter = { midterm: 0, final: 0 };
  
  // Clear existing assessment types
  document.getElementById('midtermAssessmentTypes').innerHTML = '';
  document.getElementById('finalAssessmentTypes').innerHTML = '';
  
  // Load existing subject data
  loadSubjectData(id);
  
  // Show modal
  modal.classList.add('show');
  
  // Focus on first input
  setTimeout(() => document.getElementById('edit_code').focus(), 100);
}

// Load existing subject data
async function loadSubjectData(subjectId) {
  try {
    const response = await fetch(`/subjects/${subjectId}/edit-data`);
    if (response.ok) {
      const data = await response.json();
      
      // Set grading structure
      if (data.grading_structure) {
        const gradingType = data.grading_structure.type;
        document.querySelector(`input[name="grading_type"][value="${gradingType}"]`).checked = true;
        
        if (gradingType === 'custom') {
          document.getElementById('customWeights').classList.remove('hidden');
          document.getElementById('midterm_weight').value = data.grading_structure.midterm_weight;
          document.getElementById('final_weight').value = data.grading_structure.final_weight;
        }
      }
      
      // Load assessment types
      if (data.assessment_types) {
        assessmentTypes = { midterm: [], final: [] };
        assessmentTypeCounter = { midterm: 0, final: 0 };
        
        // Clear existing
        document.getElementById('midtermAssessmentTypes').innerHTML = '';
        document.getElementById('finalAssessmentTypes').innerHTML = '';
        
        // Load midterm assessment types
        if (data.assessment_types.midterm) {
          data.assessment_types.midterm.forEach(type => {
            addAssessmentTypeWithData('midterm', type.name, type.weight);
          });
        }
        
        // Load final assessment types
        if (data.assessment_types.final) {
          data.assessment_types.final.forEach(type => {
            addAssessmentTypeWithData('final', type.name, type.weight);
          });
        }
        
        // If no assessment types, add defaults
        if (!data.assessment_types.midterm || data.assessment_types.midterm.length === 0) {
          addAssessmentType('midterm');
        }
        if (!data.assessment_types.final || data.assessment_types.final.length === 0) {
          addAssessmentType('final');
        }
      } else {
        // Add default assessment types if none exist
        addAssessmentType('midterm');
        addAssessmentType('final');
      }
      
      updateProgressBars();
    }
  } catch (error) {
    console.error('Error loading subject data:', error);
    // Add default assessment types if loading fails
    addAssessmentType('midterm');
    addAssessmentType('final');
  }
}

// Add assessment type with existing data
function addAssessmentTypeWithData(term, name, weight) {
  const counter = ++assessmentTypeCounter[term];
  const typeId = `${term}_${counter}`;
  const colorIndex = (counter - 1) % colors.length;
  const color = colors[colorIndex];
  
  const assessmentType = {
    id: typeId,
    name: name,
    weight: parseFloat(weight) || 0,
    term: term,
    color: color
  };
  
  assessmentTypes[term].push(assessmentType);
  
  const container = document.getElementById(`${term}AssessmentTypes`);
  const typeElement = document.createElement('div');
  typeElement.className = 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4';
  typeElement.innerHTML = `
    <div class="flex items-center justify-between">
      <div class="flex-1 mr-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Type Name</label>
        <input type="text" 
               id="name_${typeId}"
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
               placeholder="e.g., Quiz, Lab, Project"
               value="${name}"
               oninput="updateAssessmentType('${typeId}', 'name', this.value)">
      </div>
      <div class="w-24">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (%)</label>
        <input type="number" 
               id="weight_${typeId}"
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
               min="0" max="100" step="1" value="${Math.round(parseFloat(weight) || 0)}"
               oninput="updateAssessmentType('${typeId}', 'weight', this.value)"
               onkeypress="return event.charCode >= 48 && event.charCode <= 57">
      </div>
      <button type="button" onclick="removeAssessmentType('${typeId}')" class="ml-2 text-red-600 hover:text-red-700 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    </div>
  `;
  
  container.appendChild(typeElement);
  
  // Refresh Lucide icons
  lucide.createIcons();
}

// Close edit modal
function closeEditModal() {
  const modal = document.getElementById('editModal');
  modal.classList.remove('show');
  currentSubjectId = null;
}

function nextStep() {
  // Validate Step 1
  const code = document.getElementById('edit_code').value.trim();
  const title = document.getElementById('edit_title').value.trim();
  const units = document.getElementById('edit_units').value;
  
  if (!code || !title || !units) {
    alert('Please fill in all required fields.');
    return;
  }
  
  // Show Step 2
  document.getElementById('step1').classList.add('hidden');
  document.getElementById('step2').classList.remove('hidden');
  currentStep = 2;
  updateProgress();
}

function prevStep() {
  // Show Step 1
  document.getElementById('step2').classList.add('hidden');
  document.getElementById('step1').classList.remove('hidden');
  currentStep = 1;
  updateProgress();
}

function updateProgress() {
  document.getElementById('currentStep').textContent = currentStep;
  document.getElementById('progressBar').style.width = currentStep === 1 ? '50%' : '100%';
  document.getElementById('stepTitle').textContent = currentStep === 1 ? 'Basic Information' : 'Assessment Builder';
}

function addAssessmentType(term) {
  const counter = ++assessmentTypeCounter[term];
  const typeId = `${term}_${counter}`;
  const colorIndex = (counter - 1) % colors.length;
  const color = colors[colorIndex];
  
  const assessmentType = {
    id: typeId,
    name: '',
    weight: 0,
    term: term,
    color: color
  };
  
  assessmentTypes[term].push(assessmentType);
  
  const container = document.getElementById(`${term}AssessmentTypes`);
  const typeElement = document.createElement('div');
  typeElement.className = 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4';
  typeElement.innerHTML = `
    <div class="flex items-center justify-between">
      <div class="flex-1 mr-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Type Name</label>
        <input type="text" 
               id="name_${typeId}"
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
               placeholder="e.g., Quiz, Lab, Project"
               oninput="updateAssessmentType('${typeId}', 'name', this.value)">
      </div>
      <div class="w-24">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (%)</label>
        <input type="number" 
               id="weight_${typeId}"
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
               min="0" max="100" step="1" value="0"
               oninput="updateAssessmentType('${typeId}', 'weight', this.value)"
               onkeypress="return event.charCode >= 48 && event.charCode <= 57">
      </div>
      <button type="button" onclick="removeAssessmentType('${typeId}')" class="ml-2 text-red-600 hover:text-red-700 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    </div>
  `;
  
  container.appendChild(typeElement);
  
  // Automatically distribute weights
  distributeWeightsAutomatically(term);
  updateProgressBars();
  
  // Refresh Lucide icons
  lucide.createIcons();
}

function distributeWeightsAutomatically(term) {
  const types = assessmentTypes[term].filter(t => t.name && t.name.trim() !== '');
  
  if (types.length === 0) return;
  
  if (types.length === 1) {
    // Single assessment gets 100%
    types[0].weight = 100;
  } else {
    // Multiple assessments get equal distribution
    const weightPerType = Math.floor(100 / types.length);
    const remainder = 100 % types.length;
    
    types.forEach((type, index) => {
      // Give extra weight to first assessment if there's remainder
      type.weight = weightPerType + (index === 0 ? remainder : 0);
    });
  }
  
  // Update all number inputs
  types.forEach(type => {
    const input = document.getElementById(`weight_${type.id}`);
    if (input) {
      input.value = Math.round(type.weight);
    }
  });
}

function updateAssessmentType(id, field, value) {
  const [term, index] = id.split('_');
  const type = assessmentTypes[term].find(t => t.id === id);
  if (type) {
    if (field === 'weight') {
      type[field] = parseFloat(value) || 0;
    } else {
      type[field] = value;
    }
    
    // If name was updated, redistribute weights
    if (field === 'name') {
      distributeWeightsAutomatically(term);
    }
    
    updateProgressBars();
  }
}

function removeAssessmentType(id) {
  const [term, index] = id.split('_');
  assessmentTypes[term] = assessmentTypes[term].filter(t => t.id !== id);
  
  // Remove from DOM
  const container = document.getElementById(`${term}AssessmentTypes`);
  const elements = container.querySelectorAll('.bg-gray-50');
  elements.forEach(el => {
    if (el.querySelector(`#name_${id}`) || el.querySelector(`#weight_${id}`)) {
      el.remove();
    }
  });
  
  // Redistribute weights after removal
  distributeWeightsAutomatically(term);
  updateProgressBars();
}

function updateProgressBars() {
  updateProgressBar('midterm');
  updateProgressBar('final');
}

function updateProgressBar(term) {
  const progressContainer = document.getElementById(`${term}ProgressContainer`);
  const totalWeight = document.getElementById(`${term}TotalWeight`);
  const output = document.getElementById(`${term}Output`);
  const errorElement = document.getElementById(`${term}Error`);
  
  // Clear existing content
  progressContainer.innerHTML = '';
  output.innerHTML = '';
  
  const types = assessmentTypes[term].filter(t => t.name && t.name.trim() !== '');
  
  if (types.length === 0) {
    totalWeight.textContent = '0%';
    if (errorElement) errorElement.innerHTML = '';
    return;
  }
  
  // Calculate total weight
  const total = types.reduce((sum, type) => sum + (parseFloat(type.weight) || 0), 0);
  totalWeight.textContent = `${Math.round(total)}%`;
  
  // Check if total equals 100%
  if (Math.abs(total - 100) > 0.1) { // Allow small floating point differences
    if (errorElement) {
      errorElement.innerHTML = `
        <div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
          <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-400"></i>
            <span class="text-sm text-red-600 dark:text-red-400">
              Total weight must equal 100%. Current total: ${Math.round(total)}%
            </span>
          </div>
        </div>
      `;
    }
    // Change total weight color to red
    totalWeight.className = 'text-red-600 dark:text-red-400';
  } else {
    if (errorElement) errorElement.innerHTML = '';
    // Change total weight color back to normal
    totalWeight.className = 'text-gray-600 dark:text-gray-400';
  }
  
  // Create progress bar segments
  let currentPosition = 0;
  
  types.forEach((type, index) => {
    // Create segment
    const segment = document.createElement('div');
    segment.className = `segment assessment-${type.color}`;
    segment.style.left = `${currentPosition}%`;
    segment.style.width = `${parseFloat(type.weight) || 0}%`;
    progressContainer.appendChild(segment);
    
    currentPosition += parseFloat(type.weight) || 0;
  });
  
  // Update output text
  const percentages = types.map(type => `${type.name} ${Math.round(parseFloat(type.weight) || 0)}%`);
  output.textContent = percentages.join(' / ');
}

function saveSubject() {
  // Validate Step 2
  const midtermTotal = assessmentTypes.midterm.reduce((sum, type) => sum + (parseFloat(type.weight) || 0), 0);
  const finalTotal = assessmentTypes.final.reduce((sum, type) => sum + (parseFloat(type.weight) || 0), 0);
  
  // Check if totals equal 100%
  if (Math.abs(midtermTotal - 100) > 0.1) {
    alert(`Midterm total must equal 100%. Current total: ${Math.round(midtermTotal)}%`);
    return;
  }
  
  if (Math.abs(finalTotal - 100) > 0.1) {
    alert(`Final total must equal 100%. Current total: ${Math.round(finalTotal)}%`);
    return;
  }
  
  // Create form dynamically
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = `/subjects/${currentSubjectId}`;
  form.style.display = 'none';
  
  // Add CSRF token
  const csrfToken = document.createElement('input');
  csrfToken.type = 'hidden';
  csrfToken.name = '_token';
  csrfToken.value = '{{ csrf_token() }}';
  form.appendChild(csrfToken);
  
  // Add form data
  const formData = {
    'code': document.getElementById('edit_code').value,
    'title': document.getElementById('edit_title').value,
    'units': document.getElementById('edit_units').value,
    'grading_type': document.querySelector('input[name="grading_type"]:checked').value,
    'midterm_weight': document.querySelector('input[name="grading_type"]:checked').value === 'custom' ? document.getElementById('midterm_weight').value : 50,
    'final_weight': document.querySelector('input[name="grading_type"]:checked').value === 'custom' ? document.getElementById('final_weight').value : 50,
    'assessment_types': JSON.stringify(assessmentTypes)
  };
  
  // Add each form field
  Object.keys(formData).forEach(key => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = key;
    input.value = formData[key];
    form.appendChild(input);
  });
  
  // Debug logging
  console.log('=== EDIT FORM DEBUG ===');
  console.log('Form action:', form.action);
  console.log('Current subject ID:', currentSubjectId);
  console.log('Form method:', form.method);
  console.log('Form data:', formData);
  console.log('Assessment types:', assessmentTypes);
  console.log('Form element:', form);
  console.log('========================');
  
  // Add form to document and submit
  document.body.appendChild(form);
  form.submit();
}

// Delete subject function
function deleteSubject(id) {
  if (confirm('Are you sure you want to delete this subject? This action cannot be undone.')) {
    // Create a form to submit the delete request
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/subjects/${id}`;
    
    // Add CSRF token
    const csrfToken = document.createElement('input');
    csrfToken.type = 'hidden';
    csrfToken.name = '_token';
    csrfToken.value = '{{ csrf_token() }}';
    form.appendChild(csrfToken);
    
    // Add method override for DELETE
    const methodField = document.createElement('input');
    methodField.type = 'hidden';
    methodField.name = '_method';
    methodField.value = 'DELETE';
    form.appendChild(methodField);
    
    // Submit the form
    document.body.appendChild(form);
    form.submit();
  }
}

// Handle grading type radio buttons
document.querySelectorAll('input[name="grading_type"]').forEach(radio => {
  radio.addEventListener('change', function() {
    const customWeights = document.getElementById('customWeights');
    if (this.value === 'custom') {
      customWeights.classList.remove('hidden');
    } else {
      customWeights.classList.add('hidden');
    }
  });
});

// Handle custom weight inputs
document.getElementById('midterm_weight')?.addEventListener('input', function() {
  const value = parseFloat(this.value) || 0;
  const finalWeight = document.getElementById('final_weight');
  finalWeight.value = Math.max(0, Math.min(100, 100 - value));
});

document.getElementById('final_weight')?.addEventListener('input', function() {
  const value = parseFloat(this.value) || 0;
  const midtermWeight = document.getElementById('midterm_weight');
  midtermWeight.value = Math.max(0, Math.min(100, 100 - value));
});

// Add input validation for weight fields
document.addEventListener('input', function(e) {
  if (e.target.id && e.target.id.startsWith('weight_')) {
    const value = parseFloat(e.target.value) || 0;
    e.target.value = Math.max(0, Math.min(100, Math.round(value)));
  }
});

// Close modal when clicking outside
document.getElementById('editModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeEditModal();
  }
});

// Handle escape key to close modal
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeEditModal();
  }
});

// Create modal functions
function createSubject() {
  const modal = document.getElementById('createModal');
  
  // Reset form
  document.getElementById('create_code').value = '';
  document.getElementById('create_title').value = '';
  document.getElementById('create_units').value = '';
  
  // Reset to step 1
  createCurrentStep = 1;
  createUpdateProgress();
  document.getElementById('createStep1').classList.remove('hidden');
  document.getElementById('createStep2').classList.add('hidden');
  
  // Reset assessment types
  createAssessmentTypes = { midterm: [], final: [] };
  createAssessmentTypeCounter = { midterm: 0, final: 0 };
  
  // Clear existing assessment types
  document.getElementById('createMidtermAssessmentTypes').innerHTML = '';
  document.getElementById('createFinalAssessmentTypes').innerHTML = '';
  
  // Add default assessment types
  createAddAssessmentType('midterm');
  createAddAssessmentType('final');
  
  // Show modal
  modal.classList.add('show');
  
  // Focus on first input
  setTimeout(() => document.getElementById('create_code').focus(), 100);
}

// Close create modal
function closeCreateModal() {
  const modal = document.getElementById('createModal');
  modal.classList.remove('show');
}

// Create modal step functions
function createNextStep() {
  // Validate Step 1
  const code = document.getElementById('create_code').value.trim();
  const title = document.getElementById('create_title').value.trim();
  const units = document.getElementById('create_units').value;
  
  if (!code || !title || !units) {
    alert('Please fill in all required fields.');
    return;
  }
  
  // Show Step 2
  document.getElementById('createStep1').classList.add('hidden');
  document.getElementById('createStep2').classList.remove('hidden');
  createCurrentStep = 2;
  createUpdateProgress();
}

function createPrevStep() {
  // Show Step 1
  document.getElementById('createStep2').classList.add('hidden');
  document.getElementById('createStep1').classList.remove('hidden');
  createCurrentStep = 1;
  createUpdateProgress();
}

function createUpdateProgress() {
  document.getElementById('createCurrentStep').textContent = createCurrentStep;
  document.getElementById('createProgressBar').style.width = createCurrentStep === 1 ? '50%' : '100%';
  document.getElementById('createStepTitle').textContent = createCurrentStep === 1 ? 'Basic Information' : 'Assessment Builder';
}

// Create assessment type functions
function createAddAssessmentType(term) {
  const counter = ++createAssessmentTypeCounter[term];
  const typeId = `create_${term}_${counter}`;
  const colorIndex = (counter - 1) % colors.length;
  const color = colors[colorIndex];
  
  const assessmentType = {
    id: typeId,
    name: '',
    weight: 0,
    term: term,
    color: color
  };
  
  createAssessmentTypes[term].push(assessmentType);
  
  const container = document.getElementById(`create${term.charAt(0).toUpperCase() + term.slice(1)}AssessmentTypes`);
  const typeElement = document.createElement('div');
  typeElement.className = 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4';
  typeElement.innerHTML = `
    <div class="flex items-center justify-between">
      <div class="flex-1 mr-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Type Name</label>
        <input type="text" 
               id="name_${typeId}"
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
               placeholder="e.g., Quiz, Lab, Project"
               oninput="createUpdateAssessmentType('${typeId}', 'name', this.value)">
      </div>
      <div class="w-24">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (%)</label>
        <input type="number" 
               id="weight_${typeId}"
               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" 
               min="0" max="100" step="1" value="0"
               oninput="createUpdateAssessmentType('${typeId}', 'weight', this.value)"
               onkeypress="return event.charCode >= 48 && event.charCode <= 57">
      </div>
      <button type="button" onclick="createRemoveAssessmentType('${typeId}')" class="ml-2 text-red-600 hover:text-red-700 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    </div>
  `;
  
  container.appendChild(typeElement);
  
  // Automatically distribute weights
  createDistributeWeightsAutomatically(term);
  createUpdateProgressBars();
  
  // Refresh Lucide icons
  lucide.createIcons();
}

function createDistributeWeightsAutomatically(term) {
  const types = createAssessmentTypes[term].filter(t => t.name && t.name.trim() !== '');
  
  if (types.length === 0) return;
  
  if (types.length === 1) {
    // Single assessment gets 100%
    types[0].weight = 100;
  } else {
    // Multiple assessments get equal distribution
    const weightPerType = Math.floor(100 / types.length);
    const remainder = 100 % types.length;
    
    types.forEach((type, index) => {
      // Give extra weight to first assessment if there's remainder
      type.weight = weightPerType + (index === 0 ? remainder : 0);
    });
  }
  
  // Update all number inputs
  types.forEach(type => {
    const input = document.getElementById(`weight_${type.id}`);
    if (input) {
      input.value = Math.round(type.weight);
    }
  });
}

function createUpdateAssessmentType(id, field, value) {
  const [prefix, term, index] = id.split('_');
  const type = createAssessmentTypes[term].find(t => t.id === id);
  if (type) {
    if (field === 'weight') {
      type[field] = parseFloat(value) || 0;
    } else {
      type[field] = value;
    }
    
    // If name was updated, redistribute weights
    if (field === 'name') {
      createDistributeWeightsAutomatically(term);
    }
    
    createUpdateProgressBars();
  }
}

function createRemoveAssessmentType(id) {
  const [prefix, term, index] = id.split('_');
  createAssessmentTypes[term] = createAssessmentTypes[term].filter(t => t.id !== id);
  
  // Remove from DOM
  const container = document.getElementById(`create${term.charAt(0).toUpperCase() + term.slice(1)}AssessmentTypes`);
  const elements = container.querySelectorAll('.bg-gray-50');
  elements.forEach(el => {
    if (el.querySelector(`#name_${id}`) || el.querySelector(`#weight_${id}`)) {
      el.remove();
    }
  });
  
  // Redistribute weights after removal
  createDistributeWeightsAutomatically(term);
  createUpdateProgressBars();
  
  // Refresh Lucide icons
  lucide.createIcons();
}

function createUpdateProgressBars() {
  createUpdateProgressBar('midterm');
  createUpdateProgressBar('final');
}

function createUpdateProgressBar(term) {
  const progressContainer = document.getElementById(`create${term.charAt(0).toUpperCase() + term.slice(1)}ProgressContainer`);
  const totalWeight = document.getElementById(`create${term.charAt(0).toUpperCase() + term.slice(1)}TotalWeight`);
  const output = document.getElementById(`create${term.charAt(0).toUpperCase() + term.slice(1)}Output`);
  const errorElement = document.getElementById(`create${term.charAt(0).toUpperCase() + term.slice(1)}Error`);
  
  // Clear existing content
  progressContainer.innerHTML = '';
  output.innerHTML = '';
  
  const types = createAssessmentTypes[term].filter(t => t.name && t.name.trim() !== '');
  
  if (types.length === 0) {
    totalWeight.textContent = '0%';
    if (errorElement) errorElement.innerHTML = '';
    return;
  }
  
  // Calculate total weight
  const total = types.reduce((sum, type) => sum + (parseFloat(type.weight) || 0), 0);
  totalWeight.textContent = `${Math.round(total)}%`;
  
  // Check if total equals 100%
  if (Math.abs(total - 100) > 0.1) { // Allow small floating point differences
    if (errorElement) {
      errorElement.innerHTML = `
        <div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
          <div class="flex items-center gap-2">
            <i data-lucide="alert-circle" class="w-4 h-4 text-red-600 dark:text-red-400"></i>
            <span class="text-sm text-red-600 dark:text-red-400">
              Total weight must equal 100%. Current total: ${Math.round(total)}%
            </span>
          </div>
        </div>
      `;
    }
    // Change total weight color to red
    totalWeight.className = 'text-red-600 dark:text-red-400';
  } else {
    if (errorElement) errorElement.innerHTML = '';
    // Change total weight color back to normal
    totalWeight.className = 'text-gray-600 dark:text-gray-400';
  }
  
  // Create progress bar segments
  let currentPosition = 0;
  
  types.forEach((type, index) => {
    // Create segment
    const segment = document.createElement('div');
    segment.className = `segment assessment-${type.color}`;
    segment.style.left = `${currentPosition}%`;
    segment.style.width = `${parseFloat(type.weight) || 0}%`;
    progressContainer.appendChild(segment);
    
    currentPosition += parseFloat(type.weight) || 0;
  });
  
  // Update output text
  const percentages = types.map(type => `${type.name} ${Math.round(parseFloat(type.weight) || 0)}%`);
  output.textContent = percentages.join(' / ');
}

function createSaveSubject() {
  // Validate Step 2
  const midtermTotal = createAssessmentTypes.midterm.reduce((sum, type) => sum + (parseFloat(type.weight) || 0), 0);
  const finalTotal = createAssessmentTypes.final.reduce((sum, type) => sum + (parseFloat(type.weight) || 0), 0);
  
  // Check if totals equal 100%
  if (Math.abs(midtermTotal - 100) > 0.1) {
    alert(`Midterm total must equal 100%. Current total: ${Math.round(midtermTotal)}%`);
    return;
  }
  
  if (Math.abs(finalTotal - 100) > 0.1) {
    alert(`Final total must equal 100%. Current total: ${Math.round(finalTotal)}%`);
    return;
  }
  
  // Create form dynamically
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = "{{ route('subjects.store') }}";
  form.style.display = 'none';
  
  // Add CSRF token
  const csrfToken = document.createElement('input');
  csrfToken.type = 'hidden';
  csrfToken.name = '_token';
  csrfToken.value = '{{ csrf_token() }}';
  form.appendChild(csrfToken);
  
  // Add form data
  const formData = {
    'code': document.getElementById('create_code').value,
    'title': document.getElementById('create_title').value,
    'units': document.getElementById('create_units').value,
    'grading_type': document.querySelector('input[name="create_grading_type"]:checked').value,
    'midterm_weight': document.querySelector('input[name="create_grading_type"]:checked').value === 'custom' ? document.getElementById('create_midterm_weight').value : 50,
    'final_weight': document.querySelector('input[name="create_grading_type"]:checked').value === 'custom' ? document.getElementById('create_final_weight').value : 50,
    'assessment_types': JSON.stringify(createAssessmentTypes)
  };
  
  // Add each form field
  Object.keys(formData).forEach(key => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = key;
    input.value = formData[key];
    form.appendChild(input);
  });
  
  // Add form to document and submit
  document.body.appendChild(form);
  form.submit();
}

// Handle grading type radio buttons for create modal
document.querySelectorAll('input[name="create_grading_type"]').forEach(radio => {
  radio.addEventListener('change', function() {
    const customWeights = document.getElementById('createCustomWeights');
    if (this.value === 'custom') {
      customWeights.classList.remove('hidden');
    } else {
      customWeights.classList.add('hidden');
    }
  });
});

// Handle custom weight inputs for create modal
document.getElementById('create_midterm_weight')?.addEventListener('input', function() {
  const value = parseFloat(this.value) || 0;
  const finalWeight = document.getElementById('create_final_weight');
  finalWeight.value = Math.max(0, Math.min(100, 100 - value));
});

document.getElementById('create_final_weight')?.addEventListener('input', function() {
  const value = parseFloat(this.value) || 0;
  const midtermWeight = document.getElementById('create_midterm_weight');
  midtermWeight.value = Math.max(0, Math.min(100, 100 - value));
});

// Close create modal when clicking outside
document.getElementById('createModal').addEventListener('click', function(e) {
  if (e.target === this) {
    closeCreateModal();
  }
});

// Handle escape key to close modals
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    closeEditModal();
    closeCreateModal();
  }
});
</script>
@endsection 