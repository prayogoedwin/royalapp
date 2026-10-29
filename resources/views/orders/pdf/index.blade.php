<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; }
        h1 { font-size: 14px; margin: 0 0 6px 0; }
        .meta { margin-bottom: 12px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        .nowrap { white-space: nowrap; }
        .num { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Daftar Pesanan</h1>
    <div class="meta">
        @if(!empty($statusFilter))
            Status: {{ $statusFilter }}
        @else
            Status: Semua status
        @endif
        &nbsp;|&nbsp;
        Periode: {{ $periodLabel }}
        @if(!empty($customerPhone))
            &nbsp;|&nbsp; Nomor WA: {{ $customerPhone }}
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="nowrap">No</th>
                <th>No. Pesanan</th>
                <th>Pelanggan</th>
                <th>Divisi</th>
                <th class="nowrap">Penjemputan</th>
                <th class="num">Harga</th>
                <th>Status</th>
                <th>Pembuat</th>
                <th class="nowrap">Dibuat Pada</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $index => $order)
                @php
                    $creator = trim((string) ($order->createdBy?->name ?? ''));
                @endphp
                <tr>
                    <td class="nowrap">{{ $index + 1 }}</td>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer_name }}</td>
                    <td>{{ $order->division?->nama ?? '-' }}</td>
                    <td class="nowrap">{{ $order->pickup_datetime?->format('d M Y H:i') ?? '-' }}</td>
                    <td class="num">Rp {{ number_format((int) $order->price, 0, ',', '.') }}</td>
                    <td>{{ $order->orderStatus?->name ?? '-' }}</td>
                    <td>{{ $creator !== '' ? $creator : '-' }}</td>
                    <td class="nowrap">{{ $order->created_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Belum ada pesanan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
