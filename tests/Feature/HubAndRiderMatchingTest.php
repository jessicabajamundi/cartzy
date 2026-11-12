<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\AppNotification;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\PickupRequest;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use App\Support\PlaceMatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Drop-off vs pick-up, the closest hub for a seller, each hub's location on its
 * dashboard, and riders matched by the barangay they live in.
 */
class HubAndRiderMatchingTest extends TestCase
{
    use RefreshDatabase;

    private function hub(string $name, array $office = [], array $regions = []): LogisticsProvider
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active'] + $office);

        return LogisticsProvider::create(['user_id' => $user->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'status' => 'approved', 'service_regions' => $regions]);
    }

    private function sellerAt(array $place): Seller
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $address = Address::create(['user_id' => $user->id, 'label' => 'Shop', 'recipient' => 'Shop', 'phone' => '09170000000',
            'line1' => '12 Rizal St', 'postal_code' => '4009'] + $place);

        return Seller::create(['user_id' => $user->id, 'name' => 'Laguna Gadgets', 'slug' => 'laguna-gadgets-'.$user->id,
            'status' => 'approved', 'pickup_address_id' => $address->id]);
    }

    private function packedOrder(Seller $seller): SellerOrder
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'ORD-2026-'.(40000 + $buyer->id), 'total_minor' => 10000,
            'payment_method' => 'cod', 'payment_status' => 'pending',
            'shipping_address' => ['recipient' => 'Juan', 'address' => '45 Magsaysay St, Bubukal, Santa Cruz, Laguna']]);

        return SellerOrder::create(['order_id' => $order->id, 'seller_id' => $seller->id, 'subtotal_minor' => 10000,
            'shipping_fee_minor' => 0, 'commission_minor' => 0, 'status' => 'ready_to_ship']);
    }

    private function rider(LogisticsProvider $hub, string $name, array $home, string $type = Rider::FIRST_MILE, string $area = ''): Rider
    {
        $user = User::factory()->create(['role' => 'courier', 'status' => 'active', 'name' => $name] + $home);

        return Rider::create(['user_id' => $user->id, 'logistics_provider_id' => $hub->id, 'courier_type' => $type,
            'vehicle_type' => $type === Rider::FIRST_MILE ? 'van' : 'motorcycle', 'status' => 'approved', 'is_active' => true, 'area' => $area]);
    }

    public function test_place_names_are_matched_loosely_but_cities_need_the_same_province(): void
    {
        $sellerPlace = PlaceMatcher::placeOf(['barangay' => 'Bubukal', 'city' => 'Sta. Cruz', 'province' => 'Laguna']);
        $hubPlace = PlaceMatcher::placeOf(['barangay' => 'Barangay Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $this->assertSame('barangay', PlaceMatcher::compare($sellerPlace, $hubPlace)['level']);
        $this->assertSame('city', PlaceMatcher::compare(
            PlaceMatcher::placeOf(['city' => 'City of Calamba', 'province' => 'Laguna']),
            PlaceMatcher::placeOf(['city' => 'Calamba City', 'province' => 'Laguna']))['level']);
        // Santa Cruz, Laguna is not Santa Cruz, Davao del Sur.
        $this->assertNull(PlaceMatcher::compare($sellerPlace, PlaceMatcher::placeOf(['city' => 'Santa Cruz', 'province' => 'Davao del Sur']))['level']);
        $this->assertSame('region', PlaceMatcher::compare($sellerPlace, PlaceMatcher::placeOf(['city' => 'Naic', 'province' => 'Cavite']))['level']);
        $this->assertSame('IV-A', PlaceMatcher::regionOf('Laguna'));

        $home = PlaceMatcher::placeOf(['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $this->assertSame('barangay', PlaceMatcher::compareToText($home, '45 Magsaysay St, Bubukal, Sta. Cruz, Laguna')['level']);
        $this->assertSame('city', PlaceMatcher::compareToText($home, 'Blk 2, Poblacion, Santa Cruz, Laguna')['level']);
        $this->assertNull(PlaceMatcher::compareToText($home, 'Poblacion, Santa Cruz, Davao del Sur')['level']);
    }

    public function test_the_seller_sees_the_closest_hub_recommended_first(): void
    {
        $far = $this->hub('Cavite Hub', ['barangay' => 'Makina', 'city' => 'Naic', 'province' => 'Cavite']);
        $near = $this->hub('Sta Cruz Hub', ['street_address' => '123', 'barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna'], ['IV-A']);
        $this->hub('No Address Hub');
        $seller = $this->sellerAt(['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $this->packedOrder($seller);

        $this->actingAs($seller->user)->get(route('seller.courier'))->assertOk()
            ->assertViewHas('rankedProviders', fn ($ranked) => $ranked[0]['provider']->is($near) && $ranked[0]['recommended']
                && $ranked[0]['reason'] === 'Same barangay' && $ranked[1]['provider']->is($far) && $ranked[1]['reason'] === 'Same region')
            ->assertSee('★ Sta Cruz Hub · Same barangay (Recommended)', false)
            ->assertSee('123, Bubukal, Santa Cruz, Laguna');
    }

    public function test_pick_up_creates_a_pickup_request_for_the_hub(): void
    {
        $hub = $this->hub('Sta Cruz Hub', ['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $seller = $this->sellerAt(['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $order = $this->packedOrder($seller);

        $this->actingAs($seller->user)->post(route('seller.courier.schedule', $order), [
            'logistics_provider_id' => $hub->id, 'handover_method' => 'pickup', 'pickup_date' => now()->toDateString(),
        ])->assertSessionHasNoErrors();

        $shipment = $order->fresh()->shipment;
        $this->assertSame(Shipment::PICKUP, $shipment->handover_method);
        $pickup = PickupRequest::where('seller_order_id', $order->id)->sole();
        $this->assertSame('12 Rizal St, Bubukal, Santa Cruz, Laguna', $pickup->address);
        $this->assertSame('pending', $pickup->status);
        $this->assertContains('New pickup request', AppNotification::where('user_id', $hub->user_id)->pluck('title')->all());

        // Asking again doesn't create a second request.
        $this->post(route('seller.courier.schedule', $order), ['logistics_provider_id' => $hub->id, 'pickup_date' => now()->toDateString()]);
        $this->assertSame(1, PickupRequest::where('seller_order_id', $order->id)->count());
    }

    public function test_packing_opens_both_handover_choices_for_the_specific_sellers_parcel(): void
    {
        $hub = $this->hub('Handover Hub');
        $seller = $this->sellerAt(['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $order = $this->packedOrder($seller);
        $order->update(['status' => 'pending']);
        $another = $this->packedOrder($seller);
        $destination = route('seller.courier', ['status' => 'ready_to_ship', 'order' => $order->id]);

        $this->actingAs($seller->user)->post(route('seller.orders.pack', $order->id))->assertRedirect($destination);
        $this->get($destination)->assertOk()->assertSee('Pick up from my shop')->assertSee('Drop off at logistics partner')
            ->assertViewHas('orders', fn ($orders) => $orders->total() === 1 && $orders->first()->id === $order->id);
        $this->get(route('seller.orders'))->assertOk()->assertSee('Pickup or drop-off')->assertSee($destination);

        $otherSeller = $this->sellerAt(['barangay' => 'Other', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $this->actingAs($otherSeller->user)->get($destination)->assertOk()->assertViewHas('orders', fn ($orders) => $orders->isEmpty());
        $this->post(route('seller.courier.schedule', $order->id), ['logistics_provider_id' => $hub->id,
            'handover_method' => 'dropoff', 'pickup_date' => now()->toDateString()])->assertNotFound();
    }

    public function test_handover_choice_cannot_change_after_the_hub_receives_the_parcel(): void
    {
        $hub = $this->hub('Receiving Hub');
        $seller = $this->sellerAt(['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $order = $this->packedOrder($seller);
        $data = ['logistics_provider_id' => $hub->id, 'handover_method' => 'dropoff', 'pickup_date' => now()->toDateString()];
        $this->actingAs($seller->user)->post(route('seller.courier.schedule', $order->id), $data)->assertSessionHasNoErrors();
        $order->fresh()->shipment->update(['received_at' => now()]);
        $data['handover_method'] = 'pickup';
        $this->post(route('seller.courier.schedule', $order->id), $data)->assertUnprocessable();
        $this->assertTrue($order->fresh()->shipment->isDropoff());
        $this->assertFalse(PickupRequest::where('seller_order_id', $order->id)->exists());
    }

    public function test_drop_off_sends_no_courier_and_the_hub_receives_it(): void
    {
        $hub = $this->hub('Sta Cruz Hub', ['street_address' => '123', 'barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $seller = $this->sellerAt(['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $order = $this->packedOrder($seller);

        // Seller first asked for a pick-up, then decided to drop it off.
        $this->actingAs($seller->user)->post(route('seller.courier.schedule', $order), ['logistics_provider_id' => $hub->id, 'handover_method' => 'pickup', 'pickup_date' => now()->toDateString()]);
        $this->post(route('seller.courier.schedule', $order), ['logistics_provider_id' => $hub->id, 'handover_method' => 'dropoff', 'pickup_date' => now()->toDateString()])
            ->assertSessionHasNoErrors()->assertSessionHas('success', fn ($m) => str_contains($m, '123, Bubukal, Santa Cruz, Laguna'));

        $shipment = $order->fresh()->shipment;
        $this->assertTrue($shipment->isDropoff());
        $this->assertSame('cancelled', PickupRequest::where('seller_order_id', $order->id)->value('status'));
        $this->assertDatabaseHas('delivery_events', ['shipment_id' => $shipment->id, 'status' => 'dropoff_requested']);
        $this->assertContains('Seller drop-off coming', AppNotification::where('user_id', $hub->user_id)->pluck('title')->all());

        $this->get(route('seller.courier'))->assertOk()->assertSee('Drop-off at hub')->assertSee('Bring this parcel to Sta Cruz Hub');

        // The hub sees it in Receive, tagged.
        $this->actingAs($hub->user)->get(route('logistics.hub', ['tab' => 'receive']))->assertOk()->assertSee('Seller drop-off');

        // A First Mile courier can't take it from the seller.
        $courier = $this->rider($hub, 'Dante', []);
        Sanctum::actingAs($courier->user);
        $this->postJson('/api/rider/parcels/accept', ['code' => $shipment->tracking_code])->assertStatus(422)
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'dropping off'));
        $this->assertNull($shipment->fresh()->pickup_rider_id);
    }

    public function test_the_hub_receives_a_dropped_off_parcel_as_usual(): void
    {
        $hub = $this->hub('Sta Cruz Hub', ['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $seller = $this->sellerAt(['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $order = $this->packedOrder($seller);
        $this->actingAs($seller->user)->post(route('seller.courier.schedule', $order), ['logistics_provider_id' => $hub->id, 'handover_method' => 'dropoff', 'pickup_date' => now()->toDateString()]);
        $shipment = $order->fresh()->shipment;

        $this->actingAs($hub->user)->post(route('logistics.parcels.receive', ['id' => $shipment->id]))->assertSessionHasNoErrors();
        $this->assertNotNull($shipment->fresh()->received_at);
    }

    public function test_the_hub_dashboard_shows_its_location_service_area_and_where_riders_live(): void
    {
        $hub = $this->hub('Sta Cruz Hub', ['street_address' => '123', 'barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna'], ['IV-A']);
        $this->rider($hub, 'Ana', ['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $this->rider($hub, 'Ben', ['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna'], Rider::LAST_MILE);
        $this->rider($hub, 'Cora', []);

        $this->actingAs($hub->user)->get(route('logistics.dashboard'))->assertOk()
            ->assertSee('123, Bubukal, Santa Cruz, Laguna')
            ->assertSee('Region IV-A (CALABARZON)')
            ->assertSee('1 courier · 1 rider', false)
            ->assertSee('1 rider without a home address');

        $empty = $this->hub('New Hub');
        $this->actingAs($empty->user)->get(route('logistics.dashboard'))->assertOk()->assertSee('Add your address');
    }

    public function test_pickups_recommend_and_auto_assign_the_courier_living_in_the_sellers_barangay(): void
    {
        $hub = $this->hub('Sta Cruz Hub', ['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        // Ben's free-text service area says Laguna; Ana lives in the seller's barangay.
        $this->rider($hub, 'Ben Area', [], Rider::FIRST_MILE, 'Laguna');
        $this->rider($hub, 'Cris City', ['barangay' => 'Poblacion', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $ana = $this->rider($hub, 'Ana Barangay', ['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        $seller = $this->sellerAt(['barangay' => 'Bubukal', 'city' => 'Sta. Cruz', 'province' => 'Laguna']);
        $order = $this->packedOrder($seller);
        $this->actingAs($seller->user)->post(route('seller.courier.schedule', $order), ['logistics_provider_id' => $hub->id, 'pickup_date' => now()->toDateString()]);
        $pickup = PickupRequest::where('seller_order_id', $order->id)->sole();

        $this->actingAs($hub->user)->get(route('logistics.pickups'))->assertOk()
            ->assertViewHas('pickups', function ($pickups) {
                $ranked = $pickups->first()['ranked_riders'];

                return $ranked[0]['name'] === 'Ana Barangay' && $ranked[0]['home_level'] === 'barangay'
                    && $ranked[1]['name'] === 'Cris City' && $ranked[1]['home_level'] === 'city';
            })
            ->assertSee('Lives in the same barangay (1)')
            ->assertSee('Lives in the same city (1)')
            ->assertSee('🏠 Lives in Bubukal', false);

        $this->post(route('logistics.pickups.auto-assign', $pickup->id))->assertSessionHasNoErrors();
        $this->assertSame($ana->id, $pickup->fresh()->rider_id);
        $this->assertSame($ana->id, $order->fresh()->shipment->pickup_rider_id);
    }

    public function test_riders_save_their_home_address_from_the_app(): void
    {
        $hub = $this->hub('Sta Cruz Hub');
        $rider = $this->rider($hub, 'Dante', []);
        Sanctum::actingAs($rider->user);

        $this->getJson('/api/auth/me')->assertJsonPath('user.home_address', null);
        $this->putJson('/api/rider/home-address', ['province' => 'Laguna', 'city' => 'Santa Cruz'])->assertStatus(422);
        $this->putJson('/api/rider/home-address', ['province' => 'Laguna', 'city' => 'Santa Cruz', 'barangay' => 'Bubukal'])->assertOk()
            ->assertJsonPath('home_address.barangay', 'Bubukal');
        $this->getJson('/api/auth/me')->assertJsonPath('user.home_address.city', 'Santa Cruz');
        $this->getJson('/api/courier/dashboard')->assertJsonPath('courier.home_address.province', 'Laguna');

        Sanctum::actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));
        $this->putJson('/api/rider/home-address', ['province' => 'Laguna', 'city' => 'Santa Cruz', 'barangay' => 'Bubukal'])->assertForbidden();
    }
}
