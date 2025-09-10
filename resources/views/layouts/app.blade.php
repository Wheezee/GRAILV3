<!DOCTYPE html>
<html lang="en" class="">
<head>
    <meta charset="UTF-8">
    <title>SmartGrade+</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
      // Lucide early shim to avoid ReferenceErrors before Vite initializes
      window.applyLucideIcons = window.applyLucideIcons || function(){};
      window.lucide = window.lucide || { createIcons: function(){ if (window.applyLucideIcons) window.applyLucideIcons(); } };
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-900 font-sans text-gray-900 dark:text-gray-100 relative">
    <!-- Sidebar -->
    <div id="sidebar" class="fixed top-0 left-0 h-full w-64 bg-white dark:bg-gray-800 border-r shadow-lg z-40 transform -translate-x-full transition-transform duration-300 ease-in-out">
      <!-- Close Button -->
      <div class="flex justify-between items-center px-4 py-3 border-b dark:border-gray-700">
        <h2 class="text-lg font-bold text-red-700 dark:text-evsu">Menu</h2>
        <button id="closeSidebar" class="text-gray-600 dark:text-gray-200 hover:text-red-600 dark:hover:text-evsu">
          <i data-lucide="x" class="w-6 h-6"></i>
        </button>
      </div>
      <!-- Navigation -->
      <nav class="mt-4 px-4">
        <a href="{{ route('dashboard') }}" class="flex items-center space-x-3 py-3 px-4 rounded-md text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
          <i data-lucide="layout-dashboard" class="w-5 h-5 text-red-600 dark:text-evsu"></i>
          <span class="font-medium">Dashboard</span>
        </a>
        <a href="{{ route('subjects.index') }}" class="flex items-center space-x-3 py-3 px-4 rounded-md text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
          <i data-lucide="book" class="w-5 h-5 text-red-600 dark:text-evsu"></i>
          <span class="font-medium">Subjects</span>
        </a>
        <a href="{{ route('students.index') }}" class="flex items-center space-x-3 py-3 px-4 rounded-md text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
          <i data-lucide="users" class="w-5 h-5 text-red-600 dark:text-evsu"></i>
          <span class="font-medium">Students</span>
        </a>
        <button onclick="confirmLogout()" class="flex items-center space-x-3 py-3 px-4 rounded-md text-gray-700 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 transition w-full text-left">
            <i data-lucide="log-out" class="w-5 h-5 text-red-600 dark:text-evsu"></i>
            <span class="font-medium">Logout</span>
        </button>
        <form id="logoutForm" method="POST" action="{{ url('/logout') }}" class="hidden">
            @csrf
        </form>
      </nav>
    </div>
    <!-- Top Bar -->
    <header class="flex items-center justify-between bg-white dark:bg-gray-800 px-6 py-4 shadow-md sticky top-0 z-30">
      <div class="flex items-center gap-3">
        <button id="toggleSidebar" class="text-gray-700 dark:text-gray-100 hover:text-red-600 dark:hover:text-evsu focus:outline-none">
          <i data-lucide="menu" class="w-6 h-6"></i>
        </button>
        <h1 class="text-xl font-bold tracking-wide text-red-700 dark:text-evsu">SmartGrade+</h1>
      </div>
      <div class="flex items-center gap-3">
        <!-- AY/Sem selector -->
        <form id="aySemForm" method="POST" action="{{ url('/set-ays') }}" class="hidden sm:flex items-center gap-2">
          @csrf
          <select name="academic_year" id="aySelect" class="px-2 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-700 dark:text-gray-100">
            @php
              $currentAy = session('academic_year');
              $start = (int)date('Y') - 1;
              $years = [];
              for($i=$start; $i<=$start+4; $i++){ $years[] = $i.'-'.($i+1); }
            @endphp
            @foreach($years as $ay)
              <option value="{{ $ay }}" {{ $currentAy===$ay ? 'selected' : '' }}>{{ $ay }}</option>
            @endforeach
          </select>
          <select name="semester" id="semSelect" class="px-2 py-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded text-sm text-gray-700 dark:text-gray-100">
            @php $currentSem = session('semester'); @endphp
            <option value="1" {{ $currentSem==='1' ? 'selected' : '' }}>1st</option>
            <option value="2" {{ $currentSem==='2' ? 'selected' : '' }}>2nd</option>
            <option value="S" {{ $currentSem==='S' ? 'selected' : '' }}>Summer</option>
          </select>
        </form>
        <button onclick="document.getElementById('aySemForm').submit()" title="Apply AY/Sem" class="text-gray-700 dark:text-gray-100 hover:text-red-600 dark:hover:text-evsu focus:outline-none hidden sm:block">
          <i data-lucide="refresh-ccw" class="w-5 h-5"></i>
        </button>
        <button id="darkModeToggle" class="ml-2 text-gray-700 dark:text-gray-100 hover:text-red-600 dark:hover:text-evsu focus:outline-none" aria-label="Toggle dark mode" onclick="toggleDarkMode()">
          <i id="darkModeIcon" data-lucide="sun" class="w-6 h-6"></i>
        </button>
      </div>
    </header>
    <!-- Main Content -->
    <main class="p-6">
      @yield('content')
    </main>
    <!-- Scripts -->
    <script>
      const sidebar = document.getElementById('sidebar');
      const toggleBtn = document.getElementById('toggleSidebar');
      const closeBtn = document.getElementById('closeSidebar');
      const html = document.documentElement;
      
      // Sidebar toggle
      toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
      });
      closeBtn.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
      });
      
      // Simple dark mode toggle function
      function toggleDarkMode() {
        const darkModeIcon = document.getElementById('darkModeIcon');
        const isDark = html.classList.contains('dark');
        
        if (isDark) {
          // Switch to light mode
          html.classList.remove('dark');
          darkModeIcon.setAttribute('data-lucide', 'moon');
          localStorage.setItem('grail-darkmode', '0');
        } else {
          // Switch to dark mode
          html.classList.add('dark');
          darkModeIcon.setAttribute('data-lucide', 'sun');
          localStorage.setItem('grail-darkmode', '1');
        }
        
        if (window.applyLucideIcons) {
          window.applyLucideIcons();
        }
      }
      
      // Initial dark mode state
      const darkPref = localStorage.getItem('grail-darkmode');
      if (darkPref === '1' || (darkPref === null && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        html.classList.add('dark');
        document.getElementById('darkModeIcon').setAttribute('data-lucide', 'sun');
      } else {
        html.classList.remove('dark');
        document.getElementById('darkModeIcon').setAttribute('data-lucide', 'moon');
      }
      
      // Auto-submit AY/Sem on change so filters apply immediately
      document.addEventListener('DOMContentLoaded', function(){
        const ay = document.getElementById('aySelect');
        const sem = document.getElementById('semSelect');
        const form = document.getElementById('aySemForm');
        if(ay && sem && form){
          const submit = () => { try { form.submit(); } catch(e) {} };
          ay.addEventListener('change', submit);
          sem.addEventListener('change', submit);
        }
      });
      if (window.applyLucideIcons) {
        window.applyLucideIcons();
      }

      function confirmLogout() {
        if (confirm('Are you sure you want to log out?')) {
          document.getElementById('logoutForm').submit();
        }
      }
    </script>
</body>
</html> 