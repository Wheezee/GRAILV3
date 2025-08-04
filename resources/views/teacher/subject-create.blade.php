@extends('layouts.app')

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
      <a href="{{ route('subjects.index') }}" class="hover:text-evsu dark:hover:text-evsu transition-colors whitespace-nowrap">
        Subjects
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">Create Subject</span>
    </li>
  </ol>
</nav>

<!-- Header -->
<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create New Subject</h1>
  <p class="text-gray-600 dark:text-gray-400 mt-1">Set up your subject with grading structure and assessment types</p>
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
        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject ID</label>
        <input type="text" id="code" name="code" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., MATH101">
      </div>
      
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject Name</label>
        <input type="text" id="title" name="title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., College Algebra">
      </div>
      
      <div>
        <label for="units" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Units</label>
        <input type="number" id="units" name="units" step="0.5" min="0.5" max="6" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="3.0">
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
        Create Subject
      </button>
    </div>
  </div>
</div>

<!-- Hidden Form for Submission -->
<form id="subjectForm" method="POST" action="{{ route('subjects.store') }}" class="hidden">
  @csrf
  <input type="hidden" id="formCode" name="code">
  <input type="hidden" id="formTitle" name="title">
  <input type="hidden" id="formUnits" name="units">
  <input type="hidden" id="formGradingType" name="grading_type">
  <input type="hidden" id="formMidtermWeight" name="midterm_weight">
  <input type="hidden" id="formFinalWeight" name="final_weight">
  <input type="hidden" id="formAssessmentTypes" name="assessment_types">
</form>

<style>
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
</style>

<script>
let currentStep = 1;
let assessmentTypeCounter = { midterm: 0, final: 0 };
let assessmentTypes = { midterm: [], final: [] };
const colors = ['blue', 'green', 'purple', 'orange', 'pink', 'indigo', 'teal', 'red'];

// Initialize with default assessment types
document.addEventListener('DOMContentLoaded', function() {
  addAssessmentType('midterm');
  addAssessmentType('final');
  updateProgressBars();
});

function nextStep() {
  // Validate Step 1
  const code = document.getElementById('code').value.trim();
  const title = document.getElementById('title').value.trim();
  const units = document.getElementById('units').value;
  
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
      <button type="button" onclick="removeAssessmentType('${typeId}')" class="ml-2 text-red-600 hover:text-red-700">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    </div>
  `;
  
  container.appendChild(typeElement);
  
  // Automatically distribute weights
  distributeWeightsAutomatically(term);
  updateProgressBars();
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
    type[field] = field === 'weight' ? parseFloat(value) || 0 : value;
    
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
  
  const types = assessmentTypes[term].filter(t => t.name && t.weight > 0);
  
  if (types.length === 0) {
    totalWeight.textContent = '0%';
    if (errorElement) errorElement.innerHTML = '';
    return;
  }
  
  // Calculate total weight
  const total = types.reduce((sum, type) => sum + type.weight, 0);
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
    segment.style.width = `${type.weight}%`;
    progressContainer.appendChild(segment);
    
    currentPosition += type.weight;
  });
  
  // Update output text
  const percentages = types.map(type => `${type.name} ${Math.round(type.weight)}%`);
  output.textContent = percentages.join(' / ');
}

function saveSubject() {
  // Validate Step 2
  const midtermTotal = assessmentTypes.midterm.reduce((sum, type) => sum + type.weight, 0);
  const finalTotal = assessmentTypes.final.reduce((sum, type) => sum + type.weight, 0);
  
  // Check if totals equal 100%
  if (Math.abs(midtermTotal - 100) > 0.1) {
    alert(`Midterm total must equal 100%. Current total: ${Math.round(midtermTotal)}%`);
    return;
  }
  
  if (Math.abs(finalTotal - 100) > 0.1) {
    alert(`Final total must equal 100%. Current total: ${Math.round(finalTotal)}%`);
    return;
  }
  
  // Prepare form data
  document.getElementById('formCode').value = document.getElementById('code').value;
  document.getElementById('formTitle').value = document.getElementById('title').value;
  document.getElementById('formUnits').value = document.getElementById('units').value;
  
  const gradingType = document.querySelector('input[name="grading_type"]:checked').value;
  document.getElementById('formGradingType').value = gradingType;
  
  if (gradingType === 'custom') {
    document.getElementById('formMidtermWeight').value = document.getElementById('midterm_weight').value;
    document.getElementById('formFinalWeight').value = document.getElementById('final_weight').value;
  } else {
    document.getElementById('formMidtermWeight').value = 50;
    document.getElementById('formFinalWeight').value = 50;
  }
  
  document.getElementById('formAssessmentTypes').value = JSON.stringify(assessmentTypes);
  
  // Submit form
  document.getElementById('subjectForm').submit();
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
  const finalWeight = document.getElementById('final_weight');
  finalWeight.value = 100 - this.value;
});

document.getElementById('final_weight')?.addEventListener('input', function() {
  const midtermWeight = document.getElementById('midterm_weight');
  midtermWeight.value = 100 - this.value;
});
</script>
@endsection 