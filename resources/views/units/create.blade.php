<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('units.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Units') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Create Unit') }}</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Create Unit') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Add a new unit code') }}</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <form action="{{ route('units.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Division') }} <span class="text-red-500">*</span>
                    </label>
                    <select name="division_id" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Select Division</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>
                                {{ $division->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('division_id')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-forms.input 
                        label="Code" 
                        name="code" 
                        type="text" 
                        value="{{ old('code') }}"
                        placeholder="e.g. RA01, RT02"
                        required 
                    />
                </div>

                <div>
                    <x-forms.input 
                        label="Nomor Polisi" 
                        name="nopol" 
                        type="text" 
                        value="{{ old('nopol') }}"
                        placeholder="e.g. B 1234 XYZ"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Tahun Pembelian') }}
                    </label>
                    <input type="number" name="tahun_pembelian" min="1900" max="{{ date('Y') + 1 }}" 
                        value="{{ old('tahun_pembelian') }}" 
                        placeholder="e.g. 2023"
                        class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    @error('tahun_pembelian')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-forms.input 
                            label="Tanggal Perpanjangan Pajak Terakhir" 
                            name="tgl_perpanjangan_pajak" 
                            type="date" 
                            value="{{ old('tgl_perpanjangan_pajak') }}"
                        />
                    </div>

                    <div>
                        <x-forms.input 
                            label="Tanggal Perpanjangan Pajak Berikutnya" 
                            name="tgl_perpanjangan_pajak_berikutnya" 
                            type="date" 
                            value="{{ old('tgl_perpanjangan_pajak_berikutnya') }}"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Notifikasi akan muncul H-{{ config('app.unit_notification_days') }} hari</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <x-forms.input 
                            label="Tanggal Ganti Plat Terakhir" 
                            name="tgl_ganti_plat" 
                            type="date" 
                            value="{{ old('tgl_ganti_plat') }}"
                        />
                    </div>

                    <div>
                        <x-forms.input 
                            label="Tanggal Ganti Plat Berikutnya" 
                            name="tgl_ganti_plat_berikutnya" 
                            type="date" 
                            value="{{ old('tgl_ganti_plat_berikutnya') }}"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Notifikasi akan muncul H-{{ config('app.unit_notification_days') }} hari</p>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('Description') }}
                    </label>
                    <textarea name="description" rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                <a href="{{ route('units.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                    {{ __('Cancel') }}
                </a>
                <x-button type="primary">{{ __('Create Unit') }}</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
