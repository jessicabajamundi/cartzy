<?php

namespace Tests\Feature;

use App\Models\{Order, User};
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartSelectionTest extends TestCase
{
    use RefreshDatabase;

    private function cart(): array
    {
        return [
            'watch_black' => ['cart_key' => 'watch_black', 'selected' => true, 'id' => 1, 'name' => 'Watch', 'variation' => 'Black', 'price' => 600, 'quantity' => 2, 'image' => '/watch.jpg'],
            'watch_white' => ['cart_key' => 'watch_white', 'selected' => true, 'id' => 1, 'name' => 'Watch', 'variation' => 'White', 'price' => 400, 'quantity' => 1, 'image' => '/watch.jpg'],
        ];
    }

    public function test_checkout_displays_only_selected_variant_and_recalculates_totals(): void
    {
        $this->actingAs(User::factory()->create(['id_status' => 'verified']))
            ->withSession(['cart' => $this->cart()])
            ->get(route('checkout', ['selection' => 1, 'selected_items' => ['watch_white']]))
            ->assertOk()
            ->assertViewHas('checkoutItems', [$this->cart()['watch_white']])
            ->assertViewHas('subtotal', 400)
            ->assertViewHas('discount', 0)
            ->assertViewHas('total', 460);
    }

    public function test_placing_selected_items_preserves_unselected_cart_items(): void
    {
        $buyer = User::factory()->create(['id_status' => 'verified']);
        $order = new Order;
        $order->reference = 'TEST-SELECTION';
        $this->mock(OrderPlacement::class)->shouldReceive('place')->once()
            ->withArgs(fn ($user, $items, $payment, $address) => $user->id === $buyer->id
                && $items === [$this->cart()['watch_black']] && $payment === 'COD' && $address === 'Test address')
            ->andReturn($order);

        $this->actingAs($buyer)->withSession(['cart' => $this->cart()])
            ->post(route('checkout.process'), [
                'selected_items' => ['watch_black'], 'payment_method' => 'COD', 'delivery_address' => 'Test address',
            ])
            ->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']))
            ->assertSessionHas('cart', ['watch_white' => $this->cart()['watch_white']]);
        $this->assertDatabaseMissing('cart_items', ['cart_key' => 'watch_black']);
        $this->assertDatabaseHas('cart_items', ['cart_key' => 'watch_white', 'quantity' => 1]);
    }

    public function test_missing_or_stale_selection_cannot_place_an_order(): void
    {
        $this->actingAs(User::factory()->create(['id_status' => 'verified']));
        $this->mock(OrderPlacement::class)->shouldNotReceive('place');
        $data = ['payment_method' => 'COD', 'delivery_address' => 'Test address'];
        $this->withSession(['cart' => $this->cart()])->post(route('checkout.process'), $data)
            ->assertSessionHasErrors('selected_items');
        $this->post(route('checkout.process'), $data + ['selected_items' => ['missing']])
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('cart', $this->cart());
    }

    public function test_cart_renders_selection_controls(): void
    {
        $this->actingAs(User::factory()->create())->withSession(['cart' => $this->cart()])
            ->get(route('cart.index'))->assertOk()
            ->assertSee('id="cart-select-all"', false)
            ->assertSee('name="selected_items[]"', false)
            ->assertSee('js/cart-selection.js');
    }
}
