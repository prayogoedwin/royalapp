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
        <span class="text-gray-500 dark:text-gray-400">{{ __('Create') }}</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">{{ __('Create Order') }}</h1>
        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('Create a new order') }}</p>
    </div>

    <form action="{{ route('orders.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- Basic Information -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Basic Information') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Division') }} <span class="text-red-500">*</span></label>
                    <select name="division_id" id="division_id" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">{{ __('Select Division') }}</option>
                        @foreach($divisions as $division)
                            <option value="{{ $division->id }}" data-name="{{ $division->nama }}" {{ old('division_id') == $division->id ? 'selected' : '' }}>{{ $division->nama }}</option>
                        @endforeach
                    </select>
                    @error('division_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Status') }} <span class="text-red-500">*</span></label>
                    <select name="order_status_id" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @foreach($orderStatuses as $status)
                            <option value="{{ $status->id }}" {{ old('order_status_id', 1) == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                        @endforeach
                    </select>
                    @error('order_status_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Unit Code (Optional)') }}</label>
                    <select name="unit_code" id="unit_code" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Select Division first to see units</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Select a Division first, then units will appear here</p>
                    @error('unit_code')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <x-forms.input label="Customer Name" name="customer_name" type="text" value="{{ old('customer_name') }}" required />
                </div>

                <div>
                    <x-forms.input label="Customer Phone" name="customer_phone" type="text" value="{{ old('customer_phone') }}" required />
                </div>

                <div>
                    <x-forms.input label="Appointment" name="appointment" type="text" value="{{ old('appointment') }}" placeholder="Sebelum Jam 8 / jam 8.05 / IDEM" />
                </div>

                <div>
                    <x-forms.input label="Pickup Date & Time" name="pickup_datetime" type="datetime-local" value="{{ old('pickup_datetime') }}" required />
                </div>

                <div>
                    <x-forms.input label="Price" name="price" type="number" step="0.01" value="{{ old('price', 0) }}" required />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                    <select name="payment_method" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">-</option>
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_method')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Pembayaran <span class="text-red-500">*</span></label>
                    <select name="payment_status" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @foreach($paymentStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_status', 'UNPAID') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Pickup Address') }} <span class="text-red-500">*</span></label>
                    <textarea name="pickup_address" required rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('pickup_address') }}</textarea>
                    @error('pickup_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Destination Address') }} <span class="text-red-500">*</span></label>
                    <textarea name="destination_address" required rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('destination_address') }}</textarea>
                    @error('destination_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('notes') }}</textarea>
            </div>
        </div>

        <!-- Ambulance Details (Show if Royal Ambulance selected) -->
        <div id="ambulance-section" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hidden">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Ambulance Details') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-forms.input label="Patient Condition" name="patient_condition" type="text" value="{{ old('patient_condition') }}" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Medical Needs') }}</label>
                    <textarea name="medical_needs" rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('medical_needs') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Towing Details (Show if Royal Towing selected) -->
        <div id="towing-section" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hidden">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Towing Details') }}</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-forms.input label="Car Type" name="car_type" type="text" value="{{ old('car_type') }}" />
                </div>
                
                <div>
                    <x-forms.input label="Car Condition" name="car_condition" type="text" value="{{ old('car_condition') }}" />
                </div>
                
                <div>
                    <x-forms.input label="Receiver Phone" name="receiver_phone" type="text" value="{{ old('receiver_phone') }}" />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Payment Requirement') }}</label>
                    <textarea name="payment_requirement" rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('payment_requirement') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Crew Assignment -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Crew Assignment') }}</h2>
                <button type="button" onclick="addCrew()" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Add Crew</button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                🔴 Ongoing, 🟡 Bentrok jadwal pickup, 🟢 Tersedia, ⚫ Off/Inactive (disabled)
            </p>
            
            <div id="crew-list" class="space-y-3"></div>
        </div>

        <!-- Photo Upload -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('Photos') }}</h2>
                <button type="button" onclick="addPhoto()" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">+ Add Photo</button>
            </div>
            
            <div id="photo-list" class="space-y-3"></div>
        </div>

        <div class="flex gap-3">
            <x-button type="primary">{{ __('Create Order') }}</x-button>
            <a href="{{ route('orders.index') }}">
                <x-button type="secondary">{{ __('Cancel') }}</x-button>
            </a>
        </div>
    </form>

    <script>
        const employees = @json($employees);
        const employeeAvailability = @json($employeeAvailability);
        const units = @json($units);
        let crewCount = 0;
        let photoCount = 0;

        // Toggle sections based on division
        document.getElementById('division_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const divisionName = selectedOption.getAttribute('data-name');
            const divisionId = parseInt(this.value);

            document.getElementById('ambulance-section').classList.add('hidden');
            document.getElementById('towing-section').classList.add('hidden');
            
            if (divisionName === 'Royal Ambulance') {
                document.getElementById('ambulance-section').classList.remove('hidden');
            } else if (divisionName === 'Royal Towing') {
                document.getElementById('towing-section').classList.remove('hidden');
            }

            // Filter units by division
            const unitSelect = document.getElementById('unit_code');
            unitSelect.innerHTML = '<option value="">Select Unit</option>';
            
            if (divisionId && !isNaN(divisionId)) {
                const filteredUnits = units.filter(unit => Number(unit.division_id) === Number(divisionId));
                
                filteredUnits.forEach(unit => {
                    const option = document.createElement('option');
                    option.value = unit.code;
                    option.textContent = unit.code;
                    unitSelect.appendChild(option);
                });
            }
        });

        // Trigger on page load if there's old input
        if (document.getElementById('division_id').value) {
            document.getElementById('division_id').dispatchEvent(new Event('change'));
        }

        function getCrewIndicator(emp) {
            if ((emp.status || '').toLowerCase() !== 'active') {
                return '⚫';
            }
            const pickupValue = document.getElementById('pickup_datetime')?.value || '';
            const availability = employeeAvailability?.[emp.id] || { has_ongoing: false, pickup_slots: [] };
            if (availability.has_ongoing) {
                return '🔴';
            }
            if (pickupValue && (availability.pickup_slots || []).includes(pickupValue)) {
                return '🟡';
            }
            return '🟢';
        }

        function getCrewMonthlyStats(emp) {
            const availability = employeeAvailability?.[emp.id] || {};
            const monthlyOrders = Number(availability.monthly_orders || 0);
            const monthlyKm = Number(availability.monthly_km || 0);
            const kmLabel = Number.isInteger(monthlyKm) ? monthlyKm.toString() : monthlyKm.toFixed(2);
            return `(${monthlyOrders} order) (${kmLabel} km)`;
        }

        function addCrew() {
            const crewList = document.getElementById('crew-list');
            const crewItem = document.createElement('div');
            crewItem.className = 'flex gap-3 items-start';
            crewItem.innerHTML = `
                <div class="flex-1">
                    <select name="crew_ids[]" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Select Employee</option>
                        ${employees.map(emp => {
                            const isActive = (emp.status || '').toLowerCase() === 'active';
                            return `<option value="${emp.id}" ${isActive ? '' : 'disabled'}>${emp.full_name} - ${emp.position?.nama ?? ''} ${getCrewIndicator(emp)} ${getCrewMonthlyStats(emp)}</option>`;
                        }).join('')}
                    </select>
                </div>
                <div class="flex-1">
                    <input type="text" readonly value="Role will be auto-filled from position" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700">Remove</button>
            `;
            crewList.appendChild(crewItem);
            crewCount++;
        }

        function addPhoto() {
            const photoList = document.getElementById('photo-list');
            const photoItem = document.createElement('div');
            photoItem.className = 'border border-gray-300 dark:border-gray-600 rounded-md p-4';
            photoItem.innerHTML = `
                <div class="flex justify-between items-start mb-3">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Photo ${photoCount + 1}</h3>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-600 hover:text-red-700 text-sm">Remove</button>
                </div>
                <div class="space-y-3">
                    <div>
                        <input type="file" name="photos[]" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700">
                    </div>
                    <div>
                        <input type="text" name="photo_titles[]" placeholder="Photo Title" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                    </div>
                    <div>
                        <textarea name="photo_descriptions[]" placeholder="Photo Description" rows="2" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"></textarea>
                    </div>
                </div>
            `;
            photoList.appendChild(photoItem);
            photoCount++;
        }
    </script>
</x-layouts.app>
