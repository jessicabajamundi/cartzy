<?php

namespace Tests\Feature;

use App\Models\{Category, LogisticsMessage, LogisticsProvider, Order, Product, ProductVariant, Rider, Seller, SellerOrder, Shipment, User};
use App\Services\{LogisticsReports, OrderPlacement, VoucherService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HardcodedDataRemovalTest extends TestCase
{
    use RefreshDatabase;

    private function hub(): LogisticsProvider
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active', 'is_suspended' => false]);
        return LogisticsProvider::create(['user_id' => $user->id, 'name' => 'Hub '.$user->id, 'slug' => 'hub-'.$user->id, 'status' => 'approved']);
    }

    private function product(bool $catalog = false, bool $suspended = false): ProductVariant
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active', 'is_suspended' => $suspended]);
        if ($catalog) $user->update(['email' => 'ghost-'.$user->id.'@catalog.cartzy.invalid']);
        $seller = Seller::create(['user_id' => $user->id, 'name' => 'Store '.$user->id, 'slug' => 'store-'.$user->id, 'status' => 'approved']);
        $category = Category::firstOrCreate(['slug' => 'general'], ['name' => 'General']);
        $product = Product::create(['package_length_cm' => 20, 'package_width_cm' => 15, 'package_height_cm' => 10, 'seller_id' => $seller->id, 'category_id' => $category->id, 'name' => 'Product '.$user->id,
            'slug' => 'product-'.$user->id, 'is_active' => true, 'approval_status' => 'approved']);
        return ProductVariant::create(['weight_grams' => 500, 'product_id' => $product->id, 'name' => 'Standard', 'sku' => 'SKU-'.$user->id,
            'stock' => 10, 'price_minor' => 10000, 'is_active' => true]);
    }

    private function shipment(LogisticsProvider $hub, ?Rider $rider = null): Shipment
    {
        $variant = $this->product();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'is_suspended' => false]);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'ORD-'.$variant->id, 'total_minor' => 10000,
            'payment_method' => 'cod', 'payment_status' => 'paid', 'shipping_address' => ['recipient' => $buyer->name, 'address' => 'Actual saved address']]);
        $parcel = SellerOrder::create(['order_id' => $order->id, 'seller_id' => $variant->product->seller_id,
            'subtotal_minor' => 10000, 'commission_minor' => 1000, 'status' => 'delivered', 'delivered_at' => '2026-11-05 12:00:00']);
        $shipment = Shipment::create(['seller_order_id' => $parcel->id, 'logistics_provider_id' => $hub->id,
            'rider_id' => $rider?->id, 'tracking_code' => 'CTZ-'.$parcel->id, 'status' => 'delivered',
            'cod_collected' => true, 'cod_amount_minor' => 10000, 'received_at' => '2026-11-03 12:00:00']);
        DB::table('shipments')->where('id', $shipment->id)->update(['created_at' => '2026-11-02 12:00:00', 'updated_at' => '2026-11-05 12:00:00']);
        return $shipment->fresh();
    }

    private function rider(LogisticsProvider $hub): Rider
    {
        return Rider::create(['user_id' => User::factory()->create(['role' => 'courier', 'status' => 'active', 'is_suspended' => false])->id,
            'logistics_provider_id' => $hub->id, 'courier_type' => Rider::LAST_MILE, 'status' => 'approved', 'is_active' => true]);
    }

    public function test_web_and_mobile_hide_catalog_and_suspended_sellers_consistently(): void
    {
        $real = $this->product();
        $catalog = $this->product(true);
        $suspended = $this->product(false, true);
        $this->getJson('/api/products')->assertOk()->assertJsonCount(1, 'products')->assertJsonPath('products.0.id', $real->product_id);
        $this->getJson('/api/products/'.$catalog->product_id)->assertNotFound();
        $this->getJson('/api/products/'.$suspended->product_id)->assertNotFound();
        $this->get('/products/'.$catalog->product_id)->assertNotFound();
        $this->get('/')->assertOk()->assertViewHas('discoveryProducts', fn ($products) => $products->pluck('id')->all() === [$real->product_id]);
    }

    public function test_legacy_cart_input_cannot_create_catalog_users_products_or_orders(): void
    {
        $this->hub();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'is_suspended' => false]);
        $users = User::count();
        $item = ['id' => 5, 'name' => 'Client supplied fake mug', 'price' => 1, 'quantity' => 1];
        $this->assertNull(app(VoucherService::class)->sellerIdFor($item));
        try {
            app(OrderPlacement::class)->place($buyer, [$item], 'COD', 'Saved address');
            $this->fail('A missing real variant must be rejected.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('verified store', $e->getMessage());
        }
        $this->assertSame($users, User::count());
        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->actingAs($buyer)->postJson(route('cart.add'), $item)->assertUnprocessable();
        Sanctum::actingAs($buyer);
        $this->postJson('/api/cart/add', $item)->assertUnprocessable();
    }

    public function test_real_order_uses_database_price_and_stock_not_client_values(): void
    {
        $this->hub();
        $variant = $this->product();
        $buyer = User::factory()->create(['role' => 'buyer']);
        $order = app(OrderPlacement::class)->place($buyer, [['variant_id' => $variant->id, 'price' => .01, 'quantity' => 2]], 'COD', 'Saved address');
        $this->assertSame(20000, $order->sellerOrders->first()->subtotal_minor);
        $this->assertSame(8, $variant->fresh()->stock);
        $this->assertFalse(User::where('email', 'like', '%@catalog.cartzy.invalid')->exists());
    }

    public function test_missing_seller_gcash_returns_unavailable_without_creating_catalog_accounts(): void
    {
        Sanctum::actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));
        $count = User::count();
        $this->getJson('/api/checkout/seller-payment-info?product_ids=5')->assertUnprocessable();
        $this->assertSame($count, User::count());
        $variant = $this->product();
        $this->getJson('/api/checkout/seller-payment-info?product_ids='.$variant->product_id)->assertUnprocessable();
    }

    public function test_reports_respect_end_date_include_partial_weeks_and_compute_duration(): void
    {
        $hub = $this->hub();
        $shipment = $this->shipment($hub);
        $reports = app(LogisticsReports::class);
        $week = $reports->data($hub->id, '2026-11-01', '2026-11-07');
        $this->assertSame(1, array_sum($week['chart']['received']));
        $this->assertSame(2.0, $week['avg_delivery_days']);
        DB::table('shipments')->where('id', $shipment->id)->update(['created_at' => '2026-11-20 12:00:00']);
        $week = $reports->data($hub->id, '2026-11-01', '2026-11-07');
        $this->assertSame(0, array_sum($week['chart']['received']));
        $month = $reports->data($hub->id, '2026-11-01', '2026-11-30');
        $this->assertCount(5, $month['chart']['labels']);
        $this->actingAs($hub->user)->get(route('logistics.reports', ['from' => '2026-11-07', 'to' => '2026-11-01']))->assertSessionHasErrors('to');
    }

    public function test_rider_completed_records_are_real_and_private_to_the_assigned_rider(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub);
        $mine = $this->shipment($hub, $rider);
        $this->shipment($hub, $this->rider($hub));
        Sanctum::actingAs($rider->user);
        $this->getJson('/api/rider/completed-records')->assertOk()->assertJsonCount(1, 'records')
            ->assertJsonPath('records.0.id', $mine->id)->assertJsonPath('records.0.tracking_code', $mine->tracking_code)
            ->assertJsonPath('records.0.rider_payout', fn ($amount) => abs((float) $amount - \App\Services\LogisticsEarningsService::deliveryPayoutMinor($mine) / 100) < .001)->assertJsonPath('hub_name', $hub->name);
    }

    public function test_dispatch_saves_real_messages_for_assigned_hub_without_auto_replies(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub);
        $other = $this->rider($hub);
        LogisticsMessage::create(['logistics_provider_id' => $hub->id, 'contact_user_id' => $other->user_id, 'sender_type' => 'hub', 'message' => 'Private other rider message']);
        Sanctum::actingAs($rider->user);
        $this->getJson('/api/rider/dispatch/messages')->assertOk()->assertJsonCount(0, 'messages');
        $this->postJson('/api/rider/dispatch/messages', ['message' => 'Actual delivery update'])->assertCreated();
        $this->getJson('/api/rider/dispatch/messages')->assertOk()->assertJsonCount(1, 'messages')->assertJsonPath('messages.0.body', 'Actual delivery update');
        $this->assertDatabaseHas('logistics_messages', ['contact_user_id' => $rider->user_id, 'logistics_provider_id' => $hub->id, 'sender_type' => 'contact']);
        $this->assertSame(0, LogisticsMessage::where('contact_user_id', $rider->user_id)->where('sender_type', 'hub')->count());
        $reply = LogisticsMessage::create(['logistics_provider_id' => $hub->id, 'contact_user_id' => $rider->user_id, 'sender_type' => 'hub', 'message' => 'Real hub response']);
        $this->getJson('/api/rider/dispatch/messages')->assertOk()->assertJsonCount(2, 'messages');
        $this->assertNotNull($reply->fresh()->read_at);
    }
}
