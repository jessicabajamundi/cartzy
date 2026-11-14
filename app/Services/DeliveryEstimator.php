<?php

namespace App\Services;

use App\Models\Address;
use App\Models\Seller;
use App\Models\User;

class DeliveryEstimator
{
    /**
     * Resolves seller address details (city, province, region).
     */
    public static function sellerLocation(?Seller $seller): array
    {
        if (!$seller) {
            return [
                'city' => null,
                'province' => config('shipping.default_origin', 'Metro Manila'),
                'region' => null,
            ];
        }

        $address = $seller->pickupAddress;
        if (!$address && $seller->relationLoaded('user') && $seller->user) {
            $address = $seller->user->defaultAddress ?: $seller->user->addresses()->first();
        }

        $city = $address?->city ?: ($seller->user?->city ?? null);
        $province = $address?->province ?: ($seller->user?->province ?? config('shipping.default_origin', 'Metro Manila'));
        $region = $address?->region ?: ($seller->user?->region ?? null);

        return [
            'city' => $city,
            'province' => $province ?: config('shipping.default_origin', 'Metro Manila'),
            'region' => $region,
        ];
    }

    /**
     * Resolves buyer address details (city, province, region).
     */
    public static function buyerLocation($buyer = null): ?array
    {
        if ($buyer instanceof Address) {
            return [
                'city' => $buyer->city,
                'province' => $buyer->province,
                'region' => $buyer->region,
            ];
        }

        $user = $buyer instanceof User ? $buyer : (auth()->user() ?: request()?->user());
        if (!$user) {
            return null;
        }

        $address = $user->defaultAddress ?: $user->addresses()->first();
        $city = $address?->city ?: $user->city;
        $province = $address?->province ?: $user->province;
        $region = $address?->region ?: $user->region;

        if (!$city && !$province && !$region) {
            return null;
        }

        return [
            'city' => $city,
            'province' => $province,
            'region' => $region,
        ];
    }

    /**
     * Estimate delivery time between seller and buyer.
     *
     * Philippine logistics rules:
     * - Same City/Municipality: 1–2 days (⚡ Est. 1-2 days)
     * - Same Province or both Metro Manila / NCR: 1–3 days (🚚 Est. 1-3 days)
     * - Same Island Group (both Luzon, both Visayas, both Mindanao): 2–4 days (🚚 Est. 2-4 days)
     * - Neighbouring Island (Luzon <-> Visayas, Visayas <-> Mindanao): 3–5 days (✈️ Est. 3-5 days)
     * - Far Island (Luzon <-> Mindanao): 4–7 days (✈️ Est. 4-7 days)
     * - Default / Guest / Unknown: 2–4 days (🚚 Est. 2-4 days)
     *
     * @return array{
     *   min_days: int,
     *   max_days: int,
     *   days_text: string,
     *   badge: string,
     *   badge_icon: string,
     *   label: string,
     *   description: string,
     *   is_express: bool
     * }
     */
    public static function estimate(?Seller $seller, $buyer = null): array
    {
        $sellerLoc = self::sellerLocation($seller);
        $buyerLoc = self::buyerLocation($buyer);

        if (!$buyerLoc || empty($buyerLoc['province'])) {
            return [
                'min_days' => 2,
                'max_days' => 4,
                'days_text' => '2-4 days',
                'badge' => '🚚 Est. 2-4 days',
                'badge_icon' => '🚚',
                'label' => 'Est. 2-4 days',
                'description' => 'Standard nationwide delivery',
                'is_express' => false,
            ];
        }

        $sCity = ShippingCalculator::normalize($sellerLoc['city'] ?? '');
        $bCity = ShippingCalculator::normalize($buyerLoc['city'] ?? '');
        $sProv = ShippingCalculator::normalize($sellerLoc['province'] ?? '');
        $bProv = ShippingCalculator::normalize($buyerLoc['province'] ?? '');

        $isMetroSeller = in_array($sProv, ['metromanila', 'ncr', 'nationalcapitalregion'], true);
        $isMetroBuyer = in_array($bProv, ['metromanila', 'ncr', 'nationalcapitalregion'], true);

        // 1. Same City Check (both city names match and either same province or both in Metro Manila)
        if ($sCity !== '' && $bCity !== '' && $sCity === $bCity && ($sProv === $bProv || ($isMetroSeller && $isMetroBuyer))) {
            return [
                'min_days' => 1,
                'max_days' => 2,
                'days_text' => '1-2 days',
                'badge' => '⚡ Est. 1-2 days',
                'badge_icon' => '⚡',
                'label' => 'Est. 1-2 days',
                'description' => 'Same-city local delivery',
                'is_express' => true,
            ];
        }

        // 2. Same Province or both in Metro Manila
        if (($sProv !== '' && $sProv === $bProv) || ($isMetroSeller && $isMetroBuyer)) {
            return [
                'min_days' => 1,
                'max_days' => 3,
                'days_text' => '1-3 days',
                'badge' => '🚚 Est. 1-3 days',
                'badge_icon' => '🚚',
                'label' => 'Est. 1-3 days',
                'description' => 'Within province delivery',
                'is_express' => false,
            ];
        }

        // 3. Island groups
        $sIsland = ShippingCalculator::islandFor($sellerLoc['province'] ?? '');
        $bIsland = ShippingCalculator::islandFor($buyerLoc['province'] ?? '');

        if ($sIsland && $bIsland) {
            if ($sIsland === $bIsland) {
                return [
                    'min_days' => 2,
                    'max_days' => 4,
                    'days_text' => '2-4 days',
                    'badge' => '🚚 Est. 2-4 days',
                    'badge_icon' => '🚚',
                    'label' => 'Est. 2-4 days',
                    'description' => 'Same island group delivery',
                    'is_express' => false,
                ];
            }

            // Luzon <-> Visayas or Visayas <-> Mindanao
            if (in_array('visayas', [$sIsland, $bIsland], true)) {
                return [
                    'min_days' => 3,
                    'max_days' => 5,
                    'days_text' => '3-5 days',
                    'badge' => '✈️ Est. 3-5 days',
                    'badge_icon' => '✈️',
                    'label' => 'Est. 3-5 days',
                    'description' => 'Inter-island delivery',
                    'is_express' => false,
                ];
            }

            // Luzon <-> Mindanao
            return [
                'min_days' => 4,
                'max_days' => 7,
                'days_text' => '4-7 days',
                'badge' => '✈️ Est. 4-7 days',
                'badge_icon' => '✈️',
                'label' => 'Est. 4-7 days',
                'description' => 'Inter-island express delivery',
                'is_express' => false,
            ];
        }

        // Fallback
        return [
            'min_days' => 2,
            'max_days' => 4,
            'days_text' => '2-4 days',
            'badge' => '🚚 Est. 2-4 days',
            'badge_icon' => '🚚',
            'label' => 'Est. 2-4 days',
            'description' => 'Standard nationwide delivery',
            'is_express' => false,
        ];
    }
}
