<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPhoto;
use App\Support\SuratJalanData;
use App\Support\UploadPath;
use App\Support\WhatsAppLink;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;

class SuratJalanChecklistService
{
    /**
     * @param  list<array{name?: string, status?: string}>  $items
     * @param  array<string, string|null>  $signatures
     * @return array{items: list<array{name: string, status: string}>, ttd: array<string, string>}
     */
    public function save(Order $order, array $items, array $signatures = [], ?int $userId = null): array
    {
        $existing = is_array($order->surat_jalan_checklist) ? $order->surat_jalan_checklist : [];
        $payload = [
            'items' => $this->normalizedItems($items),
            'ttd' => is_array($existing['ttd'] ?? null) ? $existing['ttd'] : [],
        ];

        foreach (['pengirim', 'penerima', 'driver'] as $role) {
            $dataUrl = $signatures[$role] ?? null;
            if (! is_string($dataUrl) || trim($dataUrl) === '') {
                continue;
            }

            $path = $this->storeSignaturePng($order, $role, $dataUrl);
            $this->storeOrderPhoto($order, $role, $path, $userId);
            $payload['ttd'][$role] = $path;
        }

        if (! filled($order->surat_jalan_public_token)) {
            $order->surat_jalan_public_token = Str::lower(Str::random(40));
        }

        $order->surat_jalan_checklist = $payload;
        $order->save();

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    public function viewPayload(Order $order, array $extra = []): array
    {
        return array_merge(SuratJalanData::fromOrder($order), [
            'wa_url' => $this->waUrl($order),
            'wa_native_url' => $this->waNativeUrl($order),
        ], $extra);
    }

    public function publicUrl(Order $order): ?string
    {
        if (! filled($order->surat_jalan_public_token)) {
            return null;
        }

        return route('surat-jalan.public', $order->surat_jalan_public_token);
    }

    public function waUrl(Order $order): ?string
    {
        $publicUrl = $this->publicUrl($order);
        if ($publicUrl === null) {
            return null;
        }

        $text = 'Surat Jalan '.$order->order_number."\n".$publicUrl;

        return WhatsAppLink::appUrl($order->customer_phone, $text);
    }

    public function waNativeUrl(Order $order): ?string
    {
        $publicUrl = $this->publicUrl($order);
        if ($publicUrl === null) {
            return null;
        }

        $text = 'Surat Jalan '.$order->order_number."\n".$publicUrl;

        return WhatsAppLink::nativeUrl($order->customer_phone, $text);
    }

    /**
     * @param  list<array{name?: string, status?: string}>  $items
     * @return list<array{name: string, status: string}>
     */
    private function normalizedItems(array $items): array
    {
        $submitted = [];
        foreach ($items as $item) {
            $name = trim((string) ($item['name'] ?? ''));
            $status = strtolower(trim((string) ($item['status'] ?? '')));
            if ($name === '') {
                continue;
            }
            if (! in_array($status, ['ada', 'tidak'], true)) {
                $status = '';
            }
            $submitted[$name] = $status;
        }

        $normalized = [];
        foreach (SuratJalanData::perlengkapan() as $name) {
            $normalized[] = [
                'name' => $name,
                'status' => $submitted[$name] ?? '',
            ];
        }

        return $normalized;
    }

    private function storeSignaturePng(Order $order, string $role, string $dataUrl): string
    {
        if (preg_match('/^data:image\/png;base64,(.+)$/s', $dataUrl, $matches) !== 1) {
            throw new InvalidArgumentException('Tanda tangan harus berupa gambar PNG.');
        }

        $binary = base64_decode($matches[1], true);
        if ($binary === false || strlen($binary) < 32) {
            throw new InvalidArgumentException('Tanda tangan tidak valid.');
        }

        if (strlen($binary) > 700_000) {
            throw new InvalidArgumentException('Tanda tangan terlalu besar.');
        }

        $existing = is_array($order->surat_jalan_checklist) ? ($order->surat_jalan_checklist['ttd'][$role] ?? null) : null;
        if (is_string($existing) && $existing !== '') {
            Storage::disk('public')->delete($existing);
        }

        $path = UploadPath::dir('surat-jalan-ttd').'/'.$order->id.'-'.$role.'-'.Str::lower(Str::random(8)).'.png';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function storeOrderPhoto(Order $order, string $role, string $path, ?int $userId): void
    {
        $title = 'TTD Surat Jalan '.Str::headline($role);
        $photo = $order->orderPhotos()->where('title', $title)->first();

        if ($photo) {
            if ($photo->path && $photo->path !== $path) {
                Storage::disk('public')->delete($photo->path);
            }
            $photo->update([
                'path' => $path,
                'updated_by' => $userId,
            ]);

            return;
        }

        OrderPhoto::create([
            'order_id' => $order->id,
            'title' => $title,
            'path' => $path,
            'created_by' => $userId,
        ]);
    }
}
