<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 14px; margin: 0 0 6px 0; }
        .meta { margin-bottom: 12px; color: #444; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; vertical-align: top; }
        th { background: #f5f5f5; text-align: left; }
        .nowrap { white-space: nowrap; }
    </style>
</head>
<body>
    <h1>Absensi Semua Pegawai</h1>
    <div class="meta">
        Tanggal: {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th class="nowrap">No</th>
                <th>Pegawai</th>
                <th>Pool</th>
                <th class="nowrap">Jam Masuk</th>
                <th class="nowrap">Jam Pulang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td class="nowrap">{{ $index + 1 }}</td>
                    <td>{{ $row['pegawai'] }}</td>
                    <td>{{ $row['pool'] }}</td>
                    <td class="nowrap">{{ $row['jam_masuk'] }}</td>
                    <td class="nowrap">{{ $row['jam_pulang'] }}</td>
                    <td>{{ $row['status'] ? ($statusOptions[$row['status']] ?? $row['status']) : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada data pegawai.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
