<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - {{ $assessment->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .score-circle {
            background: conic-gradient(#10b981 0deg, #10b981 {{ $score->percentage_score * 3.6 }}deg, #e5e7eb {{ $score->percentage_score * 3.6 }}deg, #e5e7eb 360deg);
        }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                Quiz Completed! 🎉
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                {{ $assessment->name }}
            </p>
        </div>

        @if (session('success'))
            <div class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-600 dark:text-green-400"></i>
                    <p class="text-green-800 dark:text-green-200 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- Score Display -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mb-8">
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <!-- Score Circle -->
                <div class="flex-shrink-0">
                    <div class="relative w-48 h-48">
                        <div class="w-48 h-48 rounded-full score-circle flex items-center justify-center">
                            <div class="w-40 h-40 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center">
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-gray-900 dark:text-gray-100">
                                        {{ number_format($score->percentage_score, 1) }}%
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        Score
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Score Details -->
                <div class="flex-1 text-center lg:text-left">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
                        Your Results
                    </h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Raw Score</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ $score->score }} / {{ $assessment->max_score }}
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Percentage</div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {{ number_format($score->percentage_score, 1) }}%
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Submission Time</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                                {{ $score->submitted_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                            <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Status</div>
                            <div class="text-lg font-semibold">
                                @if($score->percentage_score >= 75)
                                    <span class="text-green-600 dark:text-green-400">Passed</span>
                                @elseif($score->percentage_score >= 60)
                                    <span class="text-yellow-600 dark:text-yellow-400">Warning</span>
                                @else
                                    <span class="text-red-600 dark:text-red-400">Failed</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($score->is_late)
                        <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                            <div class="flex items-center gap-2 text-yellow-800 dark:text-yellow-200">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span class="text-sm font-medium">Late Submission</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Assessment Info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                Assessment Details
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Assessment Name</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $assessment->name }}</div>
                </div>
                
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Questions</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $assessment->questions->count() }}</div>
                </div>
                
                <div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Points</div>
                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $assessment->max_score }}</div>
                </div>
            </div>

            @if($assessment->description)
                <div class="mt-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Description</div>
                    <div class="text-gray-900 dark:text-gray-100">{{ $assessment->description }}</div>
                </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="text-center">
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <button onclick="window.print()" class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    Print Result
                </button>
                
                <button onclick="copyToClipboard()" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                    <i data-lucide="copy" class="w-4 h-4"></i>
                    Copy Score
                </button>
                
                <a href="{{ route('student.assessment.access', $assessment->unique_url) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors">
                    <i data-lucide="home" class="w-4 h-4"></i>
                    Back to Quiz
                </a>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="text-center mt-8 text-sm text-gray-500 dark:text-gray-400">
            <p>Your score has been automatically recorded in the system.</p>
            <p class="mt-1">You can close this page or take a screenshot for your records.</p>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            const scoreText = `Quiz: ${@json($assessment->name)}\nScore: ${@json($score->score)}/${@json($assessment->max_score)} (${@json(number_format($score->percentage_score, 1))}%)\nCompleted: ${@json($score->submitted_at->format('M d, Y H:i'))}`;
            
            navigator.clipboard.writeText(scoreText).then(function() {
                // Show success message
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

        // Initialize Lucide icons
        lucide.createIcons();
    </script>
</body>
</html>
