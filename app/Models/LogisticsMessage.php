<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticsMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'logistics_provider_id',
        'contact_user_id',
        'sender_type',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    public function contactUser()
    {
        return $this->belongsTo(User::class, 'contact_user_id');
    }
}
