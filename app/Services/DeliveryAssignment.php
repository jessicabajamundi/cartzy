<?php

namespace App\Services;

use App\Models\{DeliveryEvent, Rider, Shipment};
use App\Support\PlaceMatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeliveryAssignment
{
    // Only parcels physically at the hub may be assigned or reassigned.
    public const HUB_STATUSES = ['unassigned', 'picked_up', 'at_hub', 'assigned'];

    public function candidates(Shipment $shipment)
    {
        $shipping = $shipment->sellerOrder?->order?->shipping_address ?? [];
        $shipping = is_array($shipping) ? $shipping : [];
        $shipping['barangay'] = $shipping['barangay'] ?? $shipping['brgy'] ?? '';
        $destination = PlaceMatcher::placeOf($shipping);

        return Rider::where('logistics_provider_id', $shipment->logistics_provider_id)->availableForDelivery()
            ->with('user')->withCount(['shipments as active_load' => fn ($q) => $q
                ->whereIn('status', ['assigned', 'out_for_delivery', 'in_transit'])])->get()
            ->map(function ($rider) use ($destination, $shipping) {
                $matches = collect([$rider->designated_place, $rider->user])->filter()->map(function ($place) use ($destination, $shipping) {
                    $place = PlaceMatcher::placeOf($place);
                    return PlaceMatcher::known($destination)
                        ? PlaceMatcher::compare($destination, $place)
                        : PlaceMatcher::compareToText($place, (string) ($shipping['address'] ?? ''));
                });
                $match = $matches->sortByDesc('score')->first();
                return ['rider' => $rider, 'method' => ($match['level'] ?? null) === 'barangay' ? 'barangay' : 'fallback',
                    'score' => $match['score'] ?? 0];
            })
            // Barangay preference is absolute, never outweighed by load or rating.
            ->sortBy(fn ($row) => [$row['method'] === 'barangay' ? 0 : 1, -$row['score'], $row['rider']->active_load, $row['rider']->id])
            ->values();
    }

    public function assign(Shipment $shipment, ?int $riderId = null, ?int $actorId = null): ?Shipment
    {
        return DB::transaction(function () use ($shipment, $riderId, $actorId) {
            $parcel = Shipment::whereKey($shipment->id)->lockForUpdate()->firstOrFail();
            if (!$parcel->received_at || !in_array($parcel->status, self::HUB_STATUSES, true)
                || in_array($parcel->sellerOrder?->status, ['cancelled', 'canceled', 'delivered', 'completed'], true)) {
                if ($riderId !== null) {
                    throw ValidationException::withMessages(['rider_id' => 'Only parcels still at the hub can be assigned.']);
                }
                return null;
            }
            // Repeated automatic requests must preserve any previous assignment (including manual overrides).
            if ($riderId === null && $parcel->rider_id) {
                return $parcel;
            }
            $candidates = $this->candidates($parcel);
            foreach ($candidates as $candidate) {
                if ($riderId !== null && $candidate['rider']->id !== $riderId) continue;
                // Recheck availability under a lock: a recommendation is not a reservation.
                $rider = Rider::whereKey($candidate['rider']->id)->availableForDelivery()->lockForUpdate()->first();
                if (!$rider) continue;
                if ($parcel->rider_id === $rider->id) return $parcel;
                $previous = $parcel->rider_id;
                $method = $riderId !== null ? 'manual' : $candidate['method'];
                $parcel->update(['rider_id' => $rider->id, 'status' => 'assigned', 'sort_status' => 'assigned', 'assigned_at' => now()]);
                DeliveryEvent::create([
                    'shipment_id' => $parcel->id, 'status' => 'assigned_to_rider',
                    'attempt' => DeliveryEvent::nextAttempt($parcel->id, 'assigned_to_rider'),
                    'user_id' => $actorId ?? $parcel->logisticsProvider->user_id, 'occurred_at' => now(),
                    'note' => "Assignment ({$method}): rider #{$rider->id}; previous rider: ".($previous ?? 'none').'.',
                ]);
                return $parcel;
            }
            if ($riderId !== null) {
                throw ValidationException::withMessages(['rider_id' => 'Choose an online, active, approved Last Mile rider from this hub.']);
            }
            return null; // Keep waiting when every rider is offline; never assign an unavailable rider.
        }, 3);
    }

    public function assignWaiting(int $providerId): array
    {
        $assigned = [];
        Shipment::where('logistics_provider_id', $providerId)->whereNotNull('received_at')->whereNull('rider_id')
            ->whereIn('status', self::HUB_STATUSES)->eachById(function ($parcel) use (&$assigned) {
                if ($result = $this->assign($parcel)) $assigned[$result->id] = $result->rider_id;
            });
        return $assigned;
    }
}
