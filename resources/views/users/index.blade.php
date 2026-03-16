<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Users') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Users') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Manage system users') }}</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('download-users'))
                <a href="{{ route('users.export') }}">
                    <x-button type="secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        {{ __('Download Excel') }}
                    </x-button>
                </a>
            @endif
            @if(auth()->user()->hasPermission('create-users'))
                <a href="{{ route('users.create') }}">
                    <x-button type="primary">{{ __('Create User') }}</x-button>
                </a>
            @endif
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="p-4">
            <table id="users-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Email') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Roles') }}</th>
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
            $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('users.index') }}',
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'roles', name: 'roles', orderable: false, searchable: false },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
                ],
                order: [[3, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search users...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ users",
                    infoEmpty: "No users found",
                    infoFiltered: "(filtered from _MAX_ total users)",
                    zeroRecords: "No matching users found",
                    emptyTable: "No users available"
                },
                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>',
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                stripeClasses: ['bg-white dark:bg-gray-800', 'bg-gray-50 dark:bg-gray-900']
            });
        });
    </script>

    <style>
        /* Table borders and styling */
        #users-table {
            border-collapse: separate !important;
            border-spacing: 0;
        }
        
        #users-table thead th {
            border-bottom: 2px solid #e5e7eb;
            background-color: #f9fafb;
        }
        
        .dark #users-table thead th {
            border-bottom-color: #374151;
            background-color: #1f2937;
        }
        
        #users-table tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }
        
        .dark #users-table tbody tr {
            border-bottom-color: #374151;
        }
        
        /* Alternating row colors (striping) */
        #users-table tbody tr.odd {
            background-color: #ffffff;
        }
        
        #users-table tbody tr.even {
            background-color: #f9fafb;
        }
        
        .dark #users-table tbody tr.odd {
            background-color: #1f2937;
        }
        
        .dark #users-table tbody tr.even {
            background-color: #111827;
        }
        
        #users-table tbody tr:hover {
            background-color: #e5e7eb !important;
        }
        
        .dark #users-table tbody tr:hover {
            background-color: #374151 !important;
        }
        
        #users-table tbody td {
            border-right: 1px solid #e5e7eb;
            padding: 12px 24px;
        }
        
        .dark #users-table tbody td {
            border-right-color: #374151;
        }
        
        #users-table tbody td:last-child {
            border-right: none;
        }
        
        #users-table thead th {
            border-right: 1px solid #e5e7eb;
        }
        
        .dark #users-table thead th {
            border-right-color: #374151;
        }
        
        #users-table thead th:last-child {
            border-right: none;
        }
        
        /* Action links styling - keep inline */
        #users-table tbody td a,
        #users-table tbody td form {
            display: inline;
            white-space: nowrap;
        }
        
        /* DataTables controls styling */
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
