<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'method',
        'amount_minor',
        'status',
        'provider_ref',
    ];

    protected $casts = [
        'amount_minor' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_minor / 100;
    }
}
