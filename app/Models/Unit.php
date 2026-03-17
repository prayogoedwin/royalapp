<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'tgl_perpanjangan_pajak' => 'date',
        'tgl_perpanjangan_pajak_berikutnya' => 'date',
        'tgl_ganti_plat' => 'date',
        'tgl_ganti_plat_berikutnya' => 'date',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    /**
     * Check if tax renewal is due within the notification period
     */
    public function isPajakDueSoon(): bool
    {
        if (!$this->tgl_perpanjangan_pajak_berikutnya) {
            return false;
        }

        $notificationDays = (int) config('app.unit_notification_days', 10);
        $dueDate = $this->tgl_perpanjangan_pajak_berikutnya;
        $notificationDate = now()->addDays($notificationDays);

        return $dueDate->lte($notificationDate) && $dueDate->gte(now());
    }

    /**
     * Check if plate renewal is due within the notification period
     */
    public function isPlatDueSoon(): bool
    {
        if (!$this->tgl_ganti_plat_berikutnya) {
            return false;
        }

        $notificationDays = (int) config('app.unit_notification_days', 10);
        $dueDate = $this->tgl_ganti_plat_berikutnya;
        $notificationDate = now()->addDays($notificationDays);

        return $dueDate->lte($notificationDate) && $dueDate->gte(now());
    }

    /**
     * Get days until tax renewal
     */
    public function daysUntilPajakRenewal(): ?int
    {
        if (!$this->tgl_perpanjangan_pajak_berikutnya) {
            return null;
        }

        return (int) now()->diffInDays($this->tgl_perpanjangan_pajak_berikutnya, false);
    }

    /**
     * Get days until plate renewal
     */
    public function daysUntilPlatRenewal(): ?int
    {
        if (!$this->tgl_ganti_plat_berikutnya) {
            return null;
        }

        return (int) now()->diffInDays($this->tgl_ganti_plat_berikutnya, false);
    }

    /**
     * Scope to get units with upcoming tax renewal
     */
    public function scopeWithUpcomingTaxRenewal($query)
    {
        $notificationDays = (int) config('app.unit_notification_days', 10);
        $notificationDate = now()->addDays($notificationDays);

        return $query->whereNotNull('tgl_perpanjangan_pajak_berikutnya')
            ->whereDate('tgl_perpanjangan_pajak_berikutnya', '>=', now())
            ->whereDate('tgl_perpanjangan_pajak_berikutnya', '<=', $notificationDate);
    }

    /**
     * Scope to get units with upcoming plate renewal
     */
    public function scopeWithUpcomingPlateRenewal($query)
    {
        $notificationDays = (int) config('app.unit_notification_days', 10);
        $notificationDate = now()->addDays($notificationDays);

        return $query->whereNotNull('tgl_ganti_plat_berikutnya')
            ->whereDate('tgl_ganti_plat_berikutnya', '>=', now())
            ->whereDate('tgl_ganti_plat_berikutnya', '<=', $notificationDate);
    }
}
