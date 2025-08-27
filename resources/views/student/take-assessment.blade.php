<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $assessment->name }} - Quiz</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $assessment->name }}</h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Question <span id="current-question">1</span> of {{ $questions->count() }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Student</p>
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $token->student->first_name }} {{ $token->student->last_name }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-4">
                    <div class="flex items-center justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                        <span>Progress</span>
                        <span id="progress-text">1 / {{ $questions->count() }}</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div id="progress-bar" class="bg-red-600 h-2 rounded-full transition-all duration-300" style="width: {{ 100 / $questions->count() }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quiz Content -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <form id="quiz-form" action="{{ route('student.assessment.submit', $assessment->unique_url) }}" method="POST">
                @csrf
                
                @foreach($questions as $index => $question)
                    <div class="question-container {{ $index === 0 ? '' : 'hidden' }}" data-question="{{ $index + 1 }}">
                        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-6">
                            <div class="mb-6">
                                <div class="flex items-center justify-between mb-4">
                                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Question {{ $index + 1 }}</h2>
                                    <span class="text-sm text-gray-600 dark:text-gray-400">{{ $question->points }} points</span>
                                </div>
                                <p class="text-gray-700 dark:text-gray-300 text-lg">{{ $question->question_text }}</p>
                            </div>

                            <div class="space-y-4">
                                @if($question->isMultipleChoice())
                                    @foreach($question->options as $optionIndex => $option)
                                        <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                            <input type="radio" 
                                                   name="answers[{{ $question->id }}]" 
                                                   value="{{ $option }}" 
                                                   class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                                   required>
                                            <span class="ml-3 text-gray-700 dark:text-gray-300">{{ $option }}</span>
                                        </label>
                                    @endforeach
                                @elseif($question->isTrueFalse())
                                    <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                        <input type="radio" 
                                               name="answers[{{ $question->id }}]" 
                                               value="true" 
                                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                               required>
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">True</span>
                                    </label>
                                    <label class="flex items-center p-4 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer transition-colors">
                                        <input type="radio" 
                                               name="answers[{{ $question->id }}]" 
                                               value="false" 
                                               class="h-4 w-4 text-red-600 focus:ring-red-500 border-gray-300 dark:border-gray-600 dark:bg-gray-700"
                                               required>
                                        <span class="ml-3 text-gray-700 dark:text-gray-300">False</span>
                                    </label>
                                @else
                                    <div>
                                        <label for="answer-{{ $question->id }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Your Answer
                                        </label>
                                        <input type="text" 
                                               id="answer-{{ $question->id }}"
                                               name="answers[{{ $question->id }}]" 
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                               placeholder="Enter your answer"
                                               required>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                <!-- Navigation Buttons -->
                <div class="flex items-center justify-between">
                    <button type="button" 
                            id="prev-btn" 
                            onclick="previousQuestion()" 
                            class="hidden px-6 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4 inline mr-2"></i>
                        Previous
                    </button>
                    
                    <div class="flex gap-2">
                        <button type="button" 
                                id="next-btn" 
                                onclick="nextQuestion()" 
                                class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors">
                            Next
                            <i data-lucide="arrow-right" class="w-4 h-4 inline ml-2"></i>
                        </button>
                        
                        <button type="submit" 
                                id="submit-btn" 
                                class="hidden px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors">
                            Submit Quiz
                            <i data-lucide="check" class="w-4 h-4 inline ml-2"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentQuestion = 1;
        const totalQuestions = {{ $questions->count() }};
        const questionContainers = document.querySelectorAll('.question-container');

        function showQuestion(questionNumber) {
            questionContainers.forEach((container, index) => {
                if (index === questionNumber - 1) {
                    container.classList.remove('hidden');
                } else {
                    container.classList.add('hidden');
                }
            });

            // Update progress
            document.getElementById('current-question').textContent = questionNumber;
            document.getElementById('progress-text').textContent = `${questionNumber} / ${totalQuestions}`;
            document.getElementById('progress-bar').style.width = `${(questionNumber / totalQuestions) * 100}%`;

            // Update navigation buttons
            const prevBtn = document.getElementById('prev-btn');
            const nextBtn = document.getElementById('next-btn');
            const submitBtn = document.getElementById('submit-btn');

            prevBtn.classList.toggle('hidden', questionNumber === 1);
            nextBtn.classList.toggle('hidden', questionNumber === totalQuestions);
            submitBtn.classList.toggle('hidden', questionNumber !== totalQuestions);
        }

        function nextQuestion() {
            if (currentQuestion < totalQuestions) {
                currentQuestion++;
                showQuestion(currentQuestion);
            }
        }

        function previousQuestion() {
            if (currentQuestion > 1) {
                currentQuestion--;
                showQuestion(currentQuestion);
            }
        }

        // Form validation
        document.getElementById('quiz-form').addEventListener('submit', function(e) {
            const unansweredQuestions = [];
            
            questionContainers.forEach((container, index) => {
                const questionId = container.querySelector('input, textarea').name.match(/\[(\d+)\]/)[1];
                const answer = document.querySelector(`input[name="answers[${questionId}]"]:checked, input[name="answers[${questionId}]"][type="text"]`);
                
                if (!answer || (answer.type === 'text' && !answer.value.trim())) {
                    unansweredQuestions.push(index + 1);
                }
            });

            if (unansweredQuestions.length > 0) {
                e.preventDefault();
                alert(`Please answer all questions before submitting. Unanswered questions: ${unansweredQuestions.join(', ')}`);
                return false;
            }

            if (!confirm('Are you sure you want to submit your quiz? You cannot change your answers after submission.')) {
                e.preventDefault();
                return false;
            }
        });

        // Initialize
        showQuestion(1);
    </script>
</body>
</html>
