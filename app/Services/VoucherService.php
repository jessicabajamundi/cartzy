<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Single source of truth for voucher rules: which vouchers a buyer can see,
 * how much each one takes off, and recording/reversing their use on orders.
 *
 * A buyer can use one item voucher (fixed / percent) plus one shipping voucher per order.
 */
class VoucherService
{
    public const SHIPPING_FEE_MINOR = 6000;

    /**
     * Lowest min. spend of a live voucher that makes shipping fully free for every buyer,
     * or null when there is none. Drives the "Free Shipping" badge on product cards.
     */
    public static function freeShippingMinSpendMinor(): ?int
    {
        return once(function () {
            $minSpend = Voucher::query()
                ->where('is_active', true)
                ->whereNull('seller_id')
                ->where('discount_type', 'free_shipping')
                ->whereNull('amount_minor')
                ->where('audience', 'public')
                ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
                ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()))
                ->where(fn ($q) => $q->whereNull('total_limit')->orWhereColumn('used_count', '<', 'total_limit'))
                ->min('min_spend_minor');

            return $minSpend === null ? null : (int) $minSpend;
        });
    }

    /**
     * Stores running a live public voucher that makes their parcel fully free: seller id => lowest min. spend.
     * Drives the "Free Shipping" badge together with the platform voucher above.
     */
    public static function sellerFreeShippingMinSpends(): array
    {
        return once(fn () => Voucher::query()->live()
            ->whereNotNull('seller_id')
            ->where('discount_type', 'free_shipping')
            ->whereNull('amount_minor')
            ->where('audience', 'public')
            ->where(fn ($q) => $q->whereNull('total_limit')->orWhereColumn('used_count', '<', 'total_limit'))
            ->groupBy('seller_id')
            ->selectRaw('seller_id, min(min_spend_minor) as min_spend')
            ->pluck('min_spend', 'seller_id')
            ->map(fn ($value) => (int) $value)
            ->all());
    }

    /** Platform vouchers that could apply to this buyer right now (eligible or not). Shop vouchers are separate. */
    public function visibleFor(User $buyer, bool $lock = false): Collection
    {
        $walletIds = DB::table('voucher_user')->where('user_id', $buyer->id)->pluck('voucher_id');
        $isNewBuyer = !Order::where('buyer_id', $buyer->id)->exists();

        return Voucher::query()
            ->live()
            ->whereNull('seller_id')
            ->where(function ($q) use ($walletIds, $isNewBuyer) {
                $q->where('audience', 'public')
                    ->orWhereIn('id', $walletIds)
                    ->when($isNewBuyer, fn ($q) => $q->orWhere('audience', 'new_buyers'));
            })
            ->when($lock, fn ($q) => $q->lockForUpdate())
            ->orderBy('ends_at')
            ->get();
    }

    /**
     * The store a cart line ships from, matching how OrderPlacement assigns it: the saved product's
     * seller, otherwise the catalog store for that item. Null when the item can't be ordered anymore.
     */
    public function sellerIdFor(array $item): ?int
    {
        try {
            $variant = app(ProductPricing::class)->variant($item);
            if ($variant) {
                return $variant->product?->seller_id;
            }

            return app(CatalogStores::class)->storeFor((string) ($item['id'] ?? ''))->id;
        } catch (RuntimeException) {
            return null;
        }
    }

    /** Shop vouchers from these stores that the buyer can use: public ones plus code vouchers in their wallet. */
    public function shopVouchersFor(User $buyer, array $sellerIds, bool $lock = false): Collection
    {
        if (!$sellerIds) {
            return collect();
        }
        $walletIds = DB::table('voucher_user')->where('user_id', $buyer->id)->pluck('voucher_id');

        return Voucher::query()->live()->whereIn('seller_id', $sellerIds)
            ->where(fn ($q) => $q->where('audience', 'public')->orWhereIn('id', $walletIds))
            ->when($lock, fn ($q) => $q->lockForUpdate())
            ->orderBy('ends_at')
            ->get();
    }

    /**
     * Shop voucher options per store, each checked against that store's own items and parcel fee only.
     *
     * @param  array<int, int>  $storeSubtotals  seller id => that store's merchandise subtotal (minor units)
     * @param  array<int, int>  $storeShipping  seller id => that store's parcel shipping fee (minor units)
     * @return Collection<int, Collection> seller id => options (stores without vouchers are left out)
     */
    public function shopOptions(User $buyer, array $storeSubtotals, array $storeShipping = [], ?Collection $vouchers = null): Collection
    {
        $vouchers ??= $this->shopVouchersFor($buyer, array_keys($storeSubtotals));

        return collect($storeSubtotals)
            ->map(fn ($subtotal, $sellerId) => $this->options($buyer, (int) $subtotal, (int) ($storeShipping[$sellerId] ?? self::SHIPPING_FEE_MINOR), $vouchers->where('seller_id', $sellerId)->values()))
            ->filter(fn ($options) => $options->isNotEmpty());
    }

    /** Only the discount ("items") or free-shipping ("shipping") options from a list. */
    public static function ofKind(Collection $options, string $kind): Collection
    {
        return $options->filter(fn ($o) => $o['voucher']->isShipping() === ($kind === 'shipping'))->values();
    }

    /**
     * Re-checks the chosen shop vouchers of one kind under a row lock. Per store, a buyer can use
     * one shop discount voucher and one shop free-shipping voucher, like Shopee/Lazada.
     *
     * @param  array<int, int|string|null>|null  $chosen  seller id => voucher id; null picks the best voucher for every store
     * @param  string  $kind  "items" (discount vouchers) or "shipping" (free-shipping vouchers)
     * @return array<int, array> seller id => option
     */
    public function lockShopSelection(User $buyer, ?array $chosen, array $storeSubtotals, array $storeShipping = [], string $kind = 'items', ?Collection $lockedVouchers = null): array
    {
        $options = $this->shopOptions($buyer, $storeSubtotals, $storeShipping, $lockedVouchers ?? $this->shopVouchersFor($buyer, array_keys($storeSubtotals), true))
            ->map(fn ($storeOptions) => self::ofKind($storeOptions, $kind))
            ->filter(fn ($storeOptions) => $storeOptions->isNotEmpty());
        $selection = [];
        foreach ($chosen ?? [] as $sellerId => $voucherId) {
            if ($voucherId && !$options->has($sellerId)) {
                throw new RuntimeException('A shop voucher you selected can no longer be used. Please review your vouchers and try again.');
            }
        }
        foreach ($options as $sellerId => $storeOptions) {
            if ($chosen === null) {
                if ($best = $this->best($storeOptions, $kind)) $selection[$sellerId] = $best;
                continue;
            }
            $voucherId = $chosen[$sellerId] ?? null;
            if (!$voucherId) {
                continue;
            }
            $option = $storeOptions->first(fn ($o) => $o['voucher']->id === (int) $voucherId);
            if (!$option || !$option['eligible']) {
                throw new RuntimeException('A shop voucher you selected can no longer be used'.($option['reason'] ?? null ? ' ('.$option['reason'].')' : '').'. Please review your vouchers and try again.');
            }
            $selection[$sellerId] = $option;
        }

        return $selection;
    }

    /**
     * Every visible voucher with its discount for this cart, whether it can be used, and why not.
     *
     * @return Collection<int, array{voucher: Voucher, discount_minor: int, eligible: bool, reason: ?string}>
     */
    public function options(User $buyer, int $subtotalMinor, int $shippingMinor = self::SHIPPING_FEE_MINOR, ?Collection $vouchers = null): Collection
    {
        $vouchers ??= $this->visibleFor($buyer);
        $used = VoucherRedemption::where('user_id', $buyer->id)->where('status', 'applied')
            ->whereIn('voucher_id', $vouchers->pluck('id'))
            ->selectRaw('voucher_id, count(*) as uses')->groupBy('voucher_id')->pluck('uses', 'voucher_id');

        return $vouchers->map(function (Voucher $voucher) use ($subtotalMinor, $shippingMinor, $used) {
            $reason = match (true) {
                $voucher->total_limit !== null && $voucher->used_count >= $voucher->total_limit => 'Fully claimed',
                $voucher->per_user_limit > 0 && ($used[$voucher->id] ?? 0) >= $voucher->per_user_limit => 'Already used',
                $subtotalMinor < $voucher->min_spend_minor => 'Add ₱'.number_format(($voucher->min_spend_minor - $subtotalMinor) / 100, 2).' more to use',
                default => null,
            };
            $discount = $reason ? 0 : $this->discountFor($voucher, $subtotalMinor, $shippingMinor);

            return [
                'voucher' => $voucher,
                'discount_minor' => $discount,
                'eligible' => $reason === null && $discount > 0,
                'reason' => $reason,
            ];
        })->sortByDesc(fn ($o) => [$o['eligible'], $o['discount_minor']])->values();
    }

    public function discountFor(Voucher $voucher, int $subtotalMinor, int $shippingMinor): int
    {
        $discount = match ($voucher->discount_type) {
            'fixed' => (int) $voucher->amount_minor,
            'percent' => (int) floor($subtotalMinor * $voucher->percent / 100),
            'free_shipping' => $voucher->amount_minor ?? $shippingMinor,
        };
        if ($voucher->discount_type === 'percent' && $voucher->max_discount_minor) {
            $discount = min($discount, $voucher->max_discount_minor);
        }

        return max(0, min($discount, $voucher->isShipping() ? $shippingMinor : $subtotalMinor));
    }

    /** The best usable voucher of one kind ("items" or "shipping"), or null. */
    public function best(Collection $options, string $kind): ?array
    {
        return $options->first(fn ($o) => $o['eligible'] && $o['voucher']->isShipping() === ($kind === 'shipping'));
    }

    /**
     * Re-validates the buyer's chosen vouchers under a row lock, inside the order transaction.
     *
     * @return array{items: ?array, shipping: ?array}
     */
    public function lockSelection(User $buyer, ?int $itemVoucherId, ?int $shippingVoucherId, int $subtotalMinor, int $shippingMinor): array
    {
        $selection = ['items' => null, 'shipping' => null];
        if (!$itemVoucherId && !$shippingVoucherId) {
            return $selection;
        }
        $options = $this->options($buyer, $subtotalMinor, $shippingMinor, $this->visibleFor($buyer, true))->keyBy(fn ($o) => $o['voucher']->id);

        foreach (['items' => $itemVoucherId, 'shipping' => $shippingVoucherId] as $kind => $id) {
            if (!$id) {
                continue;
            }
            $option = $options->get($id);
            if (!$option || !$option['eligible'] || $option['voucher']->isShipping() !== ($kind === 'shipping')) {
                throw new RuntimeException('A voucher you selected can no longer be used'.($option['reason'] ?? null ? ' ('.$option['reason'].')' : '').'. Please review your vouchers and try again.');
            }
            $selection[$kind] = $option;
        }

        return $selection;
    }

    public function record(Order $order, array $selection): void
    {
        foreach (array_filter($selection) as $option) {
            $option['voucher']->increment('used_count');
            VoucherRedemption::create([
                'voucher_id' => $option['voucher']->id,
                'user_id' => $order->buyer_id,
                'order_id' => $order->id,
                'discount_minor' => $option['discount_minor'],
            ]);
        }
    }

    /**
     * Gives vouchers back after cancellations: a shop voucher as soon as its store's part is cancelled,
     * platform vouchers once every store in the order has been cancelled.
     */
    public function reverseIfCancelled(Order $order): void
    {
        $allCancelled = !$order->sellerOrders()->where('status', '!=', 'cancelled')->exists();
        $cancelledStores = $order->sellerOrders()->where('status', 'cancelled')->pluck('seller_id')->all();
        VoucherRedemption::where('order_id', $order->id)->where('status', 'applied')->with('voucher')->lockForUpdate()->get()
            ->filter(fn (VoucherRedemption $redemption) => $allCancelled || in_array($redemption->voucher?->seller_id, $cancelledStores, true))
            ->each(function (VoucherRedemption $redemption) {
                $redemption->update(['status' => 'reversed']);
                Voucher::whereKey($redemption->voucher_id)->where('used_count', '>', 0)->decrement('used_count');
            });
    }

    /** Adds a code voucher to the buyer's wallet. */
    public function claimCode(User $buyer, string $code): Voucher
    {
        $voucher = Voucher::where('code', strtoupper(trim($code)))->first();
        $fail = fn (string $message) => throw ValidationException::withMessages(['voucher_code' => $message]);

        if (!$voucher || !$voucher->is_active) {
            $fail('That voucher code is not valid.');
        }
        if ($voucher->status() === 'expired') {
            $fail('That voucher has expired.');
        }
        if ($voucher->status() === 'used up') {
            $fail('That voucher has been fully claimed.');
        }
        $voucher->users()->syncWithoutDetaching([$buyer->id => ['source' => 'code']]);

        return $voucher;
    }

    /** Puts a voucher in each buyer's wallet and notifies them. Returns how many buyers were newly given it. */
    public function assign(Voucher $voucher, Collection $buyerIds): int
    {
        $existing = DB::table('voucher_user')->where('voucher_id', $voucher->id)->pluck('user_id')->all();
        $newIds = $buyerIds->unique()->diff($existing)->values();

        foreach ($newIds->chunk(200) as $chunk) {
            DB::table('voucher_user')->insert($chunk->map(fn ($id) => [
                'voucher_id' => $voucher->id, 'user_id' => $id, 'source' => 'assigned',
                'created_at' => now(), 'updated_at' => now(),
            ])->all());
            foreach ($chunk as $id) {
                NotificationService::notify(
                    $id, 'voucher', 'You received a voucher!',
                    "{$voucher->name}: {$voucher->headline()} ({$voucher->terms()}). It will be applied for you at checkout.",
                    route('buyer.dashboard', ['tab' => 'vouchers'])
                );
            }
        }

        return $newIds->count();
    }
}
