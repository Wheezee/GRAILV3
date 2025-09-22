@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto">
  <h1 class="text-2xl font-bold mb-4">Settings</h1>
  @if (session('success'))
    <div class="mb-4 px-4 py-2 bg-green-50 border border-green-200 rounded text-green-700">{{ session('success') }}</div>
  @endif

  <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg font-semibold">Academic Years</h2>
      <form method="POST" action="{{ route('settings.ay.add') }}" class="flex gap-2">
      @csrf
        <input type="text" name="year" placeholder="YYYY-YYYY" class="px-3 py-2 border rounded w-40 dark:bg-gray-700 dark:border-gray-600" required>
        <button class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded">Add</button>
      </form>
    </div>

    @error('year')
      <div class="text-sm text-red-500 mb-2">{{ $message }}</div>
    @enderror

    <div class="overflow-x-auto">
      <table class="min-w-full border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
        <thead class="bg-gray-50 dark:bg-gray-700/50 text-left text-sm text-gray-600 dark:text-gray-300">
          <tr>
            <th class="px-4 py-3 border-b dark:border-gray-700">#</th>
            <th class="px-4 py-3 border-b dark:border-gray-700">Academic Year</th>
            <th class="px-4 py-3 border-b dark:border-gray-700 text-right">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
          @forelse($years as $idx => $y)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30">
              <td class="px-4 py-3 text-sm text-gray-500">{{ $idx + 1 }}</td>
              <td class="px-4 py-3 font-medium">{{ $y }}</td>
              <td class="px-4 py-3 text-right">
                <form method="POST" action="{{ route('settings.ay.delete') }}" class="inline" onsubmit="return confirm('Remove {{ $y }}?')">
                  @csrf
                  @method('DELETE')
                  <input type="hidden" name="year" value="{{ $y }}">
                  <button class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200 dark:hover:bg-gray-600">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                    Remove
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="px-4 py-6 text-sm text-gray-500 text-center">No academic years added yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection


