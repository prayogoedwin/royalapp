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
            @php
                $canAccessEdit = auth()->user()->hasPermission('edit-orders')
                    || auth()->user()->hasPermission('edit-order-report')
                    || auth()->user()->hasPermission('create-order-expenses')
                    || auth()->user()->hasPermission('create-order-etoll')
                    || auth()->user()->hasPermission('create-order-photos')
                    || auth()->user()->hasPermission('create-order-vehicle-issues')
                    || auth()->user()->hasPermission('edit-order-vehicle-issues');
            @endphp
            @if($canAccessEdit)
                <a href="{{ route('orders.edit', $order) }}">
                    <x-button type="secondary">
                        {{ auth()->user()->hasPermission('edit-orders') ? 'Edit Order' : 'Input Data Driver' }}
                    </x-button>
                </a>
            @endif
        </div>
    </div>

    <div class="space-y-6">
        <div class="space-y-6">
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

            {{-- Order Report --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Order Report</h2>
                    @if(!$order->orderReport)
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada order report untuk order ini.</p>
                    @else
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">KM Awal</dt>
                                <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->orderReport->km_awal }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">KM Akhir</dt>
                                <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->orderReport->km_akhir }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total KM</dt>
                                <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->orderReport->km_total }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                                <dd class="text-base text-gray-900 dark:text-gray-100 capitalize">{{ $order->orderReport->status }}</dd>
                            </div>
                            @if($order->orderReport->submitted_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Submitted At</dt>
                                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->orderReport->submitted_at?->format('d M Y H:i') }}</dd>
                                </div>
                            @endif
                            @if($order->orderReport->approved_at)
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Approved At</dt>
                                    <dd class="text-base text-gray-900 dark:text-gray-100">{{ $order->orderReport->approved_at?->format('d M Y H:i') }}</dd>
                                </div>
                            @endif
                            @if($order->orderReport->notes)
                                <div class="md:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Notes</dt>
                                    <dd class="text-base text-gray-900 dark:text-gray-100 whitespace-pre-line">{{ $order->orderReport->notes }}</dd>
                                </div>
                            @endif
                        </dl>
                    @endif
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

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Photos</h2>
                    @if($order->orderPhotos->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada foto untuk order ini.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($order->orderPhotos as $photo)
                                <div class="space-y-2">
                                    <img src="{{ asset('storage/'.$photo->path) }}"
                                         alt="{{ $photo->title }}"
                                         class="rounded-md w-full object-cover max-h-40 bg-gray-100 dark:bg-gray-900">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $photo->title }}</p>
                                        @if($photo->description)
                                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $photo->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            {{-- Order Expenses --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Order Expenses</h2>
                    @if($order->orderExpenses->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada pengeluaran untuk order ini.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 text-xs uppercase text-gray-500 dark:text-gray-400">
                                        <th class="py-2 pr-4 text-left">Kategori</th>
                                        <th class="py-2 pr-4 text-left">Deskripsi</th>
                                        <th class="py-2 pr-4 text-right">Jumlah</th>
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- E-Toll Transactions --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">E-Toll</h2>
                    @if($order->orderEtollTransactions->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada transaksi e-toll untuk order ini.</p>
                    @else
                        <div class="overflow-x-auto">
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
                </div>
            </div>
        </div>

        {{-- Vehicle Issues, susunan sama seperti di halaman edit, ditaruh di bawah --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-4">Vehicle Issues</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Daftar vehicle issue untuk order ini.</p>

                @if($order->orderVehicleIssues->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada vehicle issue.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($order->orderVehicleIssues as $issue)
                            <li class="flex items-center gap-3 py-2 border-b border-gray-200 dark:border-gray-700 last:border-0">
                                <span class="text-gray-700 dark:text-gray-300">
                                    {{ \App\Models\OrderVehicleIssue::getCategoryLabel($issue->issue_category ?? '') }} – {{ \Illuminate\Support\Str::limit($issue->description, 40) }}
                                </span>
                                <a href="{{ route('order-vehicle-issues.show', $issue) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">Lihat</a>
                                @if(auth()->user()->hasPermission('edit-orders'))
                                    <a href="{{ route('order-vehicle-issues.edit', $issue) }}" class="text-gray-600 dark:text-gray-400 hover:underline text-sm">Edit</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
