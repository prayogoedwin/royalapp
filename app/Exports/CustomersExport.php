<?php

namespace App\Exports;

use App\Support\WhatsAppLink;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class CustomersExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(
        private Collection $customers,
    ) {}

    public function collection(): Collection
    {
        return $this->customers;
    }

    public function headings(): array
    {
        return [
            'Nama',
            'No. WA / HP',
            'Jumlah Order',
            'Order terakhir',
        ];
    }

    /**
     * @return list<int|string>
     */
    public function map($row): array
    {
        $phone = (string) ($row->customer_phone ?? '');
        $display = WhatsAppLink::localDisplay($phone) ?: $phone;

        return [
            (string) ($row->customer_name ?? '-'),
            $display !== '' ? $display : '-',
            (int) ($row->total_orders ?? 0),
            $row->last_order_at
                ? Carbon::parse($row->last_order_at)->format('d M Y H:i')
                : '-',
        ];
    }

    public function title(): string
    {
        return 'Customers';
    }
}
