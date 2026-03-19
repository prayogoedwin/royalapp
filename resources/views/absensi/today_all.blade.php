<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Absensi - Semua Pegawai') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-start gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Absensi Hari Ini') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}
            </p>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Daftar Pegawai') }}</h2>
        </div>

        <div class="p-4 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                        <th class="py-2 pr-4 text-left">Pegawai</th>
                        <th class="py-2 pr-4 text-left">Pool</th>
                        <th class="py-2 pr-4 text-left">Jam Masuk</th>
                        <th class="py-2 pr-4 text-left">Jam Pulang</th>
                        <th class="py-2 pr-4 text-left">Status</th>
                        <th class="py-2 pr-4 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($employees as $employee)
                        @php
                            $todayAbsensi = $todayAbsensis->get($employee->id);
                            $pendingCheckout = $pendingByEmployee[$employee->id] ?? null;

                            $absensiStatus = $pendingCheckout ?? $todayAbsensi;
                            $status = $absensiStatus?->status;
                            $badgeClass = $status ? ($statusColors[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')
                                : 'bg-gray-50 text-gray-500 dark:bg-gray-700 dark:text-gray-300';

                            $dateKey = $tanggal;

                            $showMasuk = $canMasuk && (!$todayAbsensi || $todayAbsensi->jam_masuk === null) && ! $pendingCheckout;
                            $showPulang = $canPulang && (bool) $pendingCheckout;
                        @endphp
                        <tr>
                            <td class="py-2 pr-4 whitespace-nowrap">{{ $employee->full_name }}</td>
                            <td class="py-2 pr-4 whitespace-nowrap">{{ $employee->pool?->pool_name ?? '-' }}</td>
                            <td class="py-2 pr-4 whitespace-nowrap">{{ $absensiStatus?->jam_masuk?->format('H:i') ?? '-' }}</td>
                            <td class="py-2 pr-4 whitespace-nowrap">{{ $absensiStatus?->jam_pulang?->format('H:i') ?? '-' }}</td>
                            <td class="py-2 pr-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                    {{ $status ? ($statusOptions[$status] ?? $status) : '-' }}
                                </span>
                            </td>
                            <td class="py-2 pr-4 min-w-[320px]">
                                <div class="flex flex-col gap-2">
                                    <div class="bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-md p-3 flex flex-col gap-2">
                                        <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ __('Masuk') }}</div>
                                        @if($showMasuk)
                                            <form method="POST" action="{{ route('presensi.masuk') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                <input type="hidden" name="tanggal" value="{{ $dateKey }}">
                                                <input type="file" name="foto_masuk" accept="image/*" class="text-xs mb-2 block">
                                                <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 mb-2 block">
                                                <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700 w-full">
                                                    {{ __('Simpan Masuk') }}
                                                </button>
                                            </form>
                                        @else
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Sudah ada jam masuk / tidak punya akses.') }}</div>
                                        @endif
                                    </div>

                                    <div class="bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-md p-3 flex flex-col gap-2">
                                        <div class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ __('Pulang') }}</div>
                                        @if($showPulang)
                                            <form method="POST" action="{{ route('presensi.pulang') }}" enctype="multipart/form-data">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                <input type="hidden" name="tanggal" value="{{ $pendingCheckout->tanggal->format('Y-m-d') }}">
                                                <input type="file" name="foto_pulang" accept="image/*" class="text-xs mb-2 block">
                                                <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 mb-2 block">
                                                <button type="submit" class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700 w-full">
                                                    {{ __('Simpan Pulang') }}
                                                </button>
                                            </form>
                                        @else
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Belum ada pending checkout pulang / tidak punya akses.') }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>

