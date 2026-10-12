<?php

namespace Tests\Feature;

use App\Models\{AppNotification, LogisticsProvider, SellerMessage, User, Wishlist};
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerNavigationCountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_badges_keep_cart_and_wishlist_counts_across_pages_including_zero(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        Wishlist::create(['user_id' => $buyer->id, 'ref' => '1', 'name' => 'Watch', 'price_minor' => 10000]);
        $this->actingAs($buyer)->withSession(['cart' => [
            'watch' => ['name' => 'Watch', 'price' => 100, 'quantity' => 3, 'variation' => 'Standard', 'image' => '/watch.jpg'],
        ]]);
        foreach ([route('home'), route('cart.index'), route('buyer.dashboard', ['tab' => 'wishlist'])] as $url) {
            $this->get($url)->assertOk()
                ->assertSee('data-buyer-count="cart" data-count="3"', false)
                ->assertSee('data-buyer-count="wishlist" data-count="1"', false)
                ->assertSee('data-buyer-count="messages" data-count="0"', false);
        }
        $this->getJson(route('buyer.navigation-counts'))->assertOk()->assertJsonPath('counts.cart', 3)->assertJsonPath('counts.wishlist', 1);
        $this->postJson(route('wishlist.toggle'), ['ref' => '1', 'name' => 'Watch', 'price' => 100])->assertJsonPath('count', 0);
        $this->getJson(route('buyer.navigation-counts'))->assertJsonPath('counts.wishlist', 0);
    }

    public function test_counts_are_scoped_to_buyer_and_follow_order_and_read_status(): void
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $other = User::factory()->create(['role' => 'buyer']);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
        $item = ['id' => 5, 'name' => 'Mug', 'price' => 280, 'quantity' => 1];
        $orders = [];
        foreach ([$buyer, $buyer, $other] as $owner) {
            $orders[] = app(OrderPlacement::class)->place($owner, [$item], 'COD', 'Test address')->sellerOrders()->firstOrFail();
        }
        $orders[1]->update(['status' => 'delivered']);
        foreach ([$orders[0], $orders[2]] as $order) {
            SellerMessage::create(['seller_order_id' => $order->id, 'sender_id' => $order->seller->user_id, 'body' => 'Update']);
        }
        SellerMessage::create(['seller_order_id' => $orders[0]->id, 'sender_id' => $buyer->id, 'body' => 'Thanks']);
        AppNotification::where('user_id', $buyer->id)->update(['is_read' => true]);
        AppNotification::create(['user_id' => $buyer->id, 'type' => 'order', 'title' => 'Update', 'message' => 'Ready', 'is_read' => false]);
        $this->actingAs($buyer)->getJson(route('buyer.navigation-counts'))->assertOk()->assertJson(['counts' => [
            'orders' => 2, 'history' => 1, 'track' => 1, 'messages' => 1, 'notifications' => 1,
        ]]);
        SellerMessage::where('seller_order_id', $orders[0]->id)->update(['read_at' => now()]);
        $this->postJson(route('notifications.markAllRead'))->assertOk();
        $this->getJson(route('buyer.navigation-counts'))->assertJsonPath('counts.messages', 0)->assertJsonPath('counts.notifications', 0);
    }

    public function test_count_endpoint_requires_buyer_authentication(): void
    {
        $this->getJson(route('buyer.navigation-counts'))->assertUnauthorized();
        $this->actingAs(User::factory()->create(['role' => 'seller']))->getJson(route('buyer.navigation-counts'))->assertForbidden();
    }
}
