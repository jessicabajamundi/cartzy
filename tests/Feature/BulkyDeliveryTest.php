<?php

namespace Tests\Feature;

use App\Models\{Address, Category, LogisticsProvider, Order, Product, ProductVariant, Rider, Seller, Shipment, User};
use App\Services\{CheckoutQuote, DeliveryAssignment, HubToHubService, OrderPlacement, ParcelDelivery, RiderDeliveries, RiderParcelScan, RiderParcelScanException};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BulkyDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private Address $address;
    private Seller $seller;
    private ProductVariant $variant;
    private LogisticsProvider $hub;

    protected function setUp(): void
    {
        parent::setUp();
        $place = ['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna', 'region' => 'IV-A'];
        $this->buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->address = Address::create(['user_id' => $this->buyer->id, 'label' => 'Home', 'recipient' => 'Buyer', 'phone' => '09171234567', 'line1' => '12 Test St', 'postal_code' => '4009', 'is_default' => true] + $place);
        $owner = User::factory()->create(['role' => 'seller', 'status' => 'active'] + $place);
        $pickup = Address::create(['user_id' => $owner->id, 'label' => 'Shop', 'recipient' => 'Seller', 'phone' => '09171234568', 'line1' => '10 Test St', 'postal_code' => '4009'] + $place);
        $this->seller = Seller::create(['user_id' => $owner->id, 'name' => 'Furniture', 'slug' => 'furniture', 'status' => 'approved', 'pickup_address_id' => $pickup->id]);
        $category = Category::create(['name' => 'Furniture', 'slug' => 'furniture', 'is_active' => true]);
        $product = Product::create(['seller_id' => $this->seller->id, 'category_id' => $category->id, 'name' => 'Couch', 'slug' => 'couch', 'approval_status' => 'approved', 'is_active' => true,
            'package_length_cm' => 220, 'package_width_cm' => 100, 'package_height_cm' => 90]);
        $this->variant = ProductVariant::create(['product_id' => $product->id, 'name' => 'Standard', 'sku' => 'COUCH', 'price_minor' => 100000, 'stock' => 10, 'weight_grams' => 80000, 'is_active' => true]);
        $this->hub = LogisticsProvider::create(['user_id' => User::factory()->create(['role' => 'logistics', 'status' => 'active'] + $place)->id,
            'name' => 'Laguna Hub', 'slug' => 'laguna', 'status' => 'approved', 'service_regions' => ['IV-A'], 'bulky_enabled' => true, 'bulky_surcharge_minor' => 35000]);
    }

    private function driver(string $vehicle, ?LogisticsProvider $hub = null): Rider
    {
        return Rider::create(['user_id' => User::factory()->create(['role' => 'courier', 'status' => 'active'])->id,
            'logistics_provider_id' => ($hub ?? $this->hub)->id, 'vehicle_type' => $vehicle,
            'courier_type' => $vehicle === 'motorcycle' ? 'last_mile' : 'linehaul', 'status' => 'approved', 'is_active' => true, 'online_until' => now()->addHour()]);
    }

    private function lines(int $quantity = 1): array { return [['variant_id' => $this->variant->id, 'quantity' => $quantity]]; }

    private function place(): Shipment
    {
        $order = app(OrderPlacement::class)->place($this->buyer, $this->lines(), 'COD', $this->address->formatted_address, address: $this->address);
        return $order->sellerOrders()->firstOrFail()->shipment;
    }

    public function test_couch_requires_van_or_truck_even_when_light(): void
    {
        $profile = app(ParcelDelivery::class)->profile($this->lines());
        $this->assertTrue($profile['bulky']);
        foreach (['motorcycle', 'tricycle', 'ebike', 'car'] as $vehicle) $this->assertFalse(app(ParcelDelivery::class)->fits($profile, $vehicle));
        $this->assertTrue(app(ParcelDelivery::class)->fits($profile, 'van'));
    }

    public function test_checkout_is_blocked_without_capable_fleet_and_leaves_stock_and_orders_untouched(): void
    {
        $this->driver('motorcycle');
        try { $this->place(); $this->fail('Oversized checkout must fail.'); }
        catch (\RuntimeException $error) { $this->assertStringContainsString('Bulky delivery is currently unavailable', $error->getMessage()); }
        $this->assertSame(10, $this->variant->fresh()->stock);
        $this->assertSame(0, Order::count());
    }

    public function test_quote_and_order_use_same_bulky_fee_and_snapshot(): void
    {
        $this->driver('van');
        $quote = app(CheckoutQuote::class)->build($this->buyer, $this->lines(), $this->address);
        $shipment = $this->place();
        $this->assertSame($quote['shipping_minor'], $shipment->fee_minor);
        $this->assertTrue($shipment->package_profile['bulky']);
        $this->assertSame(35000, $quote['delivery_services'][0]['surcharge_minor']);
        $this->variant->product->update(['package_length_cm' => 10]);
        $this->assertSame(220.0, (float) $shipment->fresh()->package_profile['max_dimensions_cm'][0]);
    }

    public function test_missing_measurements_block_even_standard_checkout(): void
    {
        $this->variant->product->update(['package_height_cm' => null]);
        $this->expectExceptionMessage('seller must add packaged weight and dimensions');
        app(CheckoutQuote::class)->build($this->buyer, $this->lines(), $this->address);
    }

    public function test_quantity_and_volume_can_require_a_larger_vehicle(): void
    {
        $profile = app(ParcelDelivery::class)->profile($this->lines(4));
        $this->assertFalse(app(ParcelDelivery::class)->fits($profile, 'van'));
        $this->assertTrue(app(ParcelDelivery::class)->fits($profile, 'truck'));
    }

    public function test_out_of_coverage_and_disabled_hubs_are_blocked(): void
    {
        $this->driver('truck');
        $this->address->update(['region' => 'XI', 'province' => 'Davao del Sur', 'city' => 'Davao City']);
        $this->expectExceptionMessage('Bulky delivery is currently unavailable');
        $this->place();
    }

    public function test_manual_assignment_and_scan_cannot_force_couch_onto_motorcycle(): void
    {
        $van = $this->driver('van');
        $motorcycle = $this->driver('motorcycle');
        $shipment = $this->place();
        $shipment->update(['received_at' => now(), 'status' => 'unassigned']);
        $shipment->sellerOrder->update(['status' => 'shipped']);
        $this->assertSame([$van->id], app(DeliveryAssignment::class)->candidates($shipment)->pluck('rider.id')->all());
        try { app(DeliveryAssignment::class)->assign($shipment, $motorcycle->id); $this->fail('Assignment must fail.'); }
        catch (ValidationException $error) { $this->assertNotEmpty($error->errors()); }
        $this->expectException(RiderParcelScanException::class);
        app(RiderParcelScan::class)->accept($motorcycle, $shipment->tracking_code);
    }

    public function test_van_can_complete_bulky_pickup_and_buyer_delivery(): void
    {
        $van = $this->driver('van');
        $shipment = $this->place();
        $shipment->update(['pickup_rider_id' => $van->id]);
        app(RiderParcelScan::class)->accept($van, $shipment->tracking_code);
        $this->assertSame('picked_up', $shipment->fresh()->status);
        $shipment->update(['received_at' => now(), 'status' => 'unassigned']);
        app(DeliveryAssignment::class)->assign($shipment, $van->id);
        \Laravel\Sanctum\Sanctum::actingAs($van->user);
        $this->getJson('/api/rider/deliveries')->assertOk()->assertJsonPath('deliveries.0.package_profile.bulky', true);
        app(RiderParcelScan::class)->accept($van, $shipment->tracking_code);
        $this->assertSame('out_for_delivery', $shipment->fresh()->status);
        app(RiderDeliveries::class)->deliver($van, $shipment->id, $shipment->tracking_code, 'Buyer', null);
        $this->assertSame('delivered', $shipment->fresh()->status);
    }

    public function test_bulk_capacity_checks_include_other_reserved_parcels(): void
    {
        $van = $this->driver('van');
        $first = $this->place();
        $second = $this->place();
        $third = $this->place();
        foreach ([$first, $second, $third] as $shipment) {
            $shipment->update(['received_at' => now(), 'status' => 'unassigned']);
            $shipment->sellerOrder->update(['status' => 'shipped']);
        }
        app(DeliveryAssignment::class)->assign($first, $van->id);
        app(DeliveryAssignment::class)->assign($second, $van->id);
        $this->assertEmpty(app(DeliveryAssignment::class)->candidates($third));
    }

    public function test_web_and_mobile_checkout_show_unavailable_delivery_without_creating_an_order(): void
    {
        \Laravel\Sanctum\Sanctum::actingAs($this->buyer);
        $this->postJson('/api/checkout/quote', ['address_id' => $this->address->id, 'items' => $this->lines()])
            ->assertStatus(422)->assertJsonPath('message', 'Bulky delivery is currently unavailable for this item at your address. A capable van/truck partner is required at both ends of the route.');
        $this->postJson('/api/orders', ['checkout_key' => 'bulky-blocked', 'payment_method' => 'COD', 'address_id' => $this->address->id, 'items' => $this->lines()])->assertStatus(422);
        $this->mock(\App\Services\BuyerCart::class)->shouldReceive('all')->andReturn(['variant_'.$this->variant->id => [
            'variant_id' => $this->variant->id, 'product_id' => $this->variant->product_id, 'id' => 'variant_'.$this->variant->id,
            'name' => 'Couch', 'price' => 1000, 'quantity' => 1, 'image' => '/couch.jpg', 'selected' => true]]);
        $this->actingAs($this->buyer, 'web')->get(route('checkout'))->assertOk()
            ->assertSee('Bulky delivery is currently unavailable')->assertSee('class="checkout-submit btn-blend" disabled', false);
        $this->assertSame(0, Order::count());
    }

    public function test_disabled_bulky_hub_cannot_take_new_orders(): void
    {
        $this->driver('truck');
        $this->hub->update(['bulky_enabled' => false]);
        $this->expectExceptionMessage('Bulky delivery is currently unavailable');
        $this->place();
    }

    public function test_hub_to_hub_couch_uses_compatible_vehicles_at_both_ends(): void
    {
        $originDriver = $this->driver('van');
        $target = LogisticsProvider::create(['user_id' => User::factory()->create(['role' => 'logistics', 'status' => 'active', 'city' => 'Pasig', 'province' => 'Metro Manila', 'region' => 'NCR'])->id,
            'name' => 'Manila Hub', 'slug' => 'manila', 'status' => 'approved', 'service_regions' => ['NCR'], 'bulky_enabled' => true, 'bulky_surcharge_minor' => 20000]);
        $targetDriver = $this->driver('truck', $target);
        $this->address->update(['region' => 'NCR', 'province' => 'Metro Manila', 'city' => 'Pasig', 'barangay' => 'San Antonio']);
        $quote = app(CheckoutQuote::class)->build($this->buyer, $this->lines(), $this->address);
        $this->assertSame(55000, $quote['delivery_services'][0]['surcharge_minor']);
        $shipment = $this->place();
        $this->assertSame($target->id, $shipment->destination_hub_id);
        $shipment->update(['received_at' => now(), 'status' => 'unassigned']);
        $shipment->sellerOrder->update(['status' => 'shipped']);
        $this->assertNull(app(DeliveryAssignment::class)->assign($shipment));
        app(HubToHubService::class)->dispatchToDestinationHub($shipment, $originDriver->id);
        $this->assertSame('in_transit', $shipment->fresh()->status);
        app(HubToHubService::class)->receiveAtDestinationHub($shipment->fresh(), $target->user_id);
        $shipment = $shipment->fresh();
        $this->assertSame($target->id, $shipment->logistics_provider_id);
        app(DeliveryAssignment::class)->assign($shipment, $targetDriver->id);
        app(RiderParcelScan::class)->accept($targetDriver, $shipment->tracking_code);
        app(RiderDeliveries::class)->deliver($targetDriver, $shipment->id, $shipment->tracking_code, 'Buyer', null);
        $this->assertSame('delivered', $shipment->fresh()->status);
    }

    public function test_linehaul_cannot_use_an_undersized_car(): void
    {
        $this->driver('van');
        $car = $this->driver('car');
        $shipment = $this->place();
        $target = LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Other', 'slug' => 'other', 'status' => 'approved', 'bulky_enabled' => true]);
        $shipment->update(['destination_hub_id' => $target->id, 'received_at' => now(), 'status' => 'unassigned']);
        $this->expectException(ValidationException::class);
        app(HubToHubService::class)->dispatchToDestinationHub($shipment, $car->id);
    }
}
