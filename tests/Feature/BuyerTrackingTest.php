<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, Seller, SellerOrder, User};
use App\Services\{OrderPlacement, OrderTracking};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function order(): SellerOrder
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        Seller::create(['user_id' => User::factory()->create(['role' => 'seller'])->id, 'name' => 'Test Shop', 'slug' => 'test-shop', 'status' => 'approved']);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Test Courier', 'slug' => 'courier', 'status' => 'approved']);
        $order = app(OrderPlacement::class)->place($buyer, [
            ['id' => 1, 'name' => 'Coffee mug', 'price' => 280, 'quantity' => 1],
        ], 'COD', 'Test address');
        $this->actingAs($buyer);

        return $order->sellerOrders()->firstOrFail();
    }

    private function event(SellerOrder $order, string $status, string $time, int $attempt = 1): void
    {
        $order->shipment->deliveryEvents()->create([
            'status' => $status, 'attempt' => $attempt,
            'occurred_at' => $time, 'user_id' => auth()->id(), 'note' => 'Recorded parcel scan.',
        ]);
    }

    public function test_hub_events_and_repeated_attempts_use_the_latest_recorded_time(): void
    {
        $order = $this->order();
        $this->event($order, 'received_at_hub', '2026-10-10 08:00:00');
        $this->event($order, 'sorted', '2026-10-10 09:00:00');
        $this->event($order, 'out_for_delivery', '2026-10-11 08:00:00');
        $this->event($order, 'failed', '2026-10-11 10:00:00');
        $this->event($order, 'out_for_delivery', '2026-10-12 08:00:00', 2);
        $order->shipment->update(['status' => 'out_for_delivery', 'attempts' => 2]);
        $tracking = app(OrderTracking::class)->build($order->fresh());
        $this->assertTrue(collect($tracking['steps'])->firstWhere('label', 'Received at sorting hub')['done']);
        $this->assertStringContainsString('Oct 12', collect($tracking['steps'])->firstWhere('label', 'Out for delivery')['time']);
        $this->assertCount(6, $tracking['history']);
        $this->assertSame(2, $tracking['attempts']);
        $this->assertSame('Not confirmed by courier', $tracking['eta']);
        $this->get('/dashboard?tab=track')->assertOk()->assertSee('Detailed activity')->assertSee('Sorted for delivery')->assertDontSee('Arriving today');
    }

    public function test_missing_scans_do_not_use_generic_updated_at_or_invent_pickup(): void
    {
        $order = $this->order();
        $order->update(['status' => 'shipped']);
        $order->shipment->update(['status' => 'in_transit']);
        $tracking = app(OrderTracking::class)->build($order->fresh());
        $steps = collect($tracking['steps']);
        $this->assertSame('Time not recorded', $steps->firstWhere('label', 'Packed by seller')['time']);
        $this->assertSame('Time not recorded', $steps->firstWhere('label', 'In transit')['time']);
        $this->assertFalse($steps->firstWhere('label', 'Picked up by courier')['done']);
        $this->assertSame('Awaiting update', $steps->firstWhere('label', 'Delivered')['time']);
    }

    public function test_failed_and_returned_parcels_show_attention_instead_of_a_normal_delivery_promise(): void
    {
        $order = $this->order();
        foreach (['failed', 'returned'] as $status) {
            $this->event($order, $status, '2026-10-12 09:00:00');
            $order->shipment->update(['status' => $status]);
            $tracking = app(OrderTracking::class)->build($order->fresh());
            $this->assertNotNull($tracking['attention']);
            $this->assertTrue(collect($tracking['steps'])->every(fn ($step) => $step['done']));
            $this->assertNotSame('Delivered', $tracking['eta']);
        }
    }

    public function test_tracking_search_cannot_reveal_another_buyers_parcel(): void
    {
        $order = $this->order();
        $code = $order->shipment->tracking_code;
        $this->actingAs(User::factory()->create(['role' => 'buyer']))
            ->get('/dashboard?tab=track&track=' . $code)->assertOk()
            ->assertViewHas('tracking', null);
    }

    public function test_seller_actions_record_packing_and_each_pickup_request(): void
    {
        $order = $this->order();
        $this->actingAs($order->seller->user);
        $this->post('/seller/orders/'.$order->id.'/pack')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('delivery_events', ['shipment_id' => $order->shipment->id, 'status' => 'packed']);
        $data = ['logistics_provider_id' => $order->shipment->logistics_provider_id, 'pickup_date' => now()->toDateString()];
        $this->post('/seller/courier/'.$order->id.'/schedule', $data)->assertSessionHasNoErrors();
        $this->post('/seller/courier/'.$order->id.'/schedule', $data)->assertSessionHasNoErrors();
        $this->assertSame(2, $order->shipment->deliveryEvents()->where('status', 'pickup_requested')->count());
        $tracking = app(OrderTracking::class)->build($order->fresh());
        $this->assertNotSame('Time not recorded', collect($tracking['steps'])->firstWhere('label', 'Packed by seller')['time']);
        $this->assertSame('Pickup requested', $tracking['current_status']);
    }
}
