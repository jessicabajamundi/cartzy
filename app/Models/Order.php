<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'reference',
        'total_minor',
        'payment_method',
        'payment_status',
        'shipping_address',
    ];

    protected $casts = [
        'total_minor'      => 'integer',
        'shipping_address' => 'array',
    ];

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function sellerOrders()
    {
        return $this->hasMany(SellerOrder::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getTotalAttribute(): float
    {
        return $this->total_minor / 100;
    }
}
