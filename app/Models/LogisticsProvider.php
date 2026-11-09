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
        'sec_registration_no',
        'contact_position',
        'service_regions',
    ];

    protected $casts = [
        'service_regions' => 'array',
    ];

    /**
     * Regions a partner can deliver to, as chosen on the sign-up "Service areas" step.
     * Keys are stored; labels are shown.
     */
    public const SERVICE_REGIONS = [
        'NCR' => 'NCR',
        'CAR' => 'CAR',
        'I' => 'Region I (Ilocos Region)',
        'II' => 'Region II (Cagayan Valley)',
        'III' => 'Region III (Central Luzon)',
        'IV-A' => 'Region IV-A (CALABARZON)',
        'MIMAROPA' => 'MIMAROPA Region',
        'V' => 'Region V (Bicol Region)',
        'VI' => 'Region VI (Western Visayas)',
        'NIR' => 'Negros Island Region',
        'VII' => 'Region VII (Central Visayas)',
        'VIII' => 'Region VIII (Eastern Visayas)',
        'IX' => 'Region IX (Zamboanga Peninsula)',
        'X' => 'Region X (Northern Mindanao)',
        'XI' => 'Region XI (Davao Region)',
        'XII' => 'Region XII (SOCCSKSARGEN)',
        'XIII' => 'Region XIII (Caraga)',
        'BARMM' => 'BARMM',
    ];

    /** Short list for rider-facing pickers, e.g. "NCR, Region IV-A". */
    public function serviceRegionsSummary(): string
    {
        return collect($this->service_regions ?? [])
            ->map(fn ($key) => match (true) {
                in_array($key, ['NCR', 'CAR', 'BARMM', 'MIMAROPA', 'NIR'], true) => $key,
                default => 'Region ' . $key,
            })
            ->join(', ');
    }

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

    public function pickupRequests()
    {
        return $this->hasMany(PickupRequest::class);
    }

    public function messages()
    {
        return $this->hasMany(LogisticsMessage::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
