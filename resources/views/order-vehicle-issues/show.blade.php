<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('order-vehicle-issues.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Order Vehicle Issues</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">Detail</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Vehicle Issue Detail</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">Order {{ $orderVehicleIssue->order->order_number ?? ('#' . $orderVehicleIssue->order_id) }}</p>
        </div>
        <div class="flex gap-2">
            @can('edit-orders')
            <a href="{{ route('order-vehicle-issues.edit', $orderVehicleIssue) }}">
                <x-button type="secondary">Edit</x-button>
            </a>
            @endcan
            <a href="{{ route('order-vehicle-issues.index') }}">
                <x-button type="secondary">Back</x-button>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Order</dt>
                        <dd class="text-base text-gray-900 dark:text-gray-100">
                            <a href="{{ route('orders.show', $orderVehicleIssue->order) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                {{ $orderVehicleIssue->order->order_number ?? ('Order #' . $orderVehicleIssue->order_id) }}
                            </a>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Unit</dt>
                        <dd class="text-base text-gray-900 dark:text-gray-100">{{ $orderVehicleIssue->unit_code ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Category</dt>
                        <dd class="text-base text-gray-900 dark:text-gray-100">{{ \App\Models\OrderVehicleIssue::getCategoryLabel($orderVehicleIssue->issue_category) }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Priority</dt>
                        <dd class="text-base">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ \App\Models\OrderVehicleIssue::getPriorityColor($orderVehicleIssue->priority) }}">
                                {{ \App\Models\OrderVehicleIssue::getPriorityLabel($orderVehicleIssue->priority) }}
                            </span>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Status</dt>
                        <dd class="text-base">
                            @if($orderVehicleIssue->is_resolved)
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">Resolved</span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">Open</span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Created At</dt>
                        <dd class="text-base text-gray-900 dark:text-gray-100">{{ $orderVehicleIssue->created_at->format('d M Y H:i') }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Created By</dt>
                        <dd class="text-base text-gray-900 dark:text-gray-100">{{ $orderVehicleIssue->createdBy->name ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Resolved At</dt>
                        <dd class="text-base text-gray-900 dark:text-gray-100">{{ $orderVehicleIssue->resolved_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </div>

                    <div>
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Resolved By</dt>
                        <dd class="text-base text-gray-900 dark:text-gray-100">{{ $orderVehicleIssue->resolvedBy->name ?? '-' }}</dd>
                    </div>

                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Description</dt>
                        <dd class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $orderVehicleIssue->description }}</dd>
                    </div>

                    @if($orderVehicleIssue->resolution_notes)
                    <div class="md:col-span-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Resolution Notes</dt>
                        <dd class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $orderVehicleIssue->resolution_notes }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Issue Photo</h2>
                </div>
                <div class="p-4">
                    @if($orderVehicleIssue->issue_photo)
                        <img src="{{ asset('storage/'.$orderVehicleIssue->issue_photo) }}" alt="Issue photo" class="rounded-md max-h-64 w-full object-contain bg-gray-100 dark:bg-gray-900">
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No issue photo uploaded.</p>
                    @endif
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-4 border-b border-gray-200 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-800 dark:text-gray-100">Repair Photo</h2>
                </div>
                <div class="p-4">
                    @if($orderVehicleIssue->repair_photo)
                        <img src="{{ asset('storage/'.$orderVehicleIssue->repair_photo) }}" alt="Repair photo" class="rounded-md max-h-64 w-full object-contain bg-gray-100 dark:bg-gray-900">
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">No repair photo uploaded.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
