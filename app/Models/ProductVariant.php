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
        'image_path',
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

    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image_path)) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://') || str_starts_with($this->image_path, '/')) {
            return $this->image_path;
        }

        return asset('storage/' . $this->image_path);
    }

    public function getResolvedImageUrlAttribute(): string
    {
        if ($this->image_url) {
            return $this->image_url;
        }

        $cover = $this->product?->coverImage?->path;
        if ($cover) {
            if (str_starts_with($cover, 'http://') || str_starts_with($cover, 'https://') || str_starts_with($cover, '/')) {
                return $cover;
            }
            return asset('storage/' . $cover);
        }

        return 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=400&h=400&fit=crop&q=80';
    }
}
