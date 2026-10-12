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

    public function test_seller_pages_render_real_empty_states_without_a_store_profile(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'seller', 'status' => 'active']));
        foreach (['dashboard', 'orders', 'products', 'inventory', 'courier', 'deliveries', 'feedback', 'reports', 'chat', 'account'] as $page) {
            $this->get('/seller/'.$page)->assertOk()->assertDontSee('TechZone')->assertDontSee('Juan Dela Cruz')->assertDontSee('98%')->assertDontSee('38,450');
        }
        $this->assertDatabaseCount('sellers', 0);
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
                ->assertSee('Stock alert:')->assertSee($product->name)
                ->assertSee($stock > 0 ? $stock.' left' : 'Out of stock')
                ->assertDontSee($otherProduct->name);
        }
        $this->get('/seller/account')->assertOk()->assertSee('Stock alert:');
        $variant->update(['stock' => 11]);
        $this->get('/seller/dashboard')->assertOk()->assertDontSee('Stock alert:');
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
        $this->get('/seller/products?create=1')->assertOk()->assertSee('<dialog id="add-product"', false)->assertSee('data-auto-open="true"', false);
        $this->get('/seller/dashboard?period=invalid')->assertSessionHasErrors('period');
        $this->travelBack();
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
        $this->get('/seller/products?search=does-not-exist')->assertOk()->assertSee('No products to show')
            ->assertSee('data-auto-open="true"', false)->assertSee('value="Invalid"', false);
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
            ->assertSee('product-shades-gallery');
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
}
