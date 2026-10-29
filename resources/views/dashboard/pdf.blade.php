<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h1 { font-size: 16px; margin: 0 0 6px 0; }
        h2 { font-size: 13px; margin: 16px 0 8px 0; }
        .meta { margin-bottom: 14px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        .num { text-align: right; }
    </style>
</head>
<body>
    <h1>Laporan Dashboard</h1>
    <div class="meta">Periode: {{ $periodLabel }}</div>

    <h2>Ringkasan</h2>
    <table>
        <thead>
            <tr>
                <th>Metrik</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            <tr><td>Total Users</td><td class="num">{{ number_format($total_users) }}</td></tr>
            <tr><td>Total Orders</td><td class="num">{{ number_format($total_orders) }}</td></tr>
            <tr><td>Total Tasks</td><td class="num">{{ number_format($total_tasks) }}</td></tr>
        </tbody>
    </table>

    <h2>Status Order</h2>
    <table>
        <thead>
            <tr>
                <th>Status</th>
                <th class="num">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order_statuses as $status)
                <tr>
                    <td>{{ $status['name'] }}</td>
                    <td class="num">{{ number_format($status['total']) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="2">Belum ada status order.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
