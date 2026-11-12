<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundTransaction extends Model
{
    protected $guarded = ['id'];
    protected $hidden = ['proof_path'];
    protected $casts = ['amount_minor' => 'integer', 'paid_at' => 'datetime'];
}
