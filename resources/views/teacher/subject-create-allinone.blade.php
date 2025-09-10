@extends('layouts.app')

@section('content')
<nav class="mb-6" aria-label="Breadcrumb">
  <ol class="flex flex-wrap items-center gap-1 sm:gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
    <li class="flex items-center">
      <a href="{{ route('dashboard') }}" class="hover:text-evsu dark:hover:text-evsu transition-colors whitespace-nowrap">Home</a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <a href="{{ route('subjects.allinone.index') }}" class="hover:text-evsu dark:hover:text-evsu transition-colors whitespace-nowrap">Subjects (All-in-one)</a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">Create All-in-one Subject</span>
    </li>
  </ol>
</nav>

<div class="mb-6">
  <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create All-in-one Subject</h1>
  <p class="text-gray-600 dark:text-gray-400 mt-1">Single-term subject with overall assessment types</p>
</div>

<div class="max-w-3xl mx-auto bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm">
  <form id="aoiForm" method="POST" action="{{ route('subjects.allinone.store') }}">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="md:col-span-1">
        <label for="code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject ID</label>
        <input type="text" id="code" name="code" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., MATH101">
      </div>
      <div class="md:col-span-1">
        <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject Name</label>
        <input type="text" id="title" name="title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., College Algebra">
      </div>
      <div class="md:col-span-1">
        <label for="units" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Units</label>
        <input type="number" id="units" name="units" step="0.5" min="0.5" max="6" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="3.0">
      </div>
    </div>

    <div class="mt-8">
      <div class="flex items-center justify-between mb-2">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Assessment Types (Overall)</h2>
        <button type="button" id="addTypeBtn" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
          <i data-lucide="plus" class="w-4 h-4"></i>
          Add Assessment Type
        </button>
      </div>

      <div id="typesContainer" class="space-y-4"></div>

      <div class="mt-4">
        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
          <span>Overall Weight Distribution</span>
          <span id="totalWeight">0%</span>
        </div>
        <div class="slider-container" id="progressContainer"></div>
        <div id="output" class="text-xs text-gray-500 dark:text-gray-400 mt-1"></div>
        <div id="weightError"></div>
      </div>
    </div>

    <input type="hidden" name="assessment_types" id="assessment_types">

    <div class="mt-8 flex justify-end gap-3">
      <a href="{{ route('subjects.allinone.index') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-lg transition-colors">Cancel</a>
      <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">Create</button>
    </div>
  </form>
</div>

