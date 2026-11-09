<?php

namespace App\Services;

use App\Models\DeliveryEvent;
use App\Models\Rider;
use App\Models\Shipment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * A Last Mile rider's deliveries in the app, saved on the server.
 *
 *  assigned          the hub gave the parcel to the rider
 *  out_for_delivery  the rider scanned it and left the hub ({@see RiderParcelScan::accept})
 *  delivered         the buyer received it (COD collected, buyer and seller told)
 *  failed            the rider couldn't deliver it; it goes back to the hub
 */
class RiderDeliveries
{
    public const ACTIVE = ['assigned', 'out_for_delivery'];

    /** @return Collection<int, Shipment> the rider's parcels still to deliver, oldest first */
    public function activeFor(Rider $rider): Collection
    {
        return Shipment::with(['sellerOrder.order.buyer', 'sellerOrder.seller', 'sellerOrder.items'])
            ->where('rider_id', $rider->id)
            ->whereIn('status', self::ACTIVE)
            ->orderByRaw("CASE WHEN status = 'out_for_delivery' THEN 0 ELSE 1 END")
            ->orderBy('assigned_at')->orderBy('id')
            ->get();
    }

    /**
     * The rider hands the parcel to the buyer. [$code] is the scanned or typed
     * waybill code, proving the rider has the right parcel in hand.
     *
     * @return array{shipment: Shipment, already: bool, message: string}
     */
    public function deliver(Rider $rider, int $shipmentId, string $code, ?string $receivedBy, ?string $note): array
    {
        return DB::transaction(function () use ($rider, $shipmentId, $code, $receivedBy, $note) {
            $shipment = $this->mine($rider, $shipmentId);
            if ($shipment->status === 'delivered') {
                return ['shipment' => $shipment, 'already' => true, 'message' => "Parcel {$shipment->tracking_code} was already delivered."];
            }
            if ($shipment->status !== 'out_for_delivery') {
                throw new RiderParcelScanException("Start the delivery first: scan parcel {$shipment->tracking_code} so it shows as out for delivery.");
            }
            $this->checkCode($shipment, $code);

            $sellerOrder = $shipment->sellerOrder;
            $order = $sellerOrder->order;
            $isCod = $order?->payment_method === 'cod';
            $recipient = trim((string) $receivedBy) !== '' ? trim($receivedBy) : ($order?->shipping_address['recipient'] ?? 'the buyer');
            $riderName = $rider->user?->name ?? 'your rider';

            $shipment->update(['status' => 'delivered', 'cod_collected' => $isCod]);
            $sellerOrder->update(['status' => 'delivered', 'delivered_at' => now()]);
            if ($isCod && $order) {
                $order->update(['payment_status' => 'paid']);
                $order->payments()->where('status', 'pending')->update(['status' => 'completed']);
            }

            DeliveryEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'delivered',
                'attempt' => DeliveryEvent::nextAttempt($shipment->id, 'delivered'),
                'user_id' => $rider->user_id,
                'note' => "Delivered by rider {$riderName}. Received by {$recipient}.".(trim((string) $note) !== '' ? ' ('.trim($note).')' : ''),
                'occurred_at' => now(),
            ]);
            $rider->increment('deliveries_today');

            $reference = $order?->reference ?? $shipment->tracking_code;
            if ($order?->buyer_id) {
                NotificationService::notify($order->buyer_id, 'order_delivered', 'Order delivered',
                    "Your order {$reference} was delivered by rider {$riderName}. Received by {$recipient}.",
                    route('buyer.dashboard', ['tab' => 'orders']));
            }
            if ($sellerUserId = $sellerOrder->seller?->user_id) {
                NotificationService::notify($sellerUserId, 'order_delivered', 'Order delivered',
                    "Parcel {$shipment->tracking_code} for order {$reference} was delivered to {$recipient} by rider {$riderName}.",
                    route('seller.notifications'));
            }
            if ($hubUserId = $shipment->logisticsProvider?->user_id) {
                NotificationService::notify($hubUserId, 'parcel_scan', 'Parcel delivered',
                    "Rider {$riderName} delivered parcel {$shipment->tracking_code}.".($isCod ? ' COD collected.' : ''),
                    route('logistics.notifications'));
            }

            return ['shipment' => $shipment, 'already' => false,
                'message' => "Parcel {$shipment->tracking_code} delivered. The buyer, seller and hub were notified."];
        });
    }

    /**
     * The rider couldn't deliver (buyer away, refused, wrong address...). The
     * attempt is counted and the parcel goes back to the hub.
     *
     * @return array{shipment: Shipment, message: string}
     */
    public function fail(Rider $rider, int $shipmentId, string $reason): array
    {
        return DB::transaction(function () use ($rider, $shipmentId, $reason) {
            $shipment = $this->mine($rider, $shipmentId);
            if ($shipment->status !== 'out_for_delivery') {
                throw new RiderParcelScanException("Only a parcel that is out for delivery can be marked as not delivered.");
            }

            $attempt = (int) $shipment->attempts + 1;
            $riderName = $rider->user?->name ?? 'your rider';
            $shipment->update(['status' => 'failed', 'attempts' => $attempt]);
            DeliveryEvent::create([
                'shipment_id' => $shipment->id,
                'status' => 'failed',
                'attempt' => DeliveryEvent::nextAttempt($shipment->id, 'failed'),
                'user_id' => $rider->user_id,
                'note' => "Not delivered by rider {$riderName}: {$reason}",
                'occurred_at' => now(),
            ]);

            $sellerOrder = $shipment->sellerOrder;
            $reference = $sellerOrder->order?->reference ?? $shipment->tracking_code;
            if ($buyerId = $sellerOrder->order?->buyer_id) {
                NotificationService::notify($buyerId, 'delivery', 'Delivery attempt failed',
                    "We couldn't deliver your order {$reference} today ({$reason}). The hub will try again.",
                    route('buyer.dashboard', ['tab' => 'orders']));
            }
            if ($sellerUserId = $sellerOrder->seller?->user_id) {
                NotificationService::notify($sellerUserId, 'order_update', 'Delivery attempt failed',
                    "Parcel {$shipment->tracking_code} for order {$reference} wasn't delivered: {$reason}.",
                    route('seller.notifications'));
            }
            if ($hubUserId = $shipment->logisticsProvider?->user_id) {
                NotificationService::notify($hubUserId, 'parcel_scan', 'Delivery failed',
                    "Rider {$riderName} couldn't deliver parcel {$shipment->tracking_code} (attempt {$attempt}): {$reason}. It's coming back to the hub.",
                    route('logistics.notifications'));
            }

            return ['shipment' => $shipment, 'message' => "Parcel {$shipment->tracking_code} marked as not delivered. Bring it back to the hub."];
        });
    }

    private function mine(Rider $rider, int $shipmentId): Shipment
    {
        if (!$rider->isLastMile()) {
            throw new RiderParcelScanException('Deliveries to buyers are done by Last Mile riders.', 403);
        }
        $shipment = Shipment::with(['sellerOrder.order', 'sellerOrder.seller', 'sellerOrder.items', 'logisticsProvider'])
            ->lockForUpdate()->find($shipmentId);
        if (!$shipment || $shipment->rider_id !== $rider->id) {
            throw new RiderParcelScanException('This parcel is not assigned to you.', 404);
        }

        return $shipment;
    }

    private function checkCode(Shipment $shipment, string $code): void
    {
        $code = RiderParcelScan::normalize($code);
        if ($code !== strtoupper($shipment->tracking_code) && $code !== strtoupper((string) $shipment->sellerOrder->order?->reference)) {
            throw new RiderParcelScanException("That code doesn't match parcel {$shipment->tracking_code}. Scan the waybill on this parcel.");
        }
    }
}
