@extends('layouts.app')

@section('content')
<style>
/* Modal styles (match standard subjects page) */
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.5); display: flex; align-items: center; justify-content: center; z-index: 50; opacity: 0; visibility: hidden; transition: all .3s ease; padding: 1rem; }
.modal-overlay.show { opacity: 1; visibility: visible; }
.modal-content { background: white; border-radius: .75rem; width: 100%; max-width: 42rem; max-height: 90vh; overflow-y: auto; transform: scale(.95); transition: transform .3s ease; box-shadow: 0 25px 50px -12px rgba(0,0,0,.25); }
.dark .modal-content { background: #1f2937; border: 1px solid #374151; }
.modal-overlay.show .modal-content { transform: scale(1); }
</style>
<nav class="mb-6" aria-label="Breadcrumb">
  <ol class="flex flex-wrap items-center gap-1 sm:gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400">
    <li class="flex items-center">
      <a href="{{ route('dashboard') }}" class="hover:text-evsu dark:hover:text-evsu transition-colors whitespace-nowrap">Home</a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">Subjects (All-in-one)</span>
    </li>
  </ol>
  </nav>

@if (session('success'))
  <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
    <div class="flex items-center gap-3">
      <i data-lucide="check-circle-2" class="w-5 h-5 text-green-600"></i>
      <p class="text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</p>
    </div>
  </div>
@endif

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
  <div>
    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Subjects (All-in-one)</h1>
    <p class="text-gray-600 dark:text-gray-400 mt-1">Single-term subjects with overall assessment types</p>
  </div>
  <div class="flex gap-2">
    <button type="button" onclick="openAoiCreateModal()" class="mt-4 sm:mt-0 inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
      <i data-lucide="plus" class="w-4 h-4"></i>
      Add All-in-one Subject
    </button>
  </div>
</div>

<div class="grid gap-6 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
  @forelse($subjects ?? [] as $subject)
    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow" title="AY: {{ $subject->academic_year ?? '—' }} | Sem: {{ $subject->semester ?? '—' }}">
      <div class="flex items-start justify-between mb-4">
        <div>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $subject->code }}</h3>
          <p class="text-gray-600 dark:text-gray-400 text-sm">{{ $subject->title }}</p>
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
        <i data-lucide="book-open-check" class="w-16 h-16 mx-auto"></i>
      </div>
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">No All-in-one subjects in this AY/Sem</h3>
      <p class="text-gray-500 dark:text-gray-400 mb-2">Current filter:</p>
      <p class="text-gray-500 dark:text-gray-400 mb-6">AY: {{ session('academic_year') ?? '—' }} | Sem: {{ session('semester') ?? '—' }}</p>
      <button type="button" onclick="openAoiCreateModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add All-in-one Subject
      </button>
    </div>
  @endforelse
</div>
 
<!-- Create All-in-one Modal -->
<div id="aoiCreateModal" class="modal-overlay">
  <div class="modal-content">
    <div class="p-6">
      <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Create All-in-one Subject</h3>
        <button onclick="closeAoiCreateModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>

      <form id="aoiCreateForm" method="POST" action="{{ route('subjects.allinone.store') }}">
        @csrf
        <!-- Progress -->
        <div class="mb-6">
          <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Step <span id="aoiStepLabel">1</span> of 2</span>
            <span class="text-sm text-gray-500 dark:text-gray-400" id="aoiStepTitle">Basic Information</span>
          </div>
          <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div id="aoiProgressBar" class="bg-red-600 h-2 rounded-full transition-all duration-300" style="width: 50%"></div>
          </div>
        </div>

        <!-- Step 1 -->
        <div id="aoiStep1">
          <div class="grid grid-cols-1 gap-4">
            <div class="md:col-span-1">
              <label for="aoi_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject ID</label>
              <input type="text" id="aoi_code" name="code" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., MATH101">
            </div>
            <div class="md:col-span-1">
              <label for="aoi_title" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject Name</label>
              <input type="text" id="aoi_title" name="title" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., College Algebra">
            </div>
            <div class="md:col-span-1">
              <label for="aoi_units" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Units</label>
              <input type="number" id="aoi_units" name="units" step="0.5" min="0.5" max="6" required class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="3.0">
            </div>
          </div>
          <div class="flex justify-center mt-6">
            <button type="button" onclick="aoiNextStep()" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium">Next: Assessment Builder</button>
          </div>
        </div>

        <!-- Step 2 -->
        <div id="aoiStep2" class="hidden">
          <div class="max-w-2xl mx-auto">
          <div class="flex items-center justify-between mb-2">
            <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">Assessment Types (Overall)</h4>
            <button type="button" onclick="aoiAddType()" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors">
              <i data-lucide="plus" class="w-4 h-4"></i>
              Add Assessment Type
            </button>
          </div>

          <!-- Attendance toggle -->
          <div class="mb-4 max-w-2xl">
            <label class="flex items-center p-4 bg-gray-50 dark:bg-gray-700/50 border border-gray-200 dark:border-gray-600 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700/70 transition-all duration-200 shadow-sm">
              <input type="checkbox" id="aoiAddAttendance" class="w-5 h-5 text-blue-600 bg-gray-100 dark:bg-gray-600 border-gray-300 dark:border-gray-500 rounded-md focus:ring-blue-500 focus:ring-2 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-800 transition-all duration-200 hover:scale-105" onchange="aoiToggleAttendance(this.checked)">
              <div class="ml-4">
                <div class="flex items-center gap-2">
                  <i data-lucide="calendar" class="w-5 h-5 text-blue-600"></i>
                  <span class="font-medium text-gray-900 dark:text-gray-100">Add Attendance</span>
                </div>
              </div>
            </label>
          </div>

          <div id="aoiTypesContainer" class="space-y-4"></div>

          <div class="mt-4">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
              <span>Overall Weight Distribution</span>
              <span id="aoiTotalWeight">0%</span>
            </div>
            <div class="slider-container" id="aoiProgressContainer"></div>
            <div id="aoiOutput" class="text-xs text-gray-500 dark:text-gray-400 mt-1"></div>
            <div id="aoiWeightError"></div>
          </div>
          </div>

          <input type="hidden" name="assessment_types" id="aoi_assessment_types">

          <div class="mt-8 flex justify-center gap-3">
            <button type="button" onclick="aoiPrevStep()" class="px-6 py-3 bg-gray-300 hover:bg-gray-400 dark:bg-gray-600 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-lg transition-colors font-medium">← Back</button>
            <button type="submit" class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors" onclick="return aoiBeforeSubmit()">Create</button>
          </div>
        </div>
      </form>
    </div>
  </div>
  
