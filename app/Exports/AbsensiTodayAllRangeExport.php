<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AbsensiTodayAllRangeExport implements WithMultipleSheets
{
    /**
     * @param  list<array{tanggal: string, rows: list<array{pegawai: string, pool: string, jam_masuk: string, jam_pulang: string, status: string|null}>}>  $days
     * @param  array<string, string>  $statusOptions
     */
    public function __construct(
        private array $days,
        private array $statusOptions,
    ) {}

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->days as $day) {
            $sheets[] = new AbsensiTodayAllExport(
                $day['tanggal'],
                $day['rows'],
                $this->statusOptions,
            );
        }

        return $sheets;
    }
}
