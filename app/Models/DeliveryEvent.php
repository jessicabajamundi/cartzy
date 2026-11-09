<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'status',
        'attempt',
        'user_id',
        'note',
        'photo_path',
        'occurred_at',
    ];

    protected $casts = [
        'attempt'     => 'integer',
        'occurred_at' => 'datetime',
    ];

    /**
     * The next free attempt number for this status. A parcel can be out for
     * delivery more than once (after a failed attempt or a reassignment), and
     * each event needs its own (shipment, status, attempt).
     */
    public static function nextAttempt(int $shipmentId, string $status): int
    {
        return ((int) static::where('shipment_id', $shipmentId)->where('status', $status)->max('attempt')) + 1;
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
