<?php

namespace App\Services;

use App\Models\Address;
use App\Models\LogisticsProvider;
use App\Models\Seller;
use App\Models\User;
use App\Support\PlaceMatcher;
use Illuminate\Support\Collection;

/**
 * Ranks logistics hubs for a seller by how close the hub's office is to the
 * seller's pickup address (barangay > city > province > region), and whether
 * the hub serves the seller's region. Address matching only, not GPS.
 */
class HubRecommender
{
    public function pickupLocation(?Seller $seller): Address|User|null
    {
        if (!$seller) return null;
        if ($seller->pickupAddress) return $seller->pickupAddress;
        $saved = Address::where('user_id', $seller->user_id)->orderByDesc('is_default')->orderBy('id')->first();
        if ($saved) return $saved;
        $owner = $seller->user;
        return $owner && PlaceMatcher::known(PlaceMatcher::placeOf($owner)) ? $owner : null;
    }

    /** Prefer an address match over a legacy store assignment or database default. */
    public function originHub(Seller $seller, Collection $approvedProviders): ?LogisticsProvider
    {
        $recommended = $this->rank($approvedProviders, $this->pickupLocation($seller))->firstWhere('recommended', true);
        return $recommended['provider'] ?? $approvedProviders->firstWhere('id', $seller->logistics_provider_id)
            ?? $approvedProviders->first();
    }

    /** A hub that lists the seller's region as a service area, when nothing closer matches. */
    private const SERVES_REGION_SCORE = 15;

    /**
     * @param  Collection<int, LogisticsProvider>  $providers  with `user` loaded
     * @return Collection<int, array{provider: LogisticsProvider, score: int, reason: ?string, location: ?string, recommended: bool}>
     */
    public function rank(Collection $providers, Address|User|null $sellerAddress): Collection
    {
        $seller = PlaceMatcher::placeOf($sellerAddress);
        $known = PlaceMatcher::known($seller);

        $ranked = $providers->map(function (LogisticsProvider $provider) use ($seller, $known) {
            $hub = PlaceMatcher::placeOf($provider->user);
            $match = $known && PlaceMatcher::known($hub) ? PlaceMatcher::compare($seller, $hub) : ['score' => 0, 'label' => null];
            $score = $match['score'];
            $reason = $match['label'];

            $serves = $seller['region'] !== null && in_array($seller['region'], $provider->service_regions ?? [], true);
            if ($serves && $score < PlaceMatcher::LEVELS['region']['score']) {
                $score = self::SERVES_REGION_SCORE;
                $reason = 'Serves your region';
            }

            return [
                'provider' => $provider,
                'score' => $score,
                'reason' => $reason,
                'location' => self::location($provider),
                'recommended' => false,
            ];
        })->sort(fn ($a, $b) => [$b['score'], $a['provider']->name] <=> [$a['score'], $b['provider']->name])->values();

        if ($ranked->isNotEmpty() && $ranked[0]['score'] > 0) {
            $ranked[0] = ['recommended' => true] + $ranked[0];
        }

        return $ranked;
    }

    /** The hub's office address, e.g. "123, Bubukal, Santa Cruz, Laguna", or null when not set. */
    public static function location(LogisticsProvider $provider): ?string
    {
        return PlaceMatcher::describe($provider->user, withStreet: true);
    }
}
