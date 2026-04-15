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
        <span class="text-gray-500 dark:text-gray-400">Edit</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Vehicle Issue</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">Update keluhan kendaraan.</p>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden max-w-3xl">
        <form action="{{ route('order-vehicle-issues.update', $orderVehicleIssue) }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order</label>
                <p class="text-sm text-gray-900 dark:text-gray-100 font-medium">{{ $orderVehicleIssue->order->order_number ?? ('Order #' . $orderVehicleIssue->order_id) }} - {{ $orderVehicleIssue->unit_code ?? '-' }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category <span class="text-red-500">*</span></label>
                    <select name="issue_category" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select category</option>
                        @foreach(\App\Support\OrderCategoryOptions::issueCategories() as $value => $label)
                            <option value="{{ $value }}" @selected(old('issue_category', $orderVehicleIssue->issue_category) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('issue_category')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Priority <span class="text-red-500">*</span></label>
                    <select name="priority" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select priority</option>
                        <option value="low" @selected(old('priority', $orderVehicleIssue->priority) === 'low')>Low</option>
                        <option value="medium" @selected(old('priority', $orderVehicleIssue->priority) === 'medium')>Medium</option>
                        <option value="high" @selected(old('priority', $orderVehicleIssue->priority) === 'high')>High</option>
                        <option value="urgent" @selected(old('priority', $orderVehicleIssue->priority) === 'urgent')>Urgent</option>
                    </select>
                    @error('priority')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description <span class="text-red-500">*</span></label>
                <textarea name="description" rows="4" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">{{ old('description', $orderVehicleIssue->description) }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Issue Photo</label>
                    @if($orderVehicleIssue->issue_photo)
                        <img src="{{ asset('storage/'.$orderVehicleIssue->issue_photo) }}" alt="Issue photo" class="mb-2 rounded-md max-h-40 w-full object-contain bg-gray-100 dark:bg-gray-900">
                    @endif
                    <input type="file" name="issue_photo" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload untuk mengganti foto kerusakan (opsional).</p>
                    @error('issue_photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Repair Photo</label>
                    @if($orderVehicleIssue->repair_photo)
                        <img src="{{ asset('storage/'.$orderVehicleIssue->repair_photo) }}" alt="Repair photo" class="mb-2 rounded-md max-h-40 w-full object-contain bg-gray-100 dark:bg-gray-900">
                    @endif
                    <input type="file" name="repair_photo" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Upload foto setelah perbaikan (opsional).</p>
                    @error('repair_photo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_resolved" value="1" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" @checked(old('is_resolved', $orderVehicleIssue->is_resolved))>
                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Mark as resolved</span>
                </label>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Resolution Notes</label>
                    <textarea name="resolution_notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-blue-500 focus:border-blue-500">{{ old('resolution_notes', $orderVehicleIssue->resolution_notes) }}</textarea>
                    @error('resolution_notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex justify-end gap-3 mt-4">
                <a href="{{ route('order-vehicle-issues.show', $orderVehicleIssue) }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">Cancel</a>
                <x-button type="primary">Update Issue</x-button>
            </div>
        </form>
    </div>
</x-layouts.app>
