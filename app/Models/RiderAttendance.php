<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One rider shift at a hub: when they timed in and (once they leave) timed out. */
class RiderAttendance extends Model
{
    protected $fillable = [
        'rider_id',
        'logistics_provider_id',
        'time_in_at',
        'time_in_window',
        'time_out_at',
        'time_out_window',
    ];

    protected $casts = [
        'time_in_at' => 'datetime',
        'time_out_at' => 'datetime',
        'time_in_window' => 'integer',
        'time_out_window' => 'integer',
    ];

    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    /** Minutes worked; an open shift counts up to now. */
    public function workedMinutes(): int
    {
        return (int) $this->time_in_at->diffInMinutes($this->time_out_at ?? now(), true);
    }
}
