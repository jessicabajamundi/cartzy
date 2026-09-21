<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'logistics_provider_id',
        'vehicle_type',
        'plate_no',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }
}
