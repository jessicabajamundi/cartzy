<?php

namespace Tests\Feature;

use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\PickupRequest;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * First Mile couriers bring parcels from the seller to the hub; Last Mile couriers deliver them from the hub to the buyer.
 */
class CourierTypesTest extends TestCase
{
    use RefreshDatabase;

    private LogisticsProvider $hub;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $this->hub = LogisticsProvider::create(['user_id' => $user->id, 'name' => 'Metro South Hub', 'slug' => 'metro-south-hub', 'status' => 'approved']);
    }

    private function courier(string $type, string $name, string $area = 'Laguna - Santa Cruz'): Rider
    {
        $user = User::factory()->create(['role' => 'courier', 'status' => 'active', 'name' => $name]);

        return Rider::create(['user_id' => $user->id, 'logistics_provider_id' => $this->hub->id, 'courier_type' => $type, 'vehicle_type' => 'Motorcycle', 'status' => 'approved', 'is_active' => true, 'area' => $area]);
    }

    /** A seller order with its shipment and a pickup request at this hub. */
    private function parcel(array $shipment = []): array
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Laguna Gadgets', 'slug' => 'laguna-gadgets-'.$sellerUser->id, 'status' => 'approved']);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'MILE-'.$buyer->id, 'total_minor' => 10000, 'payment_method' => 'cod', 'payment_status' => 'pending',
            'shipping_address' => ['recipient' => 'Juan Dela Cruz', 'address' => '45 Magsaysay St, Santa Cruz, Laguna', 'phone' => '09171234567']]);
        $sellerOrder = SellerOrder::create(['order_id' => $order->id, 'seller_id' => $seller->id, 'subtotal_minor' => 10000, 'shipping_fee_minor' => 0, 'commission_minor' => 0, 'status' => 'ready_to_ship', 'logistics_provider_id' => $this->hub->id]);
        $parcel = Shipment::create($shipment + ['seller_order_id' => $sellerOrder->id, 'logistics_provider_id' => $this->hub->id, 'tracking_code' => 'CTZ-MILE-'.$sellerOrder->id, 'status' => 'unassigned', 'fee_minor' => 0, 'cod_amount_minor' => 10000]);
        $pickup = PickupRequest::create(['logistics_provider_id' => $this->hub->id, 'seller_id' => $seller->id, 'seller_order_id' => $sellerOrder->id, 'parcel_count' => 1, 'weight_kg' => 1, 'pickup_date' => now()->toDateString(), 'pickup_time' => '2:00 PM', 'address' => '123 Rizal St, Santa Cruz, Laguna', 'status' => 'pending']);

        return [$parcel, $pickup];
    }

    public function test_pickups_only_go_to_first_mile_couriers_and_are_recorded_as_the_pickup_rider(): void
    {
        $firstMile = $this->courier(Rider::FIRST_MILE, 'Dante Ramos');
        $lastMile = $this->courier(Rider::LAST_MILE, 'Arnel Gomez');
        [$parcel, $pickup] = $this->parcel();

        $this->actingAs($this->hub->user)->get(route('logistics.pickups'))->assertOk()
            ->assertViewHas('allRiders', fn ($riders) => $riders->pluck('id')->all() === [$firstMile->id]);

        $this->post(route('logistics.pickups.assign', $pickup->id), ['rider_id' => $lastMile->id])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNull($pickup->fresh()->rider_id);

        $this->post(route('logistics.pickups.auto-assign', $pickup->id))->assertRedirect()->assertSessionHas('success');
        $this->assertSame($firstMile->id, $pickup->fresh()->rider_id);
        $parcel->refresh();
        $this->assertSame($firstMile->id, $parcel->pickup_rider_id);
        $this->assertNull($parcel->rider_id, 'The last-mile courier is chosen later, at the hub.');
    }

    public function test_dispatch_all_needs_a_first_mile_courier(): void
    {
        $this->courier(Rider::LAST_MILE, 'Arnel Gomez');
        [, $pickup] = $this->parcel();

        $this->actingAs($this->hub->user)->post(route('logistics.pickups.dispatch-all'))
            ->assertRedirect()->assertSessionHas('error', fn ($message) => str_contains($message, 'First Mile'));
        $this->assertNull($pickup->fresh()->rider_id);
    }

    public function test_a_parcel_collected_by_a_first_mile_courier_is_delivered_by_a_last_mile_courier(): void
    {
        $firstMile = $this->courier(Rider::FIRST_MILE, 'Dante Ramos');
        $lastMile = $this->courier(Rider::LAST_MILE, 'Arnel Gomez');
        [$parcel] = $this->parcel(['pickup_rider_id' => $firstMile->id, 'received_at' => now(), 'sort_status' => 'sorted', 'delivery_area' => 'Laguna - Santa Cruz']);

        // Still waiting for a delivery courier, even though a courier collected it from the seller.
        $this->actingAs($this->hub->user)->get(route('logistics.hub', ['tab' => 'assign']))->assertOk()
            ->assertSee($parcel->tracking_code)->assertSee('Arnel Gomez')->assertDontSee('Dante Ramos');

        $this->post(route('logistics.assignments.assign', $parcel->id), ['rider_id' => $firstMile->id])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNull($parcel->fresh()->rider_id);

        $this->post(route('logistics.assignments.auto-assign'))->assertRedirect()->assertSessionHas('success');
        $parcel->refresh();
        $this->assertSame($lastMile->id, $parcel->rider_id);
        $this->assertSame($firstMile->id, $parcel->pickup_rider_id);
    }

    public function test_hub_can_change_a_courier_type_unless_they_still_have_open_work(): void
    {
        $rider = $this->courier(Rider::FIRST_MILE, 'Dante Ramos');
        [, $pickup] = $this->parcel();
        $pickup->update(['rider_id' => $rider->id, 'status' => 'confirmed']);

        $this->actingAs($this->hub->user)->post(route('logistics.riders.type', $rider->id), ['courier_type' => Rider::LAST_MILE])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertTrue($rider->fresh()->isFirstMile());

        $pickup->update(['status' => 'picked_up']);
        $this->post(route('logistics.riders.type', $rider->id), ['courier_type' => Rider::LAST_MILE])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertTrue($rider->fresh()->isLastMile());

        $this->post(route('logistics.riders.type', $rider->id), ['courier_type' => 'teleport'])->assertSessionHasErrors('courier_type');

        $this->get(route('logistics.riders', ['type' => Rider::LAST_MILE]))->assertOk()
            ->assertViewHas('riders', fn ($riders) => $riders->total() === 1)
            ->assertViewHas('typeCounts', fn ($counts) => $counts === ['all' => 1, 'first_mile' => 0, 'last_mile' => 1])
            ->assertSee('Courier type');
    }

    public function test_couriers_can_only_do_their_own_leg(): void
    {
        $lastMile = $this->courier(Rider::LAST_MILE, 'Arnel Gomez');
        $firstMile = $this->courier(Rider::FIRST_MILE, 'Dante Ramos');
        [$parcel, $pickup] = $this->parcel();

        $this->actingAs($lastMile->user)->post(route('courier.pickups.accept', $pickup->id))
            ->assertRedirect()->assertSessionHas('error');
        $this->assertNull($pickup->fresh()->rider_id);

        $this->actingAs($firstMile->user)->postJson(route('courier.deliver', $parcel->id))->assertForbidden();
        $this->assertNotSame('delivered', $parcel->fresh()->status);

        $this->post(route('courier.pickups.accept', $pickup->id))->assertRedirect()->assertSessionHas('success');
        $parcel->refresh();
        $this->assertSame($firstMile->id, $parcel->pickup_rider_id);
        $this->assertNull($parcel->rider_id);
        $this->assertSame('picked_up', $parcel->status);
    }
}
