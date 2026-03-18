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
    <form action="{{ route('orders.update', $order) }}" method="POST" class="space-y-6 mb-8">
        @csrf
        @method('PUT')
        <input type="hidden" name="update_section" value="basic">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-6">
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

    {{-- Form 2: Crew --}}
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
            <div id="crew-list" class="space-y-3">
                @foreach($order->orderCrews as $crew)
                    <div class="flex gap-3 items-start crew-row">
                        <div class="flex-1">
                            <select name="crew_ids[]" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" @selected($crew->employee_id == $employee->id)>
                                        {{ $employee->full_name }} - {{ $employee->position->nama ?? '' }}
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

    {{-- Form 3: New Photos --}}
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
                            <button type="submit" form="delete-photo-{{ $photo->id }}" class="text-xs text-red-600 dark:text-red-400 hover:underline">Delete</button>
                        </div>
                    @endforeach
                </div>
            @endif

            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Add New Photos</p>
            <div id="photo-list" class="space-y-3 mb-4"></div>
            <button type="button" onclick="addPhoto()" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs mb-4">+ Add Photo</button>

            <x-button type="primary">Simpan Foto</x-button>
        </div>
    </form>

    {{-- Form 4: Order Report --}}
    <form action="{{ route('orders.update', $order) }}" method="POST" class="mb-8">
        @csrf
        @method('PUT')
        <input type="hidden" name="update_section" value="order_report">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Order Report</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <x-forms.input
                        label="KM Awal"
                        name="km_awal"
                        type="number"
                        step="0.01"
                        value="{{ old('km_awal', $order->orderReport->km_awal ?? null) }}"
                    />
                </div>
                <div>
                    <x-forms.input
                        label="KM Akhir"
                        name="km_akhir"
                        type="number"
                        step="0.01"
                        value="{{ old('km_akhir', $order->orderReport->km_akhir ?? null) }}"
                    />
                </div>
                <div>
                    <x-forms.input
                        label="Status"
                        name="status"
                        type="text"
                        value="{{ old('status', $order->orderReport->status ?? 'draft') }}"
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

            <x-button type="primary">Update Order Report</x-button>
        </div>
    </form>

    {{-- Form 5: Order Expenses (tambah baris baru + upload foto) --}}
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

    {{-- Form 6: E-Toll (tambah baris baru + upload foto) --}}
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
                                <th class="py-2 pr-4 text-right">Saldo Sebelum</th>
                                <th class="py-2 pr-4 text-right">Saldo Sesudah</th>
                                <th class="py-2 pr-4 text-left">Foto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($order->orderEtollTransactions as $trx)
                                <tr>
                                    <td class="py-2 pr-4 text-right text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format($trx->balance_before ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2 pr-4 text-right text-gray-900 dark:text-gray-100">
                                        Rp {{ number_format($trx->balance_after ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-2 pr-4">
                                        @if($trx->receipt_photo ?? null)
                                            <a href="{{ asset('storage/'.$trx->receipt_photo) }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Lihat foto</a>
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

            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tambah Transaksi E-Toll Baru (dengan upload foto)</p>
            <div id="etoll-list" class="space-y-3 mb-4"></div>
            <button type="button" onclick="addEtollRow()" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 text-xs mb-4">+ Tambah Transaksi</button>

            <x-button type="primary">Simpan E-Toll</x-button>
        </div>
    </form>

    {{-- Section: Vehicle Issues (paling bawah) + form tambah dengan upload foto --}}
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
                        <a href="{{ route('order-vehicle-issues.edit', $issue) }}" class="text-gray-600 dark:text-gray-400 hover:underline text-sm">Edit</a>
                    </li>
                @endforeach
            </ul>
        @endif

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
    </div>

    @foreach($order->orderPhotos as $photo)
        <form id="delete-photo-{{ $photo->id }}" action="{{ route('order-photos.destroy', $photo) }}" method="POST" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <script>
        const units = @json($units);
        const employees = @json($employees);
        let photoCount = 0;
        let expenseCount = 0;
        let etollCount = 0;

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

        function addCrew() {
            const crewList = document.getElementById('crew-list');
            const crewItem = document.createElement('div');
            crewItem.className = 'flex gap-3 items-start crew-row';
            crewItem.innerHTML = `
                <div class="flex-1">
                    <select name="crew_ids[]" class="block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100">
                        <option value="">Select Employee</option>
                        ${employees.map(emp => `<option value="${emp.id}">${emp.full_name} - ${emp.position?.nama ?? ''}</option>`).join('')}
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
            row.className = 'grid grid-cols-1 md:grid-cols-3 gap-3 border border-gray-200 dark:border-gray-700 rounded-md p-3';
            row.innerHTML = `
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Saldo Sebelum</label>
                    <input type="number" step="0.01" min="0" name="etolls[${idx}][balance_before]" class="block w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" placeholder="Rp" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Saldo Sesudah</label>
                    <input type="number" step="0.01" min="0" name="etolls[${idx}][balance_after]" class="block w-full px-2 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-gray-100" placeholder="Rp" />
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Foto receipt e-toll</label>
                    <input type="file" name="etolls[${idx}][receipt_photo]" accept="image/*" class="block w-full text-sm text-gray-900 dark:text-gray-100 border border-gray-300 dark:border-gray-600 rounded-md cursor-pointer bg-white dark:bg-gray-700" />
                </div>
            `;
            container.appendChild(row);
        }
    </script>
</x-layouts.app>
