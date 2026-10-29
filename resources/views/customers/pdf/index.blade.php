<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 14px; margin: 0 0 6px 0; }
        .meta { margin-bottom: 12px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 5px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        .nowrap { white-space: nowrap; }
        .num { text-align: right; white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Daftar Customer</h1>
    <div class="meta">Dikelompokkan berdasarkan nomor HP di order.</div>

    <table>
        <thead>
            <tr>
                <th class="nowrap">No</th>
                <th>Nama</th>
                <th>No. WA / HP</th>
                <th class="num">Jumlah Order</th>
                <th class="nowrap">Order terakhir</th>
            </tr>
        </thead>
        <tbody>
            @forelse($customers as $index => $row)
                @php
                    $phone = (string) ($row->customer_phone ?? '');
                    $display = \App\Support\WhatsAppLink::localDisplay($phone) ?: $phone;
                @endphp
                <tr>
                    <td class="nowrap">{{ $index + 1 }}</td>
                    <td>{{ $row->customer_name ?: '-' }}</td>
                    <td>{{ $display !== '' ? $display : '-' }}</td>
                    <td class="num">{{ number_format((int) $row->total_orders) }}</td>
                    <td class="nowrap">{{ $row->last_order_at ? \Carbon\Carbon::parse($row->last_order_at)->format('d M Y H:i') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada customer.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
