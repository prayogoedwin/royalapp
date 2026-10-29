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
    </style>
</head>
<body>
    <h1>Daftar Karyawan</h1>
    <div class="meta">{{ now()->format('d M Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th class="nowrap">No</th>
                <th>NIK</th>
                <th>Nama Lengkap</th>
                <th>Jabatan</th>
                <th>Divisi</th>
                <th>Tipe</th>
                <th>Status</th>
                <th class="nowrap">Dibuat</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $index => $employee)
                <tr>
                    <td class="nowrap">{{ $index + 1 }}</td>
                    <td>{{ $employee->nik }}</td>
                    <td>{{ $employee->full_name }}</td>
                    <td>{{ $employee->position?->nama ?? '-' }}</td>
                    <td>{{ $employee->division?->nama ?? '-' }}</td>
                    <td>{{ $employee->employeeType?->nama ?? '-' }}</td>
                    <td>{{ $employee->status ? ucfirst($employee->status) : '-' }}</td>
                    <td class="nowrap">{{ $employee->created_at?->format('d M Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Belum ada karyawan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
