<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderReport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'km_awal',
        'km_akhir',
        'saldo_etoll_before',
        'saldo_etoll_after',
        'driver_signature',
        'status',
        'submitted_at',
        'approved_by',
        'approved_at',
        'deliver_datetime',
        'rejection_reason',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'deliver_datetime' => 'datetime',
        'saldo_etoll_before' => 'decimal:2',
        'saldo_etoll_after' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * KM selisih = km_akhir - km_awal (disimpan di DB sebagai kolom generated `km_total`).
     * Untuk agregasi per crew: SUM(order_reports.km_total) JOIN orders … order_crews.
     */
    public function getKmSelisihAttribute(): ?float
    {
        if ($this->km_awal === null || $this->km_akhir === null) {
            return null;
        }

        return (float) $this->km_akhir - (float) $this->km_awal;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'rejected']);
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'submitted';
    }
}
