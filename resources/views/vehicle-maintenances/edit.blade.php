<x-layouts.app>
    <div class="mb-6 flex items-center text-sm">
        <a href="{{ route('dashboard') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Dashboard</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <a href="{{ route('vehicle-maintenances.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline">Vehicle Maintenance</a>
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mx-2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="text-gray-500 dark:text-gray-400">Edit</span>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Edit Vehicle Maintenance #{{ $vehicleMaintenance->id }}</h1>
    </div>

    @php
        $selectedIdentification = old('identification_pic_ids', $vehicleMaintenance->identificationPics->pluck('employee_id')->toArray());
        $selectedRepair = old('repair_pic_ids', $vehicleMaintenance->repairPics->pluck('employee_id')->toArray());
        $identificationInitial = collect($selectedIdentification)->map(function ($id) use ($vehicleMaintenance) {
            $pic = $vehicleMaintenance->identificationPics->firstWhere('employee_id', (int) $id);
            return [
                'id' => (int) $id,
                'name' => $pic?->employee?->full_name
                    ? trim($pic->employee->full_name . ($pic->employee?->position?->nama ? ' - '.$pic->employee->position->nama : ''))
                    : 'Employee #'.$id,
            ];
        })->values();
        $repairInitial = collect($selectedRepair)->map(function ($id) use ($vehicleMaintenance) {
            $pic = $vehicleMaintenance->repairPics->firstWhere('employee_id', (int) $id);
            return [
                'id' => (int) $id,
                'name' => $pic?->employee?->full_name
                    ? trim($pic->employee->full_name . ($pic->employee?->position?->nama ? ' - '.$pic->employee->position->nama : ''))
                    : 'Employee #'.$id,
            ];
        })->values();
    @endphp

    <form method="POST" action="{{ route('vehicle-maintenances.update', $vehicleMaintenance) }}" class="space-y-6"
        x-data="vehiclePicForm({ identificationInitial: @js($identificationInitial), repairInitial: @js($repairInitial) })">
        @csrf
        @method('PUT')
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Informasi Umum</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Tipe Maintenance</label>
                    <select name="maintenance_type" required class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">
                        @foreach(\App\Models\VehicleMaintenance::TYPE_OPTIONS as $value => $label)
                            <option value="{{ $value }}" @selected(old('maintenance_type', $vehicleMaintenance->maintenance_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">Referensi Order (optional)</label>
                    <select name="order_id" class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">
                        <option value="">-</option>
                        @foreach($orders as $order)
                            <option value="{{ $order->id }}" @selected((string) old('order_id', $vehicleMaintenance->order_id) === (string) $order->id)>
                                {{ $order->order_number }} - {{ $order->customer_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">Unit</label>
                    <select name="unit_id" required class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) old('unit_id', $vehicleMaintenance->unit_id) === (string) $unit->id)>{{ $unit->code }} - {{ $unit->division?->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm mb-1">Status</label>
                    <select name="order_status_id" required class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700">
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @selected((string) old('order_status_id', $vehicleMaintenance->order_status_id) === (string) $status->id)>{{ $status->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Identifikasi Kerusakan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-forms.input label="ODO / Kilometer Identifikasi" name="odo_identification" type="number" step="0.01" value="{{ old('odo_identification', $vehicleMaintenance->odo_identification) }}" />
                <x-forms.input label="Waktu Teridentifikasi" name="identified_at" type="datetime-local" value="{{ old('identified_at', $vehicleMaintenance->identified_at?->format('Y-m-d\TH:i')) }}" />
            </div>
            <div>
                <label class="block text-sm mb-1">Deskripsi</label>
                <textarea
                    name="damage_description"
                    rows="5"
                    class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700"
                    placeholder="- Indikator layar muncul check perawatan&#10;- Ganti oli di 150.480 km&#10;- Cek rem depan&#10;- Butuh Perbarui KIR Segera"
                >{{ old('damage_description', $vehicleMaintenance->damage_description) }}</textarea>
                <ul class="mt-2 list-disc pl-5 text-xs text-gray-500">
                    <li>Tulis per poin di baris baru.</li>
                    <li>Boleh awali dengan `-`, `*`, atau `•`.</li>
                </ul>
            </div>
            <div>
                <label class="block text-sm mb-1">PIC Identifikasi (bisa lebih dari 1)</label>
                <div class="relative">
                    <input
                        type="text"
                        x-model="identification.query"
                        @input.debounce.300ms="search('identification')"
                        placeholder="Cari nama / NIK / HP / email..."
                        class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700"
                    >
                    <div x-show="identification.loading" class="mt-1 text-xs text-gray-500">Searching...</div>
                    <div x-show="identification.results.length > 0" class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-56 overflow-auto">
                        <template x-for="item in identification.results" :key="item.id">
                            <button type="button" @click="add('identification', item)" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700" x-text="item.name"></button>
                        </template>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Maksimal 2 PIC.</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="item in identification.selected" :key="item.id">
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-md text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <span x-text="item.name"></span>
                            <button type="button" @click="remove('identification', item.id)">x</button>
                            <input type="hidden" name="identification_pic_ids[]" :value="item.id">
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6 space-y-4">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Perbaikan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-forms.input label="ODO / Kilometer Perbaikan" name="odo_repair" type="number" step="0.01" value="{{ old('odo_repair', $vehicleMaintenance->odo_repair) }}" />
                <x-forms.input label="Waktu Perbaikan" name="repaired_at" type="datetime-local" value="{{ old('repaired_at', $vehicleMaintenance->repaired_at?->format('Y-m-d\TH:i')) }}" />
            </div>
            <div>
                <label class="block text-sm mb-1">PIC Perbaikan (bisa lebih dari 1)</label>
                <div class="relative">
                    <input
                        type="text"
                        x-model="repair.query"
                        @input.debounce.300ms="search('repair')"
                        placeholder="Cari nama / NIK / HP / email..."
                        class="w-full px-3 py-2 border rounded-md bg-white dark:bg-gray-700"
                    >
                    <div x-show="repair.loading" class="mt-1 text-xs text-gray-500">Searching...</div>
                    <div x-show="repair.results.length > 0" class="absolute z-20 mt-1 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg max-h-56 overflow-auto">
                        <template x-for="item in repair.results" :key="item.id">
                            <button type="button" @click="add('repair', item)" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700" x-text="item.name"></button>
                        </template>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Maksimal 2 PIC.</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <template x-for="item in repair.selected" :key="item.id">
                        <span class="inline-flex items-center gap-2 px-2 py-1 rounded-md text-xs bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            <span x-text="item.name"></span>
                            <button type="button" @click="remove('repair', item.id)">x</button>
                            <input type="hidden" name="repair_pic_ids[]" :value="item.id">
                        </span>
                    </template>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex justify-between items-center mb-3">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Detail Perbaikan/Perawatan dan Biaya</h2>
                <button type="button" onclick="addCostRow()" class="px-3 py-1 bg-blue-600 text-white rounded text-xs">+ Add Biaya</button>
            </div>
            <div id="cost-list" class="space-y-3">
                @php $initial = old('cost_details', $vehicleMaintenance->costDetails->map(fn($d)=>['type'=>$d->type,'description'=>$d->description,'amount'=>$d->amount])->toArray()); @endphp
                @foreach($initial as $i => $detail)
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-3 border border-gray-200 dark:border-gray-700 rounded-md p-3">
                        <div>
                            <label class="block text-xs mb-1">Tipe</label>
                            <select name="cost_details[{{ $i }}][type]" class="w-full px-2 py-1.5 border rounded-md bg-white dark:bg-gray-700">
                                @foreach(\App\Models\VehicleMaintenanceCostDetail::TYPE_OPTIONS as $type => $label)
                                    <option value="{{ $type }}" @selected(($detail['type'] ?? null) === $type)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-xs mb-1">Deskripsi</label>
                            <input type="text" name="cost_details[{{ $i }}][description]" value="{{ $detail['description'] ?? '' }}" class="w-full px-2 py-1.5 border rounded-md bg-white dark:bg-gray-700" />
                        </div>
                        <div>
                            <label class="block text-xs mb-1">Biaya</label>
                            <input type="number" step="0.01" min="0" name="cost_details[{{ $i }}][amount]" value="{{ $detail['amount'] ?? '' }}" class="w-full px-2 py-1.5 border rounded-md bg-white dark:bg-gray-700" />
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex gap-2">
            <x-button type="primary">Update</x-button>
            <a href="{{ route('vehicle-maintenances.show', $vehicleMaintenance) }}"><x-button type="secondary">Back</x-button></a>
        </div>
    </form>

    <script>
        function vehiclePicForm({ identificationInitial = [], repairInitial = [] }) {
            return {
                identification: { query: '', loading: false, results: [], selected: identificationInitial },
                repair: { query: '', loading: false, results: [], selected: repairInitial },
                async search(target) {
                    const state = this[target];
                    if (state.query.length < 1) {
                        state.results = [];
                        return;
                    }
                    state.loading = true;
                    try {
                        const response = await fetch(`{{ route('vehicle-maintenances.pic-search') }}?q=${encodeURIComponent(state.query)}`);
                        if (!response.ok) {
                            state.results = [];
                            return;
                        }
                        const data = await response.json();
                        state.results = (Array.isArray(data) ? data : []).filter(
                            item => !state.selected.some(sel => Number(sel.id) === Number(item.id))
                        );
                    } catch (e) {
                        state.results = [];
                    } finally {
                        state.loading = false;
                    }
                },
                add(target, item) {
                    const state = this[target];
                    if (state.selected.length >= 2) {
                        alert('Maksimal 2 PIC untuk tiap bagian.');
                        return;
                    }
                    if (state.selected.some(sel => Number(sel.id) === Number(item.id))) return;
                    state.selected.push(item);
                    state.query = '';
                    state.results = [];
                },
                remove(target, id) {
                    const state = this[target];
                    state.selected = state.selected.filter(item => Number(item.id) !== Number(id));
                }
            };
        }

        let costCount = {{ count($initial) }};
        function addCostRow() {
            const container = document.getElementById('cost-list');
            const idx = costCount++;
            const row = document.createElement('div');
            row.className = 'grid grid-cols-1 md:grid-cols-4 gap-3 border border-gray-200 dark:border-gray-700 rounded-md p-3';
            row.innerHTML = `
                <div>
                    <label class="block text-xs mb-1">Tipe</label>
                    <select name="cost_details[${idx}][type]" class="w-full px-2 py-1.5 border rounded-md bg-white dark:bg-gray-700">
                        <option value="JASA">JASA</option>
                        <option value="BARANG">BARANG</option>
                        <option value="LAINNYA">LAINNYA</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs mb-1">Deskripsi</label>
                    <input type="text" name="cost_details[${idx}][description]" class="w-full px-2 py-1.5 border rounded-md bg-white dark:bg-gray-700" />
                </div>
                <div>
                    <label class="block text-xs mb-1">Biaya</label>
                    <input type="number" step="0.01" min="0" name="cost_details[${idx}][amount]" class="w-full px-2 py-1.5 border rounded-md bg-white dark:bg-gray-700" />
                </div>
            `;
            container.appendChild(row);
        }
    </script>
</x-layouts.app>
