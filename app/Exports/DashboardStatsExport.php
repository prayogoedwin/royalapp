<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class DashboardStatsExport implements FromArray, WithTitle
{
    /**
     * @param  array{
     *     total_users: int,
     *     total_orders: int,
     *     total_tasks: int,
     *     order_statuses: list<array{name: string, color: string, total: int}>
     * }  $counts
     */
    public function __construct(
        private string $periodLabel,
        private array $counts,
    ) {}

    public function array(): array
    {
        $rows = [
            ['Laporan Dashboard'],
            ['Periode', $this->periodLabel],
            [],
            ['Ringkasan', 'Jumlah'],
            ['Total Users', $this->counts['total_users']],
            ['Total Orders', $this->counts['total_orders']],
            ['Total Tasks', $this->counts['total_tasks']],
            [],
            ['Status Order', 'Jumlah'],
        ];

        foreach ($this->counts['order_statuses'] as $status) {
            $rows[] = [$status['name'], $status['total']];
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Dashboard';
    }
}
