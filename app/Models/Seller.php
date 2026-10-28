<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo_path',
        'banner_path',
        'status',
        'rejection_reason',
        'commission_bps',
        'pickup_address_id',
    ];

    protected $casts = [
        'commission_bps' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pickupAddress()
    {
        return $this->belongsTo(Address::class, 'pickup_address_id');
    }

    public function vouchers()
    {
        return $this->hasMany(Voucher::class);
    }

    /** Shop vouchers buyers can see on the store and product pages: public, live and not used up. Code vouchers stay private. */
    public function publicVouchers()
    {
        return $this->vouchers()->live()->where('audience', 'public')
            ->where(fn ($q) => $q->whereNull('total_limit')->orWhereColumn('used_count', '<', 'total_limit'))
            ->orderBy('min_spend_minor')->limit(8)->get();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sellerOrders()
    {
        return $this->hasMany(SellerOrder::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'seller_followers')->withTimestamps();
    }

    /** Reviews left on any of this seller's products. */
    public function reviews()
    {
        return $this->hasManyThrough(Review::class, Product::class);
    }

    public function logoUrl(): ?string
    {
        if (!$this->logo_path) return null;
        return preg_match('~^(https?://|/)~', $this->logo_path) ? $this->logo_path : \Illuminate\Support\Facades\Storage::disk('public')->url($this->logo_path);
    }

    public function bannerUrl(): ?string
    {
        if (!$this->banner_path) return null;
        return preg_match('~^(https?://|/)~', $this->banner_path) ? $this->banner_path : \Illuminate\Support\Facades\Storage::disk('public')->url($this->banner_path);
    }

    /** Only approved sellers with an active account have a public store. */
    public function scopePubliclyVisible($query)
    {
        return $query->where('status', 'approved')->whereHas('user', fn ($user) => $user->where('status', 'active'));
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
