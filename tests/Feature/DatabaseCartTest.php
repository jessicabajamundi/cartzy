<?php

namespace Tests\Feature;

use App\Models\{Cart, CartItem, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseCartTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_and_selection_survive_new_session_and_are_private_to_buyer(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $key = '5_'.md5('Standard');
        $this->actingAs($buyer)->postJson(route('cart.add'), [
            'id' => 5, 'name' => 'Mug', 'price' => 280, 'quantity' => 2, 'variation' => 'Standard',
        ])->assertOk()->assertJsonPath('cartCount', 2);
        $this->assertDatabaseHas('cart_items', ['cart_key' => $key, 'quantity' => 2, 'selected' => true]);
        $this->postJson(route('cart.selection'), ['selected_items' => []])->assertOk();
        $this->flushSession();
        $this->actingAs($buyer)->get(route('cart.index'))->assertOk()
            ->assertViewHas('cart', fn ($cart) => $cart[$key]['quantity'] === 2 && $cart[$key]['selected'] === false);
        $this->getJson(route('buyer.navigation-counts'))->assertJsonPath('counts.cart', 2);

        $this->flushSession();
        $other = User::factory()->create(['role' => 'buyer']);
        $this->actingAs($other)->get(route('cart.index'))->assertViewHas('cart', []);
        $this->postJson(route('cart.remove'), ['cart_key' => $key])->assertOk();
        $this->assertDatabaseHas('cart_items', ['cart_key' => $key, 'quantity' => 2]);

        $this->flushSession();
        $this->actingAs($buyer)->postJson(route('cart.update'), ['cart_key' => $key, 'quantity' => 4])->assertOk();
        $this->assertDatabaseHas('cart_items', ['cart_key' => $key, 'quantity' => 4]);
        $this->postJson(route('cart.clear'))->assertOk();
        $this->flushSession();
        $this->get(route('cart.index'))->assertViewHas('cart', []);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_existing_session_cart_is_imported_once_without_duplicate_quantity(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $legacy = ['legacy_key' => ['id' => 5, 'name' => 'Mug', 'price' => 280, 'quantity' => 2, 'variation' => 'Standard', 'image' => '/mug.jpg']];
        $this->actingAs($buyer)->withSession(['cart' => $legacy])->get(route('cart.index'))->assertOk();
        $this->get(route('cart.index'))->assertOk();
        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['cart_key' => 'legacy_key', 'quantity' => 2]);
        $this->assertEquals($buyer->id, Cart::firstOrFail()->user_id);
        $this->assertEquals('Mug', CartItem::firstOrFail()->snapshot['name']);
    }
}
