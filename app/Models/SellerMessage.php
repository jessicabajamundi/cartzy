<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerMessage extends Model
{
    protected $fillable = ['seller_order_id', 'sender_id', 'body', 'delivered_at', 'read_at'];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
