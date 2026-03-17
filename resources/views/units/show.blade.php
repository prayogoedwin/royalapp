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
        <span class="text-gray-500 dark:text-gray-400">{{ __('View Unit') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Unit Details') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('View unit information') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-units'))
                <a href="{{ route('units.edit', $unit) }}">
                    <x-button type="secondary">{{ __('Edit Unit') }}</x-button>
                </a>
            @endif
            <a href="{{ route('units.index') }}">
                <x-button type="secondary">{{ __('Back to List') }}</x-button>
            </a>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <!-- Notification Alerts -->
        @if($unit->isPajakDueSoon() || $unit->isPlatDueSoon())
        <div class="p-6 border-b border-gray-200 dark:border-gray-700 space-y-3">
            @if($unit->isPajakDueSoon())
            <div class="flex items-start p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Perpanjangan Pajak Segera Jatuh Tempo</h3>
                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        {{ $unit->tgl_perpanjangan_pajak_berikutnya->format('d M Y') }} 
                        ({{ abs($unit->daysUntilPajakRenewal()) }} hari lagi)
                    </p>
                </div>
            </div>
            @endif

            @if($unit->isPlatDueSoon())
            <div class="flex items-start p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Ganti Plat Nomor Segera Jatuh Tempo</h3>
                    <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                        {{ $unit->tgl_ganti_plat_berikutnya->format('d M Y') }} 
                        ({{ abs($unit->daysUntilPlatRenewal()) }} hari lagi)
                    </p>
                </div>
            </div>
            @endif
        </div>
        @endif

        <div class="p-6">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Code') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->code }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Division') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->division->nama }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Nomor Polisi') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->nopol ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Tahun Pembelian') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->tahun_pembelian ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Perpanjangan Pajak Terakhir') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->tgl_perpanjangan_pajak?->format('d M Y') ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Perpanjangan Pajak Berikutnya') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->tgl_perpanjangan_pajak_berikutnya?->format('d M Y') ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Ganti Plat Terakhir') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->tgl_ganti_plat?->format('d M Y') ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Ganti Plat Berikutnya') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->tgl_ganti_plat_berikutnya?->format('d M Y') ?? '-' }}</dd>
                </div>

                <div class="md:col-span-2">
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Description') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->description ?? '-' }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Created At') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->created_at->format('M d, Y H:i') }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">{{ __('Updated At') }}</dt>
                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $unit->updated_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-layouts.app>
