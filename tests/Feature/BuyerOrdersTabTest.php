<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, User};
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerOrdersTabTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_and_past_orders_share_one_orders_tab(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
        $active = app(OrderPlacement::class)->place($buyer, [['id' => 5, 'name' => 'Mug', 'price' => 280, 'quantity' => 1]], 'COD', 'Test address');
        $past = app(OrderPlacement::class)->place($buyer, [['id' => 6, 'name' => 'Lamp', 'price' => 900, 'quantity' => 1]], 'COD', 'Test address');
        $past->sellerOrders()->update(['status' => 'delivered']);

        $this->actingAs($buyer)->get(route('buyer.dashboard', ['tab' => 'orders']))->assertOk()
            ->assertSee($active->reference)->assertSee($past->reference)
            ->assertSee('In progress')->assertSee('Past')
            ->assertSee('data-filter="completed"', false)->assertSee('id="orderSearch"', false)
            ->assertSee('<span class="buyer-order-payment">COD</span>', false)
            ->assertDontSee('data-panel="history"', false)
            ->assertDontSee('data-buyer-nav="history"', false);

        // Old Order History links still land on the Orders tab.
        $this->get(route('buyer.dashboard', ['tab' => 'history']))->assertOk()->assertSee($past->reference)
            ->assertSee('data-buyer-nav="orders" data-count-label="orders in progress" data-nav-label="Orders"', false);
        // Track Order lives inside Orders: each active card links to its tracking, shown above the list.
        $code = $active->sellerOrders()->firstOrFail()->shipment->tracking_code;
        $this->get(route('buyer.dashboard', ['tab' => 'orders']))
            ->assertSee(e(route('buyer.dashboard', ['tab' => 'orders', 'track' => $code])), false)
            ->assertDontSee('id="order-tracking"', false)
            ->assertDontSee('data-panel="track"', false)->assertDontSee('data-buyer-nav="track"', false);
        $this->get(route('buyer.dashboard', ['tab' => 'orders', 'track' => $code]))->assertOk()
            ->assertSee('id="order-tracking"', false)->assertSee('Delivery timeline')->assertSee('Close tracking');

        $this->getJson(route('buyer.navigation-counts'))->assertJsonMissingPath('counts.history')->assertJsonPath('counts.track', 1)->assertJsonPath('counts.orders', 1);
    }
}
