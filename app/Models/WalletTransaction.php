<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $guarded = ['id'];
    protected $casts = ['amount_minor' => 'integer'];
    public function order() { return $this->belongsTo(Order::class); }
}
