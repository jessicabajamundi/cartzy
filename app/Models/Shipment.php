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
        'package_profile',
        'seller_order_id',
        'logistics_provider_id',
        'origin_hub_id',
        'destination_hub_id',
        'current_hub_id',
        'rider_id',
        'pickup_rider_id',
        'linehaul_courier_id',
        'linehaul_batch_code',
        'tracking_code',
        'status',
        'handover_method',
        'delivery_area',
        'sort_status',
        'weight_kg',
        'received_at',
        'sorted_at',
        'assigned_at',
        'dispatched_to_hub_at',
        'arrived_at_destination_hub_at',
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
        'delivery_proof_photo',
        'attempts',
    ];

    protected $casts = [
        'package_profile' => 'array',
        'fee_minor'        => 'integer',
        'cod_amount_minor' => 'integer',
        'cod_collected'    => 'boolean',
        'cod_remitted_at'  => 'datetime',
        'attempts'         => 'integer',
        'weight_kg'        => 'float',
        'received_at'      => 'datetime',
        'sorted_at'        => 'datetime',
        'assigned_at'      => 'datetime',
        'dispatched_to_hub_at' => 'datetime',
        'arrived_at_destination_hub_at' => 'datetime',
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

    public function originHub()
    {
        return $this->belongsTo(LogisticsProvider::class, 'origin_hub_id');
    }

    public function destinationHub()
    {
        return $this->belongsTo(LogisticsProvider::class, 'destination_hub_id');
    }

    public function currentHub()
    {
        return $this->belongsTo(LogisticsProvider::class, 'current_hub_id');
    }

    /** The linehaul courier transporting this parcel between hubs. */
    public function linehaulCourier()
    {
        return $this->belongsTo(Rider::class, 'linehaul_courier_id');
    }

    public function requiresHubToHub(): bool
    {
        return $this->origin_hub_id !== null
            && $this->destination_hub_id !== null
            && (int) $this->origin_hub_id !== (int) $this->destination_hub_id;
    }

    public function isWaitingForLinehaulDispatch(): bool
    {
        return $this->requiresHubToHub()
            && $this->received_at !== null
            && $this->dispatched_to_hub_at === null
            && $this->arrived_at_destination_hub_at === null
            && (int) $this->current_hub_id !== (int) $this->destination_hub_id;
    }

    public function isInHubToHubTransit(): bool
    {
        return $this->requiresHubToHub()
            && $this->dispatched_to_hub_at !== null
            && $this->arrived_at_destination_hub_at === null;
    }

    public function hasArrivedAtDestinationHub(): bool
    {
        return !$this->requiresHubToHub()
            || $this->arrived_at_destination_hub_at !== null
            || (int) $this->current_hub_id === (int) $this->destination_hub_id;
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

    public function getDeliveryProofUrlAttribute(): ?string
    {
        if (empty($this->delivery_proof_photo)) {
            return null;
        }
        if (str_starts_with($this->delivery_proof_photo, 'http://') || str_starts_with($this->delivery_proof_photo, 'https://')) {
            return $this->delivery_proof_photo;
        }

        return asset('storage/' . ltrim($this->delivery_proof_photo, '/'));
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

    public function isCodRemitted(): bool
    {
        return $this->isCod() && $this->cod_remitted_at !== null;
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
        // Refunded money must never be remitted a second time to the seller.
        if (RefundTransaction::where('seller_order_id', $this->seller_order_id)->exists()) {
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
