<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class AbsensiTodayAllExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
     * @param  list<array{pegawai: string, pool: string, jam_masuk: string, jam_pulang: string, status: string|null}>  $rows
     * @param  array<string, string>  $statusOptions
     */
    public function __construct(
        private string $tanggal,
        private array $rows,
        private array $statusOptions,
    ) {}

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Pegawai',
            'Pool',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
        ];
    }

    public function map($row): array
    {
        $status = $row['status'] ?? null;

        return [
            $this->tanggal,
            $row['pegawai'],
            $row['pool'],
            $row['jam_masuk'],
            $row['jam_pulang'],
            $status ? ($this->statusOptions[$status] ?? $status) : '-',
        ];
    }

    public function title(): string
    {
        return 'Absensi '.$this->tanggal;
    }
}
