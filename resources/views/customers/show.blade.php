<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('customers.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Customers') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ $name }}</span>
    </div>

    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-start">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ $name }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">
                {{ $phone }}
                <button type="button" class="ml-2 text-sm text-blue-600 dark:text-blue-400 hover:underline" data-copy="{{ $phone }}">Copy</button>
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ __('Jumlah order:') }} <span class="font-semibold">{{ number_format($totalOrders) }}</span>
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(auth()->user()->hasPermission('create-orders'))
                <x-button type="primary" tag="a" href="{{ route('orders.create', ['customer_name' => $name, 'customer_phone' => $phone]) }}">
                    {{ __('Repeat Order') }}
                </x-button>
            @endif
            <x-button type="secondary" tag="a" href="{{ route('orders.index', ['customer_phone' => $phone]) }}">
                {{ __('Lihat di Orders') }}
            </x-button>
        </div>
    </div>

    <div class="min-w-0 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-4 pt-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('History order') }}</h2>
        </div>
        <div class="p-4 dt-scroll-x">
            <table id="customer-orders-table" class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Order #') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Customer') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Division') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Pickup') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Price') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ __('Created At') }}</th>
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
            $('#customer-orders-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                autoWidth: false,
                ajax: '{{ route('orders.index', ['customer_phone' => $phone]) }}',
                columns: [
                    { data: 'order_number', name: 'order_number' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'division_name', name: 'division.nama' },
                    { data: 'pickup_datetime', name: 'pickup_datetime' },
                    { data: 'price', name: 'price' },
                    { data: 'status_badge', name: 'order_status_id' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-right whitespace-nowrap' }
                ],
                order: [[6, 'desc']],
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search orders...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ orders",
                    infoEmpty: "No orders found",
                    zeroRecords: "No matching orders found",
                    emptyTable: "No orders available"
                },
                dom: '<"flex flex-col md:flex-row justify-between items-center mb-4"lf>rt<"flex flex-col md:flex-row justify-between items-center mt-4"ip>',
                pageLength: 10
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
