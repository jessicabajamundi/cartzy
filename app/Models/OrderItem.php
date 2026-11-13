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
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id')->withTrashed();
    }

    /** The option's own photo, else the product cover; null when neither exists. */
    public function getThumbnailUrlAttribute(): ?string
    {
        if ($url = $this->variant?->image_url) {
            return $url;
        }

        $cover = $this->product?->coverImage?->path;
        if (!$cover) {
            $firstImg = $this->product?->images?->first()?->path;
            if ($firstImg) {
                return preg_match('~^(https?://|/)~', $firstImg) ? $firstImg : \Illuminate\Support\Facades\Storage::disk('public')->url($firstImg);
            }
            return null;
        }

        return preg_match('~^(https?://|/)~', $cover) ? $cover : \Illuminate\Support\Facades\Storage::disk('public')->url($cover);
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
