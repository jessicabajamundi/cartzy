<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buyer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'middle_initial',
        'sex',
        'birthday',
        'age',
        'phone',
        'default_address_id',
        'id_photo',
        'id_type',
        'id_number',
        'id_status',
        'id_rejection_reason',
        'id_verified_at',
        'loyalty_points',
        'membership_tier',
        'status',
        'notes',
    ];

    protected $casts = [
        'birthday' => 'date',
        'age' => 'integer',
        'loyalty_points' => 'integer',
        'id_verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function defaultAddress()
    {
        return $this->belongsTo(Address::class, 'default_address_id');
    }

    public function addresses()
    {
        return $this->hasMany(Address::class, 'user_id', 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id', 'user_id');
    }

    public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id', 'user_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'user_id', 'user_id');
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class, 'user_id', 'user_id');
    }

    public function isVerified(): bool
    {
        return $this->id_status === 'verified';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
