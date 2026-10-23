<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BuyerCart;
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerShoppingAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_cannot_access_cart_or_checkout_even_with_a_legacy_cart(): void
    {
        $seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $this->actingAs($seller)->withSession(['cart' => ['old' => ['quantity' => 1, 'price' => 100]]]);
        foreach (['/cart', '/checkout'] as $url) {
            $this->get($url)->assertForbidden()->assertSee('Use a buyer account to shop');
        }
        foreach (['/cart/add', '/cart/update', '/cart/remove', '/cart/clear', '/cart/selection', '/checkout'] as $url) {
            $this->postJson($url, [])->assertForbidden()->assertJsonPath('requires_buyer', true);
        }
        $this->assertSame([], app(BuyerCart::class)->all());
        $this->assertDatabaseCount('carts', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertAuthenticatedAs($seller);
    }

    public function test_order_service_rejects_sellers_before_writing_any_order(): void
    {
        $seller = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Purchases require a separate buyer account');
        app(OrderPlacement::class)->place($seller, [], 'COD', 'Test address');
    }

    public function test_buyer_and_guest_cart_access_remain_available(): void
    {
        $this->get('/cart')->assertOk();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->actingAs($buyer)->get('/cart')->assertOk();
        $this->postJson('/cart/add', [])->assertUnprocessable();
        $this->get('/checkout')->assertRedirect(route('cart.index'));
    }
}
