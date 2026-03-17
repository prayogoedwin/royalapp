<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Dashboard') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('orders.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">{{ __('Orders') }}</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">{{ __('Edit') }}</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Edit Order') }} {{ $order->order_number }}</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Update order data') }}</p>
        </div>
    </div>

    <form action="{{ route('orders.update', $order) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Basic Information') }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Division') }} <span class="text-red-500">*</span></label>
                    <select name="division_id" id="division_id" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">{{ __('Select Division') }}</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" data-name="{{ $division->nama }}" @selected(old('division_id', $order->division_id) == $division->id)>{{ $division->nama }}</option>
                        @endforeach
                    </select>
                    @error('division_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Status') }} <span class="text-red-500">*</span></label>
                    <select name="order_status_id" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @foreach($orderStatuses as $status)
                            <option value="{{ $status->id }}" @selected(old('order_status_id', $order->order_status_id) == $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                    @error('order_status_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Unit Code (Optional)</label>
                    <select name="unit_code" id="unit_code" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Select Division first to see units</option>
                    </select>
                    @error('unit_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <x-forms.input label="Customer Name" name="customer_name" type="text" value="{{ old('customer_name', $order->customer_name) }}" required />
                </div>

                <div>
                    <x-forms.input label="Customer Phone" name="customer_phone" type="text" value="{{ old('customer_phone', $order->customer_phone) }}" required />
                </div>

                <div>
                    <x-forms.input label="Pickup Date & Time" name="pickup_datetime" type="datetime-local" value="{{ old('pickup_datetime', $order->pickup_datetime->format('Y-m-d\TH:i')) }}" required />
                </div>

                <div>
                    <x-forms.input label="Price" name="price" type="number" step="0.01" value="{{ old('price', $order->price) }}" required />
                </div>

                <div>
                    <x-forms.input label="Payment Method" name="payment_method" type="text" value="{{ old('payment_method', $order->payment_method) }}" />
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Pickup Address') }} <span class="text-red-500">*</span></label>
                    <textarea name="pickup_address" required rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('pickup_address', $order->pickup_address) }}</textarea>
                    @error('pickup_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Destination Address') }} <span class="text-red-500">*</span></label>
                    <textarea name="destination_address" required rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('destination_address', $order->destination_address) }}</textarea>
                    @error('destination_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('notes', $order->notes) }}</textarea>
            </div>
        </div>

        <div class="flex gap-3">
            <x-button type="primary">{{ __('Update Order') }}</x-button>
            <a href="{{ route('orders.show', $order) }}">
                <x-button type="secondary">{{ __('Cancel') }}</x-button>
            </a>
        </div>
    </form>

    <script>
        const units = @json($units);

        function populateUnits() {
            const divisionSelect = document.getElementById('division_id');
            const unitSelect = document.getElementById('unit_code');
            const divisionId = parseInt(divisionSelect.value);
            const currentUnit = @json($order->unit_code);

            unitSelect.innerHTML = '<option value="">Select Unit</option>';

            if (divisionId && !isNaN(divisionId)) {
                const filteredUnits = units.filter(unit => unit.division_id === divisionId);
                filteredUnits.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.code;
                    option.textContent = unit.code;
                    if (currentUnit && currentUnit === unit.code) {
                        option.selected = true;
                    }
                    unitSelect.appendChild(option);
                });
            }
        }

        document.getElementById('division_id').addEventListener('change', populateUnits);

        if (document.getElementById('division_id').value) {
            populateUnits();
        }
    </script>
</x-layouts.app>
