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

    @if(session('status'))
        <div class="mb-4 p-3 rounded bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-200 text-sm">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 p-3 rounded bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-200 text-sm">{{ $errors->first() }}</div>
    @endif

    {{-- Form 1: Basic Information --}}
    @if(auth()->user()->hasPermission('edit-orders'))
    <form action="{{ route('orders.update', $order) }}" method="POST" class="space-y-6 mb-8">
        @csrf
        @method('PUT')
        <input type="hidden" name="update_section" value="basic">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Basic Information') }}</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @php
                    $currentDivisionId = old('division_id', $order->division_id);
                    $currentDivisionName = $divisions->firstWhere('id', (int) $currentDivisionId)?->nama
                        ?? $order->division?->nama
                        ?? '—';
                @endphp
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Division') }} <span class="text-red-500">*</span></label>
                    {{-- Division tidak di-submit; server memakai division order yang ada --}}
                    <input type="hidden" id="division_id" value="{{ $currentDivisionId }}">
                    <div class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-600 text-gray-800 dark:text-gray-100 cursor-not-allowed select-none">
                        {{ $currentDivisionName }}
                    </div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Division cannot be changed for an existing order.') }}</p>
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
                    <x-forms.input label="Appointment" name="appointment" type="text" value="{{ old('appointment', $order->appointment) }}" placeholder="Sebelum Jam 8 / jam 8.05 / IDEM" />
                </div>

                <div>
                    <x-forms.input label="Pickup Date & Time" name="pickup_datetime" type="datetime-local" value="{{ old('pickup_datetime', $order->pickup_datetime->format('Y-m-d\TH:i')) }}" required />
                </div>

                <div>
                    <x-forms.input label="Price" name="price" type="number" step="0.01" value="{{ old('price', $order->price) }}" required />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
                    <select name="payment_method" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">-</option>
                        @foreach($paymentMethods as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method', $order->payment_method) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_method')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status Pembayaran <span class="text-red-500">*</span></label>
                    <select name="payment_status" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        @foreach($paymentStatuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_status', $order->payment_status ?? 'UNPAID') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('payment_status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Notes') }}</label>
                <textarea name="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('notes', $order->notes) }}</textarea>
            </div>

            @if($order->orderAmbulance)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-md font-medium text-gray-800 dark:text-gray-100 mb-3">Ambulance</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Patient Condition</label>
                            <textarea name="patient_condition" rows="2" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('patient_condition', $order->orderAmbulance->patient_condition) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Medical Needs</label>
                            <textarea name="medical_needs" rows="2" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('medical_needs', $order->orderAmbulance->medical_needs) }}</textarea>
                        </div>
                    </div>
                </div>
            @endif

            @if($order->orderTowing)
                <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                    <h3 class="text-md font-medium text-gray-800 dark:text-gray-100 mb-3">Towing</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-forms.input label="Car Type" name="car_type" type="text" value="{{ old('car_type', $order->orderTowing->car_type) }}" />
                        </div>
                        <div>
                            <x-forms.input label="Car Condition" name="car_condition" type="text" value="{{ old('car_condition', $order->orderTowing->car_condition) }}" />
                        </div>
                        <div>
                            <x-forms.input label="Receiver Phone" name="receiver_phone" type="text" value="{{ old('receiver_phone', $order->orderTowing->receiver_phone) }}" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Payment Requirement</label>
                            <textarea name="payment_requirement" rows="2" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">{{ old('payment_requirement', $order->orderTowing->payment_requirement) }}</textarea>
                        </div>
                    </div>
                </div>
            @endif

            <div class="flex gap-3 pt-2">
                <x-button type="primary">{{ __('Update Order') }}</x-button>
                <a href="{{ route('orders.show', $order) }}">
                    <x-button type="secondary">{{ __('Cancel') }}</x-button>
                </a>
            </div>
        </div>
    </form>
    @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">{{ __('Basic Information') }}</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Anda tidak punya akses untuk mengubah data order. (Hanya bisa isi section driver di bawah.)</p>
        </div>
    @endif

    {{-- Form 2: Crew --}}
    @if(auth()->user()->hasPermission('edit-orders'))
    <form id="form-crew" action="{{ route('orders.update', $order) }}" method="POST" class="mb-8">
        @csrf
        @method('PUT')
        <input type="hidden" name="update_section" value="crew">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Crew</h2>
            <div class="flex justify-between items-center mb-3">
                <p class="text-sm text-gray-500 dark:text-gray-400">Tambahkan / ubah crew yang bertugas pada order ini.</p>
                <button type="button" onclick="addCrew()" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs">+ Add Crew</button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                🔴 Ongoing, 🟡 Bentrok jadwal pickup, 🟢 Tersedia, ⚫ Off/Inactive (disabled)
            </p>
            <div id="crew-list" class="space-y-3">
                @foreach($order->orderCrews as $crew)
                    <div class="flex gap-3 items-start crew-row">
                        <div class="flex-1">
                            <select name="crew_ids[]" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    @php
                                        $availability = $employeeAvailability[$employee->id] ?? ['has_ongoing' => false, 'pickup_slots' => []];
                                        $isConflict = in_array($order->pickup_datetime->format('Y-m-d\TH:i'), $availability['pickup_slots'] ?? [], true);
                                        $isActive = strtolower((string) $employee->status) === 'active';
                                        $indicator = ! $isActive ? '⚫' : ($availability['has_ongoing'] ? '🔴' : ($isConflict ? '🟡' : '🟢'));
                                        $monthlyOrders = (int) ($availability['monthly_orders'] ?? 0);
                                        $monthlyKm = (float) ($availability['monthly_km'] ?? 0);
                                    @endphp
                                    <option value="{{ $employee->id }}" @disabled(! $isActive) @selected($crew->employee_id == $employee->id)>
                                        {{ $employee->full_name }} - {{ $employee->position->nama ?? '' }} {{ $indicator }} ({{ $monthlyOrders }} order) ({{ fmod($monthlyKm, 1.0) == 0.0 ? (int) $monthlyKm : number_format($monthlyKm, 2, '.', '') }} km)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <input type="text" readonly value="{{ $crew->role ?? 'Role from position' }}" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-400">
                        </div>
                        <button type="button" onclick="this.closest('.crew-row').remove()" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Remove</button>
                    </div>
                @endforeach
            </div>
            <div class="mt-4">
                <x-button type="primary" class="!mb-0">Update Crew</x-button>
            </div>
        </div>
    </form>
    @endif

    {{-- Form 3: New Photos --}}
    @if(auth()->user()->hasPermission('create-order-photos') || auth()->user()->hasPermission('edit-orders'))
    <form id="form-photos" action="{{ route('orders.update', $order) }}" method="POST" enctype="multipart/form-data" class="mb-8">
        @csrf
        @method('PUT')
        <input type="hidden" name="update_section" value="photos">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Photos</h2>

            @if($order->orderPhotos->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Belum ada foto. Anda dapat menambahkan foto baru di bawah.</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    @foreach($order->orderPhotos as $photo)
                        <div class="space-y-2">
                            <img src="{{ asset('storage/'.$photo->path) }}" alt="{{ $photo->title }}" class="rounded-md w-full object-cover max-h-32 bg-gray-100 dark:bg-gray-900">
                            <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $photo->title }}</p>
                            @if($photo->description)
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $photo->description }}</p>
                            @endif
                            @if(auth()->user()->hasPermission('delete-order-photos') || auth()->user()->hasPermission('edit-orders'))
                                <button type="submit" form="delete-photo-{{ $photo->id }}" class="text-xs text-red-600 dark:text-red-400 hover:underline">Delete</button>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Add New Photos</p>
            <div id="photo-list" class="space-y-3 mb-4"></div>
            <button type="button" onclick="addPhoto()" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs mb-4">+ Add Photo</button>

            @if(auth()->user()->hasPermission('create-order-photos') || auth()->user()->hasPermission('edit-orders'))
                <x-button type="primary">Simpan Foto</x-button>
            @endif
        </div>
    </form>
    @endif

    {{-- Form 4: Order Report --}}
    @if(auth()->user()->hasPermission('edit-order-report') || auth()->user()->hasPermission('edit-orders'))
    <form action="{{ route('orders.update', $order) }}" method="POST" class="mb-8">
        @csrf
        @method('PUT')
        <input type="hidden" name="update_section" value="order_report">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Order Report</h2>

            <div
                class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4"
                x-data="{
                    kmAwal: @js(old('km_awal', $order->orderReport->km_awal ?? '')),
                    kmAkhir: @js(old('km_akhir', $order->orderReport->km_akhir ?? '')),
                    get selisih() {
                        const a = parseFloat(this.kmAwal);
                        const b = parseFloat(this.kmAkhir);
                        if (isNaN(a) || isNaN(b)) return '—';
                        const d = b - a;
                        return Number.isInteger(d) ? String(d) : d.toFixed(2);
                    }
                }"
            >
                <div>
                    <label for="order_report_km_awal" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">KM Awal</label>
                    <input
                        id="order_report_km_awal"
                        type="number"
                        name="km_awal"
                        step="0.01"
                        x-model="kmAwal"
                        class="w-full px-4 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    @error('km_awal')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="order_report_km_akhir" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">KM Akhir</label>
                    <input
                        id="order_report_km_akhir"
                        type="number"
                        name="km_akhir"
                        step="0.01"
                        x-model="kmAkhir"
                        class="w-full px-4 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                    @error('km_akhir')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="order_report_order_status_id" class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status <span class="text-red-500">*</span> <span class="text-xs font-normal text-gray-500 dark:text-gray-400">(sama dengan status order)</span></label>
                    <select
                        id="order_report_order_status_id"
                        name="order_status_id"
                        required
                        class="w-full px-4 py-1.5 rounded-lg text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        @foreach($orderStatuses as $status)
                            <option value="{{ $status->id }}" @selected(old('order_status_id', $order->order_status_id) == $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                    @error('order_status_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <span class="block ml-1 text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">KM Selisih</span>
                    <div
                        class="w-full px-4 py-1.5 rounded-lg text-gray-800 dark:text-gray-100 bg-gray-100 dark:bg-gray-600 border border-gray-200 dark:border-gray-600 cursor-not-allowed select-none"
                        x-text="selisih"
                    ></div>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Dihitung otomatis (KM Akhir − KM Awal).') }}</p>
                </div>
                <div>
                    <x-forms.input
                        label="Deliver"
                        name="deliver_datetime"
                        type="datetime-local"
                        value="{{ old('deliver_datetime', ($order->orderReport?->deliver_datetime ? $order->orderReport->deliver_datetime->format('Y-m-d\TH:i') : null)) }}"
                    />
                </div>
                <div>
                    <x-forms.input
                        label="Saldo Sebelum E-Toll"
                        name="saldo_etoll_before"
                        type="number"
                        step="0.01"
                        value="{{ old('saldo_etoll_before', $order->orderReport?->saldo_etoll_before) }}"
                    />
                </div>
                <div>
                    <x-forms.input
                        label="Saldo Sesudah E-Toll"
                        name="saldo_etoll_after"
                        type="number"
                        step="0.01"
                        value="{{ old('saldo_etoll_after', $order->orderReport?->saldo_etoll_after) }}"
                    />
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Notes</label>
                <textarea
                    name="notes"
                    rows="3"
                    class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100"
                >{{ old('notes', $order->orderReport->notes ?? null) }}</textarea>
            </div>

            <div class="flex items-center gap-3">
                <x-button type="primary">Update Order Report</x-button>
                @if(($order->orderReport ?? null) && (auth()->user()->hasPermission('delete-order-report') || auth()->user()->hasPermission('edit-orders')))
                    <button type="submit"
                            form="delete-order-report"
                            class="text-xs text-red-600 dark:text-red-400 hover:underline">
                        Hapus
                    </button>
                @endif
            </div>
        </div>
    </form>
    @endif

    {{-- Form 5: Order Expenses (tambah baris baru + upload foto) --}}
    @if(auth()->user()->hasPermission('create-order-expenses') || auth()->user()->hasPermission('edit-orders'))
    <form action="{{ route('orders.update', $order) }}" method="POST" enctype="multipart/form-data" class="mb-8" id="form-expenses">
        @csrf
        @method('PUT')
        <input type="hidden" name="update_section" value="order_expenses">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Order Expenses</h2>

            @if($order->orderExpenses->isNotEmpty())
                <div class="overflow-x-auto mb-4">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <th class="py-2 pr-4 text-left">Kategori</th>
                                <th class="py-2 pr-4 text-left">Deskripsi</th>
                                <th class="py-2 pr-4 text-right">Jumlah</th>
                                <th class="py-2 pr-4 text-left">Foto</th>
                                <th class="py-2 pr-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($order->orderExpenses as $expense)
                                <tr>
                                    <td class="py-2 pr-4 text-gray-900 dark:text-gray-100">
                                        {{ \App\Models\OrderExpense::getCategoryLabel($expense->expense_category) }}
                                    </td>
                                    <td class="py-2 pr-4 text-gray-900 dark:text-gray-100">
                                        {{ $expense->description ?? '-' }}
                                    </td>
                                    <td class="py-2 pr-4 text-right text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format($expense->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        @if($expense->receipt_photo)
                                            <a href="{{ asset('storage/'.$expense->receipt_photo) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Lihat foto</a>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4 text-right">
                                        @if(auth()->user()->hasPermission('delete-order-expenses') || auth()->user()->hasPermission('edit-orders'))
                                            <button type="submit"
                                                    form="delete-expense-{{ $expense->id }}"
                                                    class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                Hapus
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tambah Pengeluaran Baru (dengan upload foto kwitansi)</p>
            <div id="expense-list" class="space-y-3 mb-4"></div>
            <button type="button" onclick="addExpenseRow()" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs mb-4">+ Tambah Pengeluaran</button>

            <x-button type="primary">Simpan Expenses</x-button>
        </div>
    </form>
    @endif

    {{-- Form 6: E-Toll (tambah baris baru + upload foto) --}}
    @if(auth()->user()->hasPermission('create-order-etoll') || auth()->user()->hasPermission('edit-orders'))
    <form action="{{ route('orders.update', $order) }}" method="POST" enctype="multipart/form-data" class="mb-8" id="form-etoll">
        @csrf
        @method('PUT')
        <input type="hidden" name="update_section" value="order_etoll">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">E-Toll</h2>

            @if($order->orderEtollTransactions->isNotEmpty())
                <div class="overflow-x-auto mb-4">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                                <th class="py-2 pr-4 text-right">Bayar Per Pintu Tol</th>
                                <th class="py-2 pr-4 text-left">Foto</th>
                                <th class="py-2 pr-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($order->orderEtollTransactions as $trx)
                                @php
                                    $paidPerGate = $trx->usage_amount;
                                    if ($paidPerGate === null && $trx->balance_before !== null && $trx->balance_after !== null) {
                                        $paidPerGate = max(0, (float) $trx->balance_before - (float) $trx->balance_after);
                                    }
                                @endphp
                                <tr>
                                    <td class="py-2 pr-4 text-right text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format($paidPerGate ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        @if($trx->receipt_photo ?? null)
                                            <a href="{{ asset('storage/'.$trx->receipt_photo) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Lihat foto</a>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4 text-right">
                                        @if(auth()->user()->hasPermission('delete-order-etoll') || auth()->user()->hasPermission('edit-orders'))
                                            <button type="submit"
                                                    form="delete-etoll-{{ $trx->id }}"
                                                    class="text-xs text-red-600 dark:text-red-400 hover:underline">
                                                Hapus
                                            </button>
                                        @else
                                            <span class="text-gray-400 text-xs">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tambah Transaksi E-Toll Baru (bayar per pintu tol + upload foto struk)</p>
            <div id="etoll-list" class="space-y-3 mb-4"></div>
            <button type="button" onclick="addEtollRow()" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs mb-4">+ Tambah Transaksi</button>

            <x-button type="primary">Simpan E-Toll</x-button>
        </div>
    </form>
    @endif

    {{-- Section: Vehicle Issues (paling bawah) + form tambah dengan upload foto --}}
    @if(auth()->user()->hasPermission('view-order-vehicle-issues') || auth()->user()->hasPermission('show-order-vehicle-issues') || auth()->user()->hasPermission('create-order-vehicle-issues') || auth()->user()->hasPermission('edit-order-vehicle-issues') || auth()->user()->hasPermission('edit-orders'))
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-8">
        <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Vehicle Issues</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Daftar vehicle issue untuk order ini. Tambah baru dengan form di bawah (termasuk upload foto).</p>

        @if($order->orderVehicleIssues->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Belum ada vehicle issue.</p>
        @else
            <ul class="space-y-2 mb-6">
                @foreach($order->orderVehicleIssues as $issue)
                    <li class="flex items-center gap-3 py-2 border-b border-gray-200 dark:border-gray-700 last:border-0">
                        <span class="text-gray-700 dark:text-gray-300">{{ \App\Models\OrderVehicleIssue::getCategoryLabel($issue->issue_category ?? '') }} – {{ \Illuminate\Support\Str::limit($issue->description, 40) }}</span>
                        <a href="{{ route('order-vehicle-issues.show', $issue) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">Lihat</a>
                        @if(auth()->user()->hasPermission('edit-order-vehicle-issues') || auth()->user()->hasPermission('edit-orders'))
                            <a href="{{ route('order-vehicle-issues.edit', $issue) }}" class="text-gray-600 dark:text-gray-400 hover:underline text-sm">Edit</a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif

        @if(auth()->user()->hasPermission('create-order-vehicle-issues') || auth()->user()->hasPermission('edit-orders'))
        <form action="{{ route('order-vehicle-issues.store', $order) }}?from=edit" method="POST" enctype="multipart/form-data" class="border-t border-gray-200 dark:border-gray-700 pt-6">
            @csrf
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Tambah Vehicle Issue (dengan upload foto)</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori <span class="text-red-500">*</span></label>
                    <select name="issue_category" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="mechanical">Mechanical</option>
                        <option value="body">Body/Exterior</option>
                        <option value="interior">Interior</option>
                        <option value="safety">Safety Equipment</option>
                        <option value="medical_equipment">Medical Equipment</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Prioritas <span class="text-red-500">*</span></label>
                    <select name="priority" required class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="urgent">Urgent</option>
                    </select>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi <span class="text-red-500">*</span></label>
                <textarea name="description" required rows="2" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" placeholder="Deskripsi issue"></textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Issue</label>
                    <input type="file" name="issue_photo" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto Perbaikan</label>
                    <input type="file" name="repair_photo" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700">
                </div>
            </div>
            <x-button type="primary">Tambah Vehicle Issue</x-button>
        </form>
        @endif
    </div>
    @endif

    @foreach($order->orderPhotos as $photo)
        <form id="delete-photo-{{ $photo->id }}" action="{{ route('order-photos.destroy', $photo) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    @if($order->orderReport ?? null)
        <form id="delete-order-report" action="{{ route('order-report.destroy', $order) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif

    @foreach($order->orderExpenses as $expense)
        <form id="delete-expense-{{ $expense->id }}" action="{{ route('order-expenses.destroy', $expense) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    @foreach($order->orderEtollTransactions as $trx)
        <form id="delete-etoll-{{ $trx->id }}" action="{{ route('order-etoll.destroy', $trx) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <script>
        const units = @json($units);
        const employees = @json($employees);
        const employeeAvailability = @json($employeeAvailability);
        let photoCount = 0;
        let expenseCount = 0;
        let etollCount = 0;

        function populateUnits() {
            const divisionSelect = document.getElementById('division_id');
            const unitSelect = document.getElementById('unit_code');
            const divisionId = parseInt(divisionSelect.value);
            const currentUnit = @json(old('unit_code', $order->unit_code));

            unitSelect.innerHTML = '<option value="">Select Unit</option>';

            if (divisionId && !isNaN(divisionId)) {
                const filteredUnits = units.filter(unit => Number(unit.division_id) === Number(divisionId));
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

        if (document.getElementById('division_id').value) {
            populateUnits();
        }

        function getCrewIndicator(emp) {
            if ((emp.status || '').toLowerCase() !== 'active') {
                return '⚫';
            }
            const pickupValue = document.getElementById('pickup_datetime')?.value || @js($order->pickup_datetime->format('Y-m-d\TH:i'));
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
            crewItem.className = 'flex gap-3 items-start crew-row';
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
                <button type="button" onclick="this.closest('.crew-row').remove()" class="px-3 py-2 bg-red-600 text-white rounded hover:bg-red-700 text-xs">Remove</button>
            `;
            crewList.appendChild(crewItem);
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

        function addExpenseRow() {
            const container = document.getElementById('expense-list');
            const idx = expenseCount++;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-4 gap-3 border border-gray-200 dark:border-gray-700 rounded-md p-3';
            row.innerHTML = `
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Kategori</label>
                    <select name="expenses[${idx}][expense_category]" class="block w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100">
                        <option value="solar">Solar/BBM</option>
                        <option value="e-toll">E-Toll</option>
                        <option value="parkir">Parkir</option>
                        <option value="tol_manual">Tol Manual</option>
                        <option value="makan">Makan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Deskripsi</label>
                    <input type="text" name="expenses[${idx}][description]" class="block w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah</label>
                    <input type="number" step="0.01" min="0" name="expenses[${idx}][amount]" class="block w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Foto kwitansi</label>
                    <input type="file" name="expenses[${idx}][receipt_photo]" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700" />
                </div>
            `;
            container.appendChild(row);
        }

        function addEtollRow() {
            const container = document.getElementById('etoll-list');
            const idx = etollCount++;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-2 gap-3 border border-gray-200 dark:border-gray-700 rounded-md p-3';
            row.innerHTML = `
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Bayar Per Pintu Tol</label>
                    <input type="number" step="0.01" min="0" name="etolls[${idx}][amount]" class="block w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" placeholder="Rp" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Foto struk e-toll</label>
                    <input type="file" name="etolls[${idx}][receipt_photo]" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700" />
                </div>
            `;
            container.appendChild(row);
        }
    </script>
</x-layouts.app>
