<?php

namespace Tests\Feature;

use App\Models\{Category, LogisticsProvider, Product, ProductVariant, Seller, User, Voucher};
use App\Services\{BuyerCart, OrderPlacement, VoucherService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopVoucherTest extends TestCase
{
    use RefreshDatabase;

    private function shop(string $name): Seller
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        return Seller::create(['user_id' => $user->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'status' => 'approved']);
    }

    private function variant(Seller $shop, int $priceMinor): ProductVariant
    {
        $category = Category::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'is_active' => true]);
        $product = Product::create(['seller_id' => $shop->id, 'category_id' => $category->id, 'name' => $shop->name.' item', 'slug' => 'item-'.$shop->id.'-'.$priceMinor, 'is_active' => true]);

        return $product->variants()->create(['sku' => 'SKU-'.$shop->id.'-'.$priceMinor, 'name' => 'Standard', 'price_minor' => $priceMinor, 'stock' => 50, 'is_active' => true]);
    }

    private function line(ProductVariant $variant, int $qty = 1): array
    {
        return ['id' => 'variant_'.$variant->id, 'variant_id' => $variant->id, 'name' => $variant->product->name, 'image' => '/x.jpg', 'variation' => 'Standard',
            'price' => $variant->price_minor / 100, 'quantity' => $qty, 'selected' => true];
    }

    private function shopVoucher(Seller $shop, array $attributes = []): Voucher
    {
        return $shop->vouchers()->create($attributes + [
            'name' => 'Shop ₱50', 'discount_type' => 'fixed', 'amount_minor' => 5000, 'min_spend_minor' => 50000,
            'audience' => 'public', 'funded_by' => 'seller', 'total_limit' => 100, 'per_user_limit' => 1,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addDays(10),
        ]);
    }

    private function form(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Payday Sale', 'discount_type' => 'fixed', 'amount' => 50, 'min_spend' => 500, 'audience' => 'public',
            'total_limit' => 100, 'per_user_limit' => 1, 'starts_at' => now('Asia/Manila')->format('Y-m-d'), 'ends_at' => now('Asia/Manila')->addDays(30)->format('Y-m-d'),
        ];
    }

    public function test_seller_creates_a_shop_voucher_with_guardrails(): void
    {
        $shop = $this->shop('Nike Store');
        $this->actingAs($shop->user);
        $this->get(route('seller.vouchers'))->assertOk()->assertSee('No vouchers yet')->assertSee('Start in one click');
        $this->get(route('seller.vouchers.create', ['template' => 'percent']))->assertOk()->assertSee('Payday Deal');

        $this->post(route('seller.vouchers.store'), $this->form(['amount' => 300, 'min_spend' => 500]))->assertSessionHasErrors('min_spend');
        $this->post(route('seller.vouchers.store'), $this->form(['discount_type' => 'percent', 'percent' => 60]))->assertSessionHasErrors('percent');
        $this->post(route('seller.vouchers.store'), $this->form(['ends_at' => now('Asia/Manila')->addDays(120)->format('Y-m-d')]))->assertSessionHasErrors('ends_at');
        $this->post(route('seller.vouchers.store'), $this->form(['audience' => 'code', 'code' => 'bad code']))->assertSessionHasErrors('code');
        $this->assertSame(0, Voucher::count() - 1, 'Only the seeded platform voucher exists.');

        $this->post(route('seller.vouchers.store'), $this->form())->assertRedirect(route('seller.vouchers'))->assertSessionHas('success');
        $voucher = $shop->vouchers()->firstOrFail();
        $this->assertSame(['fixed', 5000, 50000, 'seller', 'public'], [$voucher->discount_type, $voucher->amount_minor, $voucher->min_spend_minor, $voucher->funded_by, $voucher->audience]);
        $this->get(route('seller.vouchers'))->assertOk()->assertSee('Payday Sale')->assertSee('₱50 OFF')->assertSee('Ongoing');

        // Another store can't touch it, and it never shows up as a platform voucher.
        $this->actingAs($this->shop('Other Store')->user)->get(route('seller.vouchers.edit', $voucher))->assertNotFound();
        $this->assertTrue(app(VoucherService::class)->visibleFor(User::factory()->create())->doesntContain('id', $voucher->id));
    }

    public function test_shop_voucher_only_counts_its_own_store_and_is_paid_by_that_store(): void
    {
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
        $nike = $this->shop('Nike Store');
        $adidas = $this->shop('Adidas Store');
        $nikeItem = $this->variant($nike, 30000);   // ₱300
        $adidasItem = $this->variant($adidas, 40000); // ₱400
        $voucher = $this->shopVoucher($nike);        // ₱50 off, min. ₱500 of Nike items
        $buyer = User::factory()->create();

        // ₱700 cart but only ₱300 from Nike: the Nike voucher doesn't apply yet.
        $cart = ['n' => $this->line($nikeItem), 'a' => $this->line($adidasItem)];
        $this->mock(BuyerCart::class, fn ($m) => $m->shouldReceive('all')->andReturn($cart)->shouldReceive('remove'));
        $this->actingAs($buyer)->get(route('checkout'))->assertOk()
            ->assertSee('Shop voucher')->assertSee('Nike Store')->assertSee('Add ₱200.00 more to use')
            ->assertViewHas('shopVoucherDiscount', 0);

        // Two Nike items (₱600): the voucher is picked automatically for Nike only.
        $cart['n'] = $this->line($nikeItem, 2);
        $this->mock(BuyerCart::class, fn ($m) => $m->shouldReceive('all')->andReturn($cart)->shouldReceive('remove'));
        $this->get(route('checkout'))->assertOk()
            ->assertViewHas('shopVoucherDiscount', 50)
            ->assertViewHas('total', 600 + 400 + 60 + 60 - 50); // one ₱60 parcel per store

        $this->post(route('checkout.process'), [
            'payment_method' => 'COD', 'delivery_address' => 'Test address', 'selected_items' => ['n', 'a'],
            'shop_voucher' => [$nike->id => $voucher->id],
        ])->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']));

        $order = $buyer->orders()->firstOrFail();
        $nikeOrder = $order->sellerOrders()->where('seller_id', $nike->id)->firstOrFail();
        $adidasOrder = $order->sellerOrders()->where('seller_id', $adidas->id)->firstOrFail();
        $this->assertSame(107000, $order->total_minor);
        $this->assertSame(5000, $nikeOrder->seller_voucher_discount_minor);
        $this->assertSame(0, $adidasOrder->seller_voucher_discount_minor);
        $this->assertSame(6000, $nikeOrder->commission_minor, 'Commission stays on the full ₱600.');
        $this->assertSame(60000 - 6000 - 5000, $nikeOrder->sellerNetMinor(), 'The shop voucher comes out of the seller earnings.');
        $this->assertSame($order->total_minor, $nikeOrder->buyerDueMinor() + $adidasOrder->buyerDueMinor());
        $this->assertSame(1, $voucher->fresh()->used_count);

        // Used vouchers are locked: the deal can't change and it can't be deleted.
        $this->actingAs($nike->user)->delete(route('seller.vouchers.destroy', $voucher))->assertSessionHasErrors('voucher');
        $this->post(route('seller.vouchers.update', $voucher), ['name' => 'Renamed', 'total_limit' => 200, 'ends_at' => now('Asia/Manila')->addDays(5)->format('Y-m-d'), 'amount' => 1])
            ->assertRedirect(route('seller.vouchers'));
        $this->assertSame(['Renamed', 200, 5000], [$voucher->fresh()->name, $voucher->fresh()->total_limit, $voucher->fresh()->amount_minor]);
    }

    public function test_buyers_see_public_shop_vouchers_and_admin_can_only_pause_them(): void
    {
        $shop = $this->shop('Nike Store');
        $item = $this->variant($shop, 60000);
        $this->shopVoucher($shop, ['name' => 'Public deal']);
        $secret = $this->shopVoucher($shop, ['name' => 'Secret', 'audience' => 'code', 'code' => 'NIKE100']);

        // Store and product pages show public vouchers only; private codes stay hidden.
        $this->get(route('store.show', $shop->slug))->assertOk()->assertSee('Shop vouchers')->assertSee('₱50 OFF')->assertDontSee('NIKE100');
        $this->get(route('products.show', $item->product_id))->assertOk()->assertSee('Shop vouchers')->assertSee('Min. spend ₱500');

        // A claimed code voucher shows in the buyer's My vouchers tab with its store.
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        app(VoucherService::class)->claimCode($buyer, 'NIKE100');
        $this->actingAs($buyer)->get(route('buyer.dashboard', ['tab' => 'vouchers']))->assertOk()->assertSee('Secret')->assertSee('Shop at Nike Store');

        // Admin sees it under Seller vouchers, can pause it (seller notified), but can't edit or delete it.
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->get(route('admin.vouchers', ['tab' => 'shops']))->assertOk()->assertSee('Nike Store')->assertSee('Secret');
        $this->post(route('admin.vouchers.toggle', $secret))->assertRedirect();
        $this->assertFalse($secret->fresh()->is_active);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $shop->user_id, 'title' => 'Voucher paused by cartzy']);
        $this->delete(route('admin.vouchers.destroy', $secret))->assertForbidden();
        $this->get(route('admin.vouchers'))->assertOk()
            ->assertViewHas('vouchers', fn ($platform) => $platform->getCollection()->every(fn ($v) => $v->seller_id === null))
            ->assertViewHas('shopVouchers', fn ($shops) => $shops->total() === 2);
    }

    public function test_paused_and_code_vouchers_and_cancellation_refund(): void
    {
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
        $shop = $this->shop('Nike Store');
        $item = $this->variant($shop, 60000);
        $paused = $this->shopVoucher($shop, ['name' => 'Paused', 'amount_minor' => 20000, 'is_active' => false]);
        $code = $this->shopVoucher($shop, ['name' => 'Secret', 'audience' => 'code', 'code' => 'NIKE100', 'amount_minor' => 10000]);
        $buyer = User::factory()->create();
        $service = app(VoucherService::class);

        $this->assertTrue($service->shopVouchersFor($buyer, [$shop->id])->isEmpty(), 'Paused vouchers and unclaimed codes are hidden.');
        $service->claimCode($buyer, 'nike100');
        $this->assertSame([$code->id], $service->shopVouchersFor($buyer, [$shop->id])->pluck('id')->all());

        $order = app(OrderPlacement::class)->place($buyer, [$this->line($item)], 'COD', 'Test address');
        $sellerOrder = $order->sellerOrders()->firstOrFail();
        $this->assertSame(10000, $sellerOrder->seller_voucher_discount_minor, 'Best shop voucher is applied automatically.');
        $this->assertSame(1, $code->fresh()->used_count);
        $this->assertSame(0, $paused->fresh()->used_count);

        $sellerOrder->update(['status' => 'cancelled']);
        $service->reverseIfCancelled($order);
        $this->assertSame(0, $code->fresh()->used_count, 'Cancelling the store order gives the shop voucher back.');
        $this->assertDatabaseHas('voucher_redemptions', ['voucher_id' => $code->id, 'status' => 'reversed']);
    }
}
