<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    public const MIN_LOW_STOCK_THRESHOLD = 10;

    public const APPROVAL_PENDING = 'pending';
    public const APPROVAL_APPROVED = 'approved';
    public const APPROVAL_REJECTED = 'rejected';

    public function getStockAlertThresholdAttribute(): int
    {
        return max(self::MIN_LOW_STOCK_THRESHOLD, (int) $this->low_stock_threshold);
    }

    protected $fillable = [
        'seller_id',
        'category_id',
        'name',
        'slug',
        'description',
        'gender',
        'badge',
        'discount_type',
        'discount_value',
        'discount_starts_at',
        'discount_ends_at',
        'is_active',
        'approval_status',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
        'low_stock_threshold',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'low_stock_threshold' => 'integer',
        'discount_value' => 'decimal:2',
        'discount_starts_at' => 'immutable_datetime',
        'discount_ends_at' => 'immutable_datetime',
        'reviewed_at' => 'datetime',
    ];

    public function isApproved(): bool
    {
        return $this->approval_status === self::APPROVAL_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->approval_status === self::APPROVAL_PENDING;
    }

    public function isRejected(): bool
    {
        return $this->approval_status === self::APPROVAL_REJECTED;
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', self::APPROVAL_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', self::APPROVAL_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('approval_status', self::APPROVAL_REJECTED);
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function getSaleStatusAttribute(): string
    {
        if (!$this->discount_type) return 'Regular pricing';
        $now = now();
        if ($this->discount_ends_at && $now->gte($this->discount_ends_at)) return 'Sale ended';
        if ($this->discount_starts_at && $now->lt($this->discount_starts_at)) return 'Scheduled';
        return 'Sale active';
    }

    public function getSaleIsActiveAttribute(): bool
    {
        return $this->sale_status === 'Sale active';
    }

    /** Card rating text; needs withAvg('reviews', 'rating'). Shows 0 until a buyer has reviewed it. */
    public function ratingLabel(): string
    {
        return $this->reviews_avg_rating ? number_format($this->reviews_avg_rating, 1) : '0';
    }

    /**
     * Whether a live voucher makes shipping fully free for an order of just this item at this price:
     * a cartzy (platform) voucher, or the item's own store's free-shipping voucher (Shopee/Lazada style).
     */
    public static function qualifiesForFreeShipping(?int $priceMinor, ?int $sellerId = null): bool
    {
        if ($priceMinor === null) {
            return false;
        }
        $platform = \App\Services\VoucherService::freeShippingMinSpendMinor();
        $store = $sellerId ? (\App\Services\VoucherService::sellerFreeShippingMinSpends()[$sellerId] ?? null) : null;

        return ($platform !== null && $priceMinor >= $platform) || ($store !== null && $priceMinor >= $store);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_product')->withTimestamps();
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function coverImage()
    {
        return $this->hasOne(ProductImage::class)->orderBy('position');
    }
}