</div>

<script>
let aoiTypes = [];
let aoiCounter = 0;
const aoiColors = ['blue','green','purple','orange','pink','indigo','teal','red'];

function openAoiCreateModal(){
  const m = document.getElementById('aoiCreateModal');
  aoiTypes = []; aoiCounter = 0;
  document.getElementById('aoiTypesContainer').innerHTML='';
  aoiAddType();
  aoiUpdateProgress();
  m.classList.add('show');
  if (window.lucide) lucide.createIcons();
}
function closeAoiCreateModal(){ document.getElementById('aoiCreateModal').classList.remove('show'); }

function aoiAddType(){
  const id = ++aoiCounter;
  const color = aoiColors[(id-1)%aoiColors.length];
  const t = { id, name:'', weight:0, color };
  aoiTypes.push(t);
  const wrap = document.createElement('div');
  wrap.className='bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4';
  wrap.setAttribute('data-id', id);
  wrap.innerHTML = `
    <div class="flex items-center justify-between">
      <div class="flex-1 mr-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Type Name</label>
        <input type="text" data-id="${id}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" placeholder="e.g., Quiz, Lab, Project" oninput="aoiOnChange(${id}, 'name', this.value)">
      </div>
      <div class="w-24">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (%)</label>
        <input type="number" data-id="${id}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" min="0" max="100" step="1" value="0" oninput="aoiOnChange(${id}, 'weight', this.value)">
      </div>
      <button type="button" class="ml-2 text-red-600 hover:text-red-700 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" onclick="aoiRemoveType(${id}, this)">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    </div>`;
  document.getElementById('aoiTypesContainer').appendChild(wrap);
  aoiAutoDistribute();
  aoiUpdateProgress();
}
function aoiOnChange(id, field, value){
  const t = aoiTypes.find(x=>x.id===id); if(!t) return;
  if(field==='weight'){ t.weight = parseFloat(value)||0; } else { t.name = value; aoiAutoDistribute(); }
  aoiUpdateProgress();
}
function aoiRemoveType(id, el){
  aoiTypes = aoiTypes.filter(x=>x.id!==id);
  // remove containing card
  let card = el.closest('.bg-gray-50');
  if(!card) card = el.closest('.bg-gray-700');
  if(card) card.remove();
  aoiAutoDistribute();
  aoiUpdateProgress();
}
function aoiAutoDistribute(){
  const named = aoiTypes.filter(t=>t.name && t.name.trim()!=='');
  if(named.length===0) return;
  if(named.length===1){ named[0].weight = 100; }
  else{
    const per = Math.floor(100/named.length);
    const rem = 100 % named.length;
    named.forEach((t,i)=> t.weight = per + (i===0?rem:0));
  }
  // Reflect weights by id to avoid mismatch
  aoiTypes.forEach(t=>{
    const num = document.querySelector(`#aoiTypesContainer input[type="number"][data-id='${t.id}']`);
    if(num){ num.value = Math.round(t.weight); }
  });
}
function aoiUpdateProgress(){
  const container = document.getElementById('aoiProgressContainer');
  const totalLabel = document.getElementById('aoiTotalWeight');
  const output = document.getElementById('aoiOutput');
  const error = document.getElementById('aoiWeightError');
  container.innerHTML=''; output.innerHTML='';
  const active = aoiTypes.filter(t=>t.name && (t.weight||0)>0);
  if(active.length===0){ totalLabel.textContent='0%'; if(error) error.innerHTML=''; }
  else{
    const total = active.reduce((s,t)=> s + (parseFloat(t.weight)||0), 0);
    totalLabel.textContent = `${Math.round(total)}%`;
    if(Math.abs(total-100)>0.1){
      error.innerHTML = `<div class=\"mt-2 p-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md\"><div class=\"flex items-center gap-2\"><i data-lucide=\"alert-circle\" class=\"w-4 h-4 text-red-600 dark:text-red-400\"></i><span class=\"text-sm text-red-600 dark:text-red-400\">Total weight must equal 100%. Current total: ${Math.round(total)}%</span></div></div>`;
      totalLabel.className = 'text-red-600 dark:text-red-400';
    } else { if(error) error.innerHTML=''; totalLabel.className = 'text-gray-600 dark:text-gray-400'; }
    let pos=0; active.forEach(t=>{ const seg=document.createElement('div'); seg.className=`segment assessment-${t.color}`; seg.style.left=`${pos}%`; seg.style.width=`${parseFloat(t.weight)||0}%`; container.appendChild(seg); pos+=parseFloat(t.weight)||0; });
    output.textContent = active.map(t=>`${t.name} ${Math.round(parseFloat(t.weight)||0)}%`).join(' / ');
  }
  if(window.lucide) lucide.createIcons();
}
function aoiBeforeSubmit(){
  const total = aoiTypes.reduce((s,t)=> s + (parseFloat(t.weight)||0), 0);
  if(Math.abs(total-100)>0.1){ alert(`Weights must equal 100%. Current: ${Math.round(total)}%`); return false; }
  const payload = aoiTypes.filter(t=>t.name && (parseFloat(t.weight)||0)>0).map(t=>({name:t.name, weight:Math.round(parseFloat(t.weight)||0)}));
  document.getElementById('aoi_assessment_types').value = JSON.stringify(payload);
  return true;
}

