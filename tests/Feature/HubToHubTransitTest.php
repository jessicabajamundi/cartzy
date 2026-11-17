<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use App\Services\DeliveryAssignment;
use App\Services\HubToHubService;
use App\Services\OrderTracking;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class HubToHubTransitTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_region_parcel_routes_from_origin_hub_via_courier_to_destination_hub_then_last_mile_rider(): void
    {
        // SQLite does not enforce Laravel enum columns. Reproduce production MySQL restrictions.
        if (DB::getDriverName() === 'sqlite') {
            DB::unprepared("CREATE TRIGGER enforce_shipment_sort_status BEFORE UPDATE OF sort_status ON shipments
                WHEN NEW.sort_status NOT IN ('unsorted', 'sorted', 'assigned')
                BEGIN SELECT RAISE(ABORT, 'Invalid shipment sort_status'); END");
            DB::unprepared("CREATE TRIGGER enforce_shipment_status BEFORE UPDATE OF status ON shipments
                WHEN NEW.status NOT IN ('unassigned', 'assigned', 'picked_up', 'in_transit', 'out_for_delivery', 'delivered', 'failed', 'returned')
                BEGIN SELECT RAISE(ABORT, 'Invalid shipment status'); END");
        }
        // 1. Create Visayas Hub & Laguna Hub
        $visayasHubUser = User::factory()->create([
            'role' => 'logistics',
            'status' => 'active',
            'region' => 'VII',
            'province' => 'Cebu',
            'city' => 'Cebu City',
            'barangay' => 'Lahug',
        ]);
        $visayasHub = LogisticsProvider::create([
            'user_id' => $visayasHubUser->id,
            'name' => 'Visayas Sorting Hub',
            'slug' => 'visayas-hub',
            'status' => 'approved',
            'service_regions' => ['VII'],
        ]);

        $lagunaHubUser = User::factory()->create([
            'role' => 'logistics',
            'status' => 'active',
            'region' => 'IV-A',
            'province' => 'Laguna',
            'city' => 'Santa Cruz',
            'barangay' => 'Bubukal',
        ]);
        $lagunaHub = LogisticsProvider::create([
            'user_id' => $lagunaHubUser->id,
            'name' => 'Laguna Fulfillment Hub',
            'slug' => 'laguna-hub',
            'status' => 'approved',
            'service_regions' => ['IV-A'],
        ]);

        // 2. Create Linehaul Courier (Visayas -> Laguna) & Laguna Last-Mile Rider
        $courierUser = User::factory()->create(['role' => 'courier', 'status' => 'active']);
        $linehaulCourier = Rider::create([
            'user_id' => $courierUser->id,
            'logistics_provider_id' => $visayasHub->id,
            'courier_type' => Rider::LINEHAUL,
            'vehicle_type' => 'van',
            'plate_no' => 'LH-101',
            'status' => 'approved',
            'is_active' => true,
            'online_until' => now()->addHours(8),
        ]);

        $lagunaRiderUser = User::factory()->create([
            'role' => 'courier',
            'status' => 'active',
            'region' => 'IV-A',
            'province' => 'Laguna',
            'city' => 'Santa Cruz',
            'barangay' => 'Bubukal',
        ]);
        $lagunaRider = Rider::create([
            'user_id' => $lagunaRiderUser->id,
            'logistics_provider_id' => $lagunaHub->id,
            'courier_type' => Rider::LAST_MILE,
            'vehicle_type' => 'motorcycle',
            'plate_no' => 'LM-202',
            'status' => 'approved',
            'is_active' => true,
            'online_until' => now()->addHours(8),
        ]);

        // 3. Create Visayas Seller & Laguna Buyer
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name' => 'Cebu Crafts Store',
            'slug' => 'cebu-crafts',
            'status' => 'approved',
        ]);

        $buyer = User::factory()->create([
            'role' => 'buyer',
            'status' => 'active',
            'region' => 'IV-A',
            'province' => 'Laguna',
            'city' => 'Santa Cruz',
            'barangay' => 'Bubukal',
        ]);
        $buyerAddress = Address::create([
            'user_id' => $buyer->id,
            'label' => 'Home',
            'recipient' => $buyer->name,
            'phone' => '09171234567',
            'region' => 'IV-A',
            'province' => 'Laguna',
            'city' => 'Santa Cruz',
            'barangay' => 'Bubukal',
            'line1' => '123 Rizal St',
            'postal_code' => '4009',
            'is_default' => true,
        ]);

        // Resolve destination hub via HubToHubService
        $resolvedDestId = app(HubToHubService::class)->resolveDestinationHub($buyerAddress, $visayasHub->id);
        $this->assertSame($lagunaHub->id, $resolvedDestId);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'reference' => 'ORD-2026-9001',
            'total_minor' => 115000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => [
                'recipient' => $buyer->name,
                'phone' => '09171234567',
                'address' => '123 Rizal St, Bubukal, Santa Cruz, Laguna',
                'barangay' => 'Bubukal',
            ],
        ]);

        $sellerOrder = SellerOrder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'logistics_provider_id' => $visayasHub->id,
            'subtotal_minor' => 100000,
            'shipping_fee_minor' => 15000,
            'commission_minor' => 10000,
            'status' => 'packed',
        ]);

        $shipment = Shipment::create([
            'seller_order_id' => $sellerOrder->id,
            'logistics_provider_id' => $visayasHub->id,
            'origin_hub_id' => $visayasHub->id,
            'destination_hub_id' => $lagunaHub->id,
            'current_hub_id' => $visayasHub->id,
            'tracking_code' => 'CTZ-H2H001',
            'status' => 'picked_up',
            'fee_minor' => 15000,
            'cod_amount_minor' => 115000,
        ]);

        // 4. Visayas Hub receives parcel; local Last-Mile assignment is blocked until it reaches Laguna Hub
        $this->actingAs($visayasHubUser)
            ->post(route('logistics.parcels.receive', $shipment->id))
            ->assertRedirect();

        $shipment->refresh();
        $this->assertNotNull($shipment->received_at);
        $this->assertNull(app(DeliveryAssignment::class)->assign($shipment));

        // 5. Visayas Hub dispatches parcel to Linehaul Courier
        $sortStatusBeforeDispatch = $shipment->sort_status;
        $this->actingAs($visayasHubUser)
            ->post(route('logistics.hub.dispatch', $shipment->id), [
                'courier_id' => $linehaulCourier->id,
                'destination_hub_id' => $lagunaHub->id,
            ])
            ->assertRedirect()->assertSessionHasNoErrors();

        $shipment->refresh();
        $this->assertSame('in_transit', $shipment->status);
        $this->assertSame($sortStatusBeforeDispatch, $shipment->sort_status);
        $this->assertSame($linehaulCourier->id, $shipment->linehaul_courier_id);
        $this->assertTrue($shipment->isInHubToHubTransit());

        // 6. Linehaul Courier sees it in API dashboard and completes handover at Laguna Hub
        Sanctum::actingAs($courierUser);
        $this->getJson('/api/courier/dashboard')
            ->assertOk()
            ->assertJsonPath('linehaul_transfers.0.tracking_code', 'CTZ-H2H001')
            ->assertJsonPath('linehaul_transfers.0.is_in_transit', true);

        $this->postJson("/api/courier/linehaul/{$shipment->id}/arrive")
            ->assertOk();

        $shipment->refresh();
        $sellerOrder->refresh();
        $this->assertSame($lagunaHub->id, $shipment->current_hub_id);
        $this->assertSame($lagunaHub->id, $shipment->logistics_provider_id);
        $this->assertSame($lagunaHub->id, $sellerOrder->logistics_provider_id);
        $this->assertNotNull($shipment->arrived_at_destination_hub_at);
        $this->assertSame('unassigned', $shipment->status);
        $this->assertSame('unsorted', $shipment->sort_status);

        // 7. OrderTracking includes Hub-to-Hub milestones
        $tracking = app(OrderTracking::class)->build($sellerOrder->fresh(['shipment.deliveryEvents', 'shipment.originHub', 'shipment.destinationHub', 'order']));
        $labels = collect($tracking['steps'])->pluck('label')->all();
        $this->assertContains('In Hub-to-Hub transit', $labels);
        $this->assertContains('Arrived at destination hub', $labels);

        // 8. Laguna Hub sorts & assigns local Laguna Last-Mile Rider
        $assigned = app(DeliveryAssignment::class)->assign($shipment->fresh());
        $this->assertNotNull($assigned);
        $this->assertSame($lagunaRider->id, $assigned->rider_id);
        $this->assertSame('assigned', $assigned->status);
    }
}
