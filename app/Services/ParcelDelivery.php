<?php

namespace App\Services;

use App\Models\{Address, LogisticsProvider, Rider, Seller, Shipment, User};
use App\Support\PlaceMatcher;
use RuntimeException;

/** One store shipment may contain several packages; save its requirements at checkout. */
class ParcelDelivery
{
    public function profile(array $items): array
    {
        $profile = ['weight_kg' => 0, 'volume_cm3' => 0, 'max_dimensions_cm' => [0, 0, 0], 'bulky' => false];
        foreach ($items as $item) {
            $item['id'] = $item['product_id'] ?? $item['id'] ?? null;
            $variant = app(ProductPricing::class)->variant($item);
            if (!$variant) throw new RuntimeException('An item is no longer available. Refresh your cart.');
            $product = $variant->product;
            $dimensions = [(float) $product->package_length_cm, (float) $product->package_width_cm, (float) $product->package_height_cm];
            if ($variant->weight_grams <= 0 || min($dimensions) <= 0) {
                throw new RuntimeException('Delivery is unavailable for '.$product->name.'. The seller must add packaged weight and dimensions before checkout.');
            }
            rsort($dimensions);
            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $profile['weight_kg'] += $variant->weight_grams / 1000 * $qty;
            $profile['volume_cm3'] += array_product($dimensions) * $qty;
            foreach ($dimensions as $index => $size) $profile['max_dimensions_cm'][$index] = max($profile['max_dimensions_cm'][$index], $size);
        }
        $profile['bulky'] = !collect(['motorcycle', 'tricycle', 'ebike'])->contains(fn ($vehicle) => $this->fits($profile, $vehicle));
        return $profile;
    }

    public function fits(array $profile, ?string $vehicle): bool
    {
        $vehicle = Rider::normalizeVehicle($vehicle);
        $limit = config('parcel_delivery.vehicles.'.$vehicle);
        if (!$limit || (($profile['bulky'] ?? false) && !in_array($vehicle, config('parcel_delivery.bulky_vehicles'), true))) return false;
        $dimensions = $limit['cm'];
        rsort($dimensions);
        if (($profile['weight_kg'] ?? 0) > $limit['kg'] || ($profile['volume_cm3'] ?? 0) > array_product($dimensions)) return false;
        foreach (($profile['max_dimensions_cm'] ?? []) as $index => $size) if ($size > ($dimensions[$index] ?? 0)) return false;
        return true;
    }

    public function canCarry(Shipment $shipment, Rider $driver): bool
    {
        // Existing shipments have no measurement snapshot; preserve their existing workflow.
        return !$shipment->package_profile || $this->fits($shipment->package_profile, $driver->vehicle_type);
    }

    private function fleetCanCarry(LogisticsProvider $hub, array $profile): bool
    {
        return $hub->riders()->where('status', 'approved')->where('is_active', true)
            ->whereHas('user', fn ($q) => $q->where('role', 'courier')->where('status', 'active')->where('is_suspended', false))
            ->get()->contains(fn ($driver) => $this->fits($profile, $driver->vehicle_type));
    }

    public function canLoad(Shipment $shipment, Rider $driver): bool
    {
        if (!$this->canCarry($shipment, $driver)) return false;
        if (!$shipment->package_profile) return true;
        $profile = $shipment->package_profile;
        $reserved = Shipment::whereKeyNot($shipment->id)->where(function ($q) use ($driver) {
            $q->where(fn ($q) => $q->where('rider_id', $driver->id)->whereIn('status', ['assigned', 'out_for_delivery']))
                ->orWhere(fn ($q) => $q->where('pickup_rider_id', $driver->id)->whereNull('received_at')->whereIn('status', ['unassigned', 'assigned', 'picked_up']))
                ->orWhere(fn ($q) => $q->where('linehaul_courier_id', $driver->id)->where('status', 'in_transit'));
        })->get();
        foreach ($reserved as $other) {
            if (!$other->package_profile) return false;
            $profile['weight_kg'] += $other->package_profile['weight_kg'];
            $profile['volume_cm3'] += $other->package_profile['volume_cm3'];
        }
        return $this->fits($profile, $driver->vehicle_type);
    }

    public function plan(Seller $seller, Address|User|null $destination, array $profile): array
    {
        $hubs = LogisticsProvider::where('status', 'approved')->with('user')->orderBy('id')->get();
        if ($hubs->isEmpty()) throw new RuntimeException('No delivery partner is available.');
        $recommender = app(HubRecommender::class);
        if (!$profile['bulky']) {
            $origin = $recommender->originHub($seller, $hubs);
            $destinationId = app(HubToHubService::class)->resolveDestinationHub($destination, $origin->id);
            return ['origin_hub_id' => $origin->id, 'destination_hub_id' => $destinationId, 'surcharge_minor' => 0];
        }
        $eligible = $hubs->filter(fn ($hub) => $hub->bulky_enabled && $this->fleetCanCarry($hub, $profile));
        $choose = function ($place) use ($eligible, $recommender) {
            $region = PlaceMatcher::placeOf($place)['region'] ?? null;
            $covered = $eligible->filter(fn ($hub) => $region && in_array($region, $hub->service_regions ?? [], true));
            $row = $recommender->rank($covered, $place)->firstWhere('recommended', true);
            return $row['provider'] ?? null;
        };
        $origin = $choose($recommender->pickupLocation($seller));
        $target = $choose($destination);
        if (!$origin || !$target) throw new RuntimeException('Bulky delivery is currently unavailable for this item at your address. A capable van/truck partner is required at both ends of the route.');
        return ['origin_hub_id' => $origin->id, 'destination_hub_id' => $target->id,
            'surcharge_minor' => (int) $origin->bulky_surcharge_minor + ($target->id !== $origin->id ? (int) $target->bulky_surcharge_minor : 0)];
    }

    public function quotes(array $storeItems, ?Address $destination, ?User $buyer = null): array
    {
        $quotes = ShippingCalculator::forStores(array_keys($storeItems), $destination);
        foreach ($storeItems as $sellerId => $items) {
            try {
                $seller = Seller::with('pickupAddress', 'user')->findOrFail($sellerId);
                $profile = $this->profile($items);
                $plan = $this->plan($seller, $destination ?? ($profile['bulky'] ? null : $buyer), $profile);
                $quotes[$sellerId] = array_merge($quotes[$sellerId], $plan, ['available' => true, 'package_profile' => $profile,
                    'service_label' => $profile['bulky'] ? 'Bulky delivery · van/truck required' : 'Standard delivery',
                    'fee_minor' => $quotes[$sellerId]['fee_minor'] + $plan['surcharge_minor']]);
            } catch (RuntimeException $error) {
                $quotes[$sellerId] += ['available' => false, 'message' => $error->getMessage()];
            }
        }
        return $quotes;
    }
}
