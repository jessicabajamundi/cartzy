<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'logistics_provider_id',
        'seller_id',
        'seller_order_id',
        'parcel_count',
        'weight_kg',
        'pickup_date',
        'pickup_time',
        'address',
        'status',
        'confirmed_at',
        'notes',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'confirmed_at' => 'datetime',
        'weight_kg' => 'float',
        'parcel_count' => 'integer',
    ];

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }
}
