<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    /** A First Mile courier collects the parcel from the seller. */
    public const PICKUP = 'pickup';

    /** The seller brings the parcel to the hub; no courier is sent. */
    public const DROPOFF = 'dropoff';

    protected $fillable = [
        'seller_order_id',
        'logistics_provider_id',
        'rider_id',
        'pickup_rider_id',
        'tracking_code',
        'status',
        'handover_method',
        'delivery_area',
        'sort_status',
        'weight_kg',
        'received_at',
        'sorted_at',
        'assigned_at',
        'rescheduled_until',
        'reschedule_reason',
        'reschedule_count',
        'return_reason',
        'returned_at',
        'fee_minor',
        'cod_amount_minor',
        'cod_collected',
        'cod_remitted_at',
        'cod_remitted_by',
        'cod_remittance_method',
        'cod_remittance_ref',
        'cod_remittance_notes',
        'attempts',
    ];

    protected $casts = [
        'fee_minor'        => 'integer',
        'cod_amount_minor' => 'integer',
        'cod_collected'    => 'boolean',
        'cod_remitted_at'  => 'datetime',
        'attempts'         => 'integer',
        'weight_kg'        => 'float',
        'received_at'      => 'datetime',
        'sorted_at'        => 'datetime',
        'assigned_at'      => 'datetime',
        'rescheduled_until'=> 'datetime',
        'returned_at'      => 'datetime',
        'reschedule_count' => 'integer',
    ];

    public function isDropoff(): bool
    {
        return $this->handover_method === self::DROPOFF;
    }

    public function sellerOrder()
    {
        return $this->belongsTo(SellerOrder::class);
    }

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    /** The last-mile courier who delivers this parcel to the buyer. */
    public function rider()
    {
        return $this->belongsTo(Rider::class);
    }

    /** The first-mile courier who collected this parcel from the seller. */
    public function pickupRider()
    {
        return $this->belongsTo(Rider::class, 'pickup_rider_id');
    }

    public function deliveryEvents()
    {
        return $this->hasMany(DeliveryEvent::class)->orderBy('occurred_at', 'desc');
    }

    public function getFeeAttribute(): float
    {
        return $this->fee_minor / 100;
    }

    public function getCodAmountAttribute(): float
    {
        return $this->cod_amount_minor / 100;
    }

    public function codRemittedBy()
    {
        return $this->belongsTo(User::class, 'cod_remitted_by');
    }

    public function isCod(): bool
    {
        return ($this->sellerOrder?->order?->payment_method === 'cod') || $this->cod_amount_minor > 0;
    }

    public function codHoldingUntil(): ?\Carbon\Carbon
    {
        $deliveredAt = $this->sellerOrder?->delivered_at;
        return $deliveredAt ? $deliveredAt->copy()->addDays(7) : null;
    }

    public function isCodHolding(): bool
    {
        if (!$this->isCod() || !$this->cod_collected || $this->cod_remitted_at) {
            return false;
        }
        $until = $this->codHoldingUntil();
        return $until && now()->lessThan($until);
    }

    public function isCodEligibleForRemittance(): bool
    {
        if (!$this->isCod() || !$this->cod_collected || $this->cod_remitted_at) {
            return false;
        }
        $until = $this->codHoldingUntil();
        if (!$until || now()->lessThan($until)) {
            return false;
        }
        // If there's an active dispute or approved refund, holding cannot be remitted to seller
        $hasActiveDispute = $this->sellerOrder?->reports()
            ->whereIn('status', ['open', 'investigating', 'escalated'])
            ->exists();
        if ($hasActiveDispute) {
            return false;
        }
        $returnCase = $this->sellerOrder?->openReport()?->returnCase;
        if ($returnCase && !in_array($returnCase->status, ['rejected', 'refunded'], true)) {
            return false;
        }
        return true;
    }

    public function codHoldingDaysRemaining(): int
    {
        $until = $this->codHoldingUntil();
        if (!$until || now()->greaterThanOrEqualTo($until)) {
            return 0;
        }
        return max(1, (int) ceil(now()->diffInSeconds($until) / 86400));
    }
}
