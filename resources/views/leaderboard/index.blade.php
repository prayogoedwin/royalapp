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
            {{ __('Driver ongoing saat ini, plus ranking Order Done dan Total KM untuk semua driver. Statistik lain menampilkan top 5.') }}
        </p>
        <p class="mt-2 text-sm font-medium text-blue-700 dark:text-blue-300">
            {{ __('Periode aktif:') }} <span class="font-semibold">{{ $periodLabel }}</span>
        </p>
    </div>

    @include('leaderboard.partials.period-form', ['action' => route('leaderboard.index')])

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Ongoing drivers --}}
        <div class="xl:col-span-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-2">
                <span class="text-amber-500">①</span> {{ __('Driver ongoing saat ini') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Semua driver yang sedang punya order berstatus Ongoing. Tidak mengikuti filter tanggal.') }}</p>
            @include('leaderboard.partials.ranking-table', [
                'rows' => $ongoingDrivers,
                'valueKey' => 'total',
                'valueLabel' => __('Order ongoing'),
                'formatKm' => false,
                'extraKey' => 'order_numbers',
                'extraLabel' => __('Nomor order'),
                'scroll' => true,
            ])
        </div>

        {{-- Order Done --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-2">
                <span class="text-amber-500">②</span> {{ __('Order Done terbanyak') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Semua driver, jumlah order berstatus Done pada periode aktif.') }}</p>
            @include('leaderboard.partials.ranking-table', [
                'rows' => $ordersDone,
                'valueKey' => 'total',
                'valueLabel' => __('Jumlah'),
                'formatKm' => false,
                'scroll' => true,
            ])
        </div>

        {{-- KM Done --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-1 flex items-center gap-2">
                <span class="text-amber-500">③</span> {{ __('Total KM (order Done)') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Semua driver, total KM pada order Done di periode aktif.') }}</p>
            @include('leaderboard.partials.ranking-table', [
                'rows' => $kmDone,
                'valueKey' => 'total_km',
                'valueLabel' => __('Total KM'),
                'formatKm' => true,
                'scroll' => true,
            ])
        </div>

        {{-- Task Done --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">④</span> {{ __('Task Done terbanyak') }}
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
                <span class="text-amber-500">⑤</span> {{ __('Kehadiran (status Hadir)') }}
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
                <span class="text-amber-500">⑥</span> {{ __('Komentar task terbanyak') }}
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
                <span class="text-amber-500">⑦</span> {{ __('Lampiran task terbanyak') }}
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
