<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerDailyOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createShop(): Seller
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        return Seller::create([
            'user_id' => $user->id,
            'name'    => 'Boutique Store '.$user->id,
            'slug'    => 'boutique-store-'.$user->id,
            'status'  => 'approved',
        ]);
    }

    private function createOrder(Seller $shop, string $status = 'pending', string $paymentStatus = 'pending', $createdAt = null): SellerOrder
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::create([
            'buyer_id'         => $buyer->id,
            'reference'        => 'ORD-'.uniqid(),
            'total_minor'      => 15000,
            'payment_method'   => 'cod',
            'payment_status'   => $paymentStatus,
            'shipping_address' => ['recipient' => $buyer->name, 'address' => 'Sample Manila Address'],
        ]);

        $sellerOrder = SellerOrder::create([
            'order_id'         => $order->id,
            'seller_id'        => $shop->id,
            'subtotal_minor'   => 12000,
            'shipping_fee_minor' => 3000,
            'commission_minor' => 1200,
            'status'           => $status,
            'delivered_at'     => in_array($status, ['delivered', 'completed'], true) ? ($createdAt ?? now()) : null,
        ]);

        if ($createdAt) {
            $sellerOrder->created_at = $createdAt;
            $sellerOrder->updated_at = $createdAt;
            $sellerOrder->save();

            $order->created_at = $createdAt;
            $order->updated_at = $createdAt;
            $order->save();
        }

        $category = Category::firstOrCreate(['slug' => 'apparel'], ['name' => 'Apparel', 'is_active' => true]);
        $product = $shop->products()->create([
            'name'        => 'Linen Shirt',
            'slug'        => 'linen-shirt-'.uniqid(),
            'category_id' => $category->id,
            'is_active'   => true,
        ]);
        $variant = $product->variants()->create([
            'sku'         => 'SKU-'.uniqid(),
            'name'        => 'Medium',
            'price_minor' => 12000,
            'stock'       => 10,
            'is_active'   => true,
        ]);

        $sellerOrder->items()->create([
            'product_id'         => $product->id,
            'product_variant_id' => $variant->id,
            'product_name'       => 'Linen Shirt',
            'variant_name'       => 'Medium',
            'unit_price_minor'   => 12000,
            'quantity'           => 1,
        ]);

        return $sellerOrder;
    }

    public function test_seller_dashboard_order_details_start_at_zero_everyday(): void
    {
        $shop = $this->createShop();

        // An order from 2 days ago (not today)
        $pastDate = now()->subDays(2);
        $this->createOrder($shop, 'delivered', 'paid', $pastDate);

        // Access dashboard today
        $response = $this->actingAs($shop->user)->get(route('seller.dashboard'));
        $response->assertOk();

        // Today's details reset back to zero
        $response->assertSee("Today's Order Details", false)
            ->assertSee('Resets daily at 12:00 AM')
            ->assertSee('Starts at 0 today · No orders yet')
            ->assertSee('0 delivered today')
            ->assertSee('0 cancelled today')
            ->assertSee('0 refunds today')
            ->assertSee('₱0.00');
    }

    public function test_seller_dashboard_order_details_tracks_today_activity(): void
    {
        $shop = $this->createShop();

        // 1 delivered order today
        $this->createOrder($shop, 'delivered', 'paid', now());

        // 1 cancelled order today
        $this->createOrder($shop, 'cancelled', 'pending', now());

        // 1 refunded order today
        $this->createOrder($shop, 'pending', 'refunded', now());

        $response = $this->actingAs($shop->user)->get(route('seller.dashboard'));
        $response->assertOk();

        $response->assertSee("Today's Order Details", false)
            ->assertSee('3 received today')
            ->assertSee('1 completed today')
            ->assertSee('1 cancelled today')
            ->assertSee('1 refunded today');
    }

    public function test_daily_order_history_displays_delivered_cancelled_and_refund_status(): void
    {
        $shop = $this->createShop();

        $deliveredOrder = $this->createOrder($shop, 'delivered', 'paid', now()->subDays(1));
        $cancelledOrder = $this->createOrder($shop, 'cancelled', 'pending', now()->subDays(1));
        $refundedOrder = $this->createOrder($shop, 'pending', 'refunded', now()->subDays(1));

        $response = $this->actingAs($shop->user)->get(route('seller.dashboard'));
        $response->assertOk();

        $response->assertSee('Daily Order History')
            ->assertSee('Yesterday')
            ->assertSee($deliveredOrder->order->reference)
            ->assertSee($cancelledOrder->order->reference)
            ->assertSee($refundedOrder->order->reference)
            ->assertSee('Delivered')
            ->assertSee('Cancelled')
            ->assertSee('Refunded');
    }

    public function test_daily_order_history_can_be_filtered_by_status(): void
    {
        $shop = $this->createShop();

        $delivered = $this->createOrder($shop, 'delivered', 'paid', now());
        $cancelled = $this->createOrder($shop, 'cancelled', 'pending', now());

        // Filter delivered only
        $resDelivered = $this->actingAs($shop->user)->get(route('seller.dashboard', ['history_status' => 'delivered']));
        $resDelivered->assertOk();
        $contentDelivered = $resDelivered->getContent();
        $historySectionDelivered = substr($contentDelivered, strpos($contentDelivered, 'id="daily-order-history"'), strpos($contentDelivered, '<h2>Recent orders</h2>') - strpos($contentDelivered, 'id="daily-order-history"'));
        $this->assertStringContainsString($delivered->order->reference, $historySectionDelivered);
        $this->assertStringNotContainsString($cancelled->order->reference, $historySectionDelivered);

        // Filter cancelled only
        $resCancelled = $this->actingAs($shop->user)->get(route('seller.dashboard', ['history_status' => 'cancelled']));
        $resCancelled->assertOk();
        $contentCancelled = $resCancelled->getContent();
        $historySectionCancelled = substr($contentCancelled, strpos($contentCancelled, 'id="daily-order-history"'), strpos($contentCancelled, '<h2>Recent orders</h2>') - strpos($contentCancelled, 'id="daily-order-history"'));
        $this->assertStringContainsString($cancelled->order->reference, $historySectionCancelled);
        $this->assertStringNotContainsString($delivered->order->reference, $historySectionCancelled);
    }
}
