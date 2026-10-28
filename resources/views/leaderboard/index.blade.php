<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Leaderboard') }}</span>
    </div>

    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Leaderboard') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1 text-sm">
            {{ __('Ringkasan top 5 user per kategori. Statistik order & task berdasarkan kru (karyawan yang punya akun user).') }}
        </p>
        <p class="mt-2 text-sm font-medium text-blue-700 dark:text-blue-300">
            {{ __('Periode aktif:') }} <span class="font-semibold">{{ $periodLabel }}</span>
        </p>
    </div>

    @include('leaderboard.partials.period-form', ['action' => route('leaderboard.index')])

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Order Done --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <div class="mb-3 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <a href="{{ route('leaderboard.ranking', array_merge($periodQuery, ['metric' => 'orders-done'])) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                            <span class="text-amber-500">①</span> {{ __('Order Done terbanyak') }}
                        </a>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Jumlah order berstatus Done sebagai driver. Klik untuk melihat semua driver.') }}</p>
                </div>
                <a href="{{ route('leaderboard.ranking', array_merge($periodQuery, ['metric' => 'orders-done'])) }}"
                    class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap">
                    {{ __('Lihat semua driver') }}
                </a>
            </div>
            @include('leaderboard.partials.ranking-table', [
                'rows' => $ordersDone,
                'valueKey' => 'total',
                'valueLabel' => __('Jumlah'),
                'formatKm' => false,
            ])
        </div>

        {{-- KM Done --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <div class="mb-3 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-2">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 flex items-center gap-2">
                        <a href="{{ route('leaderboard.ranking', array_merge($periodQuery, ['metric' => 'km-done'])) }}" class="hover:text-blue-600 dark:hover:text-blue-400">
                            <span class="text-amber-500">②</span> {{ __('Total KM (order Done)') }}
                        </a>
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('Jumlah KM selisih (order report) pada order Done sebagai driver. Klik untuk melihat semua driver.') }}</p>
                </div>
                <a href="{{ route('leaderboard.ranking', array_merge($periodQuery, ['metric' => 'km-done'])) }}"
                    class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline whitespace-nowrap">
                    {{ __('Lihat semua driver') }}
                </a>
            </div>
            @include('leaderboard.partials.ranking-table', [
                'rows' => $kmDone,
                'valueKey' => 'total_km',
                'valueLabel' => __('Total KM'),
                'formatKm' => true,
            ])
        </div>

        {{-- Task Done --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">③</span> {{ __('Task Done terbanyak') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah task berstatus Done sebagai kru.') }}</p>
            @include('leaderboard.partials.ranking-table', [
                'rows' => $tasksDone,
                'valueKey' => 'total',
                'valueLabel' => __('Jumlah'),
                'formatKm' => false,
            ])
        </div>

        {{-- Absensi Hadir --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">④</span> {{ __('Kehadiran (status Hadir)') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah hari absensi dengan status Hadir.') }}</p>
            @include('leaderboard.partials.ranking-table', [
                'rows' => $absensiHadir,
                'valueKey' => 'total',
                'valueLabel' => __('Hadir'),
                'formatKm' => false,
            ])
        </div>

        {{-- Task comments --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">⑤</span> {{ __('Komentar task terbanyak') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah komentar task yang dibuat user.') }}</p>
            @include('leaderboard.partials.ranking-table', [
                'rows' => $taskComments,
                'valueKey' => 'total',
                'valueLabel' => __('Jumlah'),
                'formatKm' => false,
            ])
        </div>

        {{-- Task attachments --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">⑥</span> {{ __('Lampiran task terbanyak') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah lampiran task yang diunggah user.') }}</p>
            @include('leaderboard.partials.ranking-table', [
                'rows' => $taskAttachments,
                'valueKey' => 'total',
                'valueLabel' => __('Jumlah'),
                'formatKm' => false,
            ])
        </div>
    </div>
</x-layouts.app>
