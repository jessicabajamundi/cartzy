<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    public const TYPES = ['fixed' => 'Amount off items', 'percent' => 'Percent off items', 'free_shipping' => 'Free shipping'];

    public const AUDIENCES = [
        'public' => 'All buyers',
        'new_buyers' => 'New buyers (no orders yet)',
        'assigned' => 'Only buyers I choose',
        'code' => 'Buyers who enter the code',
    ];

    /** Guardrails for shop vouchers so a store can't publish a loss-making or never-ending deal. */
    public const SHOP_MAX_PERCENT = 50;

    public const SHOP_MAX_DAYS = 90;

    protected $fillable = [
        'seller_id', 'name', 'code', 'description', 'discount_type', 'amount_minor', 'percent', 'max_discount_minor',
        'min_spend_minor', 'audience', 'funded_by', 'total_limit', 'per_user_limit', 'used_count',
        'starts_at', 'ends_at', 'is_active', 'created_by',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
        'percent' => 'integer',
        'max_discount_minor' => 'integer',
        'min_spend_minor' => 'integer',
        'total_limit' => 'integer',
        'per_user_limit' => 'integer', // 0 = unlimited uses per buyer
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'voucher_user')->withPivot('source')->withTimestamps();
    }

    public function redemptions()
    {
        return $this->hasMany(VoucherRedemption::class);
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function isShopVoucher(): bool
    {
        return $this->seller_id !== null;
    }

    /** Vouchers a buyer could use right now: switched on, within dates and not used up. */
    public function scopeLive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function isShipping(): bool
    {
        return $this->discount_type === 'free_shipping';
    }

    /** Short buyer-facing headline, e.g. "₱100 OFF", "10% OFF", "FREE SHIPPING". */
    public function headline(): string
    {
        return match ($this->discount_type) {
            'fixed' => '₱'.number_format($this->amount_minor / 100, 0).' OFF',
            'percent' => $this->percent.'% OFF',
            default => $this->amount_minor ? '₱'.number_format($this->amount_minor / 100, 0).' OFF SHIPPING' : 'FREE SHIPPING',
        };
    }

    /** Conditions line, e.g. "Min. spend ₱500 · Up to ₱200 · Until Nov 30". */
    public function terms(): string
    {
        $parts = [$this->min_spend_minor > 0 ? 'Min. spend ₱'.number_format($this->min_spend_minor / 100, 0) : 'No min. spend'];
        if ($this->discount_type === 'percent' && $this->max_discount_minor) {
            $parts[] = 'Up to ₱'.number_format($this->max_discount_minor / 100, 0);
        }
        if ($this->ends_at) {
            $parts[] = 'Until '.$this->ends_at->timezone('Asia/Manila')->format('M j, Y');
        }

        return implode(' · ', $parts);
    }

    public function status(): string
    {
        return match (true) {
            !$this->is_active => 'paused',
            $this->ends_at && $this->ends_at->isPast() => 'expired',
            $this->starts_at && $this->starts_at->isFuture() => 'scheduled',
            $this->total_limit !== null && $this->used_count >= $this->total_limit => 'used up',
            default => 'active',
        };
    }
}
