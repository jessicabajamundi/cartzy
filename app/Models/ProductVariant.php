<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'options',
        'price_minor',
        'stock',
        'weight_grams',
        'is_active',
    ];

    protected $casts = [
        'options'      => 'array',
        'price_minor'  => 'integer',
        'stock'        => 'integer',
        'weight_grams' => 'integer',
        'is_active'    => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getPriceAttribute(): float
    {
        return $this->price_minor / 100;
    }
}
