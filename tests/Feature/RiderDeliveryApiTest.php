<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\DeliveryEvent;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The Last Mile rider's deliveries in the app are saved on the server. */
class RiderDeliveryApiTest extends TestCase
{
    use RefreshDatabase;

    private LogisticsProvider $hub;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $this->hub = LogisticsProvider::create(['user_id' => $user->id, 'name' => 'Metro South Hub', 'slug' => 'metro-south-hub', 'status' => 'approved']);
    }

    private function rider(string $type = Rider::LAST_MILE, string $name = 'Lito Reyes'): Rider
    {
        $user = User::factory()->create(['role' => 'courier', 'status' => 'active', 'name' => $name]);

        return Rider::create(['user_id' => $user->id, 'logistics_provider_id' => $this->hub->id, 'courier_type' => $type,
            'vehicle_type' => 'motorcycle', 'status' => 'approved', 'is_active' => true]);
    }

    /** A parcel received at the hub and assigned to [$rider]. */
    private function assignedParcel(Rider $rider, string $payment = 'cod'): Shipment
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Laguna Gadgets', 'slug' => 'laguna-gadgets-'.$sellerUser->id, 'status' => 'approved']);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'ORD-2026-'.(30000 + $buyer->id), 'total_minor' => 25000, 'payment_method' => $payment, 'payment_status' => 'pending',
            'shipping_address' => ['recipient' => 'Juan Dela Cruz', 'address' => '45 Magsaysay St, Bubukal, Santa Cruz, Laguna', 'phone' => '09171234567']]);
        $sellerOrder = SellerOrder::create(['order_id' => $order->id, 'seller_id' => $seller->id, 'subtotal_minor' => 25000, 'shipping_fee_minor' => 0,
            'commission_minor' => 0, 'status' => 'shipped', 'logistics_provider_id' => $this->hub->id]);

        return Shipment::create(['seller_order_id' => $sellerOrder->id, 'logistics_provider_id' => $this->hub->id, 'rider_id' => $rider->id,
            'tracking_code' => 'CTZ-DLV'.$sellerOrder->id, 'status' => 'assigned', 'sort_status' => 'assigned', 'received_at' => now()->subHour(),
            'assigned_at' => now()->subMinutes(30), 'fee_minor' => 0, 'cod_amount_minor' => $payment === 'cod' ? 25000 : 0]);
    }

    private function as(Rider $rider): static
    {
        Sanctum::actingAs($rider->user);

        return $this;
    }

    public function test_the_queue_lists_only_my_assigned_and_out_for_delivery_parcels(): void
    {
        $me = $this->rider();
        $mine = $this->assignedParcel($me);
        $this->assignedParcel($this->rider(name: 'Other Rider'));

        $this->as($me)->getJson('/api/rider/deliveries')->assertOk()
            ->assertJsonCount(1, 'deliveries')
            ->assertJsonPath('deliveries.0.id', $mine->id)
            ->assertJsonPath('deliveries.0.status', 'assigned')
            ->assertJsonPath('deliveries.0.recipient_name', 'Juan Dela Cruz')
            ->assertJsonPath('deliveries.0.recipient_phone', '09171234567')
            ->assertJsonPath('deliveries.0.is_cod', true)
            ->assertJsonPath('cod_to_collect', 250)
            ->assertJsonPath('delivered_today', 0);
    }

    public function test_starting_saves_out_for_delivery_and_delivering_saves_delivered(): void
    {
        $me = $this->rider();
        $parcel = $this->assignedParcel($me);

        // Start: the rider scans the waybill and accepts it.
        $this->as($me)->postJson('/api/rider/parcels/accept', ['code' => $parcel->tracking_code])->assertOk()
            ->assertJsonPath('stage', 'out_for_delivery');
        $this->assertSame('out_for_delivery', $parcel->fresh()->status);
        $this->assertDatabaseHas('delivery_events', ['shipment_id' => $parcel->id, 'status' => 'out_for_delivery']);
        $this->getJson('/api/rider/deliveries')->assertJsonPath('deliveries.0.status', 'out_for_delivery');

        // Deliver with the wrong waybill: refused, nothing saved.
        $this->postJson("/api/rider/deliveries/{$parcel->id}/deliver", ['code' => 'CTZ-NOPE'])->assertStatus(422);
        $this->assertSame('out_for_delivery', $parcel->fresh()->status);

        $this->postJson("/api/rider/deliveries/{$parcel->id}/deliver", ['code' => strtolower($parcel->tracking_code), 'received_by' => 'Maria (sister)'])
            ->assertOk()->assertJsonPath('delivery.status', 'delivered');

        $parcel->refresh();
        $this->assertSame('delivered', $parcel->status);
        $this->assertTrue($parcel->cod_collected);
        $this->assertSame('delivered', $parcel->sellerOrder->status);
        $this->assertSame('paid', $parcel->sellerOrder->order->payment_status);
        $this->assertStringContainsString('Maria (sister)', DeliveryEvent::where('shipment_id', $parcel->id)->where('status', 'delivered')->value('note'));
        $this->assertContains('Order delivered', AppNotification::where('user_id', $parcel->sellerOrder->order->buyer_id)->pluck('title')->all());
        $this->assertContains('Parcel delivered', AppNotification::where('user_id', $this->hub->user_id)->pluck('title')->all());

        $this->getJson('/api/rider/deliveries')->assertJsonCount(0, 'deliveries')->assertJsonPath('delivered_today', 1);

        // Tapping again doesn't deliver twice.
        $this->postJson("/api/rider/deliveries/{$parcel->id}/deliver", ['code' => $parcel->tracking_code])->assertOk();
        $this->assertSame(1, DeliveryEvent::where('shipment_id', $parcel->id)->where('status', 'delivered')->count());
    }

    public function test_a_parcel_must_be_out_for_delivery_before_it_is_delivered(): void
    {
        $me = $this->rider();
        $parcel = $this->assignedParcel($me);

        $this->as($me)->postJson("/api/rider/deliveries/{$parcel->id}/deliver", ['code' => $parcel->tracking_code])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'Start the delivery first'));
        $this->assertSame('assigned', $parcel->fresh()->status);
    }

    public function test_a_failed_attempt_is_saved_and_a_second_trip_does_not_crash(): void
    {
        $me = $this->rider();
        $parcel = $this->assignedParcel($me);
        $this->as($me)->postJson('/api/rider/parcels/accept', ['code' => $parcel->tracking_code])->assertOk();

        $this->postJson("/api/rider/deliveries/{$parcel->id}/fail", ['reason' => 'Nobody home'])->assertOk()
            ->assertJsonPath('delivery.status', 'failed');
        $this->assertSame(1, $parcel->fresh()->attempts);
        $this->assertContains('Delivery attempt failed', AppNotification::where('user_id', $parcel->sellerOrder->order->buyer_id)->pluck('title')->all());

        // Next day: out for delivery again. This used to collide on (shipment, status, attempt).
        $this->postJson('/api/rider/parcels/accept', ['code' => $parcel->tracking_code])->assertOk();
        $this->assertSame('out_for_delivery', $parcel->fresh()->status);
        $this->assertSame([1, 2], DeliveryEvent::where('shipment_id', $parcel->id)->where('status', 'out_for_delivery')->orderBy('attempt')->pluck('attempt')->all());

        $this->postJson("/api/rider/deliveries/{$parcel->id}/deliver", ['code' => $parcel->tracking_code])->assertOk();
        $this->assertSame('delivered', $parcel->fresh()->status);
    }

    public function test_riders_cannot_touch_someone_elses_parcel_and_first_mile_cannot_deliver(): void
    {
        $owner = $this->rider();
        $parcel = $this->assignedParcel($owner);
        $this->as($owner)->postJson('/api/rider/parcels/accept', ['code' => $parcel->tracking_code])->assertOk();

        $this->as($this->rider(name: 'Someone Else'))
            ->postJson("/api/rider/deliveries/{$parcel->id}/deliver", ['code' => $parcel->tracking_code])->assertNotFound();
        $this->as($this->rider(Rider::FIRST_MILE, 'First Mile Fe'))
            ->postJson("/api/rider/deliveries/{$parcel->id}/fail", ['reason' => 'x'])->assertForbidden();
        $this->assertSame('out_for_delivery', $parcel->fresh()->status);
    }
}
