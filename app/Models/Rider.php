<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A courier at a logistics hub. First-mile couriers collect parcels from sellers and bring them to the hub;
 * last-mile couriers deliver parcels from the hub to buyers.
 */
class Rider extends Model
{
    use HasFactory;

    public const FIRST_MILE = 'first_mile';

    public const LAST_MILE = 'last_mile';

    /** Courier types with the label and short description shown in the portals. */
    public const COURIER_TYPES = [
        self::FIRST_MILE => ['First Mile', 'Picks up parcels from sellers and brings them to the hub'],
        self::LAST_MILE => ['Last Mile', 'Delivers parcels from the hub to buyers'],
    ];

    /** Vehicles each courier type may use (value => label): couriers haul bigger loads, riders ride. */
    public const VEHICLE_TYPES = [
        self::FIRST_MILE => ['car' => 'Car', 'van' => 'Van', 'truck' => 'Truck'],
        self::LAST_MILE => ['motorcycle' => 'Motorcycle', 'tricycle' => 'Tricycle', 'ebike' => 'E-bike'],
    ];

    /** The vehicle options for a courier type; anything unknown counts as last mile. */
    public static function vehicleTypesFor(?string $courierType): array
    {
        return self::VEHICLE_TYPES[$courierType] ?? self::VEHICLE_TYPES[self::LAST_MILE];
    }

    protected $fillable = [
        'user_id',
        'logistics_provider_id',
        'courier_type',
        'vehicle_type',
        'plate_no',
        'status',
        'rejection_reason',
        'area',
        'rating',
        'deliveries_today',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'float',
        'deliveries_today' => 'integer',
    ];

    public function scopeFirstMile($query)
    {
        return $query->where('courier_type', self::FIRST_MILE);
    }

    public function scopeLastMile($query)
    {
        return $query->where('courier_type', self::LAST_MILE);
    }

    public function isFirstMile(): bool
    {
        return $this->courier_type === self::FIRST_MILE;
    }

    public function isLastMile(): bool
    {
        return ($this->courier_type ?: self::LAST_MILE) === self::LAST_MILE;
    }

    public function courierTypeLabel(): string
    {
        return self::COURIER_TYPES[$this->courier_type ?: self::LAST_MILE][0];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    /** Parcels this courier delivers to buyers (last mile). */
    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    /** Parcels this courier collected from sellers (first mile). */
    public function pickedUpShipments()
    {
        return $this->hasMany(Shipment::class, 'pickup_rider_id');
    }

    public function pickupRequests()
    {
        return $this->hasMany(PickupRequest::class);
    }
}
