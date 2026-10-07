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
use App\Models\Seller;
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
    private const SHIPPING_FEE = 60.00;

    public function place(User $buyer, array $cartItems, string $paymentMethod, string $deliveryAddress): Order
    {
        if (empty($cartItems)) {
            throw new RuntimeException('Your cart is empty.');
        }

        return DB::transaction(function () use ($buyer, $cartItems, $paymentMethod, $deliveryAddress) {
            $seller = Seller::query()->orderBy('id')->first();
            $provider = LogisticsProvider::query()->orderBy('id')->first();

            if (!$seller || !$provider) {
                throw new RuntimeException('The marketplace is not ready to take orders yet (no seller or courier configured).');
            }

            $category = Category::firstOrCreate(
                ['slug' => 'general'],
                ['name' => 'General', 'position' => 0, 'is_active' => true]
            );

            $lines = [];
            $subtotalMinor = 0;

            foreach ($cartItems as $item) {
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

                $lines[] = compact('product', 'variant', 'priceMinor', 'qty');
                $subtotalMinor += $priceMinor * $qty;
            }

            $shippingMinor = (int) round(self::SHIPPING_FEE * 100);
            $discountMinor = $subtotalMinor >= 100000 ? 5000 : 0;
            $totalMinor = max(0, $subtotalMinor + $shippingMinor - $discountMinor);
            $isCod = strtoupper($paymentMethod) === 'COD';

            $order = Order::create([
                'buyer_id'         => $buyer->id,
                'reference'        => $this->reference(),
                'total_minor'      => $totalMinor,
                'payment_method'   => $isCod ? 'cod' : 'wallet',
                'payment_status'   => 'pending',
                'shipping_address' => [
                    'recipient' => $buyer->name,
                    'phone'     => $buyer->phone,
                    'address'   => $deliveryAddress,
                ],
            ]);

            $sellerOrder = SellerOrder::create([
                'order_id'              => $order->id,
                'seller_id'             => $seller->id,
                'logistics_provider_id' => $provider->id,
                'subtotal_minor'        => $subtotalMinor,
                'shipping_fee_minor'    => $shippingMinor,
                'commission_minor'      => (int) round($subtotalMinor * ($seller->commission_bps ?? 0) / 10000),
                'status'                => 'pending',
            ]);

            foreach ($lines as $l) {
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

            Payment::create([
                'order_id'     => $order->id,
                'method'       => $paymentMethod,
                'amount_minor' => $totalMinor,
                'status'       => 'pending',
            ]);

            $shipment = Shipment::create([
                'seller_order_id'       => $sellerOrder->id,
                'logistics_provider_id' => $provider->id,
                'tracking_code'         => 'CTZ-' . strtoupper(Str::random(10)),
                'status'                => 'unassigned',
                'fee_minor'             => $shippingMinor,
                'cod_amount_minor'      => $isCod ? $totalMinor : 0,
            ]);

            DeliveryEvent::create([
                'shipment_id' => $shipment->id,
                'status'      => 'order_placed',
                'attempt'     => 1,
                'user_id'     => $buyer->id,
                'note'        => 'Order placed by buyer.',
                'occurred_at' => now(),
            ]);

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

    private function reference(): string
    {
        do {
            $ref = 'ORD-' . now()->format('Y') . '-' . random_int(10000, 99999);
        } while (Order::where('reference', $ref)->exists());

        return $ref;
    }
}
