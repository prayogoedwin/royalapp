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
        .muted { color: #666; }
    </style>
</head>
<body>
    <h1>Presensi - {{ $employee->full_name }}</h1>
    <div class="meta">
        <div>NIK: {{ $employee->nik }}</div>
        <div>Bulan: {{ strtoupper($month) }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="nowrap">Tanggal</th>
                <th class="nowrap">Jam Masuk</th>
                <th class="nowrap">Jam Pulang</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($days as $row)
                @php
                    $absensi = $row['absensi'];
                    $status = $absensi?->status;
                    $statusLabel = $status ? ($statusOptions[$status] ?? $status) : '-';
                @endphp
                <tr>
                    <td class="nowrap">{{ $row['date']->format('d M Y') }}</td>
                    <td class="nowrap">{{ $absensi?->jam_masuk?->format('H:i') ?? '-' }}</td>
                    <td class="nowrap">{{ $absensi?->jam_pulang?->format('H:i') ?? '-' }}</td>
                    <td>{{ $statusLabel }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>

