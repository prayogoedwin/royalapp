<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Divisions') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Divisions') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Manage company divisions') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('create-divisions'))
                <a href="{{ route('divisions.create') }}">
                    <x-button type="primary">{{ __('Create Division') }}</x-button>
                </a>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4">
            <table id="divisions-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Logo') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
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
            $('#divisions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('divisions.index') }}',
                columns: [
                    { data: 'logo_display', name: 'logo_display', orderable: false, searchable: false },
                    { data: 'nama', name: 'nama' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
                ],
                order: [[2, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search divisions...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ divisions",
                    infoEmpty: "No divisions found",
                    infoFiltered: "(filtered from _MAX_ total divisions)",
                    zeroRecords: "No matching divisions found",
                    emptyTable: "No divisions available"
                },
                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>',
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                stripeClasses: ['bg-white dark:bg-gray-800', 'bg-gray-50 dark:bg-gray-900']
            });
        });
    </script>

    <style>
        #divisions-table {
            border-collapse: separate !important;
            border-spacing: 0;
        }
        
        #divisions-table thead th {
            border-bottom: 2px solid #e5e7eb;
            background-color: #f9fafb;
        }
        
        .dark #divisions-table thead th {
            border-bottom-color: #374151;
            background-color: #1f2937;
        }
        
        #divisions-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        .dark #divisions-table tbody tr {
            border-bottom-color: #374151;
        }
        
        #divisions-table tbody tr.odd {
            background-color: #ffffff;
        }
        
        #divisions-table tbody tr.even {
            background-color: #f9fafb;
        }
        
        .dark #divisions-table tbody tr.odd {
            background-color: #1f2937;
        }
        
        .dark #divisions-table tbody tr.even {
            background-color: #111827;
        }
        
        #divisions-table tbody tr:hover {
            background-color: #e5e7eb !important;
        }
        
        .dark #divisions-table tbody tr:hover {
            background-color: #374151 !important;
        }
        
        #divisions-table tbody td {
            border-right: 1px solid #e5e7eb;
            padding: 12px 24px;
        }
        
        .dark #divisions-table tbody td {
            border-right-color: #374151;
        }
        
        #divisions-table tbody td:last-child {
            border-right: none;
        }
        
        #divisions-table thead th {
            border-right: 1px solid #e5e7eb;
        }
        
        .dark #divisions-table thead th {
            border-right-color: #374151;
        }
        
        #divisions-table thead th:last-child {
            border-right: none;
        }
        
        #divisions-table tbody td a,
        #divisions-table tbody td form {
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
