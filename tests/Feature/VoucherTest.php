<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, Seller, User, Voucher, VoucherRedemption};
use App\Services\{BuyerCart, OrderPlacement};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoucherTest extends TestCase
{
    use RefreshDatabase;

    private function marketplace(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        Seller::create(['user_id' => $sellerUser->id, 'name' => 'Test Shop', 'slug' => 'test-shop', 'status' => 'approved']);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
    }

    private function cart(int $price = 500, int $qty = 1): array
    {
        return ['mug' => ['id' => 1, 'name' => 'Coffee mug', 'image' => '/mug.jpg', 'variation' => 'White', 'price' => $price, 'quantity' => $qty, 'selected' => true]];
    }

    private function voucher(array $attributes = []): Voucher
    {
        return Voucher::create($attributes + [
            'name' => '₱100 off', 'discount_type' => 'fixed', 'amount_minor' => 10000,
            'min_spend_minor' => 0, 'audience' => 'public', 'per_user_limit' => 1,
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    public function test_admin_creates_voucher_and_gives_it_to_buyers_by_email(): void
    {
        $buyer = User::factory()->create(['email' => 'juan@example.com']);
        $other = User::factory()->create();

        $list = $this->actingAs($this->admin())->get(route('admin.vouchers'))->assertOk()->assertSee('+ Create voucher');
        $this->assertMatchesRegularExpression('/data-voucher-panel="create"\s+hidden/', $list->getContent());
        $create = $this->get(route('admin.vouchers', ['tab' => 'create']))->assertOk();
        $this->assertDoesNotMatchRegularExpression('/data-voucher-panel="create"\s+hidden/', $create->getContent());
        $this->post(route('admin.vouchers.store'), [
            'name' => 'VIP ₱150', 'discount_type' => 'fixed', 'amount' => '150', 'min_spend' => '300',
            'audience' => 'assigned', 'per_user_limit' => 1, 'recipients' => 'emails', 'emails' => "JUAN@example.com\nnobody@example.com",
        ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('success', fn ($m) => str_contains($m, 'Given to 1') && str_contains($m, 'nobody@example.com'));

        $voucher = Voucher::where('name', 'VIP ₱150')->firstOrFail();
        $this->assertSame(15000, $voucher->amount_minor);
        $this->assertDatabaseHas('voucher_user', ['voucher_id' => $voucher->id, 'user_id' => $buyer->id, 'source' => 'assigned']);
        $this->assertDatabaseMissing('voucher_user', ['voucher_id' => $voucher->id, 'user_id' => $other->id]);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $buyer->id, 'type' => 'voucher']);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'Voucher created']);

        $this->get(route('admin.vouchers.show', $voucher))->assertOk()->assertSee('juan@example.com');
        $this->post(route('admin.vouchers.toggle', $voucher))->assertRedirect();
        $this->assertFalse($voucher->fresh()->is_active);
    }

    public function test_admin_form_validates_code_and_amounts(): void
    {
        $this->actingAs($this->admin())->post(route('admin.vouchers.store'), [
            'name' => 'Code', 'discount_type' => 'percent', 'audience' => 'code', 'code' => 'bad code!', 'per_user_limit' => 1,
        ])->assertSessionHasErrors(['percent', 'code']);
        $this->assertDatabaseMissing('vouchers', ['name' => 'Code']);
    }

    public function test_checkout_preselects_best_voucher_and_order_records_it(): void
    {
        $this->marketplace();
        $buyer = User::factory()->create();
        $this->voucher(['name' => 'Small', 'amount_minor' => 2000]);
        $best = $this->voucher(['name' => 'Big', 'amount_minor' => 10000]);
        $this->voucher(['name' => 'Locked', 'amount_minor' => 30000, 'min_spend_minor' => 500000]);
        $this->mock(BuyerCart::class, fn ($m) => $m->shouldReceive('all')->andReturn($this->cart())->shouldReceive('remove'));

        $this->actingAs($buyer)->get(route('checkout'))->assertOk()
            ->assertViewHas('selectedItemVoucher', fn ($o) => $o['voucher']->is($best))
            ->assertViewHas('voucherDiscount', 100)
            ->assertViewHas('total', 460)
            ->assertSee('Add ₱4,500.00 more to use');

        $this->post(route('checkout.process'), [
            'payment_method' => 'COD', 'delivery_address' => 'Test address', 'selected_items' => ['mug'], 'voucher_id' => $best->id,
        ])->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']));

        $so = $buyer->orders()->firstOrFail()->sellerOrders()->firstOrFail();
        $this->assertSame(46000, $so->order->total_minor);
        $this->assertSame(10000, $so->voucher_discount_minor);
        $this->assertSame(5000, $so->commission_minor, 'Commission stays on the full merchandise amount.');
        $this->assertSame(46000, $so->shipment->cod_amount_minor);
        $this->assertSame(1, $best->fresh()->used_count);
        $this->assertDatabaseHas('voucher_redemptions', ['voucher_id' => $best->id, 'user_id' => $buyer->id, 'discount_minor' => 10000, 'status' => 'applied']);

        // Single use per buyer: the next checkout falls back to the next best voucher.
        $this->get(route('checkout'))->assertViewHas('selectedItemVoucher', fn ($o) => $o['voucher']->name === 'Small')->assertSee('Already used');
    }

    public function test_free_shipping_voucher_keeps_courier_fee_and_percent_cap_applies(): void
    {
        $this->marketplace();
        $buyer = User::factory()->create();
        Voucher::where('name', 'Shipping Saver')->delete();
        $this->voucher(['name' => 'Free ship', 'discount_type' => 'free_shipping', 'amount_minor' => null]);
        $this->voucher(['name' => '10% off', 'discount_type' => 'percent', 'amount_minor' => null, 'percent' => 10, 'max_discount_minor' => 5000]);

        $order = app(OrderPlacement::class)->place($buyer, array_values($this->cart(1000)), 'COD', 'Test address');
        $so = $order->sellerOrders()->firstOrFail();

        $this->assertSame(100000 - 5000, $order->total_minor);
        $this->assertSame(0, $so->shipping_fee_minor);
        $this->assertSame(6000, $so->shipping_discount_minor);
        $this->assertSame(6000, $so->shipment->fee_minor, 'The courier is still paid the full shipping fee.');
        $this->assertSame(5000, $so->voucher_discount_minor);
    }

    public function test_buyer_can_skip_vouchers_and_cannot_use_a_voucher_they_do_not_have(): void
    {
        $this->marketplace();
        $buyer = User::factory()->create();
        $public = $this->voucher();
        $private = $this->voucher(['name' => 'Private', 'audience' => 'assigned']);
        $this->mock(BuyerCart::class, fn ($m) => $m->shouldReceive('all')->andReturn($this->cart())->shouldReceive('remove'));

        $this->actingAs($buyer)->get(route('checkout', ['voucher' => 'none']))->assertViewHas('selectedItemVoucher', null)->assertDontSee('Private');

        $this->post(route('checkout.process'), [
            'payment_method' => 'COD', 'delivery_address' => 'Test address', 'selected_items' => ['mug'], 'voucher_id' => $private->id,
        ])->assertRedirect(route('cart.index'))->assertSessionHas('info', fn ($m) => str_contains($m, 'can no longer be used'));
        $this->assertSame(0, $buyer->orders()->count());

        $this->post(route('checkout.process'), [
            'payment_method' => 'COD', 'delivery_address' => 'Test address', 'selected_items' => ['mug'], 'voucher_id' => '',
        ])->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']));
        $this->assertSame(56000, $buyer->orders()->firstOrFail()->total_minor);
        $this->assertSame(0, $public->fresh()->used_count);
    }

    public function test_code_voucher_is_added_from_checkout_and_dashboard(): void
    {
        $buyer = User::factory()->create();
        $voucher = $this->voucher(['name' => 'Pasko', 'audience' => 'code', 'code' => 'PASKO100']);
        $this->mock(BuyerCart::class, fn ($m) => $m->shouldReceive('all')->andReturn($this->cart()));

        $this->actingAs($buyer)->post(route('checkout.voucher'), ['voucher_code' => 'nope', 'selected_items' => ['mug']])->assertSessionHasErrors('voucher_code');
        $this->post(route('checkout.voucher'), ['voucher_code' => ' pasko100 ', 'selected_items' => ['mug']])
            ->assertRedirect(route('checkout', ['selection' => 1, 'selected_items' => ['mug'], 'voucher' => $voucher->id]));
        $this->assertDatabaseHas('voucher_user', ['voucher_id' => $voucher->id, 'user_id' => $buyer->id, 'source' => 'code']);
        $this->get(route('checkout', ['selection' => 1, 'selected_items' => ['mug'], 'voucher' => $voucher->id]))
            ->assertViewHas('selectedItemVoucher', fn ($o) => $o['voucher']->is($voucher));

        $other = User::factory()->create();
        $this->actingAs($other)->post(route('vouchers.claim'), ['voucher_code' => 'PASKO100'])->assertRedirect(route('buyer.dashboard', ['tab' => 'vouchers']));
        $this->get(route('buyer.dashboard', ['tab' => 'vouchers']))->assertOk()->assertSee('My Vouchers')->assertSee('Pasko')->assertSee('Ready to use on your cart');
    }

    public function test_total_limit_and_new_buyer_audience(): void
    {
        $this->marketplace();
        $buyer = User::factory()->create();
        $welcome = $this->voucher(['name' => 'Welcome', 'audience' => 'new_buyers', 'amount_minor' => 5000]);
        $limited = $this->voucher(['name' => 'Limited', 'amount_minor' => 1000, 'total_limit' => 1, 'used_count' => 1]);

        $order = app(OrderPlacement::class)->place($buyer, array_values($this->cart()), 'COD', 'Test address');
        $this->assertDatabaseHas('voucher_redemptions', ['order_id' => $order->id, 'voucher_id' => $welcome->id]);
        $this->assertDatabaseMissing('voucher_redemptions', ['voucher_id' => $limited->id]);

        $second = app(OrderPlacement::class)->place($buyer, array_values($this->cart()), 'COD', 'Test address');
        $this->assertDatabaseMissing('voucher_redemptions', ['order_id' => $second->id]);
    }

    public function test_cancelled_order_returns_the_voucher(): void
    {
        $this->marketplace();
        $buyer = User::factory()->create();
        $voucher = $this->voucher();
        $so = app(OrderPlacement::class)->place($buyer, array_values($this->cart()), 'COD', 'Test address')->sellerOrders()->firstOrFail();

        $this->actingAs($buyer)->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind'])->assertSessionHasNoErrors();
        $this->flushSession();
        $this->actingAs($so->seller->user)->post(route('seller.orders.cancellation', $so->id), ['decision' => 'approved'])->assertSessionHasNoErrors();

        $this->assertSame(0, $voucher->fresh()->used_count);
        $this->assertSame('reversed', VoucherRedemption::where('voucher_id', $voucher->id)->value('status'));
    }
}
