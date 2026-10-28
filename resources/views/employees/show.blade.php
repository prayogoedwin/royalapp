<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('employees.index') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">Karyawan</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">Lihat</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Detail Karyawan</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Detail data karyawan</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-employees') && ! $employee->user)
                <form action="{{ route('employees.create-account', $employee) }}" method="POST" onsubmit="return confirm('Buat akun login untuk karyawan ini? Password default sama dengan NIK.')">
                    @csrf
                    <x-button type="primary">{{ __('Buat Akun') }}</x-button>
                </form>
            @endif
            @if(auth()->user()->hasPermission('edit-employees'))
                <a href="{{ route('employees.edit', $employee) }}">
                    <x-button type="primary">Ubah Karyawan</x-button>
                </a>
            @endif
            @if(auth()->user()->hasPermission('delete-employees') && (int) $employee->user_id !== (int) auth()->id())
                <form action="{{ route('employees.destroy', $employee) }}" method="POST" onsubmit="return confirm('Hapus karyawan ini? Akun pengguna terkait juga akan dihapus.')">
                    @csrf
                    @method('DELETE')
                    <x-button type="danger">{{ __('Hapus') }}</x-button>
                </form>
            @endif
            @php
                $myEmployeeId = auth()->user()?->employee?->id;
                $presensiHref = ($myEmployeeId && (int)$myEmployeeId === (int)$employee->id)
                    ? route('presensi.my')
                    : route('employees.presensi', $employee);
            @endphp
            @if($myEmployeeId && (int)$myEmployeeId === (int)$employee->id)
                @if(auth()->user()->hasPermission('view-presensi'))
                    <a href="{{ $presensiHref }}">
                        <x-button type="secondary">{{ __('Lihat Presensi') }}</x-button>
                    </a>
                @endif
            @else
                @if(auth()->user()->hasPermission('view-presensi-all'))
                    <a href="{{ $presensiHref }}">
                        <x-button type="secondary">{{ __('Lihat Presensi') }}</x-button>
                    </a>
                @endif
            @endif
            <a href="{{ route('employees.index') }}">
                <x-button type="secondary">Kembali</x-button>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- User Account Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Informasi Akun Pengguna</h2>
                    @if(auth()->user()->hasPermission('edit-employees') && ! $employee->user)
                        <form action="{{ route('employees.create-account', $employee) }}" method="POST" onsubmit="return confirm('Buat akun login untuk karyawan ini? Password default sama dengan NIK.')">
                            @csrf
                            <x-button type="primary">{{ __('Buat Akun') }}</x-button>
                        </form>
                    @endif
                </div>
                <div class="p-6">
                    @if(! $employee->user)
                        <p class="mb-4 text-sm text-amber-700 dark:text-amber-300">Karyawan ini belum punya akun login. Password default akan memakai NIK.</p>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nama Pengguna
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->user->name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('Email') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->user->email ?? '-' }}
                            </div>
                        </div>

                        @if($employee->user && $employee->user->roles->count() > 0)
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Peran
                            </label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($employee->user->roles as $role)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ $role->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Employee Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Informasi Karyawan</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                {{ __('NIK') }}
                            </label>
                            <div class="text-gray-900 dark:text-gray-100 font-semibold">
                                {{ $employee->nik }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nama Lengkap
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->full_name }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jabatan
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->position->nama }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Divisi
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->division->nama }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Pool
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->pool?->pool_name ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipe Karyawan
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->employeeType->nama }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status
                            </label>
                            <div>
                                @php
                                    $colors = [
                                        'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'inactive' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'resigned' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                    ];
                                    $color = $colors[$employee->status] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $color }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Informasi Pribadi</h2>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Telepon
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->phone ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tanggal Lahir
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->birth_date ? $employee->birth_date->format('M d, Y') : '-' }}
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Alamat
                            </label>
                            <div class="text-gray-900 dark:text-gray-100">
                                {{ $employee->address ?? '-' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Employment Dates -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Tanggal Kepegawaian</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Masuk
                        </label>
                        <div class="text-gray-900 dark:text-gray-100">
                            {{ $employee->join_date->format('M d, Y') }}
                        </div>
                    </div>

                    @if($employee->resign_date)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Tanggal Resign
                        </label>
                        <div class="text-gray-900 dark:text-gray-100">
                            {{ $employee->resign_date->format('M d, Y') }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Timestamps -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Informasi Rekaman</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Dibuat Pada
                        </label>
                        <div class="text-gray-900 dark:text-gray-100 text-sm">
                            {{ $employee->created_at->format('M d, Y H:i') }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Diperbarui Pada
                        </label>
                        <div class="text-gray-900 dark:text-gray-100 text-sm">
                            {{ $employee->updated_at->format('M d, Y H:i') }}
                        </div>
                    </div>
                </div>
            </div>

            @php
                $fcmDevices = $employee->user?->devices ?? collect();
                $fcmCount = $fcmDevices->count();
            @endphp
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Notifikasi FCM') }}</h2>
                </div>
                <div class="p-6 space-y-4">
                    @error('error')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Status token
                        </label>
                        @if(! $employee->user)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                Belum punya akun
                            </span>
                        @elseif($fcmCount > 0)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                Sudah punya FCM token ({{ $fcmCount }} perangkat)
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                Belum punya FCM token
                            </span>
                        @endif
                    </div>

                    @if($fcmCount > 0)
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Terakhir aktif: {{ optional($fcmDevices->sortByDesc('last_used_at')->first()?->last_used_at)->format('d M Y H:i') ?? '-' }}
                        </p>
                    @endif

                    @error('error')
                        <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror

                    <form action="{{ route('employees.test-notification', $employee) }}" method="POST">
                        @csrf
                        <button
                            type="submit"
                            @disabled(! $employee->user || $fcmCount < 1)
                            class="w-full px-4 py-2 rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Tes notifikasi
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
