<?php

namespace App\Services;

use App\Models\Category;
use App\Models\DeliveryEvent;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
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
 * The storefront catalog is still hard-coded in the views, so each cart line is
 * materialised into products/product_variants the first time it is ordered
 * (slug "catalog-{id}") to keep every foreign key valid.
 */
class OrderPlacement
{
    /**
     * @param  array{items?: ?int, shipping?: ?int, shops?: array<int, ?int>, shop_shipping?: array<int, ?int>}|null  $voucherIds
     *         Chosen voucher ids (shops / shop_shipping: seller id => shop voucher id); null applies the best vouchers automatically.
     */
    public function place(User $buyer, array $cartItems, string $paymentMethod, string $deliveryAddress, ?array $voucherIds = null): Order
    {
        if (!$buyer->isBuyer()) {
            throw new RuntimeException('Purchases require a separate buyer account. Please sign in as a buyer.');
        }
        if (empty($cartItems)) {
            throw new RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($buyer, $cartItems, $paymentMethod, $deliveryAddress, $voucherIds) {
            $defaultAddress = \App\Models\Address::where('user_id', $buyer->id)->where('is_default', true)->first();
            $useDefaultContact = $defaultAddress && $defaultAddress->formatted_address === $deliveryAddress;
            $provider = LogisticsProvider::query()->orderBy('id')->first();

            if (!$provider) {
                throw new RuntimeException('The marketplace is not ready to take orders yet (no courier configured).');
            }

            $category = Category::firstOrCreate(
                ['slug' => 'general'],
                ['name' => 'General', 'position' => 0, 'is_active' => true]
            );

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
                    $qty = max(1, (int) ($item['quantity'] ?? 1));
                    if ($variant->stock < $qty) throw new RuntimeException('There is not enough stock for '.$product->name.'.');
                    $priceMinor = $variant->sale_price_minor;
                    $variant->decrement('stock', $qty);
                    $lines[] = compact('product', 'variant', 'priceMinor', 'qty', 'seller');
                    $subtotalMinor += $priceMinor * $qty;
                    continue;
                }
                $seller = app(CatalogStores::class)->storeFor((string) $item['id']);
                $variation = $item['variation'] ?? 'Standard';
                $priceMinor = (int) round(((float) $item['price']) * 100);
                $qty = max(1, (int) ($item['quantity'] ?? 1));

                $product = Product::firstOrCreate(
                    ['slug' => 'catalog-' . $item['id']],
                    [
                        'seller_id'   => $seller->id,
                        'category_id' => $category->id,
                        'name'        => $item['name'],
                        'is_active'   => true,
                    ]
                );

                // Correct catalog products previously attached to the first registered seller.
                if ($product->seller_id !== $seller->id) {
                    $product->update(['seller_id' => $seller->id]);
                }

                if (!empty($item['image']) && !$product->images()->exists()) {
                    ProductImage::forceCreate(['product_id' => $product->id, 'path' => $item['image'], 'position' => 0]);
                }

                $variant = ProductVariant::firstOrCreate(
                    ['sku' => 'CAT-' . $item['id'] . '-' . substr(md5($variation), 0, 6)],
                    [
                        'product_id'  => $product->id,
                        'name'        => $variation,
                        'price_minor' => $priceMinor,
                        'stock'       => 999,
                        'is_active'   => true,
                    ]
                );

                $lines[] = compact('product', 'variant', 'priceMinor', 'qty', 'seller');
                $subtotalMinor += $priceMinor * $qty;
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
            $normalizedMethod = $isCod ? 'COD' : 'GCash';

            $order = Order::create([
                'buyer_id'         => $buyer->id,
                'reference'        => $this->reference(),
                'total_minor'      => $totalMinor,
                'payment_method'   => $isCod ? 'cod' : 'wallet',
                'payment_status'   => 'pending',
                'shipping_address' => [
                    'recipient' => $useDefaultContact ? $defaultAddress->recipient : $buyer->name,
                    'phone'     => $useDefaultContact ? $defaultAddress->phone : $buyer->phone,
                    'address'   => $deliveryAddress,
                ],
            ]);

            // cartzy vouchers are shared across stores in proportion to what each store has left to pay.
            // They don't change commission (it stays on the full subtotal), and the courier is always paid
            // the full parcel fee. Shop vouchers belong to their own store only and are paid for by that store.
            $itemShares = self::allocate($itemDiscountMinor, collect($storeSubtotals)->map(fn ($subtotal, $id) => $subtotal - ($shopSelection[$id]['discount_minor'] ?? 0))->all());
            $shippingShares = self::allocate($shippingDiscountMinor, collect($storeShipping)->map(fn ($fee, $id) => $fee - ($shopShippingSelection[$id]['discount_minor'] ?? 0))->all());
            foreach ($groups as $groupLines) {
                $seller = $groupLines->first()['seller'];
                $groupSubtotal = $storeSubtotals[$seller->id];
                $groupShopDiscount = $shopSelection[$seller->id]['discount_minor'] ?? 0;
                $groupParcelFee = $storeShipping[$seller->id];
                $groupShopShippingDiscount = $shopShippingSelection[$seller->id]['discount_minor'] ?? 0;
                $groupShippingDiscount = $shippingShares[$seller->id];
                $groupVoucherDiscount = $itemShares[$seller->id];
                $sellerOrder = SellerOrder::create([
                    'order_id'                => $order->id,
                    'seller_id'               => $seller->id,
                    'logistics_provider_id'   => $provider->id,
                    'subtotal_minor'          => $groupSubtotal,
                    'shipping_fee_minor'      => $groupParcelFee - $groupShopShippingDiscount - $groupShippingDiscount,
                    'shipping_discount_minor' => $groupShippingDiscount,
                    'seller_shipping_discount_minor' => $groupShopShippingDiscount,
                    'voucher_discount_minor'  => $groupVoucherDiscount,
                    'seller_voucher_discount_minor' => $groupShopDiscount,
                    'commission_minor'        => SellerOrder::commissionFor($groupSubtotal),
                    'status'                  => 'pending',
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
                    'logistics_provider_id' => $provider->id,
                    'tracking_code'         => 'CTZ-' . strtoupper(Str::random(10)),
                    'status'                => 'unassigned',
                    'fee_minor'             => $sellerOrder->courierFeeMinor(),
                    'cod_amount_minor'      => $isCod ? $sellerOrder->buyerDueMinor() : 0,
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

            Payment::create([
                'order_id' => $order->id, 'method' => $normalizedMethod,
                'amount_minor' => $totalMinor, 'status' => 'pending',
            ]);

            $vouchers->record($order, [...array_values($selection), ...array_values($shopSelection), ...array_values($shopShippingSelection)]);

            NotificationService::notify(
                $buyer->id,
                'order',
                'Order Placed',
                "Your order {$order->reference} was successfully placed.",
                route('buyer.dashboard', ['tab' => 'orders'])
            );

            return $order;
        });
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
