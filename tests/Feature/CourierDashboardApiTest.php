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
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The First Mile courier dashboard in the app shows only real data. */
class CourierDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    private LogisticsProvider $hub;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $this->hub = LogisticsProvider::create(['user_id' => $user->id, 'name' => 'Metro South Hub', 'slug' => 'metro-south-hub', 'status' => 'approved']);
    }

    private function courier(string $name = 'Dante Ramos', array $extra = []): Rider
    {
        $user = User::factory()->create(['role' => 'courier', 'status' => 'active', 'name' => $name]);

        return Rider::create($extra + ['user_id' => $user->id, 'logistics_provider_id' => $this->hub->id, 'courier_type' => Rider::FIRST_MILE,
            'vehicle_type' => 'van', 'status' => 'approved', 'is_active' => true]);
    }

    /** A seller order waiting at the seller, with a pickup request. */
    private function parcelAtSeller(?Rider $assignedTo): array
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Laguna Gadgets', 'slug' => 'laguna-gadgets-'.$sellerUser->id, 'status' => 'approved']);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'ORD-2026-'.(20000 + $buyer->id), 'total_minor' => 10000, 'payment_method' => 'cod', 'payment_status' => 'pending',
            'shipping_address' => ['recipient' => 'Juan Dela Cruz', 'address' => 'Santa Cruz, Laguna']]);
        $sellerOrder = SellerOrder::create(['order_id' => $order->id, 'seller_id' => $seller->id, 'subtotal_minor' => 10000, 'shipping_fee_minor' => 0,
            'commission_minor' => 0, 'status' => 'ready_to_ship', 'logistics_provider_id' => $this->hub->id]);
        $shipment = Shipment::create(['seller_order_id' => $sellerOrder->id, 'logistics_provider_id' => $this->hub->id,
            'tracking_code' => 'CTZ-DASH'.$sellerOrder->id, 'status' => 'unassigned', 'fee_minor' => 0, 'cod_amount_minor' => 10000]);
        $pickup = PickupRequest::create(['logistics_provider_id' => $this->hub->id, 'seller_id' => $seller->id, 'seller_order_id' => $sellerOrder->id,
            'rider_id' => $assignedTo?->id, 'parcel_count' => 1, 'weight_kg' => 2.5, 'pickup_date' => now()->toDateString(), 'pickup_time' => '2:00 PM',
            'address' => '123 Rizal St, Santa Cruz, Laguna', 'status' => $assignedTo ? 'confirmed' : 'pending']);

        return [$shipment, $pickup];
    }

    private function dashboard(Rider $courier)
    {
        Sanctum::actingAs($courier->user);

        return $this->getJson('/api/courier/dashboard')->assertOk();
    }

    public function test_a_new_courier_sees_their_real_profile_and_empty_lists_not_sample_data(): void
    {
        $courier = $this->courier('Dante Ramos', ['plate_no' => 'NBC 1234', 'area' => 'Laguna']);

        $this->dashboard($courier)
            ->assertJsonPath('courier.name', 'Dante Ramos')
            ->assertJsonPath('courier.courier_type_label', 'First Mile')
            ->assertJsonPath('courier.vehicle_label', 'Van')
            ->assertJsonPath('courier.plate_no', 'NBC 1234')
            ->assertJsonPath('courier.hub', 'Metro South Hub')
            ->assertJsonMissingPath('courier.rating') // only a column default, never a real rating
            ->assertJsonPath('shift.on_shift', false)
            ->assertJsonPath('today.pickups_to_do', 0)
            ->assertJsonPath('today.in_vehicle', 0)
            ->assertJsonPath('today.handed_to_hub_today', 0)
            ->assertJsonPath('pickups', [])
            ->assertJsonPath('in_vehicle', [])
            ->assertJsonPath('handed_to_hub', []);
    }

    public function test_missing_plate_and_area_stay_empty_instead_of_a_default(): void
    {
        $this->dashboard($this->courier())
            ->assertJsonPath('courier.plate_no', null)
            ->assertJsonPath('courier.area', null);
    }

    public function test_a_pickup_moves_from_to_do_to_vehicle_to_hub(): void
    {
        $courier = $this->courier();
        [$shipment, $pickup] = $this->parcelAtSeller($courier);
        $this->parcelAtSeller(null); // not assigned to anyone: not shown
        $this->parcelAtSeller($this->courier('Other Courier')); // someone else's: not shown

        $this->dashboard($courier)
            ->assertJsonPath('today.pickups_to_do', 1)
            ->assertJsonPath('today.parcels_to_collect', 1)
            ->assertJsonPath('pickups.0.id', $pickup->id)
            ->assertJsonPath('pickups.0.seller_name', 'Laguna Gadgets')
            ->assertJsonPath('pickups.0.address', '123 Rizal St, Santa Cruz, Laguna')
            ->assertJsonCount(1, 'pickups');

        // The courier scans and accepts the parcel at the seller.
        Sanctum::actingAs($courier->user);
        $this->postJson('/api/rider/parcels/accept', ['code' => $shipment->tracking_code])->assertOk();

        $this->dashboard($courier)
            ->assertJsonPath('today.pickups_to_do', 0)
            ->assertJsonPath('today.in_vehicle', 1)
            ->assertJsonPath('today.collected_today', 1)
            ->assertJsonPath('in_vehicle.0.tracking_code', $shipment->tracking_code)
            ->assertJsonPath('in_vehicle.0.seller_name', 'Laguna Gadgets');

        // The hub receives it.
        $shipment->update(['received_at' => now()]);

        $this->dashboard($courier)
            ->assertJsonPath('today.in_vehicle', 0)
            ->assertJsonPath('today.handed_to_hub_today', 1)
            ->assertJsonPath('handed_to_hub.0.tracking_code', $shipment->tracking_code);
    }

    public function test_login_data_says_which_dashboard_to_open(): void
    {
        $courier = $this->courier();
        Sanctum::actingAs($courier->user);
        $this->getJson('/api/auth/me')->assertOk()->assertJsonPath('user.courier_type', Rider::FIRST_MILE);

        Sanctum::actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));
        $this->getJson('/api/auth/me')->assertJsonPath('user.courier_type', null);
        $this->getJson('/api/courier/dashboard')->assertForbidden();
    }
}
