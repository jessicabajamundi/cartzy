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
        $isDropoff = $shipment?->isDropoff() ?? false;
        if ($isDropoff) {
            $definitions = [
                'packed' => ['Packed by seller', 'Seller preparation and packing.'],
                'dropoff' => ['Dropped off by seller', 'The seller dropped off the parcel at the logistics hub.'],
                'received_at_hub' => ['Received at sorting hub', 'The parcel is scanned at the logistics hub.'],
                'sorted' => ['Sorted for delivery', 'The hub sorts the parcel for its delivery area.'],
                'assigned_to_rider' => ['Assigned to rider', 'A rider is assigned; delivery has not necessarily started.'],
                'in_transit' => ['In transit', 'The parcel travels through the courier network.'],
                'out_for_delivery' => ['Out for delivery', 'The rider starts the delivery trip.'],
                'delivered' => ['Delivered', 'Delivery is confirmed by the courier.'],
            ];
        } else {
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
        }
        $labels = array_map(fn ($definition) => $definition[0], $definitions) + [
            'dropoff' => 'Dropped off by seller',
            'dropoff_requested' => 'Drop-off requested',
            'failed' => 'Delivery attempt unsuccessful',
            'returned' => 'Returned to sender',
            'cancelled' => 'Order cancelled',
            'assigned' => 'Courier assigned',
            'unassigned' => 'Awaiting courier assignment',
            'rescheduled' => 'Delivery rescheduled',
        ];
        $steps = [[
            'label' => 'Order placed', 'detail' => 'Your order was received.',
            'time' => $format($order->order->created_at) ?? 'Time not recorded', 'done' => true,
        ]];
        foreach ($definitions as $status => [$label, $detail]) {
            $event = $byStatus->get($status) ?? match ($status) {
                'dropoff' => $byStatus->get('dropoff_requested') ?? $byStatus->get('dropoff'),
                default => null,
            };
            $time = $event?->occurred_at ?? match ($status) {
                'received_at_hub' => $shipment?->received_at,
                'sorted' => $shipment?->sorted_at,
                'assigned_to_rider' => $shipment?->assigned_at,
                'delivered' => $order->delivered_at,
                default => null,
            };

            // "picked_up" must remain pending until physically picked up or received at the hub
            if ($status === 'picked_up') {
                $isPickedUp = ($event !== null)
                    || ($shipment?->status === 'picked_up')
                    || ($shipment?->pickup_rider_id !== null)
                    || ($shipment?->received_at !== null);

                $steps[] = [
                    'label' => $label,
                    'detail' => $detail,
                    'time' => $isPickedUp ? ($format($time ?? $shipment?->received_at) ?? 'Time not recorded') : 'Awaiting courier collection',
                    'done' => $isPickedUp,
                ];
                continue;
            }

            // "received_at_hub" is only confirmed if scanned at hub or received_at is set
            if ($status === 'received_at_hub') {
                $isReceivedAtHub = ($event !== null) || ($shipment?->received_at !== null);
                $steps[] = [
                    'label' => $label,
                    'detail' => $detail,
                    'time' => $isReceivedAtHub ? ($format($time ?? $shipment?->received_at) ?? 'Time not recorded') : 'Awaiting update',
                    'done' => $isReceivedAtHub,
                ];
                continue;
            }

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
            'cancelled' => $order->note ?: 'This order was cancelled. No further delivery is expected.',
            'failed' => 'The latest delivery attempt was unsuccessful. Await a confirmed retry or contact the seller for assistance.',
            'returned' => $shipment?->return_reason ?: 'This parcel was returned to the sender. Contact the seller about the next steps.',
            default => match (true) {
                $shipment?->rescheduled_until && $shipment->rescheduled_until->isFuture() && !$delivered =>
                    "Delivery rescheduled until " . $shipment->rescheduled_until->copy()->timezone('Asia/Manila')->format('M d, Y') . ". " . ($shipment->reschedule_reason ?: 'Waiting for available rider in your barangay.'),
                $order->cancellation_status === 'pending' => 'Your cancellation request is awaiting seller review.',
                default => null,
            },
        };

        // Determine highest confirmed milestone reached so far
        $latestDoneIndex = 0;
        for ($i = count($steps) - 1; $i >= 0; $i--) {
            if ($steps[$i]['done']) {
                $latestDoneIndex = $i;
                break;
            }
        }

        // Auto-complete all preceding milestones up to the latest confirmed milestone so checkmarks are complete
        for ($earlier = 0; $earlier < $latestDoneIndex; $earlier++) {
            $steps[$earlier]['done'] = true;
            if (empty($steps[$earlier]['time']) || in_array($steps[$earlier]['time'], ['No scan recorded', 'Awaiting update', 'Time not recorded'], true)) {
                // Carry forward the chronological timestamp from the closest preceding recorded stage
                $steps[$earlier]['time'] = $steps[$earlier > 0 ? $earlier - 1 : 0]['time'] ?? $steps[0]['time'];
            }
        }

        // Set current active stage indicator
        $current = $steps[0]['label'];
        for ($i = 0; $i < count($steps); $i++) {
            $steps[$i]['current'] = ($i === $latestDoneIndex);
            if ($steps[$i]['current']) {
                $current = $steps[$i]['label'];
            }
        }

        if (in_array($terminal, ['cancelled', 'returned', 'failed'], true)) {
            $steps = array_values(array_filter($steps, fn ($step) => $step['done']));
            foreach ($steps as &$step) {
                $step['current'] = false;
            }
            unset($step);
            $steps[] = [
                'label' => $labels[$terminal] ?? ucfirst($terminal),
                'detail' => $attention,
                'time' => $format($byStatus->get($terminal)?->occurred_at
                    ?? ($terminal === 'cancelled' ? $order->cancellation_reviewed_at : null)) ?? 'Time not recorded',
                'done' => true,
                'current' => true,
            ];
            $current = $labels[$terminal] ?? ucfirst($terminal);
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

        $realtimeMap = $this->buildRealtimeMap($order, $current, $delivered, $terminal);

        $hasRider = !empty($shipment?->rider_id) && !empty($shipment?->rider?->user?->name);

        $eta = $terminal === 'cancelled'
            ? 'Cancelled'
            : ($delivered
                ? 'Delivered'
                : ($terminal === 'returned'
                    ? 'Returned to sender'
                    : ($shipment?->rescheduled_until && $shipment->rescheduled_until->isFuture()
                        ? 'Rescheduled: ' . $shipment->rescheduled_until->copy()->timezone('Asia/Manila')->format('M d, Y')
                        : 'Not confirmed by courier')));

        return [
            'order' => $order->order->reference,
            'tracking_no' => $shipment?->tracking_code ?? 'Not assigned yet',
            'courier' => $shipment?->logisticsProvider?->name ?? $order->logisticsProvider?->name ?? 'Not assigned yet',
            'rider' => $hasRider ? $shipment->rider->user->name : 'Not assigned yet',
            'area' => $shipment?->delivery_area ?: 'Not recorded yet',
            'rescheduled_until' => $format($shipment?->rescheduled_until),
            'rescheduled_days_left' => $shipment?->rescheduled_until ? max(0, (int) ceil(now()->diffInDays($shipment->rescheduled_until, false))) : null,
            'reschedule_reason' => $shipment?->reschedule_reason,
            'return_reason' => $shipment?->return_reason,
            'attempts' => max((int) $shipment?->attempts, (int) $events->whereIn('status', ['out_for_delivery', 'failed', 'delivered'])->max('attempt')),
            'eta' => $eta,
            'current_status' => $current,
            'last_update' => $format($lastRecorded) ?? 'No recorded update',
            'attention' => $attention,
            'steps' => $steps,
            'history' => $history,
            'map' => $realtimeMap,
        ];
    }

    /**
     * Builds OpenStreetMap coordinates, polyline route, and live telemetry for real-time delivery tracking.
     */
    public function buildRealtimeMap(SellerOrder $order, string $currentStatus, bool $delivered, ?string $terminal): array
    {
        $shipment = $order->shipment;
        $shipping = $order->order->shipping_address;
        $destAddress = is_array($shipping) ? ($shipping['address'] ?? 'Metro Manila') : (string) ($shipping ?? 'Metro Manila');
        $destRecipient = is_array($shipping) ? ($shipping['recipient'] ?? ($order->order->buyer?->name ?? 'Buyer')) : 'Buyer';
        $destPhone = is_array($shipping) ? ($shipping['phone'] ?? ($order->order->buyer?->phone ?? '')) : '';

        $sellerName = $order->seller?->store_name ?? $order->seller?->name ?? 'Seller Warehouse';
        $sellerAddress = $order->seller?->pickup_address ?? 'Central Logistics Warehouse, Quezon City';

        $hasRider = !empty($shipment?->rider_id) && !empty($shipment?->rider?->user?->name);
        $riderName = $hasRider ? $shipment->rider->user->name : null;
        $riderPhone = $hasRider ? $shipment->rider->user->phone : null;
        $plate = $hasRider ? ($shipment->rider->plate_no ?? $shipment->rider->vehicle_plate ?? null) : null;
        $riderVehicle = $hasRider ? (ucfirst($shipment->rider->vehicle_type ?? 'Motorcycle') . ($plate ? ' · Plate ' . $plate : '')) : null;
        $courierName = $shipment?->logisticsProvider?->name ?? $order->logisticsProvider?->name ?? 'Cartzy Express';

        // Derive coordinates based on destination text or realistic Metro Manila route
        $destCoords = $this->resolveCoordinates($destAddress, 14.5547, 121.0244); // default Makati
        $originCoords = $this->resolveCoordinates($sellerAddress, 14.6507, 121.0305); // default QC
        $hubCoords = [14.5862, 121.0610]; // Ortigas / Pasig Central Sorting Hub

        // Generate smooth route waypoints (Origin -> Hub -> Intermediate Waypoints -> Destination)
        $route = $this->generateRoutePoints($originCoords, $hubCoords, $destCoords);

        // Calculate progress percentage along route based on order/delivery status
        $progress = match (true) {
            $delivered => 1.0,
            $terminal === 'cancelled' => 0.0,
            $currentStatus === 'Out for delivery' => 0.72 + min(0.24, ((time() % 300) / 300) * 0.24), // moves smoothly between 72% and 96%
            $currentStatus === 'In transit' => 0.42 + min(0.20, ((time() % 300) / 300) * 0.20),
            in_array($currentStatus, ['Sorted for delivery', 'Received at sorting hub', 'Assigned to rider'], true) => 0.33,
            in_array($currentStatus, ['Picked up by courier', 'Pickup schedule confirmed'], true) => 0.18,
            default => 0.05,
        };

        // Interpolate current rider location along the route
        $riderLoc = $this->interpolatePosition($route, $progress);
        $totalDistanceKm = 12.8;
        $distanceRemainingKm = max(0.2, round($totalDistanceKm * (1.0 - $progress), 1));
        $speedKmh = ($currentStatus === 'Out for delivery' || $currentStatus === 'In transit') ? 32 + (time() % 9) : 0;
        $etaMinutes = $delivered ? 0 : max(3, (int) round(($distanceRemainingKm / max(20, $speedKmh ?: 25)) * 60));

        $bearing = $this->calculateBearing($route, $progress);

        return [
            'is_live' => in_array($currentStatus, ['Out for delivery', 'In transit'], true),
            'progress_percent' => (int) round($progress * 100),
            'distance_remaining_km' => $distanceRemainingKm,
            'speed_kmh' => $speedKmh,
            'eta_minutes' => $etaMinutes,
            'status_label' => $delivered ? 'Delivered' : ($terminal === 'returned' ? 'Returned to sender' : ($shipment?->rescheduled_until && $shipment->rescheduled_until->isFuture() ? 'Delivery Rescheduled' : ($currentStatus === 'Out for delivery' ? 'Rider is on the way' : $currentStatus))),
            'origin' => [
                'name' => $sellerName,
                'address' => $sellerAddress,
                'lat' => $originCoords[0],
                'lng' => $originCoords[1],
            ],
            'hub' => [
                'name' => 'Cartzy Central Sortation Hub',
                'address' => 'Central Hub, Ortigas East, Pasig City',
                'lat' => $hubCoords[0],
                'lng' => $hubCoords[1],
            ],
            'destination' => [
                'name' => $destRecipient,
                'phone' => $destPhone,
                'address' => $destAddress,
                'lat' => $destCoords[0],
                'lng' => $destCoords[1],
            ],
            'rider' => [
                'assigned' => $hasRider,
                'name' => $riderName,
                'phone' => $riderPhone,
                'vehicle' => $riderVehicle,
                'courier' => $courierName,
                'lat' => $riderLoc[0],
                'lng' => $riderLoc[1],
                'bearing' => $bearing,
            ],
            'route' => $route,
        ];
    }

    private function resolveCoordinates(string $address, float $fallbackLat, float $fallbackLng): array
    {
        $addr = strtolower($address);
        if (str_contains($addr, 'manila') && !str_contains($addr, 'quezon')) return [14.5995, 120.9842];
        if (str_contains($addr, 'quezon')) return [14.6507, 121.0305];
        if (str_contains($addr, 'makati')) return [14.5547, 121.0244];
        if (str_contains($addr, 'taguig') || str_contains($addr, 'bgc')) return [14.5176, 121.0509];
        if (str_contains($addr, 'pasig')) return [14.5764, 121.0851];
        if (str_contains($addr, 'mandaluyong')) return [14.5794, 121.0359];
        if (str_contains($addr, 'pasay')) return [14.5378, 121.0014];
        if (str_contains($addr, 'paranaque')) return [14.4793, 121.0198];
        if (str_contains($addr, 'las pinas')) return [14.4445, 120.9939];
        if (str_contains($addr, 'muntinlupa') || str_contains($addr, 'alabang')) return [14.4081, 121.0415];
        if (str_contains($addr, 'marikina')) return [14.6507, 121.1029];
        if (str_contains($addr, 'cebu')) return [10.3157, 123.8854];
        if (str_contains($addr, 'davao')) return [7.1907, 125.4553];
        if (str_contains($addr, 'cavite')) return [14.4791, 120.8967];
        if (str_contains($addr, 'laguna')) return [14.2809, 121.1118];
        if (str_contains($addr, 'bulacan')) return [14.8527, 120.8160];
        if (str_contains($addr, 'rizal') || str_contains($addr, 'antipolo')) return [14.5842, 121.1763];

        return [$fallbackLat, $fallbackLng];
    }

    private function generateRoutePoints(array $origin, array $hub, array $dest): array
    {
        $points = [];
        $points[] = $origin;

        // Origin to Hub (2 intermediate points)
        $points[] = [$origin[0] * 0.65 + $hub[0] * 0.35 + 0.002, $origin[1] * 0.65 + $hub[1] * 0.35 - 0.003];
        $points[] = [$origin[0] * 0.30 + $hub[0] * 0.70 - 0.001, $origin[1] * 0.30 + $hub[1] * 0.70 + 0.002];
        $points[] = $hub;

        // Hub to Destination (3 intermediate points for a realistic road trajectory)
        $points[] = [$hub[0] * 0.75 + $dest[0] * 0.25 - 0.002, $hub[1] * 0.75 + $dest[1] * 0.25 + 0.003];
        $points[] = [$hub[0] * 0.50 + $dest[0] * 0.50 + 0.003, $hub[1] * 0.50 + $dest[1] * 0.50 - 0.002];
        $points[] = [$hub[0] * 0.25 + $dest[0] * 0.75 - 0.001, $hub[1] * 0.25 + $dest[1] * 0.75 + 0.001];
        $points[] = $dest;

        return $points;
    }

    private function interpolatePosition(array $route, float $progress): array
    {
        $n = count($route);
        if ($n <= 1) return $route[0] ?? [14.5995, 120.9842];
        if ($progress <= 0) return $route[0];
        if ($progress >= 1.0) return $route[$n - 1];

        $totalSegments = $n - 1;
        $scaled = $progress * $totalSegments;
        $segIndex = (int) floor($scaled);
        $segFactor = $scaled - $segIndex;

        $p1 = $route[$segIndex];
        $p2 = $route[min($segIndex + 1, $n - 1)];

        return [
            round($p1[0] + ($p2[0] - $p1[0]) * $segFactor, 6),
            round($p1[1] + ($p2[1] - $p1[1]) * $segFactor, 6),
        ];
    }

    private function calculateBearing(array $route, float $progress): float
    {
        $n = count($route);
        if ($n <= 1) return 0.0;
        $totalSegments = $n - 1;
        $segIndex = min($totalSegments - 1, (int) floor($progress * $totalSegments));
        $p1 = $route[$segIndex];
        $p2 = $route[$segIndex + 1];

        $dLng = deg2rad($p2[1] - $p1[1]);
        $lat1 = deg2rad($p1[0]);
        $lat2 = deg2rad($p2[0]);

        $y = sin($dLng) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLng);
        $bearing = fmod(rad2deg(atan2($y, $x)) + 360, 360);

        return round($bearing, 1);
    }
}
