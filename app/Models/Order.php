<?php

namespace App\Models;

use App\Support\GoogleMapsLink;
use Carbon\CarbonInterface;
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
        'appointment',
        'pickup_address',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_maps_url',
        'destination_address',
        'destination_latitude',
        'destination_longitude',
        'destination_maps_url',
        'pickup_datetime',
        'price',
        'payment_method',
        'payment_status',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'pickup_datetime' => 'datetime',
        'price' => 'integer',
        'pickup_latitude' => 'float',
        'pickup_longitude' => 'float',
        'destination_latitude' => 'float',
        'destination_longitude' => 'float',
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

    public function vehicleMaintenances(): HasMany
    {
        return $this->hasMany(VehicleMaintenance::class);
    }

    /**
     * Waktu selesai diisi sekali, saat status menjadi Done. Nilai yang sudah ada tidak ditimpa.
     */
    public function completionTimeForStatus(int $statusId): ?CarbonInterface
    {
        $existing = $this->orderReport?->deliver_datetime;

        if ($existing !== null) {
            return $existing;
        }

        $isDone = OrderStatus::query()
            ->whereKey($statusId)
            ->whereRaw('LOWER(name) = ?', ['done'])
            ->exists();

        return $isDone ? now() : null;
    }

    public function getPickupMapsUrlAttribute(?string $value): ?string
    {
        return $this->resolvedMapsUrl($value, $this->pickup_latitude, $this->pickup_longitude);
    }

    public function getDestinationMapsUrlAttribute(?string $value): ?string
    {
        return $this->resolvedMapsUrl($value, $this->destination_latitude, $this->destination_longitude);
    }

    private function resolvedMapsUrl(?string $stored, mixed $latitude, mixed $longitude): ?string
    {
        if (filled($stored)) {
            return $stored;
        }

        return GoogleMapsLink::url(
            is_numeric($latitude) ? (float) $latitude : null,
            is_numeric($longitude) ? (float) $longitude : null,
        );
    }
}
