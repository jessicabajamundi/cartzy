<?php

namespace Tests\Feature;

use App\Models\{Address, Category, LogisticsProvider, Product, ProductVariant, Seller, User, Voucher};
use App\Services\{BuyerCart, OrderPlacement, ShippingCalculator};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Once;
use Tests\TestCase;

class LocationShippingTest extends TestCase
{
    use RefreshDatabase;

    private function address(User $user, string $province, string $city = 'Town'): Address
    {
        return Address::create(['user_id' => $user->id, 'label' => 'Home', 'recipient' => $user->name, 'phone' => '09170000000',
            'line1' => '1 Street', 'barangay' => 'Poblacion', 'postal_code' => '4000', 'city' => $city, 'province' => $province, 'is_default' => true]);
    }

    private function shop(string $name, ?string $province): Seller
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $shop = Seller::create(['user_id' => $user->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'status' => 'approved']);
        if ($province) {
            $shop->update(['pickup_address_id' => $this->address($user, $province)->id]);
        }

        return $shop;
    }

    private function variant(Seller $shop, int $priceMinor): ProductVariant
    {
        $category = Category::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'is_active' => true]);
        $product = Product::create(['seller_id' => $shop->id, 'category_id' => $category->id, 'name' => $shop->name.' item', 'slug' => 'item-'.$shop->id, 'is_active' => true]);

        return $product->variants()->create(['sku' => 'SKU-'.$shop->id, 'name' => 'Standard', 'price_minor' => $priceMinor, 'stock' => 50, 'is_active' => true]);
    }

    private function line(ProductVariant $variant): array
    {
        return ['id' => 'variant_'.$variant->id, 'variant_id' => $variant->id, 'name' => $variant->product->name, 'image' => '/x.jpg',
            'variation' => 'Standard', 'price' => $variant->price_minor / 100, 'quantity' => 1, 'selected' => true];
    }

    public function test_zones_follow_the_route_between_provinces(): void
    {
        $this->assertSame('same_area', ShippingCalculator::zoneFor('Metro Manila', 'NCR'));
        $this->assertSame('same_area', ShippingCalculator::zoneFor('Laguna', 'laguna'));
        $this->assertSame('same_island', ShippingCalculator::zoneFor('Metro Manila', 'Laguna'));
        $this->assertSame('same_island', ShippingCalculator::zoneFor('Davao del Sur', 'Agusan Del Sur'));
        $this->assertSame('near_island', ShippingCalculator::zoneFor('Metro Manila', 'Cebu'));
        $this->assertSame('near_island', ShippingCalculator::zoneFor('Cebu', 'Tawi-Tawi'));
        $this->assertSame('far_island', ShippingCalculator::zoneFor('Metro Manila', 'Davao del Sur'));
        $this->assertSame('standard', ShippingCalculator::zoneFor('Metro Manila', null));
        $this->assertSame('standard', ShippingCalculator::zoneFor('Atlantis', 'Laguna'));
        $this->assertSame([5000, 8500, 12000, 15000, 6000], array_column(array_values(ShippingCalculator::zones()), 'fee_minor'));
    }

    public function test_shared_discounts_never_exceed_what_a_store_has_left_and_always_add_up(): void
    {
        $this->assertSame([1 => 0, 2 => 3000, 3 => 3000], OrderPlacement::allocate(6000, [1 => 0, 2 => 8500, 3 => 8500]), 'A parcel already free gets none.');
        $this->assertSame([1 => 50, 2 => 50], OrderPlacement::allocate(500, [1 => 50, 2 => 50]), 'Capped at what is left to pay.');
        $shares = OrderPlacement::allocate(1001, [1 => 333, 2 => 333, 3 => 335]);
        $this->assertSame(1001, array_sum($shares));
        $this->assertTrue($shares[1] <= 333 && $shares[2] <= 333 && $shares[3] <= 335);
    }

    public function test_each_store_ships_its_own_parcel_and_free_shipping_stacks_like_shopee(): void
    {
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
        $manila = $this->shop('Manila Store', 'Metro Manila');   // → Laguna: same island ₱85
        $laguna = $this->shop('Laguna Store', 'Laguna');         // → Laguna: same province ₱50
        $manilaItem = $this->variant($manila, 60000);
        $lagunaItem = $this->variant($laguna, 50000);
        $freeShip = $manila->vouchers()->create(['name' => 'Free ship ₱300+', 'discount_type' => 'free_shipping', 'amount_minor' => null,
            'min_spend_minor' => 30000, 'audience' => 'public', 'funded_by' => 'seller', 'total_limit' => 50, 'per_user_limit' => 1,
            'starts_at' => now()->subDay(), 'ends_at' => now()->addDays(5)]);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->address($buyer, 'Laguna', 'Santa Cruz');

        $cart = ['m' => $this->line($manilaItem), 'l' => $this->line($lagunaItem)];
        $this->mock(BuyerCart::class, fn ($m) => $m->shouldReceive('all')->andReturn($cart)->shouldReceive('remove'));
        // ₱1,100 items + ₱85 + ₱50 shipping; Manila's free shipping waives ₱85, cartzy "Shipping Saver" takes ₱50 off the rest.
        $this->actingAs($buyer)->get(route('checkout'))->assertOk()
            ->assertSee('Metro Manila')->assertSee('Same island group')->assertSee('Same province')->assertSee('2 parcels')
            ->assertSee('Shop free shipping')->assertSee('FREE')
            ->assertViewHas('shippingFee', 135)
            ->assertViewHas('shopShippingDiscount', 85)
            ->assertViewHas('discount', 50)
            ->assertViewHas('total', 1100);

        $this->post(route('checkout.process'), [
            'payment_method' => 'COD', 'delivery_address' => Address::where('user_id', $buyer->id)->first()->formatted_address,
            'selected_items' => ['m', 'l'], 'shop_shipping_voucher' => [$manila->id => $freeShip->id], 'shipping_voucher_id' => Voucher::where('name', 'Shipping Saver')->value('id'),
        ])->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']));

        $order = $buyer->orders()->firstOrFail();
        $manilaOrder = $order->sellerOrders()->where('seller_id', $manila->id)->firstOrFail();
        $lagunaOrder = $order->sellerOrders()->where('seller_id', $laguna->id)->firstOrFail();
        $this->assertSame(110000, $order->total_minor);
        $this->assertSame([0, 8500, 0, 8500], [$manilaOrder->shipping_fee_minor, $manilaOrder->seller_shipping_discount_minor, $manilaOrder->shipping_discount_minor, $manilaOrder->courierFeeMinor()]);
        $this->assertSame([0, 0, 5000, 5000], [$lagunaOrder->shipping_fee_minor, $lagunaOrder->seller_shipping_discount_minor, $lagunaOrder->shipping_discount_minor, $lagunaOrder->courierFeeMinor()]);
        $this->assertSame(60000 - 6000 - 8500, $manilaOrder->sellerNetMinor(), 'The seller pays for its own free shipping.');
        $this->assertSame(50000 - 5000, $lagunaOrder->sellerNetMinor(), 'cartzy pays for its shipping voucher.');
        $this->assertSame($order->total_minor, $manilaOrder->buyerDueMinor() + $lagunaOrder->buyerDueMinor());
        $this->assertSame($manilaOrder->buyerDueMinor(), $manilaOrder->shipment->cod_amount_minor);
        $this->assertSame(1, $freeShip->fresh()->used_count);
    }

    public function test_admin_edits_rates_with_checks_and_new_quotes_use_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin)->get(route('admin.shipping'))->assertOk()->assertSee('Same island group')->assertSee('Luzon ↔ Mindanao');
        $fees = ['same_area' => 55, 'same_island' => 90, 'near_island' => 125, 'far_island' => 160, 'standard' => 70];

        $this->post(route('admin.shipping.update'), ['fees' => ['far_island' => 15] + $fees])->assertSessionHasErrors('fees.far_island');
        $this->post(route('admin.shipping.update'), ['fees' => $fees])->assertRedirect(route('admin.shipping'))->assertSessionHas('success');
        Once::flush();
        $this->assertSame(9000, ShippingCalculator::quote('Metro Manila', 'Laguna')['fee_minor']);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'Shipping rates updated']);
    }

    public function test_product_page_shows_shipping_estimate_and_store_free_shipping(): void
    {
        $shop = $this->shop('Manila Store', 'Metro Manila');
        $item = $this->variant($shop, 60000);
        $this->get(route('products.show', $item->product_id))->assertOk()->assertSee('Shipping from')->assertSee('₱50.00');

        $shop->vouchers()->create(['name' => 'Free ship', 'discount_type' => 'free_shipping', 'amount_minor' => null, 'min_spend_minor' => 50000,
            'audience' => 'public', 'funded_by' => 'seller', 'total_limit' => 10, 'per_user_limit' => 1, 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->address($buyer, 'Cebu', 'Cebu City');
        Once::flush();
        $this->actingAs($buyer)->get(route('products.show', $item->product_id))->assertOk()
            ->assertSee('Ships to')->assertSee('Cebu City, Cebu')->assertSee('₱120.00')->assertSee('Neighbouring island group')
            ->assertSee('Free shipping from this store on orders ₱500+');
        $this->assertTrue(Product::qualifiesForFreeShipping(60000, $shop->id), 'The store\'s own free shipping earns the badge.');
        $this->assertFalse(Product::qualifiesForFreeShipping(60000, null));
    }

    public function test_seller_creates_a_free_shipping_voucher(): void
    {
        $shop = $this->shop('Manila Store', 'Metro Manila');
        $this->actingAs($shop->user)->get(route('seller.vouchers.create', ['template' => 'shipping']))->assertOk()->assertSee('Free Shipping Deal')->assertSee('Max shipping off');
        $this->post(route('seller.vouchers.store'), [
            'name' => 'Free ship', 'discount_type' => 'free_shipping', 'shipping_cap' => 80, 'min_spend' => 0, 'audience' => 'public',
            'total_limit' => 20, 'per_user_limit' => 1, 'starts_at' => now('Asia/Manila')->format('Y-m-d'), 'ends_at' => now('Asia/Manila')->addDays(7)->format('Y-m-d'),
        ])->assertRedirect(route('seller.vouchers'))->assertSessionHasNoErrors();
        $voucher = $shop->vouchers()->firstOrFail();
        $this->assertSame(['free_shipping', 8000, '₱80 OFF SHIPPING'], [$voucher->discount_type, $voucher->amount_minor, $voucher->headline()]);
        $this->get(route('seller.vouchers'))->assertOk()->assertSee('₱80 OFF SHIPPING')->assertSee('SHIPPING');
    }
}
