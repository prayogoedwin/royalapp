<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceData
{
    public const BRAND_AMBULANCE = 'ambulance';

    public const BRAND_TOWING = 'towing';

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function brands(): array
    {
        /** @var array<string, array<string, mixed>> $brands */
        $brands = config('royal.invoice_brands', []);

        return $brands;
    }

    public static function isValidBrand(string $brand): bool
    {
        return array_key_exists($brand, static::brands());
    }

    /**
     * @return array<string, string|null>
     */
    public static function pickerLogos(): array
    {
        $logos = [];

        foreach (static::brands() as $key => $brand) {
            $logos[$key] = SuratJalanData::imageDataUri((string) ($brand['logo'] ?? ''));
        }

        return $logos;
    }

    public static function suggestedBrand(Order $order): string
    {
        $order->loadMissing(['division', 'orderTowing', 'orderAmbulance']);

        return SuratJalanData::isTowing($order) ? self::BRAND_TOWING : self::BRAND_AMBULANCE;
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromOrder(Order $order, string $brand): array
    {
        if (! static::isValidBrand($brand)) {
            throw new InvalidArgumentException('Brand invoice tidak valid.');
        }

        $config = static::brands()[$brand];
        $order->loadMissing(['division', 'orderTowing', 'orderAmbulance']);
        $unit = Unit::query()->where('code', $order->unit_code)->first();
        $issued = $order->pickup_datetime?->copy() ?? $order->created_at?->copy() ?? Carbon::now();
        $due = $issued->copy()->addDay();
        $amount = (int) $order->price;
        $number = static::ensureInvoiceNumber($order);

        return [
            'order' => $order,
            'brand_key' => $brand,
            'brand_name' => $config['name'],
            'logo_src' => SuratJalanData::imageDataUri((string) $config['logo']),
            'address' => $config['address'],
            'city' => $config['city'],
            'phone' => $config['phone'],
            'email' => $config['email'],
            'banks' => $config['banks'],
            'invoice_number' => $number,
            'issued_label' => $issued->locale('en')->translatedFormat('d M Y'),
            'due_label' => $due->locale('en')->translatedFormat('d M Y'),
            'bill_to_name' => (string) $order->customer_name,
            'bill_to_phone' => (string) $order->customer_phone,
            'bill_to_extra' => static::billToExtra($order, $unit, $brand),
            'item_title' => static::itemTitle($order, $unit, $brand),
            'item_details' => static::itemDetails($order, $unit),
            'quantity' => 1,
            'unit_price' => $amount,
            'amount' => $amount,
            'picker_url' => route('orders.invoice', $order),
            'ambulance_url' => route('orders.invoice', [$order, 'brand' => self::BRAND_AMBULANCE]),
            'towing_url' => route('orders.invoice', [$order, 'brand' => self::BRAND_TOWING]),
        ];
    }

    public static function ensureInvoiceNumber(Order $order): int
    {
        if ($order->invoice_number) {
            return (int) $order->invoice_number;
        }

        return (int) DB::transaction(function () use ($order) {
            $locked = Order::query()->whereKey($order->id)->lockForUpdate()->firstOrFail();

            if ($locked->invoice_number) {
                $order->setAttribute('invoice_number', $locked->invoice_number);

                return (int) $locked->invoice_number;
            }

            $next = (int) Order::query()->max('invoice_number') + 1;
            $locked->forceFill(['invoice_number' => $next])->save();
            $order->setAttribute('invoice_number', $next);

            return $next;
        });
    }

    public static function formatMoney(int $amount): string
    {
        return 'Rp'.number_format($amount, 0, '.', ',');
    }

    private static function itemTitle(Order $order, ?Unit $unit, string $brand): string
    {
        if ($brand === self::BRAND_TOWING) {
            $carType = trim((string) $order->orderTowing?->car_type);
            if ($carType !== '') {
                return 'Sewa Towing '.$carType;
            }

            $description = trim((string) $unit?->description);

            return $description !== '' ? 'Sewa Towing '.$description : 'Sewa Towing';
        }

        $description = trim((string) $unit?->description);
        if ($description !== '') {
            return 'Sewa Ambulance '.$description;
        }

        $unitCode = trim((string) $order->unit_code);

        return $unitCode !== '' ? 'Sewa Ambulance Unit '.$unitCode : 'Sewa Ambulance';
    }

    /**
     * @return list<string>
     */
    private static function itemDetails(Order $order, ?Unit $unit): array
    {
        $lines = [];
        $nopol = trim((string) ($unit?->nopol ?: ''));
        if ($nopol !== '') {
            $lines[] = $nopol;
        }

        $pickup = trim((string) $order->pickup_address);
        $destination = trim((string) $order->destination_address);
        if ($pickup !== '' || $destination !== '') {
            $lines[] = trim($pickup.' - '.$destination, ' -');
        }

        return $lines;
    }

    private static function billToExtra(Order $order, ?Unit $unit, string $brand): ?string
    {
        if ($brand !== self::BRAND_TOWING) {
            return null;
        }

        $nopol = trim((string) ($unit?->nopol ?: ''));
        if ($nopol !== '') {
            return $nopol;
        }

        $carType = trim((string) $order->orderTowing?->car_type);

        return $carType !== '' ? $carType : null;
    }
}
