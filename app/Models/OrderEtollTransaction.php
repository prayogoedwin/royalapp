<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderEtollTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'topup_amount',
        'usage_amount',
        'balance_before',
        'balance_after',
        'receipt_photo',
        'created_by',
    ];

    protected $casts = [
        'topup_amount' => 'decimal:2',
        'usage_amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
