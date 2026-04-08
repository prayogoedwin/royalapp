<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenanceCostDetail extends Model
{
    public const TYPE_OPTIONS = [
        'JASA' => 'JASA',
        'BARANG' => 'BARANG',
        'LAINNYA' => 'LAINNYA',
    ];

    protected $fillable = [
        'vehicle_maintenance_id',
        'type',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function vehicleMaintenance(): BelongsTo
    {
        return $this->belongsTo(VehicleMaintenance::class);
    }
}
