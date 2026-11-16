<?php

namespace App\Services;

use App\Models\{ActivityLog, Order, Payment, SellerOrder, User};
use Illuminate\Support\Facades\DB;

class PaymentVerification
{
    public function verify(SellerOrder $parcel, User $actor, array $data): Payment
    {
        abort_unless($actor->role === 'seller' && $actor->status === 'active' && !$actor->is_suspended
            && (int) $parcel->seller->user_id === (int) $actor->id, 403);

        return DB::transaction(function () use ($parcel, $actor, $data) {
            $order = Order::whereKey($parcel->order_id)->lockForUpdate()->firstOrFail();
            abort_if($order->payment_method === 'cod', 422, 'COD collection must be recorded by the delivery workflow.');
            abort_unless($order->sellerOrders()->count() === 1, 422, 'This legacy multi-store payment needs administration review; one seller cannot verify another seller\'s payment.');
            abort_if($order->sellerOrders()->where('status', 'cancelled')->exists(), 422, 'Cancelled orders cannot be verified.');
            abort_unless($order->payment_status === 'pending', 409, 'This payment has already been reviewed. Refresh the order.');
            $payment = $order->payments()->latest('id')->lockForUpdate()->firstOrFail();
            abort_unless($payment->status === 'pending', 409, 'This payment has already been reviewed.');
            abort_unless(in_array($data['decision'], ['approved', 'rejected'], true), 422);
            $approved = $data['decision'] === 'approved';
            abort_if($approved && (!$payment->provider_ref || !$payment->receipt_path), 422,
                'A transaction reference and receipt are required before verifying a manual GCash payment.');
            $notes = $data['notes'] ?? null;
            $payment->update([
                'status' => $approved ? 'completed' : 'rejected',
                'verified_at' => now(), 'verified_by' => $actor->id,
                'verification_notes' => $notes ?: ($approved ? 'Seller confirmed receipt of the manual GCash transfer.' : 'Payment receipt could not be verified.'),
            ]);
            $order->update(['payment_status' => $approved ? 'paid' : 'payment_rejected']);
            ActivityLog::forUser($actor, $approved ? 'payment_approved' : 'payment_rejected',
                "Order {$order->reference}; payment #{$payment->id}; reference {$payment->provider_ref}; manually verified by seller.");
            NotificationService::notify($order->buyer_id, 'payment', $approved ? 'Payment verified by seller' : 'Payment rejected',
                "Order {$order->reference}: ".($approved ? 'the seller confirmed your GCash transfer.' : 'the seller could not verify your GCash transfer. Contact the seller for assistance.'),
                route('buyer.dashboard', ['tab' => 'orders']));
            $shipment = $parcel->shipment;
            $hubIds = collect([$parcel->logistics_provider_id, $shipment?->logistics_provider_id,
                $shipment?->origin_hub_id, $shipment?->destination_hub_id])->filter()->unique();
            foreach (\App\Models\LogisticsProvider::whereIn('id', $hubIds)->whereNotNull('user_id')->pluck('user_id')->unique() as $userId) {
                NotificationService::notify($userId, 'payment', 'Online payment record updated',
                    "Order {$order->reference}: manual GCash payment ".($approved ? 'verified' : 'rejected')." by {$actor->name}.",
                    route('logistics.online_payments', ['search' => $order->reference]));
            }
            return $payment->fresh();
        }, 3);
    }
}
