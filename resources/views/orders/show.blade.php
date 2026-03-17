<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('orders.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Orders</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">Detail</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Order {{ $order->order_number }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Detail order dan kendaraan.</p>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-orders'))
                <a href="{{ route('orders.edit', $order) }}">
                    <x-button type="secondary">Edit Order</x-button>
                </a>
            @endif
            @if(auth()->user()->hasPermission('edit-orders'))
                <a href="{{ route('order-vehicle-issues.create', $order) }}">
                    <x-button type="primary">Tambah Vehicle Issue</x-button>
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Informasi Order</h2>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Customer</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->customer_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->customer_phone }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Division</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->division->nama }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Unit Code</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->unit_code ?? '-' }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pickup Address</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->pickup_address }}</dd>
                        </div>
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Destination Address</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->destination_address }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pickup Datetime</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->pickup_datetime->format('d M Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Price</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">Rp {{ number_format($order->price, 0, ',', '.') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Payment Method</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->payment_method ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->orderStatus->name }}</dd>
                        </div>
                        @if($order->notes)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                            <dd class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $order->notes }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Crew</h2>
                    @if($order->orderCrews->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada crew untuk order ini.</p>
                    @else
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($order->orderCrews as $crew)
                                <li class="py-2 flex justify-between items-center">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $crew->employee->full_name ?? '-' }}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $crew->role ?? ($crew->employee->position->nama ?? '-') }}</p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Vehicle Issues</h2>
                </div>
                <div class="p-4 space-y-2">
                    @php
                        $issues = $order->orderVehicleIssues()->latest()->take(5)->get();
                    @endphp
                    @if($issues->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada vehicle issue untuk order ini.</p>
                    @else
                        <ul class="space-y-2">
                            @foreach($issues as $issue)
                                <li class="text-sm">
                                    <a href="{{ route('order-vehicle-issues.show', $issue) }}" class="text-blue-600 dark:text-blue-400 hover:underline font-medium">
                                        {{ \App\Models\OrderVehicleIssue::getCategoryLabel($issue->issue_category) }} - {{ \App\Models\OrderVehicleIssue::getPriorityLabel($issue->priority) }}
                                    </a>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $issue->created_at->format('d M Y H:i') }}</p>
                                </li>
                            @endforeach
                        </ul>
                        <a href="{{ route('order-vehicle-issues.index') }}" class="inline-block mt-2 text-xs text-blue-600 dark:text-blue-400 hover:underline">Lihat semua vehicle issues</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
