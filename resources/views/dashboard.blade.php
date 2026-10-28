<x-layouts.app>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Dashboard')}}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Welcome to the dashboard') }}</p>
    </div>

    @if(auth()->user()->hasPermission('view-units'))
        @php
            $upcomingTaxUnits = \App\Models\Unit::with('division')->withUpcomingTaxRenewal()->get();
            $upcomingPlateUnits = \App\Models\Unit::with('division')->withUpcomingPlateRenewal()->get();
            $upcomingKirUnits = \App\Models\Unit::with('division')->withUpcomingKirRenewal()->get();
        @endphp

        @if($upcomingTaxUnits->count() > 0 || $upcomingPlateUnits->count() > 0 || $upcomingKirUnits->count() > 0)
            <div class="mb-6 space-y-3">
                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">
                        Notifikasi Kendaraan / Unit (H-{{ config('app.unit_notification_days', 30) }} hari)
                    </h3>
                    <div class="space-y-1 text-sm text-yellow-700 dark:text-yellow-300">
                        @foreach($upcomingTaxUnits as $unit)
                            <div>
                                <span class="font-medium">Pajak:</span>
                                <a href="{{ route('units.show', $unit) }}" class="hover:underline font-medium">{{ $unit->code }}</a>
                                ({{ $unit->division->nama }}) - {{ $unit->tgl_perpanjangan_pajak_berikutnya->format('d M Y') }}
                            </div>
                        @endforeach
                        @foreach($upcomingPlateUnits as $unit)
                            <div>
                                <span class="font-medium">Ganti Plat:</span>
                                <a href="{{ route('units.show', $unit) }}" class="hover:underline font-medium">{{ $unit->code }}</a>
                                ({{ $unit->division->nama }}) - {{ $unit->tgl_ganti_plat_berikutnya->format('d M Y') }}
                            </div>
                        @endforeach
                        @foreach($upcomingKirUnits as $unit)
                            <div>
                                <span class="font-medium">KIR:</span>
                                <a href="{{ route('units.show', $unit) }}" class="hover:underline font-medium">{{ $unit->code }}</a>
                                ({{ $unit->division->nama }}) - {{ $unit->tgl_kir_berikutnya->format('d M Y') }}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Users') }}</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ number_format($total_users) }}</p>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500 dark:text-blue-300"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Orders') }}</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ number_format($total_orders) }}</p>
                </div>
                <div class="bg-purple-100 dark:bg-purple-900 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-500 dark:text-purple-300"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('Total Tasks') }}</p>
                    <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ number_format($total_tasks) }}</p>
                </div>
                <div class="bg-amber-100 dark:bg-amber-900 p-3 rounded-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500 dark:text-amber-300"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    @php
        $statusCardColors = [
            'yellow' => ['wrap' => 'bg-yellow-100 dark:bg-yellow-900', 'icon' => 'text-yellow-600 dark:text-yellow-300'],
            'gray' => ['wrap' => 'bg-gray-100 dark:bg-gray-700', 'icon' => 'text-gray-600 dark:text-gray-300'],
            'blue' => ['wrap' => 'bg-blue-100 dark:bg-blue-900', 'icon' => 'text-blue-600 dark:text-blue-300'],
            'green' => ['wrap' => 'bg-green-100 dark:bg-green-900', 'icon' => 'text-green-600 dark:text-green-300'],
            'red' => ['wrap' => 'bg-red-100 dark:bg-red-900', 'icon' => 'text-red-600 dark:text-red-300'],
        ];
    @endphp

    @if(!empty($order_statuses))
        <div class="mb-2">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Status Order') }}</h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ __('Jumlah order per status, termasuk yang dibatalkan.') }}</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-6">
            @foreach($order_statuses as $status)
                @php
                    $tone = $statusCardColors[$status['color'] ?? 'gray'] ?? $statusCardColors['gray'];
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $status['name'] }}</p>
                            <p class="text-2xl font-bold text-gray-800 dark:text-gray-100 mt-1">{{ number_format($status['total']) }}</p>
                        </div>
                        <div class="{{ $tone['wrap'] }} p-3 rounded-full">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 {{ $tone['icon'] }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</x-layouts.app>
