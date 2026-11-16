<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, Order, PickupRequest, Seller, SellerOrder, Shipment, User};
use App\Services\OrderTracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerTrackingTest extends TestCase
{
    use RefreshDatabase;

    private function order(): SellerOrder
    {
        \Illuminate\Support\Facades\Http::fake(['*' => \Illuminate\Support\Facades\Http::response([
            'code' => 'Ok', 'routes' => [['geometry' => ['coordinates' => [[121.03, 14.65], [121.02, 14.55]]],
                'distance' => 12000, 'duration' => 1800]],
        ])]);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $seller = Seller::create(['user_id' => User::factory()->create(['role' => 'seller'])->id, 'name' => 'Test Shop', 'slug' => 'test-shop', 'status' => 'approved']);
        $hub = LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Test Courier', 'slug' => 'courier', 'status' => 'approved']);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'ORD-TRACK-1', 'total_minor' => 28000,
            'payment_method' => 'cod', 'payment_status' => 'pending', 'shipping_address' => ['address' => 'Test address']]);
        $sellerOrder = SellerOrder::create(['order_id' => $order->id, 'seller_id' => $seller->id,
            'subtotal_minor' => 28000, 'shipping_fee_minor' => 0, 'commission_minor' => 0, 'status' => 'pending']);
        Shipment::create(['seller_order_id' => $sellerOrder->id, 'logistics_provider_id' => $hub->id,
            'tracking_code' => 'CTZ-TRACK-1', 'status' => 'unassigned', 'handover_method' => 'pickup']);
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
        $this->assertCount(5, $tracking['history']);
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
        $this->assertFalse($steps->firstWhere('label', 'Picked up by rider')['done']);
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
        $this->post(route('seller.orders.decision', $order->id), ['decision' => 'accepted'])->assertSessionHasNoErrors();
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

    public function test_pickup_assignment_confirms_schedule_without_claiming_physical_collection(): void
    {
        $order = $this->order();
        $this->event($order, 'pickup_assigned', '2026-11-16 08:00:00');
        $tracking = app(OrderTracking::class)->build($order->fresh());
        $steps = collect($tracking['steps']);
        $this->assertTrue($steps->firstWhere('label', 'Pickup schedule confirmed')['done']);
        $this->assertTrue($steps->firstWhere('label', 'Pickup rider assigned')['done']);
        $this->assertFalse($steps->firstWhere('label', 'Picked up by rider')['done']);
        $this->assertSame('Pickup rider assigned', $tracking['current_status']);
        $this->assertSame('Pickup rider assigned', $tracking['history'][0]['label']);
    }

    public function test_saved_pickup_confirmation_is_visible_without_a_delivery_event(): void
    {
        $order = $this->order();
        PickupRequest::create(['seller_order_id' => $order->id, 'seller_id' => $order->seller_id,
            'logistics_provider_id' => $order->shipment->logistics_provider_id, 'parcel_count' => 1,
            'pickup_date' => now()->toDateString(), 'address' => 'Seller address',
            'status' => 'confirmed', 'confirmed_at' => now()]);
        $tracking = app(OrderTracking::class)->build($order->fresh());
        $this->assertSame('Pickup schedule confirmed', $tracking['current_status']);
        $this->assertFalse(collect($tracking['steps'])->firstWhere('label', 'Picked up by rider')['done']);
    }

    public function test_hub_transfer_precedes_final_rider_assignment_and_uses_saved_timestamps(): void
    {
        $order = $this->order();
        $destination = LogisticsProvider::create(['user_id' => User::factory()->create()->id,
            'name' => 'Destination', 'slug' => 'destination', 'status' => 'approved']);
        $order->shipment->update(['origin_hub_id' => $order->shipment->logistics_provider_id,
            'destination_hub_id' => $destination->id, 'status' => 'in_transit',
            'dispatched_to_hub_at' => '2026-11-16 08:00:00']);
        $tracking = app(OrderTracking::class)->build($order->fresh());
        $labels = array_column($tracking['steps'], 'label');
        $this->assertSame('In Hub-to-Hub transit', $tracking['current_status']);
        $this->assertLessThan(array_search('Assigned to delivery rider', $labels), array_search('Arrived at destination hub', $labels));
        $order->shipment->update(['arrived_at_destination_hub_at' => '2026-11-16 10:00:00']);
        $tracking = app(OrderTracking::class)->build($order->fresh());
        $this->assertSame('Arrived at destination hub', $tracking['current_status']);
        $this->assertFalse(collect($tracking['steps'])->firstWhere('label', 'Assigned to delivery rider')['done']);
    }
}
