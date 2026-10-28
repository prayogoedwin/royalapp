<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}"
            class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Customers') }}</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Customers') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Daftar customer dari nomor HP di order, termasuk yang sering repeat.') }}</p>
    </div>

    <div class="min-w-0 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-4 dt-scroll-x">
            <table id="customers-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Nama') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('No. WA / HP') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Jumlah Order') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Order terakhir') }}</th>
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
    @include('partials.datatables-mobile')

    <script>
        $(document).ready(function() {
            $('#customers-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                autoWidth: false,
                ajax: '{{ route('customers.index') }}',
                columns: [
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'customer_phone', name: 'customer_phone' },
                    { data: 'total_orders', name: 'total_orders', searchable: false },
                    { data: 'last_order_at', name: 'last_order_at', searchable: false },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
                ],
                order: [[3, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Cari nama atau nomor HP...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ customers",
                    infoEmpty: "No customers found",
                    infoFiltered: "(filtered from _MAX_ total customers)",
                    zeroRecords: "No matching customers found",
                    emptyTable: "No customers available"
                },
                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>',
                pageLength: 10,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]]
            });

            document.addEventListener('click', function (event) {
                var button = event.target.closest('[data-copy]');
                if (!button) {
                    return;
                }
                navigator.clipboard.writeText(button.getAttribute('data-copy') || '').then(function () {
                    var original = button.textContent;
                    button.textContent = 'Copied';
                    setTimeout(function () { button.textContent = original; }, 1200);
                });
            });
        });
    </script>
</x-layouts.app>
