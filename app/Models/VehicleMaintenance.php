<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class VehicleMaintenance extends Model
{
    use SoftDeletes;

    public const TYPE_OPTIONS = [
        'PERBAIKAN' => 'PERBAIKAN',
        'PERAWATAN' => 'PERAWATAN',
        'ADMINISTRASI' => 'ADMINISTRASI',
        'LAINNYA' => 'LAINNYA',
    ];

    protected $fillable = [
        'maintenance_type',
        'damage_description',
        'order_id',
        'unit_id',
        'odo_identification',
        'identified_at',
        'odo_repair',
        'repaired_at',
        'order_status_id',
        'total_cost',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'identified_at' => 'datetime',
        'repaired_at' => 'datetime',
        'odo_identification' => 'decimal:2',
        'odo_repair' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function orderStatus(): BelongsTo
    {
        return $this->belongsTo(OrderStatus::class);
    }

    public function pics(): HasMany
    {
        return $this->hasMany(VehicleMaintenancePic::class);
    }

    public function identificationPics(): HasMany
    {
        return $this->hasMany(VehicleMaintenancePic::class)->where('type', 'identification');
    }

    public function repairPics(): HasMany
    {
        return $this->hasMany(VehicleMaintenancePic::class)->where('type', 'repair');
    }

    public function costDetails(): HasMany
    {
        return $this->hasMany(VehicleMaintenanceCostDetail::class);
    }
}
