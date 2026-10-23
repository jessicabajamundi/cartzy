<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerOrder extends Model
{
    /** Flat platform commission on every item a seller sells: 10.00% (basis points). */
    public const COMMISSION_BPS = 1000;

    public static function commissionFor(int $subtotalMinor): int
    {
        return (int) round($subtotalMinor * self::COMMISSION_BPS / 10000);
    }

    use HasFactory;

    public const CANCELLATION_REASONS = [
        'I changed my mind',
        'I selected the wrong item or variation',
        'I entered the wrong delivery address',
        'I placed a duplicate order',
        'I want to change my payment method',
        'The order is taking too long to prepare',
        'Other reason',
    ];

    protected $fillable = [
        'order_id',
        'seller_id',
        'logistics_provider_id',
        'subtotal_minor',
        'shipping_fee_minor',
        'shipping_discount_minor',
        'seller_shipping_discount_minor',
        'voucher_discount_minor',
        'seller_voucher_discount_minor',
        'commission_minor',
        'status',
        'delivered_at',
        'note',
        'cancellation_status',
        'cancellation_reason',
        'cancellation_requested_at',
        'cancellation_reviewed_at',
    ];

    protected $casts = [
        'subtotal_minor' => 'integer',
        'shipping_fee_minor' => 'integer',
        'shipping_discount_minor' => 'integer',
        'seller_shipping_discount_minor' => 'integer',
        'voucher_discount_minor' => 'integer',
        'seller_voucher_discount_minor' => 'integer',
        'commission_minor' => 'integer',
        'delivered_at' => 'datetime',
        'cancellation_requested_at' => 'datetime',
        'cancellation_reviewed_at' => 'datetime',
    ];

    /** What the buyer pays for this store's part: items + shipping, less the platform and shop vouchers. */
    public function buyerDueMinor(): int
    {
        return max(0, $this->subtotal_minor + $this->shipping_fee_minor - $this->voucher_discount_minor - $this->seller_voucher_discount_minor);
    }

    /** What the store earns: items less commission (on the full subtotal) and less the vouchers it funded. */
    public function sellerNetMinor(): int
    {
        return $this->subtotal_minor - $this->commission_minor - $this->sellerFundedMinor();
    }

    /** Everything this store paid for: its shop discount voucher plus its free-shipping voucher. */
    public function sellerFundedMinor(): int
    {
        return $this->seller_voucher_discount_minor + $this->seller_shipping_discount_minor;
    }

    /** The full delivery fee owed to the courier, including any part a platform or shop voucher waived. */
    public function courierFeeMinor(): int
    {
        return $this->shipping_fee_minor + $this->shipping_discount_minor + $this->seller_shipping_discount_minor;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function canBeCancelledByBuyer(): bool
    {
        return in_array($this->status, ['pending', 'accepted', 'packed', 'ready_to_ship'], true)
            && (!$this->shipment || (in_array($this->shipment->status, ['unassigned', 'assigned'], true)
                && !$this->shipment->cod_collected));
    }

    public function reports()
    {
        return $this->hasMany(AdminDispute::class);
    }

    public function openReport(): ?AdminDispute
    {
        return $this->reports()->where('status', 'open')->latest('id')->first();
    }

    /** Buyers can report any order, up to a set number of days after it was delivered. */
    public function canBeReported(): bool
    {
        if (!in_array($this->status, ['delivered', 'completed'], true)) {
            return true;
        }

        return !$this->delivered_at || $this->delivered_at->gt(now()->subDays(AdminDispute::REPORT_WINDOW_DAYS));
    }

    public function messages()
    {
        return $this->hasMany(SellerMessage::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(SellerMessage::class)->latestOfMany();
    }

    public function seller()
    {
        return $this->belongsTo(Seller::class);
    }

    public function logisticsProvider()
    {
        return $this->belongsTo(LogisticsProvider::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function getSubtotalAttribute(): float
    {
        return $this->subtotal_minor / 100;
    }

    public function getShippingFeeAttribute(): float
    {
        return $this->shipping_fee_minor / 100;
    }

    public function getCommissionAttribute(): float
    {
        return $this->commission_minor / 100;
    }
}
