<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unit extends Model
{
    protected $fillable = [
        'division_id',
        'code',
        'description',
        'nopol',
        'tahun_pembelian',
        'tgl_perpanjangan_pajak',
        'tgl_perpanjangan_pajak_berikutnya',
        'tgl_ganti_plat',
        'tgl_ganti_plat_berikutnya',
        'tgl_kir_terakhir',
        'tgl_kir_berikutnya',
    ];

    protected $casts = [
        'tgl_perpanjangan_pajak' => 'date',
        'tgl_perpanjangan_pajak_berikutnya' => 'date',
        'tgl_ganti_plat' => 'date',
        'tgl_ganti_plat_berikutnya' => 'date',
        'tgl_kir_terakhir' => 'date',
        'tgl_kir_berikutnya' => 'date',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function vehicleMaintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    public function isPajakDueSoon(): bool
    {
        if (!$this->tgl_perpanjangan_pajak_berikutnya) {
            return false;
        }

        $notificationDays = (int) config('app.unit_notification_days', 30);
        $dueDate = $this->tgl_perpanjangan_pajak_berikutnya;
        $notificationDate = now()->addDays($notificationDays);

        return $dueDate->lte($notificationDate) && $dueDate->gte(now());
    }

    public function isPlatDueSoon(): bool
    {
        if (!$this->tgl_ganti_plat_berikutnya) {
            return false;
        }

        $notificationDays = (int) config('app.unit_notification_days', 30);
        $dueDate = $this->tgl_ganti_plat_berikutnya;
        $notificationDate = now()->addDays($notificationDays);

        return $dueDate->lte($notificationDate) && $dueDate->gte(now());
    }

    public function isKirDueSoon(): bool
    {
        if (!$this->tgl_kir_berikutnya) {
            return false;
        }

        $notificationDays = (int) config('app.unit_notification_days', 30);
        $dueDate = $this->tgl_kir_berikutnya;
        $notificationDate = now()->addDays($notificationDays);

        return $dueDate->lte($notificationDate) && $dueDate->gte(now());
    }

    public function daysUntilPajakRenewal(): ?int
    {
        if (!$this->tgl_perpanjangan_pajak_berikutnya) {
            return null;
        }

        return (int) now()->diffInDays($this->tgl_perpanjangan_pajak_berikutnya, false);
    }

    public function daysUntilPlatRenewal(): ?int
    {
        if (!$this->tgl_ganti_plat_berikutnya) {
            return null;
        }

        return (int) now()->diffInDays($this->tgl_ganti_plat_berikutnya, false);
    }

    public function daysUntilKirRenewal(): ?int
    {
        if (!$this->tgl_kir_berikutnya) {
            return null;
        }

        return (int) now()->diffInDays($this->tgl_kir_berikutnya, false);
    }

    public function scopeWithUpcomingTaxRenewal($query)
    {
        $notificationDays = (int) config('app.unit_notification_days', 30);
        $notificationDate = now()->addDays($notificationDays);

        return $query->whereNotNull('tgl_perpanjangan_pajak_berikutnya')
            ->whereDate('tgl_perpanjangan_pajak_berikutnya', '>=', now())
            ->whereDate('tgl_perpanjangan_pajak_berikutnya', '<=', $notificationDate);
    }

    public function scopeWithUpcomingPlateRenewal($query)
    {
        $notificationDays = (int) config('app.unit_notification_days', 30);
        $notificationDate = now()->addDays($notificationDays);

        return $query->whereNotNull('tgl_ganti_plat_berikutnya')
            ->whereDate('tgl_ganti_plat_berikutnya', '>=', now())
            ->whereDate('tgl_ganti_plat_berikutnya', '<=', $notificationDate);
    }

    public function scopeWithUpcomingKirRenewal($query)
    {
        $notificationDays = (int) config('app.unit_notification_days', 30);
        $notificationDate = now()->addDays($notificationDays);

        return $query->whereNotNull('tgl_kir_berikutnya')
            ->whereDate('tgl_kir_berikutnya', '>=', now())
            ->whereDate('tgl_kir_berikutnya', '<=', $notificationDate);
    }
}
