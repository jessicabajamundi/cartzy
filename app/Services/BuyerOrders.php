<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SellerOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * A buyer's orders and the actions on them, shared by the website dashboard and the mobile API.
 */
class BuyerOrders
{
    /** One entry per seller order, newest first. */
    public function sellerOrders(int $buyerId): Collection
    {
        return SellerOrder::query()
            ->whereHas('order', fn ($q) => $q->where('buyer_id', $buyerId))
            ->with(['order.sellerOrders', 'order.payments', 'seller', 'items.product.coverImage', 'shipment.deliveryEvents', 'shipment.rider.user', 'shipment.logisticsProvider', 'logisticsProvider', 'reports'])
            ->latest()
            ->get();
    }

    public function status(SellerOrder $so): string
    {
        return match (true) {
            $so->status === 'cancelled'                                   => 'cancelled',
            in_array($so->status, ['delivered', 'completed'], true)       => 'completed',
            $so->status === 'shipped'                                     => 'to_receive',
            in_array(optional($so->shipment)->status, ['picked_up', 'in_transit', 'out_for_delivery'], true) => 'to_receive',
            $so->order->payment_method === 'wallet' && $so->order->payment_status === 'pending' => 'to_pay',
            default                                                       => 'to_ship',
        };
    }

    public function imageUrl(?string $path): string
    {
        if (!$path) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=160&h=160&fit=crop&q=80';
        }

        return str_starts_with($path, 'http') ? $path : Storage::disk('public')->url($path);
    }

    public function card(SellerOrder $so): array
    {
        $order = $so->order;
        $singleSeller = $order->sellerOrders->count() === 1;

        return [
            'id'       => $order->reference,
            'seller_order_id' => $so->id,
            'tracking_code' => $so->shipment?->tracking_code,
            'can_cancel' => $so->cancellation_status !== 'pending' && $so->canBeCancelledByBuyer(),
            'cancellation_status' => $so->cancellation_status,
            'report'   => $so->reports->sortByDesc('id')->first(),
            'can_report' => $so->reports->where('status', 'open')->isEmpty() && $so->canBeReported(),
            'store'    => $so->seller->name ?? 'Cartzy Seller',
            'status'   => $this->status($so),
            'date'     => $order->created_at->format('M d, Y'),
            'total'    => $singleSeller ? $order->total_minor / 100 : $so->buyerDueMinor() / 100,
            'payment'  => match (strtoupper((string) ($order->payments->first()->method ?? $order->payment_method))) {
                'WALLET', 'E-WALLET', 'GCASH' => 'GCash',
                'COD', 'CASH ON DELIVERY'     => 'COD',
                default                       => strtoupper((string) ($order->payments->first()->method ?? $order->payment_method)),
            },
            'items'    => $so->items->map(fn ($i) => [
                'id'        => $i->id,
                'name'      => $i->product_name,
                'variation' => $i->variant_name,
                'qty'       => $i->quantity,
                'price'     => $i->unit_price_minor / 100,
                'image'     => $this->imageUrl(optional(optional($i->product)->coverImage)->path),
            ])->all(),
        ];
    }

    /**
     * Demo payment for wallet/card orders. There is no payment gateway wired in yet,
     * so this simply records the order as paid.
     */
    public function pay(string $reference, int $buyerId): Order
    {
        return DB::transaction(function () use ($reference, $buyerId) {
            $order = Order::where('reference', $reference)->where('buyer_id', $buyerId)->lockForUpdate()->firstOrFail();

            if (!$order->sellerOrders()->where('status', '!=', 'cancelled')->exists()) {
                throw ValidationException::withMessages(['order' => 'This order has been cancelled and cannot be paid.']);
            }

            if ($order->payment_status !== 'paid') {
                $order->update(['payment_status' => 'paid']);
                $order->payments()->update(['status' => 'paid']);
                NotificationService::notify(
                    $buyerId,
                    'payment',
                    'Payment Confirmed',
                    "Payment for order {$order->reference} has been received.",
                    route('buyer.dashboard', ['tab' => 'orders'])
                );
            }

            return $order;
        });
    }

    /** Sends the seller a cancellation request; the order is only cancelled once they approve. */
    public function requestCancellation(int $sellerOrderId, int $buyerId, array $input): string
    {
        $message = DB::transaction(function () use ($sellerOrderId, $buyerId, $input) {
            $sellerOrder = SellerOrder::whereHas('order', fn ($q) => $q->where('buyer_id', $buyerId))->findOrFail($sellerOrderId);
            $order = $sellerOrder->order()->lockForUpdate()->firstOrFail();
            $sellerOrder = SellerOrder::whereKey($sellerOrderId)->lockForUpdate()->firstOrFail();
            $sellerOrder->setRelation('shipment', $sellerOrder->shipment()->lockForUpdate()->first());

            if ($sellerOrder->status === 'cancelled') {
                return 'This order has already been cancelled.';
            }
            if ($sellerOrder->cancellation_status === 'pending') {
                return 'Your cancellation request is awaiting seller approval.';
            }

            if (!$sellerOrder->canBeCancelledByBuyer()) {
                throw ValidationException::withMessages(['order' => 'This order can no longer be cancelled because it has already been collected or delivered.']);
            }

            $data = Validator::make($input, [
                'reason' => ['required', Rule::in(SellerOrder::CANCELLATION_REASONS)],
                'reason_details' => ['nullable', 'required_if:reason,Other reason', 'string', 'max:1000'],
            ])->validate();
            $reason = $data['reason'] === 'Other reason'
                ? 'Other reason: ' . $data['reason_details'] : $data['reason'];
            $sellerOrder->update([
                'cancellation_status' => 'pending',
                'cancellation_reason' => $reason,
                'cancellation_requested_at' => now(),
                'cancellation_reviewed_at' => null,
            ]);
            NotificationService::notify(
                $sellerOrder->seller->user_id, 'cancellation_requested', 'Cancellation request',
                "The buyer requested cancellation of {$order->reference}. Reason: {$reason}",
                route('seller.orders', ['status' => 'cancellation_requested'])
            );
            return 'Cancellation request sent. Your order will only be cancelled after seller approval.';
        });

        try {
            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
        } catch (\Throwable $e) {
            // SQL dump sync is best-effort.
        }

        return $message;
    }
}
