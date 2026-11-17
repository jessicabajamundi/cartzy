<?php

namespace App\Services;

use App\Models\DeliveryEvent;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Turns the session cart into real database records:
 * orders -> seller_orders -> order_items, plus payment, shipment and first delivery event.
 *
 * Cart lines must resolve to real, approved product variants and public sellers.
 * Prices and stock are taken from the database; legacy sample items are rejected.
 */
class OrderPlacement
{
    /**
     * @param  array{items?: ?int, shipping?: ?int, shops?: array<int, ?int>, shop_shipping?: array<int, ?int>}|null  $voucherIds
     *         Chosen voucher ids (shops / shop_shipping: seller id => shop voucher id); null applies the best vouchers automatically.
     * @param  \App\Models\Address|null  $address  A saved address of this buyer to ship to (the mobile app picks one);
     *         its recipient, phone and province are used. Null falls back to the default address when the text matches.
     */
    public function place(User $buyer, array $cartItems, string $paymentMethod, string $deliveryAddress, ?array $voucherIds = null, ?\App\Models\Address $address = null, ?string $providerRef = null, ?string $receiptPath = null, int $walletAmount = 0, ?string $checkoutKey = null): Order
    {
        if ($address && $address->user_id !== $buyer->id) {
            throw new RuntimeException('Please choose one of your own saved addresses.');
        }
        if (!$buyer->isBuyer()) {
            throw new RuntimeException('Purchases require a separate buyer account. Please sign in as a buyer.');
        }
        if (empty($cartItems)) {
            throw new RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($buyer, $cartItems, $paymentMethod, $deliveryAddress, $voucherIds, $address, $providerRef, $receiptPath, $walletAmount, $checkoutKey) {
            User::whereKey($buyer->id)->lockForUpdate()->firstOrFail();
            if ($checkoutKey && ($existing = Order::where('buyer_id', $buyer->id)->where('checkout_key', $checkoutKey)->first())) return $existing;
            if ($walletAmount < 0 || $walletAmount > app(BuyerWallet::class)->balance($buyer->id)) {
                throw new RuntimeException('Your wallet balance changed. Refresh checkout and review your payment.');
            }
            $defaultAddress = $address ?? \App\Models\Address::where('user_id', $buyer->id)->where('is_default', true)->first();
            $useDefaultContact = $defaultAddress && ($address !== null || $defaultAddress->formatted_address === $deliveryAddress);
            $providers = LogisticsProvider::where('status', 'approved')->with('user')->orderBy('id')->get();

            if ($providers->isEmpty()) {
                throw new RuntimeException('The marketplace is not ready to take orders yet (no courier configured).');
            }

            $lines = [];
            $subtotalMinor = 0;

            foreach ($cartItems as $item) {
                $savedVariant = app(ProductPricing::class)->variant($item);
                if ($savedVariant) {
                    $variant = ProductVariant::whereKey($savedVariant->id)->lockForUpdate()->firstOrFail();
                    $product = Product::whereKey($variant->product_id)->lockForUpdate()->firstOrFail();
                    $variant->setRelation('product', $product);
                    if (!$product->is_active || !$product->isApproved()) {
                        throw new RuntimeException($product->name.' is not currently available for purchase.');
                    }
                    $seller = $product->seller;
                    if (!$seller || !\App\Models\Seller::publiclyVisible()->whereKey($seller->id)->exists()) {
                        throw new RuntimeException($product->name.' is not from a verified store.');
                    }
                    $qty = max(1, (int) ($item['quantity'] ?? 1));
                    if ($variant->stock < $qty) throw new RuntimeException('There is not enough stock for '.$product->name.'.');
                    $priceMinor = $variant->sale_price_minor;
                    $variant->decrement('stock', $qty);
                    $lines[] = compact('product', 'variant', 'priceMinor', 'qty', 'seller');
                    $subtotalMinor += $priceMinor * $qty;
                    continue;
                }
                throw new RuntimeException('This product is no longer available. Select a product from a verified store.');
            }

            $vouchers = app(VoucherService::class);
            $groups = collect($lines)->groupBy(fn ($line) => $line['seller']->id);
            $storeSubtotals = $groups->map(fn ($groupLines) => $groupLines->sum(fn ($line) => $line['priceMinor'] * $line['qty']))->all();

            // Every store ships its own parcel, priced by route (store pickup province → buyer province).
            $destination = $useDefaultContact ? $defaultAddress : null;
            $storeShipping = collect(ShippingCalculator::forStores(array_keys($storeSubtotals), $destination))->map(fn ($quote) => $quote['fee_minor'])->all();
            $shippingMinor = array_sum($storeShipping);

            // Shop vouchers first, per store: one discount voucher and one free-shipping voucher, each on that store only.
            // A missing "shops"/"shop_shipping" list means "pick the best for me" (e.g. orders placed outside checkout).
            $chosen = fn (string $key) => $voucherIds !== null && array_key_exists($key, $voucherIds) ? (array) $voucherIds[$key] : null;
            $lockedShopVouchers = $vouchers->shopVouchersFor($buyer, array_keys($storeSubtotals), true);
            $shopSelection = $vouchers->lockShopSelection($buyer, $chosen('shops'), $storeSubtotals, $storeShipping, 'items', $lockedShopVouchers);
            $shopShippingSelection = $vouchers->lockShopSelection($buyer, $chosen('shop_shipping'), $storeSubtotals, $storeShipping, 'shipping', $lockedShopVouchers);
            $shopDiscountMinor = array_sum(array_map(fn ($option) => $option['discount_minor'], $shopSelection));
            $shopShippingDiscountMinor = array_sum(array_map(fn ($option) => $option['discount_minor'], $shopShippingSelection));

            // cartzy vouchers then apply to what is left after shop vouchers, as on Shopee/Lazada.
            $platformBaseMinor = $subtotalMinor - $shopDiscountMinor;
            $platformShippingBaseMinor = $shippingMinor - $shopShippingDiscountMinor;
            if ($voucherIds === null) {
                $options = $vouchers->options($buyer, $platformBaseMinor, $platformShippingBaseMinor);
                $voucherIds = ['items' => $vouchers->best($options, 'items')['voucher']->id ?? null, 'shipping' => $vouchers->best($options, 'shipping')['voucher']->id ?? null];
            }
            $selection = $vouchers->lockSelection($buyer, $voucherIds['items'] ?? null, $voucherIds['shipping'] ?? null, $platformBaseMinor, $platformShippingBaseMinor);
            $itemDiscountMinor = $selection['items']['discount_minor'] ?? 0;
            $shippingDiscountMinor = $selection['shipping']['discount_minor'] ?? 0;
            $totalMinor = max(0, $subtotalMinor + $shippingMinor - $shopShippingDiscountMinor - $shippingDiscountMinor - $itemDiscountMinor - $shopDiscountMinor);
            $isCod = in_array(strtoupper(trim((string) $paymentMethod)), ['COD', 'CASH ON DELIVERY'], true);
            $walletOnly = strtoupper($paymentMethod) === 'CARTZYWALLET';
            if ($walletAmount > $totalMinor || ($walletOnly && $walletAmount !== $totalMinor)) {
                throw new RuntimeException('Review your wallet payment. The amount must match the current order total for wallet-only checkout.');
            }
            $externalAmount = $totalMinor - $walletAmount;
            if (!$isCod && $externalAmount > 0 && $groups->count() !== 1) {
                throw new RuntimeException('For direct GCash payments, check out one store at a time so each seller receives and verifies the correct payment.');
            }

            $order = Order::create([
                'buyer_id'         => $buyer->id,
                'reference'        => $this->reference(),
                'total_minor'      => $totalMinor,
                'payment_method'   => $isCod && $externalAmount > 0 ? 'cod' : 'wallet',
                'payment_status'   => $externalAmount === 0 ? 'paid' : 'pending',
                'wallet_paid_minor' => $walletAmount,
                'checkout_key' => $checkoutKey,
                'shipping_address' => [
                    'recipient' => $useDefaultContact ? $defaultAddress->recipient : $buyer->name,
                    'phone'     => $useDefaultContact ? $defaultAddress->phone : $buyer->phone,
                    'address'   => $deliveryAddress,
                ],
            ]);
            if ($walletAmount > 0) {
                app(BuyerWallet::class)->record($buyer, -$walletAmount, 'checkout:'.$order->id, 'purchase', 'Payment for '.$order->reference, $order);
            }

            // cartzy vouchers are shared across stores in proportion to what each store has left to pay.
            // They don't change commission (it stays on the full subtotal), and the courier is always paid
            // the full parcel fee. Shop vouchers belong to their own store only and are paid for by that store.
            $itemShares = self::allocate($itemDiscountMinor, collect($storeSubtotals)->map(fn ($subtotal, $id) => $subtotal - ($shopSelection[$id]['discount_minor'] ?? 0))->all());
            $shippingShares = self::allocate($shippingDiscountMinor, collect($storeShipping)->map(fn ($fee, $id) => $fee - ($shopShippingSelection[$id]['discount_minor'] ?? 0))->all());
            $walletParts = collect($storeSubtotals)->map(fn ($subtotal, $id) => max(0, $subtotal + $storeShipping[$id]
                - ($shopSelection[$id]['discount_minor'] ?? 0) - ($shopShippingSelection[$id]['discount_minor'] ?? 0)
                - $itemShares[$id] - $shippingShares[$id]))->all();
            $walletShares = self::allocate($walletAmount, $walletParts);
            foreach ($groups as $groupLines) {
                $seller = $groupLines->first()['seller'];
                $groupSubtotal = $storeSubtotals[$seller->id];
                $groupShopDiscount = $shopSelection[$seller->id]['discount_minor'] ?? 0;
                $groupParcelFee = $storeShipping[$seller->id];
                $groupShopShippingDiscount = $shopShippingSelection[$seller->id]['discount_minor'] ?? 0;
                $groupShippingDiscount = $shippingShares[$seller->id];
                $groupVoucherDiscount = $itemShares[$seller->id];
                $originHubId = (int) app(HubRecommender::class)->originHub($seller, $providers)->id;
                $destinationHubId = app(HubToHubService::class)->resolveDestinationHub($destination ?? $buyer, $originHubId);

                $sellerOrder = SellerOrder::create([
                    'order_id'                => $order->id,
                    'seller_id'               => $seller->id,
                    'logistics_provider_id'   => $originHubId,
                    'subtotal_minor'          => $groupSubtotal,
                    'shipping_fee_minor'      => $groupParcelFee - $groupShopShippingDiscount - $groupShippingDiscount,
                    'shipping_discount_minor' => $groupShippingDiscount,
                    'seller_shipping_discount_minor' => $groupShopShippingDiscount,
                    'voucher_discount_minor'  => $groupVoucherDiscount,
                    'seller_voucher_discount_minor' => $groupShopDiscount,
                    'commission_minor'        => SellerOrder::commissionFor($groupSubtotal),
                    'status'                  => 'pending',
                    'wallet_paid_minor' => $walletShares[$seller->id],
                ]);

                foreach ($groupLines as $l) {
                    OrderItem::create([
                        'seller_order_id'    => $sellerOrder->id,
                        'product_id'         => $l['product']->id,
                        'product_variant_id' => $l['variant']->id,
                        'product_name'       => $l['product']->name,
                        'variant_name'       => $l['variant']->name,
                        'unit_price_minor'   => $l['priceMinor'],
                        'quantity'           => $l['qty'],
                    ]);
                }

                $shipment = Shipment::create([
                    'seller_order_id'       => $sellerOrder->id,
                    'logistics_provider_id' => $originHubId,
                    'origin_hub_id'         => $originHubId,
                    'destination_hub_id'    => $destinationHubId,
                    'current_hub_id'        => $originHubId,
                    'tracking_code'         => 'CTZ-' . strtoupper(Str::random(10)),
                    'status'                => 'unassigned',
                    'fee_minor'             => $sellerOrder->courierFeeMinor(),
                    'cod_amount_minor'      => $isCod ? $sellerOrder->buyerDueMinor() - $sellerOrder->wallet_paid_minor : 0,
                ]);

                DeliveryEvent::create([
                    'shipment_id' => $shipment->id,
                    'status'      => 'order_placed',
                    'attempt'     => 1,
                    'user_id'     => $buyer->id,
                    'note'        => 'Order placed by buyer.',
                    'occurred_at' => now(),
                ]);
            }

            if ($walletAmount > 0) Payment::create([
                'order_id' => $order->id, 'method' => 'cartzy_wallet', 'amount_minor' => $walletAmount,
                'status' => 'paid', 'provider_ref' => 'WALLET-'.$order->reference,
            ]);
            if ($externalAmount > 0) Payment::create([
                'order_id' => $order->id,
                'method' => $isCod ? 'cod' : 'gcash',
                'amount_minor' => $externalAmount,
                'status' => 'pending',
                'provider_ref' => $isCod ? null : $providerRef,
                'receipt_path' => $isCod ? null : $receiptPath,
            ]);

            $vouchers->record($order, [...array_values($selection), ...array_values($shopSelection), ...array_values($shopShippingSelection)]);

            NotificationService::notify(
                $buyer->id,
                'order',
                'Order Placed',
                "Your order {$order->reference} was successfully placed.",
                route('buyer.dashboard', ['tab' => 'orders'])
            );
            \App\Models\ActivityLog::forUser($buyer, 'order_placed', $order->reference);

            return $order;
        }, 3);
    }

    /**
     * Splits an amount across stores in proportion to each store's part, never giving a store more than
     * its part, and always adding up to exactly the amount (left-over centavos go to stores with room).
     *
     * @param  array<int, int>  $parts  seller id => the most this store can take
     * @return array<int, int> seller id => its share
     */
    public static function allocate(int $amount, array $parts): array
    {
        $parts = array_map(fn ($part) => max(0, (int) $part), $parts);
        $base = array_sum($parts);
        $amount = min(max(0, $amount), $base);
        $shares = array_map(fn ($part) => $base ? intdiv($amount * $part, $base) : 0, $parts);
        $left = $amount - array_sum($shares);
        foreach ($parts as $id => $part) {
            if ($left <= 0) break;
            $extra = min($left, $part - $shares[$id]);
            $shares[$id] += $extra;
            $left -= $extra;
        }

        return $shares;
    }

    private function reference(): string
    {
        do {
            $ref = 'ORD-' . now()->format('Y') . '-' . random_int(10000, 99999);
        } while (Order::where('reference', $ref)->exists());

        return $ref;
    }
}
