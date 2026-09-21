<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogisticsProvider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'status',
        'rejection_reason',
        'contact_phone',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function riders()
    {
        return $this->hasMany(Rider::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
