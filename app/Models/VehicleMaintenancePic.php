<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleMaintenancePic extends Model
{
    protected $fillable = [
        'vehicle_maintenance_id',
        'employee_id',
        'type',
    ];

    public function vehicleMaintenance(): BelongsTo
    {
        return $this->belongsTo(VehicleMaintenance::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
