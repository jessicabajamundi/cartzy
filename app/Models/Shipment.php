<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    /** A First Mile courier collects the parcel from the seller. */
    public const PICKUP = 'pickup';

    /** The seller brings the parcel to the hub; no courier is sent. */
    public const DROPOFF = 'dropoff';

    protected $fillable = [
        'seller_order_id',
        'logistics_provider_id',
        'rider_id',
        'pickup_rider_id',
        'tracking_code',
        'status',
        'handover_method',
        'delivery_area',
        'sort_status',
        'weight_kg',
        'received_at',
        'sorted_at',
        'assigned_at',
        'fee_minor',
        'cod_amount_minor',
        'cod_collected',
        'attempts',
    ];

    protected $casts = [
        'fee_minor'        => 'integer',
        'cod_amount_minor' => 'integer',
        'cod_collected'    => 'boolean',
        'attempts'         => 'integer',
        'weight_kg'        => 'float',
        'received_at'      => 'datetime',
        'sorted_at'        => 'datetime',
        'assigned_at'      => 'datetime',
    ];

    public function isDropoff(): bool
    {
        return $this->handover_method === self::DROPOFF;
    }

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    /** The last-mile courier who delivers this parcel to the buyer. */
    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    /** The first-mile courier who collected this parcel from the seller. */
    public function pickupRider()
    {
        return $this->belongsTo(Rider::class, 'pickup_rider_id');
    }

    public function deliveryEvents()
    {
        return $this->hasMany(DeliveryEvent::class)->orderBy('occurred_at', 'desc');
    }

    public function getFeeAttribute(): float
    {
        return $this->fee_minor / 100;
    }

    public function getCodAmountAttribute(): float
    {
        return $this->cod_amount_minor / 100;
    }
}
