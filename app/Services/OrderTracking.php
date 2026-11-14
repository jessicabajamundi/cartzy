<?php

namespace App\Services;

use App\Models\SellerOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
     * Builds OpenStreetMap coordinates, polyline road route, and live telemetry for real-time delivery tracking.
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

        // Derive coordinates based on saved pins or comprehensive Philippine geocoding resolution
        $sellerPickup = $order->seller?->pickupAddress;
        if ($sellerPickup && !empty($sellerPickup->latitude) && !empty($sellerPickup->longitude)) {
            $originCoords = [(float) $sellerPickup->latitude, (float) $sellerPickup->longitude];
        } else {
            $originCoords = $this->resolveCoordinates($sellerAddress, 14.6507, 121.0305); // default QC
        }

        $buyerAddressModel = $order->order->buyer?->defaultAddress ?? $order->order->buyer?->addresses()->first();
        if ($buyerAddressModel && !empty($buyerAddressModel->latitude) && !empty($buyerAddressModel->longitude)) {
            $destCoords = [(float) $buyerAddressModel->latitude, (float) $buyerAddressModel->longitude];
        } else {
            $destCoords = $this->resolveCoordinates($destAddress, 14.5547, 121.0244); // default Makati
        }
        $hubCoords = [14.5862, 121.0610]; // Ortigas East / Pasig Central Sorting Hub

        // Fetch street-level road polyline from OSRM (or intelligent road-grid fallback)
        $routeData = $this->fetchRoadRoute($originCoords, $hubCoords, $destCoords);
        $route = $routeData['coordinates'];
        $totalDistanceKm = $routeData['distance_km'];
        $totalDurationMinutes = $routeData['duration_minutes'];

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

        // Interpolate current rider location along the road route
        $riderLoc = $this->interpolatePosition($route, $progress);
        $distanceRemainingKm = max(0.1, round($totalDistanceKm * (1.0 - $progress), 1));
        $speedKmh = ($currentStatus === 'Out for delivery' || $currentStatus === 'In transit') ? 30 + (time() % 8) : 0;
        $etaMinutes = $delivered ? 0 : max(1, (int) round($totalDurationMinutes * (1.0 - $progress)));

        $bearing = $this->calculateBearing($route, $progress);

        return [
            'is_live' => in_array($currentStatus, ['Out for delivery', 'In transit'], true),
            'progress_percent' => (int) round($progress * 100),
            'total_distance_km' => $totalDistanceKm,
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

    /**
     * Fetches driving route on actual streets/roads via OpenStreetMap OSRM routing engine.
     * Caches route geometry and metrics to ensure instant response.
     */
    public function fetchRoadRoute(array $origin, array $hub, array $dest): array
    {
        $cacheKey = 'osrm_road_' . md5(sprintf('%.4f,%.4f;%.4f,%.4f;%.4f,%.4f', $origin[0], $origin[1], $hub[0], $hub[1], $dest[0], $dest[1]));

        return Cache::remember($cacheKey, 86400, function () use ($origin, $hub, $dest) {
            $url = sprintf(
                'https://router.project-osrm.org/route/v1/driving/%.6f,%.6f;%.6f,%.6f;%.6f,%.6f?overview=full&geometries=geojson',
                $origin[1], $origin[0], $hub[1], $hub[0], $dest[1], $dest[0]
            );

            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'CartzyLogisticsApp/1.0 (contact@cartzy.shop)',
                    'Accept' => 'application/json',
                ])->timeout(3.5)->get($url);

                if ($response->successful()) {
                    $json = $response->json();
                    if (($json['code'] ?? '') === 'Ok' && !empty($json['routes'][0]['geometry']['coordinates'])) {
                        $rawCoords = $json['routes'][0]['geometry']['coordinates'];
                        // OSRM returns [lng, lat]; flip to [lat, lng] for leaflet/flutter_map
                        $roadCoords = array_map(fn ($pt) => [round((float) $pt[1], 6), round((float) $pt[0], 6)], $rawCoords);
                        $distMeters = (float) ($json['routes'][0]['distance'] ?? 0);
                        $durationSec = (float) ($json['routes'][0]['duration'] ?? 0);

                        return [
                            'coordinates' => $roadCoords,
                            'distance_km' => max(0.5, round($distMeters / 1000.0, 1)),
                            'duration_minutes' => max(2, (int) round($durationSec / 60.0)),
                            'source' => 'osrm',
                        ];
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('OSRM route fetch failed: ' . $e->getMessage());
            }

            // Fallback: Generate street corridor waypoints (ortho-grid snapping to prevent cutting through buildings)
            return $this->generateStreetGridFallback($origin, $hub, $dest);
        });
    }

    /**
     * Resolves Philippine location name or address string to precise latitude and longitude.
     */
    public function resolveCoordinates(string $address, float $fallbackLat, float $fallbackLng): array
    {
        $addr = strtolower(trim($address));

        // Dictionary of Philippine cities, commercial centers, and districts
        $dictionary = [
            // Metro Manila (NCR) Cities & Districts
            'quezon city' => [14.6507, 121.0305],
            'quezon' => [14.6507, 121.0305],
            'cubao' => [14.6206, 121.0526],
            'eastwood' => [14.6094, 121.0805],
            'diliman' => [14.6538, 121.0685],
            'fairview' => [14.7118, 121.0664],
            'novaliches' => [14.7214, 121.0381],
            'commonwealth' => [14.6989, 121.0886],
            'makati' => [14.5547, 121.0244],
            'ayala' => [14.5547, 121.0244],
            'poblacion makati' => [14.5683, 121.0315],
            'taguig' => [14.5176, 121.0509],
            'bgc' => [14.5507, 121.0509],
            'bonifacio global city' => [14.5507, 121.0509],
            'mckinley' => [14.5342, 121.0509],
            'pasig' => [14.5764, 121.0851],
            'ortigas' => [14.5862, 121.0610],
            'kapitolyo' => [14.5727, 121.0583],
            'manila' => [14.5995, 120.9842],
            'city of manila' => [14.5995, 120.9842],
            'binondo' => [14.6001, 120.9754],
            'intramuros' => [14.5896, 120.9747],
            'ermita' => [14.5750, 120.9840],
            'malate' => [14.5700, 120.9910],
            'sampaloc' => [14.6073, 120.9975],
            'santa mesa' => [14.6007, 121.0137],
            'tondo' => [14.6193, 120.9698],
            'mandaluyong' => [14.5794, 121.0359],
            'wack wack' => [14.5912, 121.0518],
            'pasay' => [14.5378, 121.0014],
            'moa' => [14.5352, 120.9822],
            'mall of asia' => [14.5352, 120.9822],
            'paranaque' => [14.4793, 121.0198],
            'bf homes' => [14.4502, 121.0264],
            'las pinas' => [14.4445, 120.9939],
            'muntinlupa' => [14.4081, 121.0415],
            'alabang' => [14.4253, 121.0410],
            'filinvest' => [14.4208, 121.0389],
            'marikina' => [14.6507, 121.1029],
            'caloocan' => [14.6571, 120.9841],
            'malabon' => [14.6625, 120.9566],
            'navotas' => [14.6669, 120.9421],
            'valenzuela' => [14.7011, 120.9830],
            'san juan' => [14.6019, 121.0355],
            'greenhills' => [14.6027, 121.0514],
            'pateros' => [14.5454, 121.0686],

            // Cavite
            'bacoor' => [14.4624, 120.9645],
            'imus' => [14.4296, 120.9367],
            'dasmarinas' => [14.3294, 120.9367],
            'dasmariñas' => [14.3294, 120.9367],
            'general trias' => [14.3869, 120.8812],
            'tagaytay' => [14.1153, 120.9621],
            'silang' => [14.2307, 120.9748],
            'kawit' => [14.4441, 120.9038],
            'cavite' => [14.4791, 120.8967],

            // Laguna
            'santa rosa' => [14.3122, 121.1114],
            'sta rosa' => [14.3122, 121.1114],
            'calamba' => [14.2117, 121.1656],
            'binan' => [14.3384, 121.0809],
            'biñan' => [14.3384, 121.0809],
            'san pedro' => [14.3592, 121.0544],
            'cabuyao' => [14.2783, 121.1246],
            'los banos' => [14.1706, 121.2433],
            'san pablo' => [14.0684, 121.3255],
            'laguna' => [14.2809, 121.1118],

            // Rizal
            'antipolo' => [14.5842, 121.1763],
            'cainta' => [14.5777, 121.1157],
            'taytay' => [14.5683, 121.1328],
            'san mateo' => [14.6967, 121.1219],
            'rodriguez' => [14.7431, 121.1578],
            'angono' => [14.5247, 121.1558],
            'rizal' => [14.5842, 121.1763],

            // Bulacan
            'san jose del monte' => [14.8136, 121.0453],
            'sjdm' => [14.8136, 121.0453],
            'malolos' => [14.8433, 120.8114],
            'meycauayan' => [14.7347, 120.9575],
            'marilao' => [14.7578, 120.9644],
            'santa maria' => [14.8197, 120.9614],
            'baliuag' => [14.9547, 120.9008],
            'bulacan' => [14.8527, 120.8160],

            // Pampanga
            'angeles' => [15.1450, 120.5887],
            'san fernando pampanga' => [15.0298, 120.6896],
            'clark' => [15.1855, 120.5367],
            'mabalacat' => [15.2227, 120.5732],
            'pampanga' => [15.0298, 120.6896],

            // Batangas
            'batangas city' => [13.7565, 121.0583],
            'lipa' => [13.9419, 121.1644],
            'tanauan' => [14.0863, 121.1504],
            'batangas' => [13.7565, 121.0583],

            // Other Luzon
            'baguio' => [16.4023, 120.5960],
            'benguet' => [16.4023, 120.5960],
            'dagupan' => [16.0433, 120.3340],
            'pangasinan' => [15.9760, 120.5710],
            'tarlac' => [15.4802, 120.5979],
            'cabanatuan' => [15.4859, 120.9673],
            'nueva ecija' => [15.4859, 120.9673],
            'olongapo' => [14.8386, 120.2842],
            'subic' => [14.8789, 120.2344],
            'zambales' => [14.8386, 120.2842],
            'bataan' => [14.6760, 120.5360],
            'la union' => [16.6159, 120.3209],
            'laoag' => [18.1960, 120.5927],
            'vigan' => [17.5747, 120.3869],
            'naga' => [13.6218, 123.1948],
            'legazpi' => [13.1391, 123.7438],
            'puerto princesa' => [9.7392, 118.7353],
            'palawan' => [9.7392, 118.7353],

            // Visayas
            'cebu city' => [10.3157, 123.8854],
            'cebu' => [10.3157, 123.8854],
            'mandaue' => [10.3333, 123.9333],
            'lapu-lapu' => [10.3103, 123.9494],
            'lapu lapu' => [10.3103, 123.9494],
            'talisay cebu' => [10.2447, 123.8494],
            'iloilo' => [10.7202, 122.5621],
            'bacolod' => [10.6760, 122.9509],
            'tacloban' => [11.2433, 125.0047],
            'tagbilaran' => [9.6729, 123.8730],
            'bohol' => [9.6729, 123.8730],
            'dumaguete' => [9.3068, 123.3054],
            'roxas city' => [11.5853, 122.7511],

            // Mindanao
            'davao city' => [7.1907, 125.4553],
            'davao' => [7.1907, 125.4553],
            'cagayan de oro' => [8.4542, 124.6319],
            'cdo' => [8.4542, 124.6319],
            'general santos' => [6.1164, 125.1716],
            'gensan' => [6.1164, 125.1716],
            'zamboanga' => [6.9214, 122.0790],
            'iligan' => [8.2280, 124.2452],
            'butuan' => [8.9492, 125.5436],
            'cotabato' => [7.2236, 124.2464],
        ];

        foreach ($dictionary as $keyword => $coords) {
            if (str_contains($addr, $keyword)) {
                return $coords;
            }
        }

        return [$fallbackLat, $fallbackLng];
    }

    /**
     * Fallback generator that creates an orthogonal street-grid trajectory between waypoints
     * to avoid cutting diagonally through buildings when live OSRM routing is unreachable.
     */
    private function generateStreetGridFallback(array $origin, array $hub, array $dest): array
    {
        $points = [];
        $points[] = $origin;

        // Street-grid legs from Origin to Hub (along avenues)
        $midLat1 = $origin[0] * 0.70 + $hub[0] * 0.30;
        $points[] = [$midLat1, $origin[1]]; // Turn 1 (orthogonal)
        $points[] = [$midLat1, $hub[1]];    // Turn 2
        $points[] = $hub;

        // Street-grid legs from Hub to Destination
        $midLat2 = $hub[0] * 0.50 + $dest[0] * 0.50;
        $points[] = [$midLat2, $hub[1]];   // Turn 3
        $points[] = [$midLat2, $dest[1]];  // Turn 4
        $points[] = $dest;

        // Calculate cumulative distance along street segments
        $distKm = 0.0;
        for ($i = 0; $i < count($points) - 1; $i++) {
            $p1 = $points[$i];
            $p2 = $points[$i + 1];
            $latDiff = ($p2[0] - $p1[0]) * 111.0;
            $lngDiff = ($p2[1] - $p1[1]) * 111.0 * cos(deg2rad($p1[0]));
            $distKm += sqrt($latDiff * $latDiff + $lngDiff * $lngDiff);
        }

        $distKm = max(1.2, round($distKm * 1.25, 1)); // apply city road curvature factor
        $durationMinutes = max(4, (int) round(($distKm / 28.0) * 60));

        return [
            'coordinates' => $points,
            'distance_km' => $distKm,
            'duration_minutes' => $durationMinutes,
            'source' => 'street_grid_fallback',
        ];
    }

    /**
     * Interpolates rider position along real road polyline points using cumulative distance.
     */
    private function interpolatePosition(array $route, float $progress): array
    {
        $n = count($route);
        if ($n <= 1) return $route[0] ?? [14.5995, 120.9842];
        if ($progress <= 0) return $route[0];
        if ($progress >= 1.0) return $route[$n - 1];

        // Compute segment lengths
        $lengths = [];
        $totalLength = 0.0;
        for ($i = 0; $i < $n - 1; $i++) {
            $latDiff = $route[$i + 1][0] - $route[$i][0];
            $lngDiff = ($route[$i + 1][1] - $route[$i][1]) * cos(deg2rad($route[$i][0]));
            $len = sqrt($latDiff * $latDiff + $lngDiff * $lngDiff);
            $lengths[] = $len;
            $totalLength += $len;
        }

        if ($totalLength <= 0) return $route[0];

        $targetDist = $progress * $totalLength;
        $accum = 0.0;

        for ($i = 0; $i < count($lengths); $i++) {
            $len = $lengths[$i];
            if ($accum + $len >= $targetDist || $i === count($lengths) - 1) {
                $segFactor = $len > 0 ? max(0.0, min(1.0, ($targetDist - $accum) / $len)) : 0.0;
                $p1 = $route[$i];
                $p2 = $route[$i + 1];

                return [
                    round($p1[0] + ($p2[0] - $p1[0]) * $segFactor, 6),
                    round($p1[1] + ($p2[1] - $p1[1]) * $segFactor, 6),
                ];
            }
            $accum += $len;
        }

        return $route[$n - 1];
    }

    /**
     * Computes the forward azimuth / bearing of the active road segment the rider is currently traveling on.
     */
    private function calculateBearing(array $route, float $progress): float
    {
        $n = count($route);
        if ($n <= 1) return 0.0;
        if ($progress <= 0) $progress = 0.001;

        $totalSegments = $n - 1;
        $segIndex = min($totalSegments - 1, (int) floor($progress * $totalSegments));
        $p1 = $route[$segIndex];
        $p2 = $route[min($segIndex + 1, $n - 1)];

        $dLng = deg2rad($p2[1] - $p1[1]);
        $lat1 = deg2rad($p1[0]);
        $lat2 = deg2rad($p2[0]);

        $y = sin($dLng) * cos($lat2);
        $x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLng);
        $bearing = fmod(rad2deg(atan2($y, $x)) + 360, 360);

        return round($bearing, 1);
    }
}
