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

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sellerOrders()
    {
        return $this->hasMany(SellerOrder::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
