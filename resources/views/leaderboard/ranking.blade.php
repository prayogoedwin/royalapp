<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('leaderboard.index', $periodQuery) }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Leaderboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ $title }}</span>
    </div>

    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $title }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">{{ $description }}</p>
        <p class="mt-2 text-sm font-medium text-blue-700 dark:text-blue-300">
            {{ __('Periode aktif:') }} <span class="font-semibold">{{ $periodLabel }}</span>
            <span class="text-gray-500 dark:text-gray-400 font-normal"> — {{ $rows->count() }} driver</span>
        </p>
    </div>

    @include('leaderboard.partials.period-form', ['action' => route('leaderboard.ranking', $metric)])

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
        @include('leaderboard.partials.ranking-table', [
            'rows' => $rows,
            'valueKey' => $valueKey,
            'valueLabel' => $valueLabel,
            'formatKm' => $formatKm,
        ])
    </div>
</x-layouts.app>
