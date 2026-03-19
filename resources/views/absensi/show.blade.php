<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('employees.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Employees') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Presensi') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-start gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">
                {{ __('Presensi') }} - {{ $employee->full_name }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ $employee->nik }} {{ $employee->pool?->pool_name ? ('• ' . $employee->pool->pool_name) : '' }}
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-3">
            <form method="GET" action="{{ $monthFormAction ?? route('employees.presensi', $employee) }}">
                <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Filter Bulan') }}</label>
                <input
                    type="month"
                    name="month"
                    value="{{ $month }}"
                    class="block w-48 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                />
                <div class="flex items-center gap-2 mt-2">
                    <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">{{ __('Tampilkan') }}</button>
                    @if(!empty($showPdf) && $showPdf)
                        <a href="{{ route('presensi.print') . '?month=' . urlencode($month) }}"
                           class="px-3 py-2 bg-gray-700 text-white rounded-md text-sm hover:bg-gray-800">
                            {{ __('Cetak PDF') }}
                        </a>
                    @else
                        <a href="{{ route('employees.presensi.print', $employee) . '?month=' . urlencode($month) }}"
                           class="px-3 py-2 bg-gray-700 text-white rounded-md text-sm hover:bg-gray-800">
                            {{ __('Cetak PDF') }}
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @php
        $statusOptions = \App\Models\Absensi::STATUS_OPTIONS;
        $statusColors = \App\Models\Absensi::STATUS_COLORS;
    @endphp

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">
                {{ \Illuminate\Support\Str::upper($month) }}
            </h2>
        </div>

        <div class="p-4">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                            <th class="py-2 pr-4 text-left">Tanggal</th>
                            <th class="py-2 pr-4 text-left">Jam Masuk</th>
                            <th class="py-2 pr-4 text-left">Jam Pulang</th>
                            <th class="py-2 pr-4 text-left">Status</th>
                            <th class="py-2 pr-4 text-left">Foto</th>
                            <th class="py-2 pr-4 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($days as $row)
                            @php
                                $date = $row['date'];
                                $absensi = $row['absensi'];
                                $status = $absensi?->status;
                                $badgeClass = $status ? ($statusColors[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300') : 'bg-gray-50 text-gray-500 dark:bg-gray-700 dark:text-gray-300';

                                $blockedStatus = $status && in_array($status, [
                                    \App\Models\Absensi::STATUS_ALPHA,
                                    \App\Models\Absensi::STATUS_IZIN,
                                    \App\Models\Absensi::STATUS_SAKIT,
                                    \App\Models\Absensi::STATUS_CUTI,
                                    \App\Models\Absensi::STATUS_LIBUR,
                                ], true);

                                $lockMasuk = ($status === \App\Models\Absensi::STATUS_TIDAK_ABSEN_MASUK) || $blockedStatus;
                                $lockPulang = ($status === \App\Models\Absensi::STATUS_TIDAK_ABSEN_PULANG) || $blockedStatus;

                                // Admin: tombol masuk/pulang harus tetap muncul (selama jam sesuai tersedia & jam target belum terisi),
                                // meskipun status sebelumnya bukan "hadir".
                                $canCreateMasuk = auth()->user()?->hasPermission('create-absensi-masuk') ?? false;
                                $canCreatePulang = auth()->user()?->hasPermission('create-absensi-pulang') ?? false;

                                $canInputMasuk = (!$absensi || $absensi->jam_masuk === null) && ! $lockMasuk && $canCreateMasuk;
                                $canInputPulang = ($absensi && $absensi->jam_masuk !== null && $absensi->jam_pulang === null) && ! $lockPulang && $canCreatePulang;

                                $dateKey = $date->format('Y-m-d');
                            @endphp
                            <tr>
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $date->format('d M Y') }}</td>
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $absensi?->jam_masuk?->format('H:i') ?? '-' }}</td>
                                <td class="py-2 pr-4 whitespace-nowrap">{{ $absensi?->jam_pulang?->format('H:i') ?? '-' }}</td>
                                <td class="py-2 pr-4">
                                    @if($status)
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                            {{ $statusOptions[$status] ?? $status }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                            -
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4">
                                    @if($absensi?->foto_masuk || $absensi?->foto_pulang)
                                        <div class="flex flex-col gap-1">
                                            @if($absensi?->foto_masuk)
                                                <a href="{{ asset('storage/'.$absensi->foto_masuk) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                                    Masuk
                                                </a>
                                            @endif
                                            @if($absensi?->foto_pulang)
                                                <a href="{{ asset('storage/'.$absensi->foto_pulang) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">
                                                    Pulang
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="py-2 pr-4 min-w-[280px]">
                                    @if($isAdmin)
                                        <div class="flex flex-col gap-2">
                                            @if(auth()->user()->hasPermission('create-absensi-masuk'))
                                                <button
                                                    type="button"
                                                    class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700"
                                                    onclick="document.getElementById('absen-masuk-{{ $dateKey }}').classList.toggle('hidden')">
                                                    {{ __('Absen Masuk') }}
                                                </button>
                                                <div id="absen-masuk-{{ $dateKey }}" class="hidden">
                                                    <form method="POST" action="{{ route('presensi.masuk') }}" enctype="multipart/form-data" class="flex flex-col gap-2 bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-md p-3 mt-2">
                                                        @csrf
                                                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                        <input type="hidden" name="tanggal" value="{{ $dateKey }}">

                                                    <div class="flex gap-2 items-center">
                                                        <label class="text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('Jam') }}</label>
                                                        <input type="time" name="jam_masuk" required
                                                            value="{{ \Carbon\Carbon::now()->format('H:i') }}"
                                                            class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                    </div>

                                                    <div class="flex gap-2 items-center">
                                                        <input type="file" name="foto_masuk" accept="image/*" class="text-xs">
                                                    </div>

                                                    <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">

                                                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">
                                                            {{ __('Simpan Masuk') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif

                                            @if(auth()->user()->hasPermission('create-absensi-pulang'))
                                                <button
                                                    type="button"
                                                    class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700"
                                                    onclick="document.getElementById('absen-pulang-{{ $dateKey }}').classList.toggle('hidden')">
                                                    {{ __('Absen Pulang') }}
                                                </button>
                                                <div id="absen-pulang-{{ $dateKey }}" class="hidden">
                                                    <form method="POST" action="{{ route('presensi.pulang') }}" enctype="multipart/form-data" class="flex flex-col gap-2 bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-md p-3 mt-2">
                                                        @csrf
                                                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                        <input type="hidden" name="tanggal" value="{{ $dateKey }}">

                                                    <div class="flex gap-2 items-center">
                                                        <label class="text-xs font-medium text-gray-600 dark:text-gray-300 whitespace-nowrap">{{ __('Jam') }}</label>
                                                        <input type="time" name="jam_pulang" required
                                                            value="{{ \Carbon\Carbon::now()->format('H:i') }}"
                                                            class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                    </div>

                                                    <div class="flex gap-2 items-center">
                                                        <input type="file" name="foto_pulang" accept="image/*" class="text-xs">
                                                    </div>

                                                    <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">

                                                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">
                                                            {{ __('Simpan Pulang') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        @if($canInputMasuk)
                                            <button
                                                type="button"
                                                class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700"
                                                onclick="document.getElementById('absen-masuk-{{ $dateKey }}').classList.toggle('hidden')">
                                                {{ __('Absen Masuk') }}
                                            </button>
                                            <div id="absen-masuk-{{ $dateKey }}" class="hidden mt-2">
                                                <form method="POST" action="{{ route('presensi.masuk') }}" enctype="multipart/form-data" class="flex flex-col gap-2 bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-md p-3">
                                                    @csrf
                                                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                    <input type="hidden" name="tanggal" value="{{ $dateKey }}">

                                                    <div class="flex gap-2 items-center">
                                                        <input type="file" name="foto_masuk" accept="image/*" class="text-xs">
                                                    </div>

                                                    <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">

                                                    <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">
                                                        {{ __('Simpan Masuk') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($canInputPulang)
                                            <button
                                                type="button"
                                                class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700"
                                                onclick="document.getElementById('absen-pulang-{{ $dateKey }}').classList.toggle('hidden')">
                                                {{ __('Absen Pulang') }}
                                            </button>
                                            <div id="absen-pulang-{{ $dateKey }}" class="hidden mt-2">
                                                <form method="POST" action="{{ route('presensi.pulang') }}" enctype="multipart/form-data" class="flex flex-col gap-2 bg-gray-50 dark:bg-gray-900/30 border border-gray-200 dark:border-gray-700 rounded-md p-3">
                                                    @csrf
                                                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                    <input type="hidden" name="tanggal" value="{{ $dateKey }}">

                                                    <div class="flex gap-2 items-center">
                                                        <input type="file" name="foto_pulang" accept="image/*" class="text-xs">
                                                    </div>

                                                    <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">

                                                    <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">
                                                        {{ __('Simpan Pulang') }}
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $absensi && $absensi->jam_pulang ? __('Selesai') : __('-') }}
                                            </span>
                                        @endif
                                    @endif

                                    @if($isAdmin)
                                        <div class="mt-3">
                                            @if($absensi)
                                                <form method="POST" action="{{ route('absensis.status.update', $absensi) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="flex gap-2 items-center flex-wrap">
                                                        <select name="status" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                            <option value="" @selected(empty($absensi->status))>-</option>
                                                            @foreach($statusOptions as $key => $label)
                                                                <option value="{{ $key }}" @selected($absensi->status === $key)>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="text" name="keterangan" placeholder="Catatan status" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                        <button type="submit" class="px-3 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-900 rounded-md text-xs hover:opacity-90">
                                                            {{ __('Update') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('absensis.status.upsert-by-date', $employee) }}">
                                                    @csrf
                                                    <input type="hidden" name="tanggal" value="{{ $date->format('Y-m-d') }}">
                                                    <div class="flex gap-2 items-center flex-wrap">
                                                        <select name="status" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                            <option value="" selected>-</option>
                                                            @foreach($statusOptions as $key => $label)
                                                                <option value="{{ $key }}">
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        <input type="text" name="keterangan" placeholder="Catatan status" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                                        <button type="submit" class="px-3 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-900 rounded-md text-xs hover:opacity-90">
                                                            {{ __('Set') }}
                                                        </button>
                                                    </div>
                                                </form>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>

