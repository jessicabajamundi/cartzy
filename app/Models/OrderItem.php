<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_order_id',
        'product_id',
        'product_variant_id',
        'product_name',
        'variant_name',
        'unit_price_minor',
        'quantity',
    ];

    protected $casts = [
        'unit_price_minor' => 'integer',
        'quantity'         => 'integer',
    ];

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }

    public function getUnitPriceAttribute(): float
    {
        return $this->unit_price_minor / 100;
    }

    public function getSubtotalAttribute(): float
    {
        return ($this->unit_price_minor * $this->quantity) / 100;
    }
}
