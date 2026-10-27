<?php

namespace App\Support;

use App\Models\Order;
use App\Models\OrderCrew;
use App\Models\Unit;
use Carbon\Carbon;

class SuratJalanData
{
    /**
     * @return array{kiri: list<string>, kanan: list<string>}
     */
    public static function perlengkapanKolom(): array
    {
        return [
            'kiri' => [
                'Kunci Kontak',
                'STNK / STCK Profit',
                'Buku Manual / Service',
                'Cadangan Cat',
                'Radio Tape / CD',
                'Asbak',
                'Lighter / Pemantik Api',
                'AC',
                'Penahan Sinar Matahari',
                'Sarung Jok',
                'Sarung Stir',
                'Kaca Spion Tengah (Dalam)',
            ],
            'kanan' => [
                'Karpet Dalam Depan',
                'Karpet Dalam Belakang',
                'Sandaran Kepala',
                'Reserve Ban / Ban Serep',
                'Wheel Dop / Dop Roda',
                'Dongkrak / Stang',
                'Tool Set',
                'Speaker Tambahan',
                'Penutup Tangki BBM',
                'P3K',
                'Segitiga Pengaman',
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function perlengkapan(): array
    {
        $kolom = static::perlengkapanKolom();

        return array_merge($kolom['kiri'], $kolom['kanan']);
    }

    /**
     * @return array<string, mixed>
     */
    public static function fromOrder(Order $order): array
    {
        $order->loadMissing([
            'division',
            'orderTowing',
            'orderAmbulance',
            'orderCrews.employee.position',
        ]);

        $isTowing = static::isTowing($order);
        $unit = Unit::query()->where('code', $order->unit_code)->first();
        $tanggal = $order->pickup_datetime?->copy() ?? Carbon::now();

        return [
            'order' => $order,
            'is_towing' => $isTowing,
            'brand_top' => 'ROYAL',
            'brand_bottom' => $isTowing ? 'TOWING' : 'AMBULANCE',
            'logo_src' => static::logoSrc(),
            'company_name' => (string) config('royal.company_name'),
            'company_phone' => (string) config('royal.company_phone'),
            'company_email' => (string) config('royal.company_email'),
            'nomor' => (string) $order->order_number,
            'tanggal' => $tanggal->locale('id')->translatedFormat('d F Y'),
            'jenis_kendaraan' => static::jenisKendaraan($order, $unit, $isTowing),
            'nopol' => $unit?->nopol ?: (string) $order->unit_code,
            'driver' => static::driverName($order),
            'asisten' => static::asistenName($order),
            'pengirim_nama' => static::dash($order->customer_name),
            'pengirim_telepon' => static::dash($order->customer_phone),
            'pengirim_alamat' => static::dash($order->pickup_address),
            'penerima_nama' => '-',
            'penerima_telepon' => static::dash($order->orderTowing?->receiver_phone),
            'penerima_alamat' => static::dash($order->destination_address),
            'keterangan' => static::keterangan($order),
            'perlengkapan' => static::perlengkapan(),
            'perlengkapan_kiri' => static::perlengkapanKolom()['kiri'],
            'perlengkapan_kanan' => static::perlengkapanKolom()['kanan'],
        ];
    }

    public static function logoSrc(): ?string
    {
        $path = public_path('images/logo-royal-towing.png');

        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }

    public static function isTowing(Order $order): bool
    {
        if ($order->orderTowing !== null) {
            return true;
        }

        $nama = strtolower((string) $order->division?->nama);

        return str_contains($nama, 'towing') || str_contains($nama, 'derek');
    }

    private static function jenisKendaraan(Order $order, ?Unit $unit, bool $isTowing): string
    {
        $carType = trim((string) $order->orderTowing?->car_type);
        if ($carType !== '') {
            return $carType;
        }

        $description = trim((string) $unit?->description);
        if ($description !== '') {
            return $description;
        }

        return $isTowing ? 'Towing / Derek' : 'Ambulance';
    }

    private static function driverName(Order $order): string
    {
        foreach ($order->orderCrews as $crew) {
            if (static::matchesRole($crew, ['driver', 'sopir'])) {
                return static::dash($crew->employee?->full_name);
            }
        }

        return '-';
    }

    private static function asistenName(Order $order): string
    {
        foreach ($order->orderCrews as $crew) {
            if (static::matchesRole($crew, ['asisten', 'assistant', 'nurse', 'perawat', 'helper'])) {
                return static::dash($crew->employee?->full_name);
            }
        }

        foreach ($order->orderCrews as $crew) {
            if (! static::matchesRole($crew, ['driver', 'sopir'])) {
                return static::dash($crew->employee?->full_name);
            }
        }

        return '-';
    }

    /**
     * @param  list<string>  $needles
     */
    private static function matchesRole(OrderCrew $crew, array $needles): bool
    {
        $haystack = strtolower(trim(implode(' ', [
            (string) $crew->role,
            (string) $crew->employee?->position?->nama,
            (string) $crew->employee?->position?->key,
        ])));

        foreach ($needles as $needle) {
            if ($haystack !== '' && str_contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
    }

    private static function keterangan(Order $order): string
    {
        $parts = [];

        if (filled($order->notes)) {
            $parts[] = (string) $order->notes;
        }

        if (filled($order->orderTowing?->car_condition)) {
            $parts[] = 'Kondisi kendaraan: '.$order->orderTowing->car_condition;
        }

        if (filled($order->orderAmbulance?->patient_condition)) {
            $parts[] = 'Kondisi pasien: '.$order->orderAmbulance->patient_condition;
        }

        return implode("\n", $parts);
    }

    private static function dash(?string $value): string
    {
        $value = trim((string) $value);

        return $value !== '' ? $value : '-';
    }
}
