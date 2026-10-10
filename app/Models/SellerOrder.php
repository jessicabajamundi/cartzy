<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerOrder extends Model
{
    use HasFactory;

    public const CANCELLATION_REASONS = [
        'I changed my mind',
        'I selected the wrong item or variation',
        'I entered the wrong delivery address',
        'I placed a duplicate order',
        'I want to change my payment method',
        'The order is taking too long to prepare',
        'Other reason',
    ];

    protected $fillable = [
        'order_id',
        'seller_id',
        'logistics_provider_id',
        'subtotal_minor',
        'shipping_fee_minor',
        'commission_minor',
        'status',
        'delivered_at',
        'note',
    ];

    protected $casts = [
        'subtotal_minor' => 'integer',
        'shipping_fee_minor' => 'integer',
        'commission_minor' => 'integer',
        'delivered_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function canBeCancelledByBuyer(): bool
    {
        return in_array($this->status, ['pending', 'accepted', 'packed', 'ready_to_ship'], true)
            && (!$this->shipment || (in_array($this->shipment->status, ['unassigned', 'assigned'], true)
                && !$this->shipment->cod_collected));
    }

    public function messages()
    {
        return $this->hasMany(SellerMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(SellerMessage::class)->latestOfMany();
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->subtotal_minor / 100;
    }

    public function getShippingFeeAttribute(): float
    {
        return $this->shipping_fee_minor / 100;
    }

    public function getCommissionAttribute(): float
    {
        return $this->commission_minor / 100;
    }
}
