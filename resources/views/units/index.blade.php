<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Units') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Units') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Manage unit codes') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('create-units'))
                <a href="{{ route('units.create') }}">
                    <x-button type="primary">{{ __('Create Unit') }}</x-button>
                </a>
            @endif
        </div>
    </div>

    <!-- Upcoming Renewals Notifications -->
    @php
        $upcomingTaxUnits = \App\Models\Unit::with('division')->withUpcomingTaxRenewal()->get();
        $upcomingPlateUnits = \App\Models\Unit::with('division')->withUpcomingPlateRenewal()->get();
    @endphp

    @if($upcomingTaxUnits->count() > 0 || $upcomingPlateUnits->count() > 0)
    <div class="mb-6 space-y-3">
        @if($upcomingTaxUnits->count() > 0)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Perpanjangan Pajak Segera Jatuh Tempo ({{ $upcomingTaxUnits->count() }} unit)</h3>
                    <ul class="space-y-1">
                        @foreach($upcomingTaxUnits as $unit)
                        <li class="text-sm text-yellow-700 dark:text-yellow-300">
                            <a href="{{ route('units.show', $unit) }}" class="hover:underline font-medium">{{ $unit->code }}</a>
                            <span class="text-yellow-600 dark:text-yellow-400">({{ $unit->division->nama }})</span>
                            - {{ $unit->tgl_perpanjangan_pajak_berikutnya->format('d M Y') }}
                            <span class="font-semibold">({{ abs($unit->daysUntilPajakRenewal()) }} hari lagi)</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        @if($upcomingPlateUnits->count() > 0)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Ganti Plat Nomor Segera Jatuh Tempo ({{ $upcomingPlateUnits->count() }} unit)</h3>
                    <ul class="space-y-1">
                        @foreach($upcomingPlateUnits as $unit)
                        <li class="text-sm text-yellow-700 dark:text-yellow-300">
                            <a href="{{ route('units.show', $unit) }}" class="hover:underline font-medium">{{ $unit->code }}</a>
                            <span class="text-yellow-600 dark:text-yellow-400">({{ $unit->division->nama }})</span>
                            - {{ $unit->tgl_ganti_plat_berikutnya->format('d M Y') }}
                            <span class="font-semibold">({{ abs($unit->daysUntilPlatRenewal()) }} hari lagi)</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4">
            <table id="units-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Code') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Division') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Description') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.tailwindcss.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#units-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('units.index') }}',
                columns: [
                    { data: 'code', name: 'code' },
                    { data: 'division_name', name: 'division.nama' },
                    { data: 'description', name: 'description' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
                ],
                order: [[0, 'asc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search units...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ units",
                    infoEmpty: "No units found",
                    infoFiltered: "(filtered from _MAX_ total units)",
                    zeroRecords: "No matching units found",
                    emptyTable: "No units available"
                },
                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>',
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                stripeClasses: ['bg-white dark:bg-gray-800', 'bg-gray-50 dark:bg-gray-900']
            });
        });
    </script>

    <style>
        #units-table {
            border-collapse: separate !important;
            border-spacing: 0;
        }
        
        #units-table thead th {
            border-bottom: 2px solid #e5e7eb;
            background-color: #f9fafb;
        }
        
        .dark #units-table thead th {
            border-bottom-color: #374151;
            background-color: #1f2937;
        }
        
        #units-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        .dark #units-table tbody tr {
            border-bottom-color: #374151;
        }
        
        #units-table tbody tr.odd {
            background-color: #ffffff;
        }
        
        #units-table tbody tr.even {
            background-color: #f9fafb;
        }
        
        .dark #units-table tbody tr.odd {
            background-color: #1f2937;
        }
        
        .dark #units-table tbody tr.even {
            background-color: #111827;
        }
        
        #units-table tbody tr:hover {
            background-color: #e5e7eb !important;
        }
        
        .dark #units-table tbody tr:hover {
            background-color: #374151 !important;
        }
        
        #units-table tbody td {
            border-right: 1px solid #e5e7eb;
            padding: 12px 24px;
        }
        
        .dark #units-table tbody td {
            border-right-color: #374151;
        }
        
        #units-table tbody td:last-child {
            border-right: none;
        }
        
        #units-table thead th {
            border-right: 1px solid #e5e7eb;
        }
        
        .dark #units-table thead th {
            border-right-color: #374151;
        }
        
        #units-table thead th:last-child {
            border-right: none;
        }
        
        #units-table tbody td a,
        #units-table tbody td form {
            display: inline;
            white-space: nowrap;
        }
        
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            @apply px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            @apply px-3 py-1 border border-gray-300 dark:border-gray-600 rounded bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 mx-1;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            @apply bg-blue-600 text-white border-blue-600;
        }
        
        .dataTables_wrapper .dataTables_info {
            @apply text-sm text-gray-600 dark:text-gray-400;
        }
    </style>
</x-layouts.app>
