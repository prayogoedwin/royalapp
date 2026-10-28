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
        <span class="text-gray-500 dark:text-gray-400">Ubah</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Ubah Karyawan</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Perbarui data karyawan dan akun pengguna</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-6">
            <form action="{{ route('employees.update', $employee) }}" method="POST" class="max-w-4xl">
                @csrf
                @method('PUT')

                <!-- User Account Section -->
                <div class="mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Informasi Akun Pengguna</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-forms.input label="Nama" name="name" type="text" value="{{ old('name', $employee->user->name ?? '') }}" required />
                        </div>
                        
                        <div>
                            <x-forms.input label="Email" name="email" type="email" value="{{ old('email', $employee->user->email ?? '') }}" required />
                        </div>
                        
                        <div>
                            <x-forms.input label="Password (kosongkan jika tidak diubah)" name="password" type="password" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Kosongkan jika tidak ingin mengubah password</p>
                        </div>
                        
                        <div>
                            <x-forms.input label="Konfirmasi Password" name="password_confirmation" type="password" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Peran
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                @foreach($roles as $role)
                                    <div>
                                        <x-forms.checkbox 
                                            name="roles[]" 
                                            value="{{ $role->id }}" 
                                            label="{{ $role->name }}"
                                            :checked="in_array($role->id, old('roles', $employee->user ? $employee->user->roles->pluck('id')->toArray() : []))" />
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employee Information Section -->
                <div class="mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Informasi Karyawan</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-forms.input label="NIK" name="nik" type="text" value="{{ old('nik', $employee->nik) }}" required />
                        </div>
                        
                        <div>
                            <x-forms.input label="Nama Lengkap" name="full_name" type="text" value="{{ old('full_name', $employee->full_name) }}" required />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Jabatan <span class="text-red-500">*</span>
                            </label>
                            <select name="position_id" required
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Pilih Jabatan</option>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ old('position_id', $employee->position_id) == $position->id ? 'selected' : '' }}>
                                        {{ $position->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('position_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Divisi <span class="text-red-500">*</span>
                            </label>
                            <select name="division_id" required
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Pilih Divisi</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" {{ old('division_id', $employee->division_id) == $division->id ? 'selected' : '' }}>
                                        {{ $division->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('division_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Pool
                            </label>
                            <select name="pool_id"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Tanpa Pool</option>
                                @foreach($pools as $pool)
                                    <option value="{{ $pool->id }}" {{ old('pool_id', $employee->pool_id) == $pool->id ? 'selected' : '' }}>
                                        {{ $pool->pool_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('pool_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Tipe Karyawan <span class="text-red-500">*</span>
                            </label>
                            <select name="employee_type_id" required
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Pilih Tipe</option>
                                @foreach($employeeTypes as $type)
                                    <option value="{{ $type->id }}" {{ old('employee_type_id', $employee->employee_type_id) == $type->id ? 'selected' : '' }}>
                                        {{ $type->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_type_id')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Status <span class="text-red-500">*</span>
                            </label>
                            <select name="status" required
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="active" {{ old('status', $employee->status) == 'active' ? 'selected' : '' }}>Aktif</option>
                                <option value="inactive" {{ old('status', $employee->status) == 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                                <option value="resigned" {{ old('status', $employee->status) == 'resigned' ? 'selected' : '' }}>Resign</option>
                            </select>
                            @error('status')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Personal Information Section -->
                <div class="mb-8 pb-6 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Informasi Pribadi</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-forms.input label="Telepon" name="phone" type="text" value="{{ old('phone', $employee->phone) }}" />
                        </div>
                        
                        <div>
                            <x-forms.input label="Tanggal Lahir" name="birth_date" type="date" value="{{ old('birth_date', $employee->birth_date?->format('Y-m-d')) }}" />
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Alamat
                            </label>
                            <textarea name="address" rows="3"
                                class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('address', $employee->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Employment Dates Section -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Tanggal Kepegawaian</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <x-forms.input label="Tanggal Masuk" name="join_date" type="date" value="{{ old('join_date', $employee->join_date?->format('Y-m-d')) }}" required />
                        </div>
                        
                        <div>
                            <x-forms.input label="Tanggal Resign" name="resign_date" type="date" value="{{ old('resign_date', $employee->resign_date?->format('Y-m-d')) }}" />
                        </div>
                    </div>
                </div>

                <div class="flex gap-3">
                    <x-button type="primary">Simpan Karyawan</x-button>
                    <a href="{{ route('employees.index') }}">
                        <x-button type="secondary">Batal</x-button>
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
