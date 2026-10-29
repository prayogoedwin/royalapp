<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class OrdersExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
     * @param  Collection<int, Order>  $orders
     */
    public function __construct(
        private Collection $orders,
        private string $title = 'Pesanan',
    ) {}

    public function collection(): Collection
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'No. Pesanan',
            'Pelanggan',
            'Divisi',
            'Penjemputan',
            'Harga',
            'Status',
            'Pembuat',
            'Dibuat Pada',
        ];
    }

    /**
     * @param  Order  $order
     * @return list<int|string>
     */
    public function map($order): array
    {
        $creator = trim((string) ($order->createdBy?->name ?? ''));

        return [
            $order->order_number,
            $order->customer_name,
            $order->division?->nama ?? '-',
            $order->pickup_datetime?->format('d M Y H:i') ?? '-',
            (int) $order->price,
            $order->orderStatus?->name ?? '-',
            $creator !== '' ? $creator : '-',
            $order->created_at?->format('d M Y H:i') ?? '-',
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}
