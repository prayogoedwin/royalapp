<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_number',
        'unit_code',
        'division_id',
        'order_status_id',
        'customer_name',
        'customer_phone',
        'pickup_address',
        'destination_address',
        'pickup_datetime',
        'price',
        'payment_method',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'price' => 'decimal:2',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function orderAmbulance(): HasOne
    {
        return $this->hasOne(OrderAmbulance::class);
    }

    public function orderTowing(): HasOne
    {
        return $this->hasOne(OrderTowing::class);
    }

    public function orderCrews(): HasMany
    {
        return $this->hasMany(OrderCrew::class);
    }

    public function orderPhotos(): HasMany
    {
        return $this->hasMany(OrderPhoto::class);
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

    // Order Report Relationships
    public function orderReport(): HasOne
    {
        return $this->hasOne(OrderReport::class);
    }

    public function orderExpenses(): HasMany
    {
        return $this->hasMany(OrderExpense::class);
    }

    public function orderEtollTransactions(): HasMany
    {
        return $this->hasMany(OrderEtollTransaction::class);
    }

    public function orderVehicleIssues(): HasMany
    {
        return $this->hasMany(OrderVehicleIssue::class);
    }
}
