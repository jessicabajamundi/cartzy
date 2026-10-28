<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, Seller, SellerOrder, User};
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiBuyerOrdersTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private function place(string $payment = 'GCash'): SellerOrder
    {
        $this->buyer ??= User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $slug = 'shop-'.$sellerUser->id;
        Seller::create(['user_id' => $sellerUser->id, 'name' => 'Test Shop', 'slug' => $slug, 'status' => 'approved']);
        if (!LogisticsProvider::exists()) {
            LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
        }
        $order = app(OrderPlacement::class)->place($this->buyer, [
            ['id' => 1, 'name' => 'Coffee mug', 'price' => 280, 'quantity' => 1],
        ], $payment, 'Test address');
        Sanctum::actingAs($this->buyer);

        return $order->sellerOrders()->firstOrFail();
    }

    public function test_orders_list_matches_the_website_cards(): void
    {
        $so = $this->place();

        $this->getJson('/api/orders')
            ->assertOk()
            ->assertJsonPath('orders.0.id', $so->order->reference)
            ->assertJsonPath('orders.0.seller_order_id', $so->id)
            ->assertJsonPath('orders.0.store', $so->seller->name)
            ->assertJsonPath('orders.0.status', 'to_pay')
            ->assertJsonPath('orders.0.can_cancel', true)
            ->assertJsonPath('orders.0.items.0.name', 'Coffee mug')
            ->assertJsonPath('orders.0.items.0.rating', null)
            ->assertJsonPath('counts.to_pay', 1)
            ->assertJsonFragment(['Other reason'])
            ->assertJsonFragment(['key' => 'damaged', 'needs_photos' => true]);
    }

    public function test_buyer_can_pay_track_and_request_cancellation(): void
    {
        $so = $this->place();

        $this->postJson('/api/orders/pay/'.$so->order->reference)->assertOk();
        $this->assertSame('paid', $so->order->fresh()->payment_status);
        $this->getJson('/api/orders')->assertJsonPath('orders.0.status', 'to_ship');

        $this->getJson("/api/orders/{$so->id}/tracking")
            ->assertOk()
            ->assertJsonPath('tracking.steps.0.label', 'Order placed');

        $this->postJson("/api/orders/{$so->id}/cancel", ['reason' => 'Other reason'])
            ->assertStatus(422)->assertJsonValidationErrors('reason_details');
        $this->postJson("/api/orders/{$so->id}/cancel", ['reason' => 'I changed my mind'])
            ->assertOk()->assertJsonPath('message', 'Cancellation request sent. Your order will only be cancelled after seller approval.');
        $this->assertSame('pending', $so->fresh()->cancellation_status);
        $this->getJson('/api/orders')
            ->assertJsonPath('orders.0.cancellation_status', 'pending')
            ->assertJsonPath('orders.0.can_cancel', false);
    }

    public function test_buyer_can_report_a_problem_and_review_received_items(): void
    {
        $so = $this->place();

        $this->postJson("/api/orders/{$so->id}/report", [
            'category' => 'not_received',
            'requested_resolution' => 'refund',
            'description' => 'The parcel never arrived at my address.',
            'contact_phone' => '09171234567',
        ])->assertOk()->assertJsonStructure(['case_id']);
        $this->getJson('/api/orders')
            ->assertJsonPath('orders.0.report.status', 'open')
            ->assertJsonPath('orders.0.can_report', false);

        $item = $so->items()->firstOrFail();
        $this->postJson('/api/reviews', ['order_item_id' => $item->id, 'rating' => 5])->assertStatus(422);
        $so->update(['status' => 'delivered', 'delivered_at' => now()]);
        $this->postJson('/api/reviews', ['order_item_id' => $item->id, 'rating' => 4, 'comment' => 'Nice mug'])->assertOk();
        $this->getJson('/api/orders')
            ->assertJsonPath('orders.0.status', 'completed')
            ->assertJsonPath('orders.0.items.0.rating', 4);
    }

    public function test_other_buyers_cannot_touch_the_order(): void
    {
        $so = $this->place();
        Sanctum::actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));

        $this->getJson('/api/orders')->assertOk()->assertJsonCount(0, 'orders');
        $this->getJson("/api/orders/{$so->id}/tracking")->assertNotFound();
        $this->postJson("/api/orders/{$so->id}/cancel", ['reason' => 'I changed my mind'])->assertNotFound();
        $this->postJson('/api/orders/pay/'.$so->order->reference)->assertNotFound();
        $this->assertNull($so->fresh()->cancellation_status);
    }
}
