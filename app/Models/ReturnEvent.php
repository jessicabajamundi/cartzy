<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnEvent extends Model
{
    protected $guarded = ['id'];

    public function actor() { return $this->belongsTo(User::class, 'user_id'); }
}
