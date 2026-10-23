<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductInquiryMessage extends Model
{
    protected $fillable = ['product_inquiry_id', 'sender_id', 'body', 'delivered_at', 'read_at'];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function inquiry()
    {
        return $this->belongsTo(ProductInquiry::class, 'product_inquiry_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
