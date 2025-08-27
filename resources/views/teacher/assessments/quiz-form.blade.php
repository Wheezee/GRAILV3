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
      <a href="{{ route('assessments.index', [$classSection->subject->id, $classSection->id, $term, $assessment->assessmentType->id]) }}" class="hover:text-red-600 dark:hover:text-red-400 max-w-[120px] sm:max-w-none truncate">
        {{ $assessment->assessmentType->name }}
      </a>
    </li>
    <li class="flex items-center">
      <i data-lucide="chevron-right" class="w-3 h-3 sm:w-4 sm:h-4 mx-1 sm:mx-2 flex-shrink-0"></i>
      <span class="text-gray-900 dark:text-gray-100 font-medium whitespace-nowrap">Make Quiz</span>
    </li>
  </ol>
</nav>

<!-- Header Section -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8 gap-4">
  <div>
    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Make Quiz: {{ $assessment->name }}</h2>
    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ $classSection->section }} - {{ $classSection->subject->code }} - {{ $classSection->subject->title }}</p>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Max Score: {{ $assessment->max_score }} | Term: {{ ucfirst($term) }}</p>
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

<!-- Quiz Form -->
<div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm p-6">
  <form action="{{ route('assessments.quiz.store', [$classSection->subject->id, $classSection->id, $term, $assessment->assessmentType->id, $assessment->id]) }}" method="POST" id="quizForm">
    @csrf
    
    <div class="mb-6">
      <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quiz Questions</h3>
      <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Add questions to make this assessment into a quiz. Students will be able to take the quiz online and scores will be automatically recorded.</p>
    </div>

    <div id="questions-container">
      @if($hasQuiz && $questions->count() > 0)
        @foreach($questions as $index => $question)
          <div class="question-item border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4">
            <div class="flex items-center justify-between mb-4">
              <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">Question {{ $index + 1 }}</h4>
              <button type="button" onclick="removeQuestion(this)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
              </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Question Type</label>
                <select name="questions[{{ $index }}][type]" class="question-type w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" onchange="toggleOptions(this)">
                  <option value="multiple_choice" {{ $question->type === 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                  <option value="identification" {{ $question->type === 'identification' ? 'selected' : '' }}>Identification</option>
                  <option value="true_false" {{ $question->type === 'true_false' ? 'selected' : '' }}>True/False</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Points</label>
                <input type="number" name="questions[{{ $index }}][points]" value="{{ $question->points }}" min="0.01" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" required>
              </div>
            </div>
            
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Question Text</label>
              <textarea name="questions[{{ $index }}][question_text]" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" required>{{ $question->question_text }}</textarea>
            </div>
            
            <div class="options-container" style="display: {{ $question->type === 'multiple_choice' ? 'block' : 'none' }};">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Options</label>
              <div class="options-list">
                @if($question->options)
                  @foreach($question->options as $optionIndex => $option)
                    <div class="flex items-center gap-2 mb-2">
                      <input type="text" name="questions[{{ $index }}][options][]" value="{{ $option }}" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Option {{ $optionIndex + 1 }}" required>
                      <button type="button" onclick="removeOption(this)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                        <i data-lucide="x" class="w-4 h-4"></i>
                      </button>
                    </div>
                  @endforeach
                @endif
              </div>
              <button type="button" onclick="addOption(this)" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
                <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i>
                Add Option
              </button>
            </div>
            
            <div class="mt-4">
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Correct Answer</label>
              <input type="text" name="questions[{{ $index }}][correct_answer]" value="{{ $question->correct_answer }}" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" required>
            </div>
          </div>
        @endforeach
      @endif
    </div>

    <div class="flex gap-4 mb-6">
      <button type="button" onclick="addQuestion()" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
        <i data-lucide="plus" class="w-4 h-4"></i>
        Add Question
      </button>
    </div>

    <div class="flex gap-4">
      <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
        <i data-lucide="save" class="w-4 h-4"></i>
        {{ $hasQuiz ? 'Update Quiz' : 'Create Quiz' }}
      </button>
      <a href="{{ route('assessments.index', [$classSection->subject->id, $classSection->id, $term, $assessment->assessmentType->id]) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg shadow transition-transform transform hover:scale-105 focus:outline-none">
        Cancel
      </a>
    </div>
  </form>
</div>

<script>
let questionIndex = {{ $hasQuiz ? $questions->count() : 0 }};

function addQuestion() {
  const container = document.getElementById('questions-container');
  const questionDiv = document.createElement('div');
  questionDiv.className = 'question-item border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4';
  
  questionDiv.innerHTML = `
    <div class="flex items-center justify-between mb-4">
      <h4 class="text-md font-medium text-gray-900 dark:text-gray-100">Question ${questionIndex + 1}</h4>
      <button type="button" onclick="removeQuestion(this)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
        <i data-lucide="trash-2" class="w-4 h-4"></i>
      </button>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Question Type</label>
        <select name="questions[${questionIndex}][type]" class="question-type w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" onchange="toggleOptions(this)">
          <option value="multiple_choice">Multiple Choice</option>
          <option value="identification">Identification</option>
          <option value="true_false">True/False</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Points</label>
        <input type="number" name="questions[${questionIndex}][points]" value="1.00" min="0.01" step="0.01" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" required>
      </div>
    </div>
    
    <div class="mb-4">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Question Text</label>
      <textarea name="questions[${questionIndex}][question_text]" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" required></textarea>
    </div>
    
    <div class="options-container" style="display: block;">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Options</label>
      <div class="options-list">
        <div class="flex items-center gap-2 mb-2">
          <input type="text" name="questions[${questionIndex}][options][]" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Option 1" required>
          <button type="button" onclick="removeOption(this)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
            <i data-lucide="x" class="w-4 h-4"></i>
          </button>
        </div>
        <div class="flex items-center gap-2 mb-2">
          <input type="text" name="questions[${questionIndex}][options][]" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Option 2" required>
          <button type="button" onclick="removeOption(this)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
            <i data-lucide="x" class="w-4 h-4"></i>
          </button>
        </div>
      </div>
      <button type="button" onclick="addOption(this)" class="text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
        <i data-lucide="plus" class="w-4 h-4 inline mr-1"></i>
        Add Option
      </button>
    </div>
    
    <div class="mt-4">
      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Correct Answer</label>
      <input type="text" name="questions[${questionIndex}][correct_answer]" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" required>
    </div>
  `;
  
  container.appendChild(questionDiv);
  questionIndex++;
}

function removeQuestion(button) {
  button.closest('.question-item').remove();
  updateQuestionNumbers();
}

function updateQuestionNumbers() {
  const questions = document.querySelectorAll('.question-item');
  questions.forEach((question, index) => {
    const title = question.querySelector('h4');
    title.textContent = `Question ${index + 1}`;
  });
}

function toggleOptions(select) {
  const questionItem = select.closest('.question-item');
  const optionsContainer = questionItem.querySelector('.options-container');
  
  if (select.value === 'multiple_choice') {
    optionsContainer.style.display = 'block';
  } else {
    optionsContainer.style.display = 'none';
  }
}

function addOption(button) {
  const optionsList = button.previousElementSibling;
  const optionDiv = document.createElement('div');
  optionDiv.className = 'flex items-center gap-2 mb-2';
  
  const optionCount = optionsList.children.length + 1;
  optionDiv.innerHTML = `
    <input type="text" name="questions[${questionIndex - 1}][options][]" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white" placeholder="Option ${optionCount}" required>
    <button type="button" onclick="removeOption(this)" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">
      <i data-lucide="x" class="w-4 h-4"></i>
    </button>
  `;
  
  optionsList.appendChild(optionDiv);
}

function removeOption(button) {
  const optionsList = button.closest('.options-list');
  if (optionsList.children.length > 1) {
    button.closest('.flex').remove();
  }
}
</script>
@endsection
