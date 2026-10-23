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
        'gallery',
        'options',
        'price_minor',
        'stock',
        'weight_grams',
        'is_active',
    ];

    protected $casts = [
        'options'      => 'array',
        'gallery'      => 'array',
        'price_minor'  => 'integer',
        'stock'        => 'integer',
        'weight_grams' => 'integer',
        'is_active'    => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function scopeAtStockAlertLevel(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(fn ($stock) => $stock
            ->where('product_variants.stock', '<=', Product::MIN_LOW_STOCK_THRESHOLD)
            ->orWhereHas('product', fn ($product) => $product
                ->whereColumn('product_variants.stock', '<=', 'products.low_stock_threshold')));
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function getSalePriceMinorAttribute(): int
    {
        $product = $this->product;
        if (!$product?->sale_is_active) return $this->price_minor;
        $value = (float) ($product?->discount_value ?? 0);
        $discount = match ($product?->discount_type) {
            'percentage' => (int) round($this->price_minor * $value / 100),
            'fixed' => (int) round($value * 100),
            default => 0,
        };
        return max(1, $this->price_minor - max(0, $discount));
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

    /** Every photo of this option as a URL, first photo first (falls back to the single image_path). */
    public function getGalleryUrlsAttribute(): array
    {
        $paths = $this->gallery ?: array_filter([$this->image_path]);

        return array_values(array_map(fn ($path) => preg_match('~^(https?://|/)~', $path) ? $path : asset('storage/'.$path), $paths));
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
