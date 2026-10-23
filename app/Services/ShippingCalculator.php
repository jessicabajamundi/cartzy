<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Seller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prices each store's parcel by route, like Shopee/Lazada: the store's pickup province to the
 * buyer's province, grouped into zones whose fees the admin sets on the Shipping rates page.
 */
class ShippingCalculator
{
    /** Zone settings with the admin's current fees: zone => [label, description, fee_minor]. */
    public static function zones(): array
    {
        return once(function () {
            $zones = config('shipping.zones');
            if (Schema::hasTable('shipping_rates')) {
                foreach (DB::table('shipping_rates')->pluck('fee_minor', 'zone') as $zone => $fee) {
                    if (isset($zones[$zone])) $zones[$zone]['fee_minor'] = (int) $fee;
                }
            }

            return $zones;
        });
    }

    public static function normalize(?string $province): string
    {
        return preg_replace('/[^a-z]/', '', strtolower((string) $province));
    }

    /** "luzon", "visayas", "mindanao", or null when the province isn't recognised. */
    public static function islandFor(?string $province): ?string
    {
        $lookup = once(function () {
            $map = [];
            foreach (config('shipping.islands') as $island => $provinces) {
                foreach ($provinces as $name) $map[self::normalize($name)] = $island;
            }

            return $map;
        });
        $key = self::normalize($province);

        return $key === '' ? null : ($lookup[$key] ?? null);
    }

    public static function zoneFor(?string $fromProvince, ?string $toProvince): string
    {
        $from = self::islandFor($fromProvince);
        $to = self::islandFor($toProvince);
        if (!$from || !$to) {
            return 'standard';
        }
        if (self::normalize($fromProvince) === self::normalize($toProvince) || self::isMetroManila($fromProvince) && self::isMetroManila($toProvince)) {
            return 'same_area';
        }
        if ($from === $to) {
            return 'same_island';
        }

        return in_array('visayas', [$from, $to], true) ? 'near_island' : 'far_island';
    }

    /** @return array{zone: string, label: string, fee_minor: int} */
    public static function quote(?string $fromProvince, ?string $toProvince): array
    {
        $zone = self::zoneFor($fromProvince, $toProvince);
        $settings = self::zones()[$zone];

        return ['zone' => $zone, 'label' => $settings['label'], 'fee_minor' => (int) $settings['fee_minor']];
    }

    /** The province a store ships from: its pickup address, or the marketplace default. */
    public static function originFor(?Seller $seller): string
    {
        return $seller?->pickupAddress?->province ?: config('shipping.default_origin');
    }

    /**
     * One quote per store for a delivery address.
     *
     * @param  array<int>  $sellerIds
     * @return array<int, array{zone: string, label: string, fee_minor: int, from: string}>
     */
    public static function forStores(array $sellerIds, ?Address $destination): array
    {
        $sellers = Seller::with('pickupAddress')->whereIn('id', $sellerIds)->get()->keyBy('id');
        $quotes = [];
        foreach ($sellerIds as $sellerId) {
            $from = self::originFor($sellers->get($sellerId));
            $quotes[$sellerId] = self::quote($from, $destination?->province) + ['from' => $from];
        }

        return $quotes;
    }

    /** Lowest fee any address could pay, for "Shipping from ₱50" hints. */
    public static function lowestFeeMinor(): int
    {
        return (int) collect(self::zones())->except('standard')->min('fee_minor');
    }

    private static function isMetroManila(?string $province): bool
    {
        return in_array(self::normalize($province), ['metromanila', 'ncr', 'nationalcapitalregion'], true);
    }
}