// Attendance helpers
function aoiToggleAttendance(checked){
  if(checked){ aoiAddAttendance(); }
  else { aoiRemoveAttendance(); }
  aoiAutoDistribute();
  aoiUpdateProgress();
}
function aoiAddAttendance(){
  // already present?
  if(aoiTypes.some(t=>t.name==='Attendance')) return;
  const id = ++aoiCounter; const color = aoiColors[(id-1)%aoiColors.length];
  const t = { id, name:'Attendance', weight:100, color };
  aoiTypes.unshift(t);
  const el = document.createElement('div');
  el.className='bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4';
  el.setAttribute('data-id', id);
  el.innerHTML = `
    <div class="flex items-center justify-between">
      <div class="flex-1 mr-4">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assessment Type Name</label>
        <input type="text" data-id="${id}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" value="Attendance" oninput="aoiOnChange(${id}, 'name', this.value)">
      </div>
      <div class="w-24">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight (%)</label>
        <input type="number" data-id="${id}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 dark:bg-gray-700 dark:text-white" min="0" max="100" step="1" value="100" oninput="aoiOnChange(${id}, 'weight', this.value)">
      </div>
      <button type="button" class="ml-2 text-red-600 hover:text-red-700 p-2 rounded-md hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" onclick="aoiRemoveType(${id}, this)">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    </div>`;
  const container = document.getElementById('aoiTypesContainer');
  container.prepend(el);
  if(window.lucide) lucide.createIcons();
}
function aoiRemoveAttendance(){
  aoiTypes = aoiTypes.filter(t=>t.name!=='Attendance');
  const container = document.getElementById('aoiTypesContainer');
  Array.from(container.children).forEach(el=>{
    const name = el.querySelector('input[type="text"]')?.value;
    if(name==='Attendance') el.remove();
  });
}

// Close on overlay click
document.getElementById('aoiCreateModal').addEventListener('click', function(e){ if(e.target === this){ closeAoiCreateModal(); } });
// Close on ESC
document.addEventListener('keydown', function(e){ if(e.key==='Escape'){ closeAoiCreateModal(); } });

// Stepper
let aoiStep = 1;
function aoiSyncStepper(){
  document.getElementById('aoiStepLabel').textContent = aoiStep;
  document.getElementById('aoiStepTitle').textContent = aoiStep===1 ? 'Basic Information' : 'Assessment Builder';
  document.getElementById('aoiProgressBar').style.width = aoiStep===1 ? '50%' : '100%';
  document.getElementById('aoiStep1').classList.toggle('hidden', aoiStep!==1);
  document.getElementById('aoiStep2').classList.toggle('hidden', aoiStep!==2);
}
function aoiNextStep(){
  const code = document.getElementById('aoi_code').value.trim();
  const title = document.getElementById('aoi_title').value.trim();
  const units = document.getElementById('aoi_units').value;
  if(!code || !title || !units){ alert('Please fill in all required fields.'); return; }
  aoiStep = 2; aoiSyncStepper();
}
function aoiPrevStep(){ aoiStep = 1; aoiSyncStepper(); }
</script>
@endsection


