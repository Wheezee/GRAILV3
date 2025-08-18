<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Access - {{ $assessment->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <div class="mx-auto h-12 w-12 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center">
                    <i data-lucide="help-circle" class="h-6 w-6 text-red-600 dark:text-red-400"></i>
                </div>
                <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-gray-100">
                    Quiz Access
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    Enter your token to access the quiz
                </p>
            </div>

            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-center gap-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 text-red-600 dark:text-red-400"></i>
                        <p class="text-red-800 dark:text-red-200 font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $assessment->name }}</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        {{ $assessment->questions->count() }} questions • {{ $assessment->questions->sum('points') }} total points
                    </p>
                    
                    @if($assessment->isExpired())
                        <div class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                            <div class="flex items-center gap-2 text-red-800 dark:text-red-200">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span class="text-sm font-medium">This quiz has expired and is no longer available.</span>
                            </div>
                        </div>
                    @else
                        <div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <div class="flex items-center gap-2 text-blue-800 dark:text-blue-200">
                                <i data-lucide="clock" class="w-4 h-4"></i>
                                <span class="text-sm font-medium">{{ $assessment->getTimeUntilExpiration() }}</span>
                            </div>
                        </div>
                    @endif
                </div>

                <form action="{{ route('student.assessment.validate-token', $assessment->unique_url) }}" method="POST" @if($assessment->isExpired()) onsubmit="return false;" @endif>
                    @csrf
                    <div>
                        <label for="token" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Access Token
                        </label>
                        <input type="text" 
                               id="token" 
                               name="token" 
                               required 
                               maxlength="8"
                               @if($assessment->isExpired()) disabled @endif
                               class="appearance-none relative block w-full px-3 py-3 border border-gray-300 dark:border-gray-600 placeholder-gray-500 dark:placeholder-gray-400 text-gray-900 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 focus:z-10 sm:text-sm dark:bg-gray-700 @if($assessment->isExpired()) opacity-50 cursor-not-allowed @endif"
                               placeholder="Enter your 8-character token"
                               autocomplete="off">
                    </div>

                    <div class="mt-6">
                        <button type="submit" 
                                @if($assessment->isExpired()) disabled @endif
                                class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-lg text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors @if($assessment->isExpired()) opacity-50 cursor-not-allowed @endif">
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <i data-lucide="arrow-right" class="h-5 w-5 text-red-500 group-hover:text-red-400"></i>
                            </span>
                            @if($assessment->isExpired())
                                Quiz Expired
                            @else
                                Start Quiz
                            @endif
                        </button>
                    </div>
                </form>

                <div class="mt-6 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Contact your teacher if you don't have a token
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script>
    </script>
</body>
</html>
