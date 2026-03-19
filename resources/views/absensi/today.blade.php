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
                {{ __('Presensi - ') . $employee->full_name }}
            </h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ $employee->nik }}
                @if($employee->pool?->pool_name)
                    {{ ' • ' . $employee->pool->pool_name }}
                @endif
            </p>
            <p class="text-gray-600 dark:text-gray-400 mt-2">
                <span class="font-medium">{{ __('Tanggal') }}:</span> {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}
            </p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            @php
                $status = $absensiStatus?->status;
                $badgeClass = $status ? ($statusColors[$status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300') : 'bg-gray-50 text-gray-500 dark:bg-gray-700 dark:text-gray-300';
            @endphp
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                    {{ $status ? ($statusOptions[$status] ?? $status) : '-' }}
                </span>
            </div>
            <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                {{ __('Jam Masuk') }}: {{ $absensiStatus?->jam_masuk?->format('H:i') ?? '-' }} |
                {{ __('Jam Pulang') }}: {{ $absensiStatus?->jam_pulang?->format('H:i') ?? '-' }}
            </div>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Absen Hari Ini') }}</h2>
        </div>

        <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">{{ __('Absen Masuk') }}</h3>
                @if($canMasuk)
                    <form method="POST" action="{{ route('presensi.masuk') }}" enctype="multipart/form-data" class="flex flex-col gap-2">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Foto (opsional)') }}</label>
                            <input type="file" name="foto_masuk" accept="image/*" class="text-xs">
                        </div>

                        <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">

                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">
                            {{ __('Simpan Masuk (jam otomatis)') }}
                        </button>
                    </form>
                @else
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Sudah ada absen masuk untuk tanggal ini.') }}</div>
                @endif
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-md p-4">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-2">{{ __('Absen Pulang') }}</h3>
                @if($canPulang && $pendingCheckout)
                    <form method="POST" action="{{ route('presensi.pulang') }}" enctype="multipart/form-data" class="flex flex-col gap-2">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <input type="hidden" name="tanggal" value="{{ $pendingCheckout->tanggal->format('Y-m-d') }}">

                        <div>
                            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Foto (opsional)') }}</label>
                            <input type="file" name="foto_pulang" accept="image/*" class="text-xs">
                        </div>

                        <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">

                        <button type="submit" class="px-3 py-2 bg-blue-600 text-white rounded-md text-xs hover:bg-blue-700">
                            {{ __('Simpan Pulang (jam otomatis)') }}
                        </button>
                    </form>
                @else
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('Belum ada absen masuk yang bisa dipulangkan (pending checkout).') }}</div>
                @endif
            </div>
        </div>

        @if($isAdmin)
            <div class="p-4 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-100 mb-3">{{ __('Update Status (Admin)') }}</h3>

                @if($absensiStatus)
                    <form method="POST" action="{{ route('absensis.status.update', $absensiStatus) }}" class="flex gap-2 items-center flex-wrap">
                        @csrf
                        @method('PUT')
                        <select name="status" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="" @selected(empty($absensiStatus->status))>-</option>
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}" @selected($absensiStatus->status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="keterangan" placeholder="Catatan status" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <button type="submit" class="px-3 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-900 rounded-md text-xs hover:opacity-90">
                            {{ __('Update') }}
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('absensis.status.upsert-by-date', $employee) }}" class="flex gap-2 items-center flex-wrap">
                        @csrf
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <select name="status" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                            <option value="" selected>-</option>
                            @foreach($statusOptions as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="keterangan" placeholder="Catatan status" class="text-xs px-2 py-1 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <button type="submit" class="px-3 py-2 bg-gray-800 dark:bg-gray-200 text-white dark:text-gray-900 rounded-md text-xs hover:opacity-90">
                            {{ __('Set') }}
                        </button>
                    </form>
                @endif
            </div>
        @endif
    </div>
</x-layouts.app>

