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

class CourierRiderHubTest extends TestCase
{
    use RefreshDatabase;

    private function createCourierRider(): array
    {
        $logisticsUser = User::factory()->create([
            'role'   => 'logistics',
            'status' => 'active',
        ]);

        $provider = LogisticsProvider::create([
            'user_id' => $logisticsUser->id,
            'name'    => 'Cartzy Express Logistics',
            'slug'    => 'cartzy-express-logistics',
            'code'    => 'CARTZY_EXP',
            'status'  => 'approved',
        ]);

        $courier = User::factory()->create([
            'name'              => 'Arnel Gomez',
            'email'             => 'arnel.rider@cartzy.ph',
            'role'              => 'courier',
            'status'            => 'active',
            'email_verified_at' => now(),
            'phone'             => '0917-889-4812',
        ]);

        $rider = Rider::create([
            'user_id'               => $courier->id,
            'logistics_provider_id' => $provider->id,
            'vehicle_type'          => 'Motorcycle',
            'plate_no'              => 'NQ-4812',
            'license_no'            => 'D02-18-984712',
            'status'                => 'approved',
            'is_active'             => true,
            'rating'                => 4.95,
            'area'                  => 'Metro South Sector',
            'deliveries_today'      => 4,
        ]);

        return [$courier, $rider, $provider];
    }

    public function test_courier_dashboard_renders_successfully(): void
    {
        [$courier] = $this->createCourierRider();

        $response = $this->actingAs($courier)->get(route('courier.dashboard'));
        $response->assertOk()
            ->assertSee('Rider Command Center')
            ->assertSee('Arnel Gomez')
            ->assertSee('NQ-4812');
    }

    public function test_courier_pickups_page_and_accept_action(): void
    {
        [$courier, $rider, $provider] = $this->createCourierRider();

        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name'    => 'TechnoHub Manila',
            'slug'    => 'technohub-manila',
            'status'  => 'approved',
        ]);

        $pickup = PickupRequest::create([
            'logistics_provider_id' => $provider->id,
            'seller_id'             => $seller->id,
            'parcel_count'          => 3,
            'pickup_date'           => now()->toDateString(),
            'address'               => 'Warehouse 4, Ortigas Ave, Pasig',
            'notes'                 => 'Fragile electronics',
            'status'                => 'pending',
        ]);

        $response = $this->actingAs($courier)->get(route('courier.pickups'));
        $response->assertOk()
            ->assertSee('TechnoHub Manila')
            ->assertSee('Warehouse 4, Ortigas Ave, Pasig');

        $acceptResponse = $this->actingAs($courier)->post(route('courier.pickups.accept', $pickup->id));
        $acceptResponse->assertRedirect();

        $pickup->refresh();
        $this->assertEquals('confirmed', $pickup->status);
    }

    public function test_courier_deliveries_page_and_deliver_action(): void
    {
        [$courier, $rider, $provider] = $this->createCourierRider();

        $buyer = User::factory()->create(['role' => 'buyer']);
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name'    => 'Zen Garden Organics',
            'slug'    => 'zen-garden-organics',
            'status'  => 'approved',
        ]);

        $order = Order::create([
            'buyer_id'         => $buyer->id,
            'reference'        => 'ORD-TEST-001',
            'subtotal_minor'   => 120000,
            'shipping_fee_minor' => 8500,
            'total_minor'      => 128500,
            'payment_method'   => 'cod',
            'payment_status'   => 'pending',
            'shipping_address' => [
                'recipient'     => 'Danilo Cruz',
                'phone'         => '0918-123-4567',
                'address_line1' => 'Unit 12B, Emerald Tower, Alabang',
                'city'          => 'Muntinlupa',
            ],
            'status'           => 'processing',
        ]);

        $sellerOrder = SellerOrder::create([
            'order_id'           => $order->id,
            'seller_id'          => $seller->id,
            'subtotal_minor'     => 120000,
            'shipping_fee_minor' => 8500,
            'total_minor'        => 128500,
            'status'             => 'shipped',
        ]);

        $shipment = Shipment::create([
            'seller_order_id'       => $sellerOrder->id,
            'logistics_provider_id' => $provider->id,
            'rider_id'              => $rider->id,
            'tracking_code'         => 'CTZ-TEST-9988',
            'status'                => 'out_for_delivery',
            'cod_amount_minor'      => 128500,
            'cod_collected'         => false,
        ]);

        $response = $this->actingAs($courier)->get(route('courier.deliveries'));
        $response->assertOk()
            ->assertSee('CTZ-TEST-9988')
            ->assertSee('Danilo Cruz');

        $deliverResponse = $this->actingAs($courier)->post(route('courier.deliver', $shipment->id), [
            'recipient_name' => 'Danilo Cruz',
            'note'           => 'Received in good condition',
        ]);

        $deliverResponse->assertRedirect();

        $shipment->refresh();
        $this->assertEquals('delivered', $shipment->status);
        $this->assertTrue($shipment->cod_collected);

        $order->refresh();
        $this->assertEquals('paid', $order->payment_status);
    }

    public function test_courier_earnings_page(): void
    {
        [$courier] = $this->createCourierRider();

        $response = $this->actingAs($courier)->get(route('courier.earnings'));
        $response->assertOk()
            ->assertSee('Payout')
            ->assertSee('COD Cash On Hand');
    }

    public function test_courier_history_page(): void
    {
        [$courier] = $this->createCourierRider();

        $response = $this->actingAs($courier)->get(route('courier.history'));
        $response->assertOk()
            ->assertSee('Completed Deliveries')
            ->assertSee('Delivery Records');
    }

    public function test_courier_chat_page(): void
    {
        [$courier] = $this->createCourierRider();

        $response = $this->actingAs($courier)->get(route('courier.chat'));
        $response->assertOk()
            ->assertSee('Cartzy Central Dispatch');
    }

    public function test_courier_account_page_and_status_toggle(): void
    {
        [$courier, $rider] = $this->createCourierRider();

        $response = $this->actingAs($courier)->get(route('courier.account'));
        $response->assertOk()
            ->assertSee('Rider Account Management')
            ->assertSee('Fully Verified Courier')
            ->assertSee('Arnel Gomez');

        $toggleResponse = $this->actingAs($courier)->post(route('courier.account.toggle-status'));
        $toggleResponse->assertRedirect();

        $rider->refresh();
        $this->assertFalse($rider->is_active);
    }
}
