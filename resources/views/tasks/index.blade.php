<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Tasks') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Tasks') }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Manage internal tasks') }}</p>
        </div>
        @if(auth()->user()->hasPermission('create-tasks'))
            <a href="{{ route('tasks.create') }}"><x-button type="primary">{{ __('Create Task') }}</x-button></a>
        @endif
    </div>

    <div class="min-w-0 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-4 min-w-0 overflow-x-auto overscroll-x-contain" style="-webkit-overflow-scrolling: touch;">
            <table id="tasks-table" class="min-w-full w-max divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Crew</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Created By</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider whitespace-nowrap">Actions</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <style>
        #tasks-table_wrapper {
            min-width: 0;
        }

        #tasks-table_wrapper .dataTables_scroll,
        #tasks-table_wrapper .dataTables_scrollBody {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch;
        }

        #tasks-table_wrapper table.dataTable {
            width: 100% !important;
            min-width: 640px;
        }
    </style>
    <script>
        $(function() {
            $('#tasks-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                autoWidth: false,
                ajax: '{{ route('tasks.index') }}',
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'title', name: 'title' },
                    { data: 'status_badge', name: 'order_status_id', orderable: false, searchable: false },
                    { data: 'crew_count', name: 'task_crews_count', searchable: false },
                    { data: 'created_by_name', name: 'createdBy.name', orderable: false, searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
                ],
                order: [[0, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search tasks..."
                },
                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4 gap-3"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4 gap-3"ip>',
            });
        });
    </script>
</x-layouts.app>

