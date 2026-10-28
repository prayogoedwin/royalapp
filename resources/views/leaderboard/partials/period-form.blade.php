@php
    $bulanLabels = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
@endphp

<form
    method="GET"
    action="{{ $action }}"
    class="mb-8 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 space-y-4"
    x-data="{ allTime: {{ $allTime ? 'true' : 'false' }} }"
>
    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-4">
        <label class="inline-flex items-center gap-2 cursor-pointer text-sm text-gray-700 dark:text-gray-300">
            <input
                type="checkbox"
                name="all_time"
                value="1"
                class="rounded border-gray-300 dark:border-gray-600"
                x-model="allTime"
                @if($allTime) checked @endif
            >
            <span>{{ __('Sepanjang waktu (tanpa filter tanggal)') }}</span>
        </label>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md w-fit">
            {{ __('Terapkan') }}
        </button>
    </div>

    <div class="flex flex-col sm:flex-row flex-wrap items-end gap-3" x-show="!allTime" x-cloak>
        <div>
            <label for="lb_year" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Tahun') }}</label>
            <select
                id="lb_year"
                name="year"
                class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm min-w-[7rem]"
            >
                @for($y = $yearOptionsTo; $y >= $yearOptionsFrom; $y--)
                    <option value="{{ $y }}" @selected((int) $year === $y)>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label for="lb_month" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">{{ __('Bulan') }}</label>
            <select
                id="lb_month"
                name="month"
                class="px-3 py-2 rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm min-w-[11rem]"
            >
                <option value="">{{ __('Semua bulan (tahun penuh)') }}</option>
                @foreach($bulanLabels as $num => $label)
                    <option value="{{ $num }}" @selected((string) $month === (string) $num)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed">
        {{ __('Default: tahun berjalan (semua bulan). Centang “Sepanjang waktu” untuk data kumulatif tanpa batas tanggal.') }}
        <br>
        <span class="opacity-90">{{ __('Order & task: filter menurut tanggal dibuat (created_at); absensi: tanggal; komentar & lampiran task: waktu buat.') }}</span>
    </p>
</form>
