<?php

namespace App\Services;

use App\Models\SellerOrder;

class OrderTracking
{
    public function build(SellerOrder $order): array
    {
        $shipment = $order->shipment;
        $events = ($shipment?->deliveryEvents ?? collect())->sortByDesc('occurred_at')->values();
        // Preserve repeated scans/attempts; use the latest scan for each milestone.
        $byStatus = $events->unique('status')->keyBy('status');
        $format = fn ($date) => $date?->copy()->timezone('Asia/Manila')->format('M d, Y · h:i A');
        $definitions = [
            'packed' => ['Packed by seller', 'Seller preparation and packing.'],
            'pickup_requested' => ['Pickup requested', 'The seller requests courier collection.'],
            'pickup_confirmed' => ['Pickup schedule confirmed', 'The logistics partner confirms the pickup request.'],
            'picked_up' => ['Picked up by courier', 'Courier collection from the seller.'],
            'received_at_hub' => ['Received at sorting hub', 'The parcel is scanned at the logistics hub.'],
            'sorted' => ['Sorted for delivery', 'The hub sorts the parcel for its delivery area.'],
            'assigned_to_rider' => ['Assigned to rider', 'A rider is assigned; delivery has not necessarily started.'],
            'in_transit' => ['In transit', 'The parcel travels through the courier network.'],
            'out_for_delivery' => ['Out for delivery', 'The rider starts the delivery trip.'],
            'delivered' => ['Delivered', 'Delivery is confirmed by the courier.'],
        ];
        $labels = array_map(fn ($definition) => $definition[0], $definitions) + [
            'failed' => 'Delivery attempt unsuccessful',
            'returned' => 'Returned to sender',
            'cancelled' => 'Order cancelled',
            'assigned' => 'Courier assigned',
            'unassigned' => 'Awaiting courier assignment',
        ];
        $steps = [[
            'label' => 'Order placed', 'detail' => 'Your order was received.',
            'time' => $format($order->order->created_at) ?? 'Time not recorded', 'done' => true,
        ]];
        foreach ($definitions as $status => [$label, $detail]) {
            $event = $byStatus->get($status);
            $time = $event?->occurred_at ?? match ($status) {
                'received_at_hub' => $shipment?->received_at,
                'sorted' => $shipment?->sorted_at,
                'assigned_to_rider' => $shipment?->assigned_at,
                'delivered' => $order->delivered_at,
                default => null,
            };
            $confirmed = $event !== null || $time !== null
                || ($status === 'packed' && in_array($order->status, ['packed', 'ready_to_ship', 'shipped', 'delivered', 'completed'], true))
                || ($status === $shipment?->status)
                || ($status === 'delivered' && in_array($order->status, ['delivered', 'completed'], true));
            $steps[] = [
                'label' => $label, 'detail' => $detail,
                'time' => $format($time) ?? ($confirmed ? 'Time not recorded' : 'Awaiting update'),
                'done' => $confirmed,
            ];
        }

        $terminal = $order->status === 'cancelled' ? 'cancelled' : $shipment?->status;
        $delivered = $terminal === 'delivered' || in_array($order->status, ['delivered', 'completed'], true);
        $attention = match ($terminal) {
            'cancelled' => 'This order was cancelled. No further delivery is expected.',
            'failed' => 'The latest delivery attempt was unsuccessful. Await a confirmed retry or contact the seller for assistance.',
            'returned' => 'This parcel was returned to the sender. Contact the seller about the next steps.',
            default => $order->cancellation_status === 'pending' ? 'Your cancellation request is awaiting seller review.' : null,
        };
        if (in_array($terminal, ['cancelled', 'returned', 'failed'], true)) {
            $steps = array_values(array_filter($steps, fn ($step) => $step['done']));
            $steps[] = [
                'label' => $labels[$terminal], 'detail' => $attention,
                'time' => $format($byStatus->get($terminal)?->occurred_at
                    ?? ($terminal === 'cancelled' ? $order->cancellation_reviewed_at : null)) ?? 'Time not recorded',
                'done' => true,
            ];
        }
        for ($i = count($steps) - 1; $i >= 0; $i--) {
            if ($steps[$i]['done']) {
                $steps[$i]['current'] = true;
                $current = $steps[$i]['label'];
                for ($earlier = 0; $earlier < $i; $earlier++) {
                    if (!$steps[$earlier]['done']) {
                        $steps[$earlier]['time'] = 'No scan recorded';
                    }
                }
                break;
            }
        }

        $history = $events->map(fn ($event) => [
            'label' => $labels[$event->status] ?? ucfirst(str_replace('_', ' ', $event->status)),
            'time' => $format($event->occurred_at) ?? 'Time not recorded',
            'note' => $event->note,
            'attempt' => in_array($event->status, ['out_for_delivery', 'failed', 'delivered'], true) ? $event->attempt : null,
        ])->all();
        $lastRecorded = collect([
            $order->order->created_at, $order->delivered_at, $order->cancellation_reviewed_at,
            $shipment?->received_at, $shipment?->sorted_at, $shipment?->assigned_at,
            ...$events->pluck('occurred_at')->all(),
        ])->filter()->sortDesc()->first();

        return [
            'order' => $order->order->reference,
            'tracking_no' => $shipment?->tracking_code ?? 'Not assigned yet',
            'courier' => $shipment?->logisticsProvider?->name ?? $order->logisticsProvider?->name ?? 'Not assigned yet',
            'rider' => $shipment?->rider?->user?->name ?? 'Not assigned yet',
            'area' => $shipment?->delivery_area ?: 'Not recorded yet',
            'attempts' => max((int) $shipment?->attempts, (int) $events->whereIn('status', ['out_for_delivery', 'failed', 'delivered'])->max('attempt')),
            'eta' => $terminal === 'cancelled' ? 'Cancelled' : ($delivered ? 'Delivered' : ($terminal === 'returned' ? 'Returned to sender' : 'Not confirmed by courier')),
            'current_status' => $current,
            'last_update' => $format($lastRecorded) ?? 'No recorded update',
            'attention' => $attention,
            'steps' => $steps,
            'history' => $history,
        ];
    }
}
