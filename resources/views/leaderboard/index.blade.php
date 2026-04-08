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

    @php
        $bulanLabels = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    @endphp

    <form
        method="GET"
        action="{{ route('leaderboard.index') }}"
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

    @php
        $tableClass = 'min-w-full text-sm';
        $thClass = 'py-2 px-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700';
        $tdClass = 'py-2 px-3 text-gray-800 dark:text-gray-100 border-b border-gray-100 dark:border-gray-700/50';
    @endphp

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        {{-- Order Done --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">①</span> {{ __('Order Done terbanyak') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah order berstatus Done sebagai kru.') }}</p>
            <div class="overflow-x-auto">
                <table class="{{ $tableClass }}">
                    <thead>
                        <tr>
                            <th class="{{ $thClass }}">#</th>
                            <th class="{{ $thClass }}">{{ __('User') }}</th>
                            <th class="{{ $thClass }} text-right">{{ __('Jumlah') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ordersDone as $i => $row)
                            <tr>
                                <td class="{{ $tdClass }}">{{ $i + 1 }}</td>
                                <td class="{{ $tdClass }}">
                                    <div class="font-medium">{{ $row->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->email }}</div>
                                </td>
                                <td class="{{ $tdClass }} text-right font-semibold">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="{{ $tdClass }} text-gray-500">{{ __('Belum ada data.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- KM Done --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">②</span> {{ __('Total KM (order Done)') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah KM selisih (order report) pada order Done sebagai kru.') }}</p>
            <div class="overflow-x-auto">
                <table class="{{ $tableClass }}">
                    <thead>
                        <tr>
                            <th class="{{ $thClass }}">#</th>
                            <th class="{{ $thClass }}">{{ __('User') }}</th>
                            <th class="{{ $thClass }} text-right">{{ __('Total KM') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kmDone as $i => $row)
                            <tr>
                                <td class="{{ $tdClass }}">{{ $i + 1 }}</td>
                                <td class="{{ $tdClass }}">
                                    <div class="font-medium">{{ $row->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->email }}</div>
                                </td>
                                <td class="{{ $tdClass }} text-right font-semibold">{{ number_format((float) $row->total_km, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="{{ $tdClass }} text-gray-500">{{ __('Belum ada data.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Task Done --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">③</span> {{ __('Task Done terbanyak') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah task berstatus Done sebagai kru.') }}</p>
            <div class="overflow-x-auto">
                <table class="{{ $tableClass }}">
                    <thead>
                        <tr>
                            <th class="{{ $thClass }}">#</th>
                            <th class="{{ $thClass }}">{{ __('User') }}</th>
                            <th class="{{ $thClass }} text-right">{{ __('Jumlah') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tasksDone as $i => $row)
                            <tr>
                                <td class="{{ $tdClass }}">{{ $i + 1 }}</td>
                                <td class="{{ $tdClass }}">
                                    <div class="font-medium">{{ $row->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->email }}</div>
                                </td>
                                <td class="{{ $tdClass }} text-right font-semibold">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="{{ $tdClass }} text-gray-500">{{ __('Belum ada data.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Absensi Hadir --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">④</span> {{ __('Kehadiran (status Hadir)') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah hari absensi dengan status Hadir.') }}</p>
            <div class="overflow-x-auto">
                <table class="{{ $tableClass }}">
                    <thead>
                        <tr>
                            <th class="{{ $thClass }}">#</th>
                            <th class="{{ $thClass }}">{{ __('User') }}</th>
                            <th class="{{ $thClass }} text-right">{{ __('Hadir') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($absensiHadir as $i => $row)
                            <tr>
                                <td class="{{ $tdClass }}">{{ $i + 1 }}</td>
                                <td class="{{ $tdClass }}">
                                    <div class="font-medium">{{ $row->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->email }}</div>
                                </td>
                                <td class="{{ $tdClass }} text-right font-semibold">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="{{ $tdClass }} text-gray-500">{{ __('Belum ada data.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Task comments --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">⑤</span> {{ __('Komentar task terbanyak') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah komentar task yang dibuat user.') }}</p>
            <div class="overflow-x-auto">
                <table class="{{ $tableClass }}">
                    <thead>
                        <tr>
                            <th class="{{ $thClass }}">#</th>
                            <th class="{{ $thClass }}">{{ __('User') }}</th>
                            <th class="{{ $thClass }} text-right">{{ __('Jumlah') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taskComments as $i => $row)
                            <tr>
                                <td class="{{ $tdClass }}">{{ $i + 1 }}</td>
                                <td class="{{ $tdClass }}">
                                    <div class="font-medium">{{ $row->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->email }}</div>
                                </td>
                                <td class="{{ $tdClass }} text-right font-semibold">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="{{ $tdClass }} text-gray-500">{{ __('Belum ada data.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Task attachments --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-3 flex items-center gap-2">
                <span class="text-amber-500">⑥</span> {{ __('Lampiran task terbanyak') }}
            </h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('Jumlah lampiran task yang diunggah user.') }}</p>
            <div class="overflow-x-auto">
                <table class="{{ $tableClass }}">
                    <thead>
                        <tr>
                            <th class="{{ $thClass }}">#</th>
                            <th class="{{ $thClass }}">{{ __('User') }}</th>
                            <th class="{{ $thClass }} text-right">{{ __('Jumlah') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($taskAttachments as $i => $row)
                            <tr>
                                <td class="{{ $tdClass }}">{{ $i + 1 }}</td>
                                <td class="{{ $tdClass }}">
                                    <div class="font-medium">{{ $row->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $row->email }}</div>
                                </td>
                                <td class="{{ $tdClass }} text-right font-semibold">{{ number_format($row->total) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="{{ $tdClass }} text-gray-500">{{ __('Belum ada data.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
