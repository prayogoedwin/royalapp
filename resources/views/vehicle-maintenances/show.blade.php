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
        <span class="text-gray-500 dark:text-gray-400">Detail</span>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-100">Vehicle Maintenance #{{ $vehicleMaintenance->id }}</h1>
        </div>
        <div class="flex gap-2">
            @if(auth()->user()->hasPermission('edit-vehicle-maintenances'))
                <a href="{{ route('vehicle-maintenances.edit', $vehicleMaintenance) }}"><x-button type="primary">Edit</x-button></a>
            @endif
            <a href="{{ route('vehicle-maintenances.index') }}"><x-button type="secondary">Back</x-button></a>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="font-semibold mb-3 text-gray-800 dark:text-gray-100">Identifikasi Kerusakan</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">Tipe</dt><dd class="text-gray-900 dark:text-gray-100">{{ $vehicleMaintenance->maintenance_type }}</dd></div>
                    <div><dt class="text-gray-500">Order Ref</dt><dd class="text-gray-900 dark:text-gray-100">{{ $vehicleMaintenance->order?->order_number ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Unit</dt><dd class="text-gray-900 dark:text-gray-100">{{ $vehicleMaintenance->unit?->code ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd class="text-gray-900 dark:text-gray-100">{{ $vehicleMaintenance->orderStatus?->name ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">ODO Identifikasi</dt><dd class="text-gray-900 dark:text-gray-100">{{ $vehicleMaintenance->odo_identification ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Waktu Teridentifikasi</dt><dd class="text-gray-900 dark:text-gray-100">{{ $vehicleMaintenance->identified_at?->format('d M Y H:i') ?? '-' }}</dd></div>
                </dl>
                <div class="mt-4">
                    <h3 class="font-medium text-gray-800 dark:text-gray-100 mb-1">Deskripsi</h3>
                    @php
                        $damageItems = collect(preg_split('/\r\n|\r|\n/', (string) $vehicleMaintenance->damage_description))
                            ->map(fn($line) => trim((string) $line))
                            ->map(fn($line) => preg_replace('/^[-*•]\s*/u', '', $line))
                            ->filter()
                            ->values();
                    @endphp
                    @if($damageItems->isNotEmpty())
                        <ul class="list-disc pl-5 text-sm">
                            @foreach($damageItems as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-sm text-gray-500">-</p>
                    @endif
                </div>
                <div class="mt-4">
                    <h3 class="font-medium text-gray-800 dark:text-gray-100 mb-1">PIC Identifikasi</h3>
                    <ul class="list-disc pl-5 text-sm">
                        @forelse($vehicleMaintenance->identificationPics as $pic)
                            <li>{{ $pic->employee?->full_name ?? '-' }}{{ $pic->employee?->position?->nama ? ' - '.$pic->employee?->position?->nama : '' }}</li>
                        @empty
                            <li>-</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="font-semibold mb-3 text-gray-800 dark:text-gray-100">Perbaikan</h2>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                    <div><dt class="text-gray-500">ODO Perbaikan</dt><dd class="text-gray-900 dark:text-gray-100">{{ $vehicleMaintenance->odo_repair ?? '-' }}</dd></div>
                    <div><dt class="text-gray-500">Waktu Perbaikan</dt><dd class="text-gray-900 dark:text-gray-100">{{ $vehicleMaintenance->repaired_at?->format('d M Y H:i') ?? '-' }}</dd></div>
                </dl>
                <div class="mt-4">
                    <h3 class="font-medium text-gray-800 dark:text-gray-100 mb-1">PIC Perbaikan</h3>
                    <ul class="list-disc pl-5 text-sm">
                        @forelse($vehicleMaintenance->repairPics as $pic)
                            <li>{{ $pic->employee?->full_name ?? '-' }}{{ $pic->employee?->position?->nama ? ' - '.$pic->employee?->position?->nama : '' }}</li>
                        @empty
                            <li>-</li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        <div class="xl:col-span-1">
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-5">
                <h2 class="font-semibold text-gray-800 dark:text-gray-100 mb-3">Detail Perbaikan/Perawatan dan Biaya</h2>
                <div class="space-y-2">
                    @forelse($vehicleMaintenance->costDetails as $detail)
                        <div class="border border-gray-200 dark:border-gray-700 rounded p-2 text-sm">
                            <div class="font-medium">{{ $detail->type }}</div>
                            @if($detail->description)<div class="text-gray-600 dark:text-gray-300">{{ $detail->description }}</div>@endif
                            <div class="text-gray-900 dark:text-gray-100">Rp {{ number_format((float) $detail->amount, 0, ',', '.') }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">-</p>
                    @endforelse
                </div>
                <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="text-sm text-gray-500">Total Biaya</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Rp {{ number_format((float) $vehicleMaintenance->total_cost, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