<style>
.slider-container { position: relative; width: 100%; height: 20px; background: #e5e7eb; border-radius: 10px; overflow: hidden; }
.segment { position: absolute; top: 0; height: 100%; border-radius: 10px; transition: all 0.2s ease; }
.assessment-blue { background: linear-gradient(90deg, #3b82f6 0%, #1d4ed8 100%); }
.assessment-green { background: linear-gradient(90deg, #10b981 0%, #059669 100%); }
.assessment-purple { background: linear-gradient(90deg, #8b5cf6 0%, #7c3aed 100%); }
.assessment-orange { background: linear-gradient(90deg, #f59e0b 0%, #d97706 100%); }
.assessment-pink { background: linear-gradient(90deg, #ec4899 0%, #db2777 100%); }
.assessment-indigo { background: linear-gradient(90deg, #6366f1 0%, #4f46e5 100%); }
.assessment-teal { background: linear-gradient(90deg, #14b8a6 0%, #0d9488 100%); }
.assessment-red { background: linear-gradient(90deg, #ef4444 0%, #dc2626 100%); }
.dark .slider-container { background: #374151; }
</style>

<script>
const colors = ['blue', 'green', 'purple', 'orange', 'pink', 'indigo', 'teal', 'red'];
let typeCounter = 0;
let types = [];

document.addEventListener('DOMContentLoaded', () => {
  document.getElementById('addTypeBtn').addEventListener('click', addType);
  addType();
  updateProgressBar();
});

function addType() {
  const container = document.getElementById('typesContainer');
  const id = ++typeCounter;
  const color = colors[(id - 1) % colors.length];
  const type = { id, name: '', weight: 0, color };
  types.push(type);

  const el = document.createElement('div');
  el.className = 'bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4';
  el.innerHTML = `
    <div class="flex items-center justify-between">
      <div class="flex-1 mr-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Type Name</label>
        <input type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., Quiz, Lab, Project" oninput="onTypeChange(${id}, 'name', this.value)">
      </div>
      <div class="w-24">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (%)</label>
        <input type="number" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" min="0" max="100" step="1" value="0" oninput="onTypeChange(${id}, 'weight', this.value)" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
      </div>
      <button type="button" class="ml-2 text-red-600 hover:text-red-700 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" onclick="removeType(${id})">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    </div>
  `;
  container.appendChild(el);

  if (window.lucide) lucide.createIcons();
}

function onTypeChange(id, field, value) {
  const t = types.find(x => x.id === id);
  if (!t) return;
  if (field === 'weight') {
    t.weight = parseFloat(value) || 0;
  } else {
    t.name = value;
    autoDistributeWeights();
  }
  updateProgressBar();
}

function removeType(id) {
  types = types.filter(x => x.id !== id);
  const container = document.getElementById('typesContainer');
  const items = Array.from(container.children);
  for (const item of items) {
    const input = item.querySelector('input[type="text"]');
    const num = item.querySelector('input[type="number"]');
    if (input && num && !types.some(t => input.value === t.name && Number(num.value) === Math.round(t.weight))) {
      item.remove();
      break;
    }
  }
  autoDistributeWeights();
  updateProgressBar();
}

function autoDistributeWeights() {
  const named = types.filter(t => t.name && t.name.trim() !== '');
  if (named.length === 0) return;
  if (named.length === 1) {
    named[0].weight = 100;
  } else {
    const per = Math.floor(100 / named.length);
    const rem = 100 % named.length;
    named.forEach((t, i) => t.weight = per + (i === 0 ? rem : 0));
  }
  // reflect in inputs
  const container = document.getElementById('typesContainer');
  const items = Array.from(container.children);
  items.forEach((item, i) => {
    const name = item.querySelector('input[type="text"]').value;
    const t = types.find(x => x.name === name);
    if (t) {
      const num = item.querySelector('input[type="number"]');
      num.value = Math.round(t.weight);
    }
  });
}

function updateProgressBar() {
  const container = document.getElementById('progressContainer');
  const totalLabel = document.getElementById('totalWeight');
  const output = document.getElementById('output');
  const error = document.getElementById('weightError');
  container.innerHTML = '';
  output.innerHTML = '';

  const active = types.filter(t => t.name && (t.weight || 0) > 0);
  if (active.length === 0) {
    totalLabel.textContent = '0%';
    if (error) error.innerHTML = '';
  } else {
    const total = active.reduce((s, t) => s + (parseFloat(t.weight) || 0), 0);
    totalLabel.textContent = `${Math.round(total)}%`;
    if (Math.abs(total - 100) > 0.1) {
      error.innerHTML = `<div class="mt-2 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md"><div class="flex items-center gap-2"><i data-lucide=\"alert-circle\" class=\"w-4 h-4 text-red-600 dark:text-red-400\"></i><span class=\"text-sm text-red-600 dark:text-red-400\">Total weight must equal 100%. Current total: ${Math.round(total)}%</span></div></div>`;
      totalLabel.className = 'text-red-600 dark:text-red-400';
    } else {
      if (error) error.innerHTML = '';
      totalLabel.className = 'text-gray-600 dark:text-gray-400';
    }

    let pos = 0;
    active.forEach(t => {
      const seg = document.createElement('div');
      seg.className = `segment assessment-${t.color}`;
      seg.style.left = `${pos}%`;
      seg.style.width = `${parseFloat(t.weight) || 0}%`;
      container.appendChild(seg);
      pos += parseFloat(t.weight) || 0;
    });

    output.textContent = active.map(t => `${t.name} ${Math.round(parseFloat(t.weight) || 0)}%`).join(' / ');
  }

  // serialize
  const payload = types
    .filter(t => t.name && (parseFloat(t.weight) || 0) > 0)
    .map(t => ({ name: t.name, weight: Math.round(parseFloat(t.weight) || 0) }));
  document.getElementById('assessment_types').value = JSON.stringify(payload);

  if (window.lucide) lucide.createIcons();
}
</script>
@endsection


