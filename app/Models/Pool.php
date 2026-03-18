<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pool extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'pool_name',
        'address',
        'lat',
        'lng',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'lat' => 'decimal:6',
        'lng' => 'decimal:6',
    ];

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
}

