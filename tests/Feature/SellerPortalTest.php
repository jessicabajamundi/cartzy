<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\SellerMessage;
use App\Models\SellerOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerPortalTest extends TestCase
{
    use RefreshDatabase;

    private function shop(): Seller
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        return Seller::create(['user_id' => $user->id, 'name' => 'Real Store '.$user->id, 'slug' => 'store-'.$user->id, 'status' => 'approved']);
    }

    private function order(Seller $shop, string $status = 'pending', string $payment = 'pending'): SellerOrder
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'REAL-'.$buyer->id, 'total_minor' => 11000, 'payment_method' => 'cod', 'payment_status' => $payment, 'shipping_address' => ['recipient' => $buyer->name, 'address' => 'Actual saved address']]);

        return SellerOrder::create(['order_id' => $order->id, 'seller_id' => $shop->id, 'subtotal_minor' => 10000, 'shipping_fee_minor' => 1000, 'commission_minor' => 800, 'status' => $status, 'delivered_at' => in_array($status, ['delivered', 'completed']) ? now() : null]);
    }

    private function product(Seller $shop): Product
    {
        $category = Category::firstOrCreate(['slug' => 'real-category'], ['name' => 'Real category', 'is_active' => true]);
        $product = $shop->products()->create(['name' => 'Saved product '.$shop->id, 'slug' => 'product-'.$shop->id, 'category_id' => $category->id, 'is_active' => true]);
        $product->variants()->create(['sku' => 'SKU-'.$shop->id, 'name' => 'Small', 'price_minor' => 10000, 'stock' => 4, 'is_active' => true]);

        return $product;
    }

    public function test_inventory_low_stock_includes_ten_units_and_updates_after_restock(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $product->update(['low_stock_threshold' => 5]);
        $variant = $product->variants()->first();
        $foreign = $this->product($this->shop());
        $this->actingAs($shop->user);

        foreach ([10, 9, 1] as $stock) {
            $variant->update(['stock' => $stock]);
            $this->get('/seller/inventory?tab=low_stock')->assertOk()
                ->assertViewHas('stockStats', fn ($stats) => $stats['low_stock'] === 1 && $stats['in_stock'] === 0)
                ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$product->id])
                ->assertSee($product->name)->assertSee('Alert at 10')
                ->assertSee('data-limit="10"', false)->assertDontSee($foreign->name);
        }

        $this->post(route('seller.inventory.stock', $product), [
            'variants' => [['id' => $variant->id, 'stock' => 11, 'previous_stock' => 1]],
        ])->assertSessionHasNoErrors();
        $this->get('/seller/inventory?tab=low_stock')->assertOk()
            ->assertViewHas('stockStats', fn ($stats) => $stats['low_stock'] === 0 && $stats['in_stock'] === 1)
            ->assertViewHas('products', fn ($products) => $products->isEmpty());

        $variant->update(['stock' => 0]);
        $this->get('/seller/inventory?tab=out_of_stock')->assertOk()
            ->assertViewHas('stockStats', fn ($stats) => $stats['low_stock'] === 0 && $stats['out_of_stock'] === 1)
            ->assertViewHas('products', fn ($products) => $products->pluck('id')->all() === [$product->id]);

        $variant->update(['stock' => 10, 'is_active' => false]);
        $this->get('/seller/inventory?tab=low_stock')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->isEmpty());
        $variant->update(['is_active' => true]);
        $product->update(['is_active' => false]);
        $this->get('/seller/inventory?tab=low_stock')->assertOk()
            ->assertViewHas('stockStats', fn ($stats) => $stats['low_stock'] === 0)
            ->assertViewHas('products', fn ($products) => $products->isEmpty());
    }

    public function test_seller_pages_render_real_empty_states_without_a_store_profile(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'seller', 'status' => 'active']));
        foreach (['dashboard', 'orders', 'products', 'inventory', 'courier', 'deliveries', 'feedback', 'reports', 'chat', 'account'] as $page) {
            $this->get('/seller/'.$page)->assertOk()->assertDontSee('TechZone')->assertDontSee('Juan Dela Cruz')->assertDontSee('98%')->assertDontSee('38,450');
        }
        $this->assertDatabaseCount('sellers', 0);
    }

    public function test_dashboard_action_counts_and_destinations_are_scoped_and_update(): void
    {
        $shop = $this->shop();
        $cancellation = $this->order($shop);
        $cancellation->update(['cancellation_status' => 'pending']);
        $pickup = $this->order($shop, 'ready_to_ship');
        $shipped = $this->order($shop, 'shipped');
        $foreign = $this->order($this->shop(), 'ready_to_ship');
        $foreign->update(['cancellation_status' => 'pending']);
        foreach ([$pickup, $foreign] as $thread) {
            $thread->messages()->create(['sender_id' => $thread->order->buyer_id, 'body' => 'Unread buyer message']);
        }
        $pickup->messages()->create(['sender_id' => $pickup->order->buyer_id, 'body' => 'Another unread message']);
        $pickup->messages()->create(['sender_id' => $shop->user_id, 'body' => 'Seller reply']);
        $shipped->messages()->create(['sender_id' => $shipped->order->buyer_id, 'body' => 'Already read', 'read_at' => now()]);

        $this->actingAs($shop->user)->get(route('seller.dashboard', ['period' => 'this_month']))->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['cancellation_requests'] === 1 && $s['ready_for_pickup'] === 1 && $s['unread_messages'] === 2)
            ->assertSee(route('seller.orders', ['status' => 'cancellation_requested']))
            ->assertSee(route('seller.courier', ['status' => 'ready_to_ship']))
            ->assertSee(route('seller.chat', ['unread' => 1]));
        $this->get(route('seller.orders', ['status' => 'cancellation_requested']))->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 1 && $orders->first()->id === $cancellation->id);
        $this->get(route('seller.courier', ['status' => 'ready_to_ship']))->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 1 && $orders->first()->id === $pickup->id);
        $this->get(route('seller.chat', ['unread' => 1]))->assertOk()
            ->assertViewHas('contacts', fn ($contacts) => $contacts->total() === 1 && $contacts->first()->id === $pickup->id);
        $this->assertNull($foreign->messages()->first()->read_at);
        $cancellation->update(['cancellation_status' => 'rejected']);
        $pickup->update(['status' => 'shipped']);
        $this->get(route('seller.dashboard'))->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['cancellation_requests'] === 0 && $s['ready_for_pickup'] === 0 && $s['unread_messages'] === 0);
        $this->get(route('seller.chat', ['unread' => 1]))->assertOk()->assertViewHas('contacts', fn ($contacts) => $contacts->isEmpty());
    }

    public function test_seller_manages_sales_separately_from_product_creation(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $variant = $product->variants->first();
        $this->actingAs($shop->user);
        $this->get(route('seller.products', ['create' => 1]))->assertOk()->assertDontSee('name="discount_type"', false);
        $this->get(route('seller.products'))->assertOk()->assertSee(route('seller.products.sale', $product))->assertDontSee('name="discount_type"', false);
        $this->get(route('seller.products.sale', $product))->assertOk()->assertSee('Save sale settings')->assertSee('Current saved prices');

        $data = ['name' => $product->name, 'variants' => [['id' => $variant->id, 'price' => '100.00']], 'discount_type' => 'percentage', 'discount_value' => '12.50'];
        $this->post(route('seller.products.sale.save', $product), $data)->assertSessionHasNoErrors();
        $this->assertSame(8750, $variant->fresh()->sale_price_minor);
        $this->assertSame(10000, $variant->fresh()->price_minor);
        $data['discount_type'] = 'fixed';
        $data['discount_value'] = '25';
        $this->post(route('seller.products.sale.save', $product), $data)->assertSessionHasNoErrors();
        $this->assertSame(7500, $variant->fresh()->sale_price_minor);
        $this->post(route('seller.products.update', $product), ['name' => 'Updated details', 'variants' => [['id' => $variant->id, 'price' => 100]]])->assertSessionHasNoErrors();
        $this->assertSame(7500, $variant->fresh()->sale_price_minor);
        $this->assertDatabaseCount('products', 1);
        $data['discount_value'] = '100';
        $this->post(route('seller.products.sale.save', $product), $data)->assertSessionHasErrors('discount_value');
        $data['discount_type'] = 'percentage';
        $this->post(route('seller.products.sale.save', $product), $data)->assertSessionHasErrors('discount_value');
        $this->assertSame(7500, $variant->fresh()->sale_price_minor);
        $this->from(route('seller.products.sale', $product))->post(route('seller.products.sale.save', $product), $data)->assertRedirect(route('seller.products.sale', $product));
        $this->get(route('seller.products.sale', $product))->assertOk()->assertSee('value="100"', false);
        $this->flushSession();
        $data['discount_type'] = '';
        unset($data['discount_value']);
        $this->post(route('seller.products.sale.save', $product), $data)->assertSessionHasNoErrors();
        $this->assertSame(10000, $variant->fresh()->sale_price_minor);
        $this->post(route('seller.products.add'), ['name' => 'Promo item', 'category_id' => $product->category_id, 'sku' => 'PROMO-NEW', 'price' => 200, 'stock' => 10, 'discount_type' => 'fixed', 'discount_value' => 50])->assertSessionHasNoErrors();
        $this->assertSame(20000, \App\Models\ProductVariant::where('sku', 'PROMO-NEW')->firstOrFail()->sale_price_minor);
        $this->actingAs($this->shop()->user)->post(route('seller.products.sale.save', $product), $data)->assertNotFound();
    }

    public function test_sale_schedule_uses_philippine_time_and_expires_at_the_boundary(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-13 00:00:00', 'UTC'));
        $shop = $this->shop();
        $product = $this->product($shop);
        $variant = $product->variants->first();
        $data = ['discount_type' => 'percentage', 'discount_value' => 10,
            'discount_starts_at' => '2026-10-13T09:00', 'discount_ends_at' => '2026-10-14T09:00'];
        $this->actingAs($shop->user)->post(route('seller.products.sale.save', $product), $data)->assertSessionHasNoErrors();
        $this->assertSame('2026-10-13 01:00:00', $product->fresh()->discount_starts_at->format('Y-m-d H:i:s'));
        $this->assertSame('Scheduled', $product->fresh()->sale_status);
        $this->assertSame(10000, $variant->fresh()->sale_price_minor);
        $this->get(route('seller.products.sale', $product))->assertOk()->assertSee('value="2026-10-13T09:00"', false);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-13 01:00:00', 'UTC'));
        $this->assertSame(9000, $variant->fresh()->sale_price_minor);
        $this->assertSame('Sale active', $product->fresh()->sale_status);
        $product->update(['discount_type' => 'fixed', 'discount_value' => 25]);
        $this->assertSame(7500, $variant->fresh()->sale_price_minor);
        $this->travelTo(\Carbon\Carbon::parse('2026-10-14 01:00:00', 'UTC'));
        $this->assertSame('Sale ended', $product->fresh()->sale_status);
        $this->assertSame(10000, $variant->fresh()->sale_price_minor);
        $this->post(route('seller.products.sale.save', $product), $data)->assertSessionHasErrors('discount_ends_at');
        $data['discount_starts_at'] = '2026-10-16T09:00';
        $data['discount_ends_at'] = '2026-10-15T09:00';
        $this->post(route('seller.products.sale.save', $product), $data)->assertSessionHasErrors('discount_ends_at');
        $this->post(route('seller.products.sale.save', $product), ['discount_type' => null])->assertSessionHasNoErrors();
        $this->assertNull($product->fresh()->discount_starts_at);
        $this->assertNull($product->fresh()->discount_ends_at);
        $this->travelBack();
    }

    public function test_promo_prices_are_recalculated_in_cart_and_at_order_placement(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $variant = $product->variants->first();
        $product->update(['discount_type' => 'percentage', 'discount_value' => 10]);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Promo courier', 'slug' => 'promo-courier', 'status' => 'approved']);
        $this->actingAs($buyer)->postJson(route('cart.add'), ['id' => $product->id, 'variant_id' => $variant->id, 'name' => 'Forged name', 'price' => 1, 'quantity' => 2])->assertOk();
        $cart = app(\App\Services\BuyerCart::class)->all();
        $this->assertEquals(90, array_values($cart)[0]['price']);
        $product->update(['discount_type' => 'fixed', 'discount_value' => 25]);
        $this->assertEquals(75, array_values(app(\App\Services\BuyerCart::class)->all())[0]['price']);
        $product->update(['discount_ends_at' => now()->subMinute()]);
        $this->assertEquals(100, array_values(app(\App\Services\BuyerCart::class)->all())[0]['price']);
        $product->update(['discount_ends_at' => now()->addHour()]);
        $order = app(\App\Services\OrderPlacement::class)->place($buyer, array_values($cart), 'COD', 'Test address');
        $sellerOrder = $order->sellerOrders()->firstOrFail();
        $this->assertSame($shop->id, $sellerOrder->seller_id);
        $this->assertSame(7500, $sellerOrder->items->first()->unit_price_minor);
        $this->assertSame(15000, $sellerOrder->subtotal_minor);
        $this->assertSame(21000, $order->total_minor);
        $this->assertSame(2, $variant->fresh()->stock);
        $product->update(['discount_type' => null, 'discount_value' => 0]);
        $this->assertSame(7500, $sellerOrder->items->first()->fresh()->unit_price_minor);
    }

    public function test_restock_reminder_opens_by_itself_only_once_after_login(): void
    {
        $shop = $this->shop();
        $this->product($shop); // stock 4, below the low-stock limit
        $this->post(route('login.submit'), ['email' => $shop->user->email, 'password' => 'password', 'portal' => 'seller'])->assertRedirect();
        $this->assertAuthenticatedAs($shop->user);

        $this->get('/seller/dashboard')->assertOk()->assertViewHas('restockPrompt', true)
            ->assertSee('data-restock-autoopen="true"', false)->assertSee('4 left')->assertSee('1</strong> running low', false);
        $this->get('/seller/dashboard')->assertOk()->assertViewHas('restockPrompt', false)
            ->assertSee('data-restock-autoopen="false"', false)->assertSee('id="restock-dialog"', false);
    }

    public function test_stock_alert_appears_at_ten_and_clears_after_restocking(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $variant = $product->variants()->firstOrFail();
        $otherProduct = $this->product($this->shop());
        $this->actingAs($shop->user);

        foreach ([10, 9, 0] as $stock) {
            $variant->update(['stock' => $stock]);
            $this->get('/seller/dashboard')->assertOk()
                ->assertViewHas('stockAlertCount', 1)
                ->assertSee('id="restock-dialog"', false)->assertSee('data-restock-open', false)->assertDontSee('Stock alert:')->assertSee($product->name)
                ->assertSee($stock > 0 ? $stock.' left' : 'Out of stock')
                ->assertDontSee($otherProduct->name);
        }
        $this->get('/seller/account')->assertOk()->assertSee('id="restock-dialog"', false);
        $variant->update(['stock' => 11]);
        $this->get('/seller/dashboard')->assertOk()->assertDontSee('id="restock-dialog"', false);
        $variant->update(['stock' => 10, 'is_active' => false]);
        $this->get('/seller/dashboard')->assertOk()->assertViewHas('stockAlertCount', 0);
        $variant->update(['is_active' => true]);
        $product->update(['is_active' => false]);
        $this->get('/seller/dashboard')->assertOk()->assertViewHas('stockAlertCount', 0);
    }

    public function test_seller_requires_authentication_and_correct_role(): void
    {
        $this->get('/seller/dashboard')->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 0);
        $this->actingAs(User::factory()->create(['role' => 'buyer']))->get('/seller/dashboard')->assertForbidden();
    }

    public function test_workspace_notifications_poll_real_cancellations_stock_and_product_updates(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $variant = $product->variants()->firstOrFail();
        $order = $this->order($shop);
        $this->actingAs($order->order->buyer)->post(route('orders.cancel', $order->id), ['reason' => 'I changed my mind'])
            ->assertSessionHasNoErrors();

        $this->actingAs($shop->user)->get('/seller/dashboard')->assertOk()->assertSee('data-notifications-url', false);
        $page = $this->get('/seller/notifications')->assertOk()->assertViewIs('seller.notifications');
        $page->assertSee('data-notification-filter="unread"', false)
            ->assertSee('data-notification-filter="stock"', false)
            ->assertSee('data-notification-category="stock"', false)
            ->assertSee('View stock for')->assertSee('Action needed')
            ->assertSee('seller-notifications.css');
        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($page->getContent());
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//aside[@id="seller-sidebar"]//*[@data-notification-items]')->length);
        $this->assertSame(1, $xpath->query('//main[@id="main"]//*[@data-notification-items]')->length);
        $this->assertSame('/seller/notifications', $xpath->query('//*[@data-notifications-url]')->item(0)->getAttribute('data-notifications-url'));
        $feed = $this->getJson('/seller/notifications')->assertOk()->assertJsonPath('count', 2);
        $this->assertStringContainsString('Cancellation request', $feed->json('html'));
        $this->assertStringContainsString('Low stock', $feed->json('html'));
        $this->assertStringContainsString($product->name, $feed->json('html'));
        $this->getJson('/seller/notifications')->assertJsonPath('count', 2);

        $notification = \App\Models\AppNotification::where('user_id', $shop->user_id)->firstOrFail();
        $this->postJson(route('seller.notifications.read', $notification))->assertOk();
        $this->getJson('/seller/notifications')->assertJsonPath('count', 1);
        $this->post(route('seller.inventory.stock', $product), ['variants' => [
            ['id' => $variant->id, 'stock' => 20, 'previous_stock' => 4],
        ]])->assertSessionHasNoErrors();
        $feed = $this->getJson('/seller/notifications')->assertOk()->assertJsonPath('count', 1);
        $this->assertStringContainsString('Stock updated', $feed->json('html'));
        $this->assertStringNotContainsString('Needs restocking', $feed->json('html'));

        $this->post(route('seller.products.update', $product), ['name' => 'New product name', 'variants' => [
            ['id' => $variant->id, 'price' => 150],
        ]])->assertSessionHasNoErrors();
        $feed = $this->getJson('/seller/notifications')->assertOk()->assertJsonPath('count', 2);
        $this->assertStringContainsString('Product updated', $feed->json('html'));
        $this->assertStringContainsString('New product name', $feed->json('html'));
    }

    public function test_seller_notifications_are_private_and_escape_content(): void
    {
        $shop = $this->shop();
        $other = $this->shop();
        $foreign = \App\Services\NotificationService::notify($other->user_id, 'product_update', 'Private update', 'Secret details');
        \App\Services\NotificationService::notify($shop->user_id, 'product_update', '<script>alert(1)</script>', 'Safe message');
        $this->getJson('/seller/notifications')->assertUnauthorized();
        $this->actingAs(User::factory()->create(['role' => 'buyer']))->getJson('/seller/notifications')->assertForbidden();
        $feed = $this->actingAs($shop->user)->getJson('/seller/notifications')->assertOk()->assertJsonPath('count', 1);
        $this->assertStringNotContainsString('Private update', $feed->json('html'));
        $this->assertStringNotContainsString('<script>', $feed->json('html'));
        $this->assertStringContainsString('&lt;script&gt;', $feed->json('html'));
        $this->postJson(route('seller.notifications.read', $foreign))->assertNotFound();
        $this->assertFalse($foreign->fresh()->is_read);
    }

    public function test_seller_notifications_paginate_twenty_combined_rows_and_filter_all_pages(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        for ($i = 1; $i <= 25; $i++) {
            \App\Models\AppNotification::create(['user_id' => $shop->user_id,
                'type' => 'product_update', 'title' => 'Update '.$i, 'message' => 'Saved update '.$i,
                'is_read' => false]);
        }
        $this->actingAs($shop->user);
        $first = $this->get(route('seller.notifications'))->assertOk();
        $first->assertViewHas('notificationPaginator', fn ($p) => $p->total() === 26 && $p->count() === 20);
        $first->assertViewHas('sellerStockNotifications', fn ($rows) => $rows->count() === 1);
        $first->assertViewHas('sellerNotifications', fn ($rows) => $rows->count() === 19);
        $second = $this->get(route('seller.notifications', ['page' => 2]))->assertOk();
        $second->assertViewHas('notificationPaginator', fn ($p) => $p->currentPage() === 2 && $p->count() === 6);
        $this->assertEmpty(array_intersect($first['sellerNotifications']->modelKeys(), $second['sellerNotifications']->modelKeys()));
        $this->getJson(route('seller.notifications', ['page' => 2]))->assertOk()
            ->assertJsonPath('filterCounts.products', 25)->assertJsonPath('count', 26);
        $filtered = $this->get(route('seller.notifications', ['filter' => 'products', 'page' => 2]))->assertOk();
        $filtered->assertViewHas('notificationPaginator', fn ($p) => $p->total() === 25 && $p->count() === 5);
        $filtered->assertViewHas('sellerStockNotifications', fn ($rows) => $rows->isEmpty());
        $this->get(route('seller.notifications', ['page' => 999]))->assertOk()
            ->assertViewHas('notificationPaginator', fn ($p) => $p->currentPage() === 2);
        for ($i = 1; $i <= 22; $i++) {
            $product->variants()->create(['name' => 'Stock '.$i, 'sku' => 'PAGINATION-'.$i, 'price_minor' => 100, 'stock' => 0, 'is_active' => true]);
        }
        $this->get(route('seller.notifications', ['filter' => 'stock', 'page' => 2]))->assertOk()
            ->assertViewHas('notificationPaginator', fn ($p) => $p->total() === 23 && $p->count() === 3);
        $this->get(route('seller.notifications', ['page' => 2]))->assertOk()
            ->assertViewHas('sellerStockNotifications', fn ($rows) => $rows->count() === 3)
            ->assertViewHas('sellerNotifications', fn ($rows) => $rows->count() === 17);
    }

    public function test_seller_can_mark_all_notifications_as_read(): void
    {
        $shop = $this->shop();
        \App\Services\NotificationService::notify($shop->user_id, 'product_update', 'Notice 1', 'Message 1');
        \App\Services\NotificationService::notify($shop->user_id, 'product_update', 'Notice 2', 'Message 2');

        $this->actingAs($shop->user)->getJson('/seller/notifications')->assertJsonPath('count', 2);
        $this->post(route('seller.notifications.markAllRead'))->assertSessionHasNoErrors();
        $this->getJson('/seller/notifications')->assertJsonPath('count', 0);
    }

    public function test_admin_product_visibility_changes_notify_the_owning_seller(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->post(route('admin.compliance.action', $product), ['action' => 'suspend_product'])
            ->assertSessionHasNoErrors();
        $feed = $this->actingAs($shop->user)->getJson('/seller/notifications')->assertOk()->assertJsonPath('count', 1);
        $this->assertStringContainsString('Product visibility updated by admin', $feed->json('html'));
        $this->assertStringContainsString('was suspended by admin', $feed->json('html'));
    }

    public function test_dashboard_and_reports_scope_paid_delivered_totals_and_dates(): void
    {
        $shop = $this->shop();
        $valid = $this->order($shop, 'completed', 'paid');
        $this->order($shop, 'completed', 'pending');
        $this->order($shop, 'cancelled', 'paid');
        $this->order($shop, 'delivered', 'refunded');
        $this->order($this->shop(), 'completed', 'paid');
        $this->actingAs($shop->user)->get('/seller/dashboard')->assertOk()->assertViewHas('stats', fn ($s) => $s['sales'] === 10000 && $s['net'] === 9200);
        $this->get('/seller/reports')->assertOk()->assertViewHas('summary', fn ($s) => $s['gross'] === 10000 && $s['commission'] === 800 && $s['count'] === 1);
        $this->get('/seller/reports?from_date=2000-01-01&to_date=2000-01-02')->assertOk()->assertViewHas('summary', fn ($s) => $s['count'] === 0);
        $csv = $this->get('/seller/reports?export=csv')->assertOk()->assertDownload();
        $this->assertStringContainsString($valid->order->reference, $csv->streamedContent());
        $this->get('/seller/reports?from_date=2026-10-08&to_date=2026-10-01')->assertSessionHasErrors('to_date');
    }

    public function test_overview_periods_filter_sales_and_chart_but_keep_current_order_counts(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-10-08 12:00:00'));
        $shop = $this->shop();
        $this->order($shop, 'completed', 'paid')->update(['delivered_at' => '2026-10-02 00:00:00']);
        $this->order($shop, 'completed', 'paid')->update(['delivered_at' => '2026-10-01 23:59:59']);
        $this->order($shop, 'completed', 'paid')->update(['delivered_at' => '2026-09-10 12:00:00']);
        $this->order($shop, 'completed', 'paid')->update(['delivered_at' => '2026-09-08 12:00:00']);
        $this->order($shop, 'completed', 'paid')->update(['delivered_at' => '2026-10-09 00:00:00']);
        foreach (['pending', 'accepted', 'packed'] as $status) {
            $this->order($shop, $status)->update(['created_at' => '2026-08-01 12:00:00']);
        }
        $this->order($this->shop(), 'completed', 'paid');
        $this->actingAs($shop->user)->get('/seller/dashboard')->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['sales'] === 10000 && $s['net'] === 9200 && $s['orders'] === 3)
            ->assertViewHas('chart', fn ($c) => $c->count() === 7 && $c->sum('sales') === 10000)
            ->assertSee('3 orders awaiting preparation')->assertDontSee('Welcome back');
        $this->get('/seller/dashboard?period=30_days')->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['sales'] === 30000 && $s['orders'] === 3)
            ->assertViewHas('chart', fn ($c) => $c->count() === 30 && $c->sum('sales') === 30000);
        $this->get('/seller/dashboard?period=this_month')->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['sales'] === 20000)
            ->assertViewHas('chart', fn ($c) => $c->count() === 8)
            ->assertViewHas('reportDates', ['from_date' => '2026-10-01', 'to_date' => '2026-10-08']);
        $this->get('/seller/orders?status=to_prepare')->assertOk()
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 3);
        $this->get('/seller/products?create=1')->assertOk()->assertSee('class="product-editor"', false)->assertDontSee('<dialog id="add-product"', false);
        $this->get('/seller/dashboard?period=invalid')->assertSessionHasErrors('period');
        $this->travelBack();
    }

    public function test_product_editor_preserves_invalid_entries_and_returns_to_catalog_after_creation(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $this->actingAs($shop->user);
        $this->get(route('seller.products'))->assertOk()->assertDontSee('id="add-product-form"', false);
        $this->get(route('seller.products', ['create' => 1]))->assertOk()
            ->assertSee('id="add-product-form"', false)->assertSee('id="product-pricing"', false)
            ->assertDontSee('Edit product details')->assertDontSee('<dialog', false);

        $data = ['name' => 'New editor listing', 'category_id' => $product->category_id, 'sku' => 'EDITOR-001', 'price' => '-1', 'stock' => 5];
        $this->from(route('seller.products', ['create' => 1]))->post(route('seller.products.add'), $data)
            ->assertRedirect(route('seller.products', ['create' => 1]))->assertSessionHasErrors('price');
        $this->get(route('seller.products', ['create' => 1]))->assertOk()
            ->assertSee('value="New editor listing"', false)->assertSee('value="EDITOR-001"', false);
        $data['price'] = '149.50';
        $this->post(route('seller.products.add'), $data)->assertSessionHasNoErrors()->assertRedirect(route('seller.products'));
        $this->get(route('seller.products'))->assertOk()->assertSee('New editor listing')->assertDontSee('id="add-product-form"', false);
        $this->assertDatabaseHas('product_variants', ['sku' => 'EDITOR-001', 'price_minor' => 14950, 'stock' => 5]);
    }

    public function test_first_product_color_and_size_are_saved_with_or_without_additional_options(): void
    {
        $shop = $this->shop();
        $category = $this->product($shop)->category_id;
        $this->actingAs($shop->user);

        foreach ([false, true] as $additional) {
            $sku = $additional ? 'FIRST-WITH-EXTRAS' : 'FIRST-ONLY';
            $data = [
                'name' => $sku, 'category_id' => $category, 'primary_variant' => 1,
                'primary_color' => 'Black', 'primary_size' => 'Medium',
                'sku' => $sku, 'price' => '249.50', 'stock' => 7,
            ];
            if ($additional) {
                $data['has_variants'] = 1;
                $data['variants'] = [['name' => 'White / Large', 'price' => '299', 'stock' => 3]];
            }
            $this->post(route('seller.products.add'), $data)->assertSessionHasNoErrors();
            $product = $shop->products()->where('name', $sku)->firstOrFail();
            $this->assertCount($additional ? 2 : 1, $product->variants);
            $first = $product->variants->firstWhere('sku', $sku);
            $this->assertSame('Black / Medium', $first->name);
            $this->assertSame(24950, $first->price_minor);
            $this->assertSame(7, $first->stock);
            $this->assertSame(['color_name' => 'Black', 'size' => 'Medium'], $first->options);
        }
    }

    public function test_one_color_can_have_multiple_sizes_with_individual_stock(): void
    {
        $shop = $this->shop();
        $category = $this->product($shop)->category_id;
        $this->actingAs($shop->user);
        $data = [
            'name' => 'Five size shoes', 'category_id' => $category, 'primary_variant' => 1,
            'primary_color' => 'Black', 'primary_size' => '35, 36, 37, 38, 39, 35',
            'sku' => 'FIVE-SIZES', 'price' => '499.50',
            'primary_size_stocks' => collect(range(35, 39))->map(fn ($size, $index) => [
                'size' => (string) $size, 'stock' => $index,
            ])->all(),
            'has_variants' => 1,
            'variants' => [['name' => 'White / 40', 'price' => '599', 'stock' => 2]],
        ];
        $this->post(route('seller.products.add'), $data)->assertSessionHasNoErrors();
        $product = $shop->products()->where('name', 'Five size shoes')->firstOrFail();
        $this->assertCount(6, $product->variants);
        $this->assertCount(6, $product->variants->pluck('sku')->unique());
        foreach (range(35, 39) as $index => $size) {
            $variant = $product->variants->firstWhere('name', 'Black / '.$size);
            $this->assertNotNull($variant);
            $this->assertSame($index, $variant->stock);
            $this->assertSame(49950, $variant->price_minor);
            $this->assertSame(['color_name' => 'Black', 'size' => (string) $size], $variant->options);
        }
        $data['sku'] = 'INVALID-SIZES';
        $data['primary_size_stocks'][0]['stock'] = -1;
        $this->post(route('seller.products.add'), $data)->assertSessionHasErrors('primary_size_stocks.0.stock');
        $data['primary_size_stocks'][0]['stock'] = 1;
        array_pop($data['primary_size_stocks']);
        $this->post(route('seller.products.add'), $data)->assertSessionHasErrors('primary_size');
        $this->assertSame(1, $shop->products()->where('name', 'Five size shoes')->count());
    }

    public function test_one_color_saves_independent_size_prices_and_stock_without_a_shared_price(): void
    {
        $shop = $this->shop();
        $category = $this->product($shop)->category_id;
        $this->actingAs($shop->user);
        $data = [
            'name' => 'Independent size pricing', 'category_id' => $category,
            'primary_variant' => 1, 'primary_color' => 'Branch / Silver Pebble / Branch',
            'primary_size' => 'US 7, US 8', 'sku' => 'SIZE-PRICES',
            'primary_size_stocks' => [
                ['size' => 'US 7', 'price' => '1299.50', 'stock' => 3],
                ['size' => 'US 8', 'price' => '1499.75', 'stock' => 0],
            ],
        ];
        $this->post(route('seller.products.add'), $data)->assertSessionHasNoErrors();
        $product = $shop->products()->where('name', $data['name'])->firstOrFail();
        $this->assertCount(2, $product->variants);
        foreach ($data['primary_size_stocks'] as $row) {
            $variant = $product->variants->firstWhere('name', $data['primary_color'].' / '.$row['size']);
            $this->assertNotNull($variant);
            $this->assertSame((int) round($row['price'] * 100), $variant->price_minor);
            $this->assertSame($row['stock'], $variant->stock);
        }
        $data['sku'] = 'INVALID-SIZE-PRICES';
        $data['primary_size_stocks'][0]['price'] = '0';
        $this->post(route('seller.products.add'), $data)->assertSessionHasErrors('primary_size_stocks.0.price');
        unset($data['primary_size_stocks'][0]['price']);
        $this->post(route('seller.products.add'), $data)->assertSessionHasErrors('primary_size_stocks.0.price');
        $this->assertSame(1, $shop->products()->where('name', $data['name'])->count());
    }

    public function test_additional_options_reject_duplicates_and_preserve_entries_for_correction(): void
    {
        $shop = $this->shop();
        $category = $this->product($shop)->category_id;
        $this->actingAs($shop->user);
        $data = [
            'name' => 'Extra option checks', 'category_id' => $category, 'primary_variant' => 1,
            'primary_color' => 'Black', 'primary_size' => '35, 36',
            'sku' => 'OPTION-CHECK', 'price' => '499', 'stock' => 2, 'has_variants' => 1,
            'variants' => [['name' => 'black/35', 'price' => '499', 'stock' => 3]],
        ];
        $this->from(route('seller.products', ['create' => 1]))->post(route('seller.products.add'), $data)
            ->assertSessionHasErrors('variants.0.name');
        $this->get(route('seller.products', ['create' => 1]))->assertOk()
            ->assertSee('value="black/35"', false)->assertSee('Review added options');
        $data['variants'] = [
            ['name' => 'White / 35', 'price' => '499', 'stock' => 3],
            ['name' => 'white/35', 'price' => '599', 'stock' => 1],
        ];
        $this->post(route('seller.products.add'), $data)->assertSessionHasErrors('variants.1.name');
        $data['variants'] = [['name' => 'White / 35', 'price' => '499', 'stock' => 3, 'sku' => 'OPTION-CHECK']];
        $this->post(route('seller.products.add'), $data)->assertSessionHasErrors('variants.0.sku');
        $this->assertFalse($shop->products()->where('name', 'Extra option checks')->exists());
        unset($data['variants']);
        $this->post(route('seller.products.add'), $data)->assertSessionHasNoErrors();
        $this->assertCount(2, $shop->products()->where('name', 'Extra option checks')->firstOrFail()->variants);
    }

    public function test_inventory_persists_validates_and_preserves_each_variant(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $second = $product->variants()->create(['sku' => 'SECOND', 'name' => 'Large', 'price_minor' => 15000, 'stock' => 10, 'is_active' => true]);
        $this->actingAs($shop->user)->get('/seller/inventory?tab=low_stock')->assertOk()->assertSee($product->name);
        $this->post('/seller/inventory/add', ['name' => 'New saved item', 'category_id' => $product->category_id, 'sku' => 'NEW-SKU', 'price' => '12.34', 'stock' => 6])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('product_variants', ['sku' => 'NEW-SKU', 'price_minor' => 1234, 'stock' => 6]);
        $this->post('/seller/inventory/'.$product->id.'/update', ['name' => 'Updated item', 'variants' => [['id' => $second->id, 'price' => '25.45', 'stock' => 20]]])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('product_variants', ['id' => $second->id, 'price_minor' => 2545, 'stock' => 20]);
        $this->assertDatabaseHas('product_variants', ['sku' => 'SKU-'.$shop->id, 'price_minor' => 10000, 'stock' => 4]);
        $this->post('/seller/inventory/'.$product->id.'/archive')->assertSessionHasNoErrors();
        $this->assertFalse($product->fresh()->is_active);
        $this->post('/seller/inventory/add', ['name' => 'Invalid', 'category_id' => $product->category_id, 'sku' => 'BAD', 'price' => -1, 'stock' => -2])->assertSessionHasErrors(['price', 'stock']);
        $this->get('/seller/products?search=does-not-exist')->assertOk()
            ->assertSee('class="product-editor"', false)->assertSee('value="Invalid"', false);
    }

    public function test_other_sellers_cannot_read_or_modify_orders_products_or_conversations(): void
    {
        $mine = $this->shop();
        $other = $this->shop();
        $order = $this->order($other);
        $product = $this->product($other);
        $this->actingAs($mine->user)->get('/seller/orders')->assertOk()->assertDontSee($order->order->reference);
        $this->get('/seller/orders/'.$order->id.'/waybill')->assertNotFound();
        $this->post('/seller/orders/'.$order->id.'/pack')->assertNotFound();
        $this->post('/seller/inventory/'.$product->id.'/archive')->assertNotFound();
        $this->post('/seller/inventory/'.$product->id.'/update', [])->assertNotFound();
        $this->get('/seller/chat?contact='.$order->id)->assertNotFound();
        $this->post('/seller/chat/'.$order->id.'/send', ['message' => 'Unauthorized'])->assertNotFound();
        $this->assertDatabaseCount('seller_messages', 0);
    }

    public function test_stock_workspace_changes_only_stock_and_rejects_stale_or_foreign_updates(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $variant = $product->variants->first();
        $foreign = $this->product($this->shop())->variants->first();
        $this->actingAs($shop->user)->get('/seller/inventory')->assertOk()->assertSee('Save stock quantities')->assertDontSee('id="add-product"', false);
        $this->get('/seller/products')->assertOk()->assertSee('Edit product details')->assertSee('Manage stock');
        $url = route('seller.inventory.stock', $product);
        $this->post($url, ['name' => 'Unwanted edit', 'variants' => [['id' => $variant->id, 'stock' => 12, 'previous_stock' => 4, 'price' => 1]]])->assertSessionHasNoErrors();
        $this->assertSame(12, $variant->fresh()->stock);
        $this->assertSame(10000, $variant->fresh()->price_minor);
        $this->assertSame($product->name, $product->fresh()->name);
        $this->post($url, ['variants' => [['id' => $variant->id, 'stock' => 20, 'previous_stock' => 4]]])->assertSessionHasErrors('stock');
        $this->post($url, ['variants' => [['id' => $variant->id, 'stock' => -1, 'previous_stock' => 12]]])->assertSessionHasErrors('variants.0.stock');
        $this->post($url, ['variants' => [['id' => $variant->id, 'stock' => 20, 'previous_stock' => 12], ['id' => $foreign->id, 'stock' => 20, 'previous_stock' => 4]]])->assertNotFound();
        $this->assertSame(12, $variant->fresh()->stock);
        $this->assertSame(4, $foreign->fresh()->stock);
        $this->post(route('seller.inventory.stock', $foreign->product_id), ['variants' => [['id' => $foreign->id, 'stock' => 2, 'previous_stock' => 4]]])->assertNotFound();
        $this->flushSession();
        $this->post(route('seller.products.update', $product), ['name' => 'Updated listing', 'variants' => [['id' => $variant->id, 'price' => 150, 'stock' => 0]]])->assertSessionHasNoErrors();
        $this->assertSame(12, $variant->fresh()->stock);
        $this->assertSame(15000, $variant->fresh()->price_minor);
    }

    public function test_packing_and_pickup_use_real_states_without_faking_delivery(): void
    {
        $shop = $this->shop();
        $order = $this->order($shop);
        $provider = LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Actual courier', 'slug' => 'actual-courier', 'status' => 'approved']);
        $this->actingAs($shop->user)->post('/seller/orders/'.$order->id.'/pack')->assertSessionHasNoErrors();
        $this->assertSame('ready_to_ship', $order->fresh()->status);
        $this->post('/seller/orders/'.$order->id.'/pack')->assertUnprocessable();
        $data = ['logistics_provider_id' => $provider->id, 'pickup_date' => now()->format('Y-m-d')];
        $this->post('/seller/courier/'.$order->id.'/schedule', $data)->assertSessionHasNoErrors();
        $this->post('/seller/courier/'.$order->id.'/schedule', $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('shipments', 1);
        $this->assertSame('ready_to_ship', $order->fresh()->status);
        $this->assertSame('unassigned', $order->fresh()->shipment->status);
        $this->get('/seller/courier')->assertOk()->assertSee('Actual courier');
        $this->get('/seller/orders/'.$order->id.'/waybill')->assertOk()->assertSee('Actual saved address');
        $this->post('/seller/deliveries/'.$order->id.'/confirm')->assertNotFound();
    }

    public function test_unpaid_wallet_orders_cannot_be_packed(): void
    {
        $shop = $this->shop();
        $order = $this->order($shop);
        $order->order->update(['payment_method' => 'wallet']);
        $this->actingAs($shop->user)->post('/seller/orders/'.$order->id.'/pack')->assertUnprocessable();
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_buyer_and_seller_can_exchange_persistent_messages_and_mark_them_read(): void
    {
        $shop = $this->shop();
        $order = $this->order($shop);
        $buyer = $order->order->buyer;
        $this->actingAs($buyer)->post('/buyer/messages/'.$order->id, ['message' => 'When will this ship?'])->assertRedirect();
        $message = SellerMessage::first();
        $this->assertNull($message->read_at);
        $this->actingAs($shop->user)->get('/seller/chat?contact='.$order->id)->assertOk()->assertSee('When will this ship?');
        $this->assertNotNull($message->fresh()->read_at);
        $this->post('/seller/chat/'.$order->id.'/send', ['message' => 'We will prepare your order today.'])->assertRedirect();
        $this->actingAs($buyer)->get('/buyer/messages?contact='.$order->id)->assertOk()->assertSee('We will prepare your order today.')
            ->assertViewIs('buyer.dashboard')->assertViewHas('tab', 'messages')
            ->assertSee('data-buyer-inbox', false)->assertSee('Back to My Orders')
            ->assertSee('Dashboard navigation')->assertDontSee('css/seller.css')->assertDontSee('Seller workspace');
        $this->get('/dashboard?tab=messages&contact='.$order->id)->assertOk()->assertSee('We will prepare your order today.');
        $this->assertSame('buyer', $buyer->fresh()->role);
        $this->post('/buyer/messages/'.$order->id, ['message' => '   '])->assertSessionHasErrors('message');
        $this->actingAs(User::factory()->create(['role' => 'buyer']))->get('/buyer/messages?contact='.$order->id)->assertNotFound();
        $this->assertDatabaseCount('seller_messages', 2);
    }

    public function test_buyer_inbox_empty_and_search_states_use_buyer_dashboard(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->actingAs($buyer)->get('/buyer/messages')->assertOk()->assertViewIs('buyer.dashboard')
            ->assertSee('Your conversations start here')->assertDontSee('css/seller.css');
        $this->get('/buyer/messages?search=Unknown')->assertOk()->assertSee('No conversations found.');
    }

    public function test_profile_address_and_review_reply_persist(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $order = $this->order($shop, 'completed', 'paid');
        $item = OrderItem::create(['seller_order_id' => $order->id, 'product_id' => $product->id, 'product_variant_id' => $product->variants->first()->id, 'product_name' => $product->name, 'variant_name' => 'Small', 'unit_price_minor' => 10000, 'quantity' => 1]);
        $review = Review::create(['user_id' => $order->order->buyer_id, 'order_item_id' => $item->id, 'product_id' => $product->id, 'rating' => 4, 'comment' => 'Real review']);
        $this->actingAs($shop->user)->post('/seller/account/profile', ['store_name' => 'My updated store', 'description' => 'Actual description', 'phone' => '09123456789'])->assertSessionHasNoErrors();
        $this->assertSame('My updated store', $shop->fresh()->name);
        $this->post('/seller/account/address', ['recipient' => 'Shop owner', 'phone' => '09123456789', 'line1' => '42 Real Street', 'barangay' => 'Central', 'city' => 'Quezon City', 'province' => 'Metro Manila', 'postal_code' => '1100'])->assertSessionHasNoErrors();
        $this->get('/seller/account')->assertOk()->assertSee('42 Real Street');
        $this->post('/seller/feedback/'.$review->id.'/reply', ['reply' => 'Thank you for your review.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reviews', ['id' => $review->id, 'seller_reply' => 'Thank you for your review.']);
        $this->get('/seller/feedback')->assertOk()->assertSee('Real review');
        $this->actingAs($order->order->buyer)->get(route('buyer.dashboard', ['tab' => 'reviews']))->assertOk()->assertSee('Thank you for your review.')->assertSee(route('buyer.messages', ['contact' => $order->id]));
        $this->actingAs($this->shop()->user)->post('/seller/feedback/'.$review->id.'/reply', ['reply' => 'Wrong store'])->assertNotFound();
    }

    public function test_new_store_profile_uses_current_account_without_demo_records(): void
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active', 'id_status' => 'verified']);
        $this->actingAs($user)->post('/seller/account/profile', ['store_name' => 'My real business'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('sellers', ['user_id' => $user->id, 'name' => 'My real business', 'status' => 'approved']);
        $this->post('/seller/account/profile', ['store_name' => 'Updated business'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('sellers', 1);
    }

    public function test_product_photo_is_stored_and_foreign_variant_changes_are_rolled_back(): void
    {
        Storage::fake('public');
        $shop = $this->shop();
        $product = $this->product($shop);
        $other = $this->product($this->shop());
        $photo = UploadedFile::fake()->createWithContent('item.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a1S8AAAAASUVORK5CYII='));
        $this->actingAs($shop->user)->post('/seller/inventory/add', ['name' => 'Photo item', 'category_id' => $product->category_id, 'sku' => 'PHOTO', 'price' => 50, 'stock' => 3, 'image' => $photo])->assertSessionHasNoErrors();
        $created = Product::where('name', 'Photo item')->firstOrFail();
        Storage::disk('public')->assertExists($created->coverImage->path);
        $this->get('/seller/products')->assertOk()->assertSee(asset('storage/'.$created->coverImage->path));
        $this->post('/seller/inventory/'.$product->id.'/update', ['name' => 'Should roll back', 'variants' => [['id' => $other->variants->first()->id, 'price' => 2, 'stock' => 1]]])->assertNotFound();
        $this->assertSame('Saved product '.$shop->id, $product->fresh()->name);
        $this->assertSame(10000, $other->variants->first()->fresh()->price_minor);
    }

    public function test_refunded_orders_cannot_be_packed_and_messages_are_escaped(): void
    {
        $shop = $this->shop();
        $order = $this->order($shop, 'pending', 'refunded');
        $this->actingAs($shop->user)->post('/seller/orders/'.$order->id.'/pack')->assertUnprocessable();
        $order->messages()->create(['sender_id' => $order->order->buyer_id, 'body' => '<script>alert(1)</script>']);
        $this->get('/seller/chat?contact='.$order->id)->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->get('/seller/chat?search=NoSuchCustomer')->assertOk()->assertSee('No conversations');
    }

    public function test_product_with_multiple_shades_and_variations_persists_with_photos(): void
    {
        Storage::fake('public');
        $shop = $this->shop();
        $base = $this->product($shop);
        $shadePhoto1 = UploadedFile::fake()->createWithContent('shade1.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a1S8AAAAASUVORK5CYII='));

        $this->actingAs($shop->user)->post('/seller/inventory/add', [
            'name' => 'Matte Lip Stain',
            'category_id' => $base->category_id,
            'has_variants' => '1',
            'variants' => [
                [
                    'name' => 'Shade 01 - Peachy Pink',
                    'sku' => 'LIP-PEACH-01',
                    'price' => '299.00',
                    'stock' => 50,
                    'color' => '#F4A5A5',
                    'image' => $shadePhoto1,
                ],
                [
                    'name' => 'Shade 02 - Cherry Pop',
                    'sku' => 'LIP-CHERRY-02',
                    'price' => '329.00',
                    'stock' => 35,
                    'color' => '#C41E3A',
                ],
            ],
        ])->assertSessionHasNoErrors();

        $product = Product::where('name', 'Matte Lip Stain')->firstOrFail();
        $this->assertCount(2, $product->variants);
        $v1 = $product->variants()->where('sku', 'LIP-PEACH-01')->firstOrFail();
        $this->assertSame('Shade 01 - Peachy Pink', $v1->name);
        $this->assertSame(29900, $v1->price_minor);
        $this->assertSame(50, $v1->stock);
        $this->assertNotNull($v1->image_path);
        Storage::disk('public')->assertExists($v1->image_path);

        $v2 = $product->variants()->where('sku', 'LIP-CHERRY-02')->firstOrFail();
        $this->assertSame('Shade 02 - Cherry Pop', $v2->name);
        $this->assertSame(32900, $v2->price_minor);
        $this->assertSame(35, $v2->stock);

        $this->get('/seller/products')
            ->assertOk()
            ->assertSee('Shade 01 - Peachy Pink')
            ->assertSee('Shade 02 - Cherry Pop')
            ->assertSee('catalog-variants')
            ->assertSee('View options &amp; stock', false)
            ->assertSee('35 left')
            ->assertSee(route('seller.inventory', ['search' => 'LIP-CHERRY-02']));
    }

    public function test_color_photo_is_saved_for_every_size_and_resolves_for_buyers(): void
    {
        Storage::fake('public');
        $shop = $this->shop();
        $base = $this->product($shop);
        $photo = UploadedFile::fake()->createWithContent('brown.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a1S8AAAAASUVORK5CYII='));
        $this->actingAs($shop->user)->post(route('seller.products.add'), [
            'name' => 'Color photo shoes', 'category_id' => $base->category_id,
            'primary_variant' => 1, 'primary_color' => 'Black', 'primary_size' => '35',
            'price' => '499', 'stock' => 3, 'has_variants' => 1,
            'variants' => collect(['35', '36', '37'])->map(fn ($size) => [
                'name' => 'Brown / '.$size, 'price' => '499', 'stock' => 2, 'image' => $photo,
            ])->all(),
        ])->assertSessionHasNoErrors();
        $product = Product::where('name', 'Color photo shoes')->firstOrFail();
        $this->assertCount(4, $product->variants);
        foreach ($product->variants->filter(fn ($variant) => str_starts_with($variant->name, 'Brown')) as $variant) {
            Storage::disk('public')->assertExists($variant->image_path);
            $this->assertSame(asset('storage/'.$variant->image_path), $variant->resolved_image_url);
        }
    }

    public function test_product_accepts_up_to_10_gallery_photos(): void
    {
        Storage::fake('public');
        $shop = $this->shop();
        $base = $this->product($shop);

        $photos = [];
        for ($i = 1; $i <= 5; $i++) {
            $photos[] = UploadedFile::fake()->createWithContent("photo_{$i}.png", base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a1S8AAAAASUVORK5CYII='));
        }

        $this->actingAs($shop->user)->post('/seller/inventory/add', [
            'name' => 'Multi Photo Item',
            'category_id' => $base->category_id,
            'sku' => 'MULTI-PHOTO',
            'price' => '89.90',
            'stock' => 12,
            'images' => $photos,
        ])->assertSessionHasNoErrors();

        $product = Product::where('name', 'Multi Photo Item')->firstOrFail();
        $this->assertCount(5, $product->images);
        $this->assertSame(0, $product->images->first()->position);
        $this->assertSame($product->images->first()->id, $product->coverImage->id);

        foreach ($product->images as $img) {
            Storage::disk('public')->assertExists($img->path);
        }
    }

    public function test_seller_can_delete_archived_product_and_preserve_order_history(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $product->update(['is_active' => false]);
        $variant = $product->variants->first();
        $order = $this->order($shop);
        $item = OrderItem::create(['seller_order_id' => $order->id, 'product_id' => $product->id,
            'product_variant_id' => $variant->id, 'product_name' => $product->name,
            'variant_name' => $variant->name, 'unit_price_minor' => 10000, 'quantity' => 1]);

        $this->actingAs($shop->user)->get(route('seller.archive'))->assertOk()
            ->assertSee(route('seller.archive.delete', $product), false)->assertSee('Delete product');
        $this->delete(route('seller.archive.delete', $product))->assertRedirect(route('seller.archive'))->assertSessionHasNoErrors();
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'product_name' => $product->name]);
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'stock' => 4]);
        $this->get(route('seller.archive'))->assertDontSee($product->name);
        $this->post(route('seller.products.archive', $product))->assertNotFound();
    }

    public function test_archive_deletion_rejects_active_and_other_sellers_products(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $foreign = $this->product($this->shop());
        $foreign->update(['is_active' => false]);
        $this->actingAs($shop->user)->delete(route('seller.archive.delete', $product))->assertStatus(422);
        $this->delete(route('seller.archive.delete', $foreign))->assertNotFound();
        $this->assertNotSoftDeleted($product);
        $this->assertNotSoftDeleted($foreign);
        $product->update(['is_active' => false]);
        $shop->user->update(['status' => 'pending']);
        $this->delete(route('seller.archive.delete', $product))->assertSessionHasErrors('name');
        $this->assertNotSoftDeleted($product);
    }

    public function test_deleted_product_in_buyer_cart_is_unavailable_and_does_not_crash(): void
    {
        $product = $this->product($this->shop());
        $product->update(['is_active' => false]);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $cart = \App\Models\Cart::create(['user_id' => $buyer->id]);
        $cart->items()->create(['product_variant_id' => $product->variants->first()->id, 'cart_key' => 'deleted-item', 'quantity' => 1, 'selected' => true]);
        $product->delete();
        $this->actingAs($buyer);
        $items = app(\App\Services\BuyerCart::class)->all();
        $this->assertTrue($items['deleted-item']['unavailable']);
        $this->assertSame('Deleted product', $items['deleted-item']['name']);
    }

    public function test_seller_sidebar_includes_archive_and_archive_portal_manages_archived_products(): void
    {
        $shop = $this->shop();
        $product = $this->product($shop);
        $product->variants()->update(['stock' => 50]);

        $response = $this->actingAs($shop->user)->get(route('seller.dashboard'));
        $response->assertOk()
            ->assertSee(route('seller.archive'), false)
            ->assertSee('<span>Archive</span>', false);

        $archiveResponse = $this->actingAs($shop->user)->get(route('seller.archive'));
        $archiveResponse->assertOk()
            ->assertSee('No archived products')
            ->assertDontSee($product->name);

        $this->actingAs($shop->user)->post(route('seller.products.archive', $product))->assertSessionHasNoErrors();
        $this->assertFalse($product->fresh()->is_active);

        // Verify product disappeared from main products catalog
        $this->actingAs($shop->user)->get(route('seller.products'))
            ->assertOk()
            ->assertDontSee($product->name);

        $archiveWithProduct = $this->actingAs($shop->user)->get(route('seller.archive'));
        $archiveWithProduct->assertOk()
            ->assertSee($product->name)
            ->assertSee('Restore product');

        $this->actingAs($shop->user)->post(route('seller.products.archive', $product))->assertSessionHasNoErrors();
        $this->assertTrue($product->fresh()->is_active);

        // Verify product reappeared in main products catalog after restore
        $this->actingAs($shop->user)->get(route('seller.products'))
            ->assertOk()
            ->assertSee($product->name);

        $this->actingAs($shop->user)->get(route('seller.archive'))
            ->assertOk()
            ->assertSee('No archived products');
    }

    public function test_seller_can_save_multiple_sizes_per_colorway_on_edit_product(): void
    {
        $shop = $this->shop();
        $this->actingAs($shop->user);

        $product = $shop->products()->create([
            'name' => 'Nike Tatum 5 PF',
            'category_id' => $this->product($shop)->category_id,
            'slug' => 'nike-tatum-5-pf-test',
            'is_active' => true,
        ]);

        $initialVariant = $product->variants()->create([
            'sku' => 'TATM-001',
            'name' => 'Black/Clementine/Amarillo/Infrared 23',
            'price_minor' => 799500,
            'stock' => 126,
            'is_active' => true,
        ]);

        // Visit edit page
        $this->get(route('seller.products.edit', $product->id))
            ->assertOk()
            ->assertSee('Black/Clementine/Amarillo/Infrared 23')
            ->assertSee('Quick sizes:');

        // Submit color group with multiple sizes
        $payload = [
            'color_groups' => [
                [
                    'color' => 'Black/Clementine/Amarillo/Infrared 23',
                    'price' => '7995.00',
                    'sizes_text' => 'US 7, US 7.5, US 8, US 8.5, US 9',
                    'sizes' => [
                        ['size' => 'US 7', 'stock' => 10, 'id' => $initialVariant->id],
                        ['size' => 'US 7.5', 'stock' => 12, 'id' => null],
                        ['size' => 'US 8', 'stock' => 15, 'id' => null],
                        ['size' => 'US 8.5', 'stock' => 20, 'id' => null],
                        ['size' => 'US 9', 'stock' => 25, 'id' => null],
                    ],
                ],
            ],
        ];

        $response = $this->post(route('seller.products.options', $product->id), $payload);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('seller.products.edit', $product->id));

        $freshVariants = $product->fresh()->variants()->where('is_active', true)->get();
        $this->assertCount(5, $freshVariants);

        $v7 = $freshVariants->firstWhere('name', 'Black/Clementine/Amarillo/Infrared 23 / US 7');
        $this->assertNotNull($v7);
        $this->assertSame($initialVariant->id, $v7->id); // Reused initial variant ID
        $this->assertSame(10, $v7->stock);
        $this->assertSame(799500, $v7->price_minor);
        $this->assertSame('US 7', $v7->options['size'] ?? null);

        $v9 = $freshVariants->firstWhere('name', 'Black/Clementine/Amarillo/Infrared 23 / US 9');
        $this->assertNotNull($v9);
        $this->assertSame(25, $v9->stock);
        $this->assertSame(799500, $v9->price_minor);
        $this->assertSame('US 9', $v9->options['size'] ?? null);
    }

    public function test_seller_can_manage_more_than_five_photos_on_edit_product(): void
    {
        Storage::fake('public');
        $shop = $this->shop();
        $product = $this->product($shop);

        $this->actingAs($shop->user)
            ->get(route('seller.products.edit', $product->id))
            ->assertOk()
            ->assertSee('data-slot-card="0"', false)
            ->assertDontSee('Product Photos (Up to 5)', false);

        // Upload twelve photos, including slots beyond the former limit.
        $photos = [];
        for ($i = 0; $i < 12; $i++) {
            $photos[$i] = UploadedFile::fake()->createWithContent("slot_{$i}.png", base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a1S8AAAAASUVORK5CYII='));
        }

        $response = $this->actingAs($shop->user)->post(route('seller.products.details', $product->id), [
            'name' => $product->name,
            'category_id' => $product->category_id,
            'price' => '1999.00',
            'sku' => 'PHOTO-5-TEST',
            'low_stock_threshold' => 5,
            'is_active' => 1,
            'photos' => $photos,
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('seller.products.edit', $product->id));

        $freshImages = $product->fresh()->images()->orderBy('position')->get();
        $this->assertCount(12, $freshImages);
        $this->get(route('seller.products.edit', $product->id))->assertOk()->assertSee('data-slot-card="11"', false);
        $this->assertSame(0, $freshImages->first()->position);
        $this->assertSame($freshImages->first()->id, $product->fresh()->coverImage->id);

        foreach ($freshImages as $img) {
            Storage::disk('public')->assertExists($img->path);
        }

        // Test removing an image and updating another
        $toRemove = $freshImages->last();
        $replacementPhoto = UploadedFile::fake()->createWithContent('new_cover.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a1S8AAAAASUVORK5CYII='));

        $this->actingAs($shop->user)->post(route('seller.products.details', $product->id), [
            'name' => $product->name,
            'category_id' => $product->category_id,
            'price' => '1999.00',
            'sku' => 'PHOTO-5-TEST',
            'low_stock_threshold' => 5,
            'is_active' => 1,
            'photos' => [0 => $replacementPhoto],
            'remove_image_ids' => [$toRemove->id],
        ])->assertSessionHasNoErrors();

        $afterImages = $product->fresh()->images()->orderBy('position')->get();
        $this->assertCount(11, $afterImages);
        $this->assertFalse($afterImages->contains('id', $toRemove->id));
        Storage::disk('public')->assertMissing($toRemove->path);
    }

    public function test_seller_can_manage_kids_shoe_sizes_on_edit_product(): void
    {
        $shop = $this->shop();
        $kidsCategory = \App\Models\Category::firstOrCreate(['slug' => 'kids'], ['name' => 'Kids']);
        $product = $this->product($shop);
        $product->update(['category_id' => $kidsCategory->id, 'gender' => 'kids']);

        $response = $this->actingAs($shop->user)
            ->get(route('seller.products.edit', $product->id))
            ->assertOk()
            ->assertSee('🧒 Kids US')
            ->assertSee('US 10.5C')
            ->assertSee('US 1Y');

        // Save Kids shoe sizes
        $payload = [
            'color_groups' => [
                [
                    'color' => 'University Blue',
                    'price' => '4595.00',
                    'sizes_text' => 'US 11C, US 12C, US 13C, US 1Y, US 2Y',
                    'sizes' => [
                        ['size' => 'US 11C', 'stock' => 8, 'id' => null],
                        ['size' => 'US 12C', 'stock' => 10, 'id' => null],
                        ['size' => 'US 13C', 'stock' => 12, 'id' => null],
                        ['size' => 'US 1Y', 'stock' => 15, 'id' => null],
                        ['size' => 'US 2Y', 'stock' => 20, 'id' => null],
                    ],
                ],
            ],
        ];

        $saveResponse = $this->actingAs($shop->user)->post(route('seller.products.options', $product->id), $payload);
        $saveResponse->assertSessionHasNoErrors();
        $saveResponse->assertRedirect(route('seller.products.edit', $product->id));

        $freshVariants = $product->fresh()->variants()->where('is_active', true)->get();
        $this->assertCount(5, $freshVariants);
        $this->assertTrue($freshVariants->contains('name', 'University Blue / US 11C'));
        $this->assertTrue($freshVariants->contains('name', 'University Blue / US 1Y'));
    }
}



