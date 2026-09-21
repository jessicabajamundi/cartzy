<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_order_id',
        'logistics_provider_id',
        'rider_id',
        'tracking_code',
        'status',
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
    ];

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    public function rider()
    {
        return $this->belongsTo(Rider::class);
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
