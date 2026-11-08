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
use App\Services\BarcodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Riders scan a waybill barcode or QR code, see the parcel, then accept or decline it.
 * Accepting hands it over and notifies seller, buyer and hub.
 */
class RiderParcelScanTest extends TestCase
{
    use RefreshDatabase;

    private LogisticsProvider $hub;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $this->hub = LogisticsProvider::create(['user_id' => $user->id, 'name' => 'Metro South Hub', 'slug' => 'metro-south-hub', 'status' => 'approved']);
    }

    private function courier(string $type, string $name = 'Dante Ramos', ?LogisticsProvider $hub = null): Rider
    {
        $user = User::factory()->create(['role' => 'courier', 'status' => 'active', 'name' => $name]);

        return Rider::create(['user_id' => $user->id, 'logistics_provider_id' => ($hub ?? $this->hub)->id, 'courier_type' => $type,
            'vehicle_type' => 'Motorcycle', 'status' => 'approved', 'is_active' => true, 'area' => 'Laguna']);
    }

    private function parcel(array $shipment = []): Shipment
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Laguna Gadgets', 'slug' => 'laguna-gadgets-'.$sellerUser->id, 'status' => 'approved']);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'ORD-2026-'.(10000 + $buyer->id), 'total_minor' => 10000, 'payment_method' => 'cod', 'payment_status' => 'pending',
            'shipping_address' => ['recipient' => 'Juan Dela Cruz', 'address' => '45 Magsaysay St, Santa Cruz, Laguna', 'phone' => '09171234567']]);
        $sellerOrder = SellerOrder::create(['order_id' => $order->id, 'seller_id' => $seller->id, 'subtotal_minor' => 10000, 'shipping_fee_minor' => 0,
            'commission_minor' => 0, 'status' => 'ready_to_ship', 'logistics_provider_id' => $this->hub->id]);

        return Shipment::create($shipment + ['seller_order_id' => $sellerOrder->id, 'logistics_provider_id' => $this->hub->id,
            'tracking_code' => 'CTZ-SCAN'.$sellerOrder->id, 'status' => 'unassigned', 'fee_minor' => 0, 'cod_amount_minor' => 10000]);
    }

    private function riderPost(string $action, Rider $rider, string $code, array $extra = [])
    {
        Sanctum::actingAs($rider->user);

        return $this->postJson("/api/rider/parcels/{$action}", ['code' => $code] + $extra);
    }

    private function scan(Rider $rider, string $code)
    {
        return $this->riderPost('scan', $rider, $code);
    }

    private function accept(Rider $rider, string $code)
    {
        return $this->riderPost('accept', $rider, $code);
    }

    private function decline(Rider $rider, string $code, ?string $reason = null)
    {
        return $this->riderPost('decline', $rider, $code, $reason ? ['reason' => $reason] : []);
    }

    /** @return array<int, string> user id => notification title */
    private function notified(Shipment $parcel): array
    {
        $so = $parcel->sellerOrder()->with('order', 'seller')->first();

        return [
            'buyer' => AppNotification::where('user_id', $so->order->buyer_id)->pluck('title')->all(),
            'seller' => AppNotification::where('user_id', $so->seller->user_id)->pluck('title')->all(),
            'hub' => AppNotification::where('user_id', $this->hub->user_id)->pluck('title')->all(),
        ];
    }

    public function test_scanning_only_shows_the_parcel_and_changes_nothing(): void
    {
        $rider = $this->courier(Rider::FIRST_MILE);
        $parcel = $this->parcel();

        $this->scan($rider, $parcel->tracking_code)
            ->assertOk()
            ->assertJsonPath('stage', 'picked_up')
            ->assertJsonPath('already', false)
            ->assertJsonPath('parcel.tracking_code', $parcel->tracking_code)
            ->assertJsonPath('parcel.seller_name', 'Laguna Gadgets');

        $this->assertSame('unassigned', $parcel->fresh()->status);
        $this->assertNull($parcel->fresh()->pickup_rider_id);
        $this->assertSame(0, AppNotification::count());
        $this->assertSame(0, DeliveryEvent::count());
    }

    public function test_first_mile_accept_picks_up_the_parcel_and_notifies_seller_buyer_and_hub(): void
    {
        $rider = $this->courier(Rider::FIRST_MILE);
        $parcel = $this->parcel();

        $this->accept($rider, $parcel->tracking_code)
            ->assertOk()
            ->assertJsonPath('stage', 'picked_up')
            ->assertJsonPath('already', false)
            ->assertJsonPath('parcel.tracking_code', $parcel->tracking_code)
            ->assertJsonPath('parcel.recipient_name', 'Juan Dela Cruz');

        $parcel->refresh();
        $this->assertSame('picked_up', $parcel->status);
        $this->assertSame($rider->id, $parcel->pickup_rider_id);
        $this->assertSame('shipped', $parcel->sellerOrder->status);
        $this->assertDatabaseHas('delivery_events', ['shipment_id' => $parcel->id, 'status' => 'picked_up', 'user_id' => $rider->user_id]);
        $this->assertSame(['buyer' => ['Parcel picked up'], 'seller' => ['Parcel picked up'], 'hub' => ['Parcel picked up from seller']], $this->notified($parcel));
    }

    public function test_last_mile_accept_at_the_hub_sends_it_out_for_delivery_and_notifies_everyone(): void
    {
        $rider = $this->courier(Rider::LAST_MILE, 'Arnel Gomez');
        $parcel = $this->parcel(['status' => 'assigned', 'rider_id' => $rider->id, 'received_at' => now()->subHour()]);

        $this->accept($rider, $parcel->tracking_code)->assertOk()->assertJsonPath('stage', 'out_for_delivery');

        $this->assertSame('out_for_delivery', $parcel->fresh()->status);
        $notes = $this->notified($parcel);
        $this->assertSame(['Out for delivery'], $notes['buyer']);
        $this->assertSame(['Out for delivery'], $notes['seller']);
        $this->assertSame(['Parcel out for delivery'], $notes['hub']);
        $this->assertStringContainsString('Arnel Gomez', AppNotification::where('user_id', $parcel->sellerOrder->order->buyer_id)->value('message'));
    }

    public function test_qr_code_links_and_lowercase_codes_work_and_accepting_twice_does_not_notify_twice(): void
    {
        $rider = $this->courier(Rider::FIRST_MILE);
        $parcel = $this->parcel();

        $this->accept($rider, 'https://cartzy.shop/track/'.strtolower($parcel->tracking_code))->assertOk()->assertJsonPath('already', false);
        $this->accept($rider, $parcel->tracking_code)->assertOk()->assertJsonPath('already', true);
        $this->scan($rider, $parcel->tracking_code)->assertOk()->assertJsonPath('already', true);

        $this->assertSame(1, DeliveryEvent::where('shipment_id', $parcel->id)->count());
        $this->assertCount(1, $this->notified($parcel)['buyer']);
    }

    public function test_order_reference_works_for_a_single_store_order(): void
    {
        $rider = $this->courier(Rider::FIRST_MILE);
        $parcel = $this->parcel();

        $this->accept($rider, $parcel->sellerOrder->order->reference)->assertOk()->assertJsonPath('parcel.tracking_code', $parcel->tracking_code);
    }

    public function test_wrong_stage_wrong_rider_and_finished_parcels_are_refused_without_notifying(): void
    {
        $firstMile = $this->courier(Rider::FIRST_MILE);
        $lastMile = $this->courier(Rider::LAST_MILE, 'Arnel Gomez');
        $otherLastMile = $this->courier(Rider::LAST_MILE, 'Ben Cruz');
        $otherHubRider = $this->courier(Rider::FIRST_MILE, 'Other Hub', LogisticsProvider::create([
            'user_id' => User::factory()->create(['role' => 'logistics'])->id, 'name' => 'North Hub', 'slug' => 'north-hub', 'status' => 'approved']));

        $atSeller = $this->parcel();
        $atHub = $this->parcel(['received_at' => now(), 'status' => 'assigned', 'rider_id' => $lastMile->id]);
        $delivered = $this->parcel(['received_at' => now(), 'status' => 'delivered', 'rider_id' => $lastMile->id]);

        $this->accept($lastMile, $atSeller->tracking_code)->assertStatus(422)->assertJsonPath('message', "Parcel {$atSeller->tracking_code} hasn't reached the hub yet. A First Mile courier picks it up from the seller first.");
        $this->scan($firstMile, $atHub->tracking_code)->assertStatus(422);
        $this->scan($otherLastMile, $atHub->tracking_code)->assertStatus(422)->assertJsonPath('message', "Parcel {$atHub->tracking_code} is assigned to another rider.");
        $this->scan($otherHubRider, $atSeller->tracking_code)->assertStatus(422)->assertJsonPath('message', 'This parcel belongs to another logistics partner.');
        $this->accept($lastMile, $delivered->tracking_code)->assertStatus(422);
        $this->scan($firstMile, 'CTZ-NOPE')->assertNotFound();

        $this->assertSame(0, AppNotification::count());
        $this->assertSame('unassigned', $atSeller->fresh()->status);
    }

    public function test_declining_a_parcel_at_the_seller_changes_nothing_and_tells_no_one(): void
    {
        $rider = $this->courier(Rider::FIRST_MILE);
        $parcel = $this->parcel();

        $this->decline($rider, $parcel->tracking_code)
            ->assertOk()
            ->assertJsonPath('released', false)
            ->assertJsonPath('message', "You declined parcel {$parcel->tracking_code}. Nothing changed and no one was notified.");

        $this->assertSame('unassigned', $parcel->fresh()->status);
        $this->assertSame(0, AppNotification::count());
    }

    public function test_declining_a_parcel_the_hub_assigned_sends_it_back_and_tells_only_the_hub(): void
    {
        $rider = $this->courier(Rider::LAST_MILE, 'Arnel Gomez');
        $parcel = $this->parcel(['status' => 'assigned', 'rider_id' => $rider->id, 'sort_status' => 'assigned',
            'received_at' => now()->subHours(2), 'sorted_at' => now()->subHour(), 'assigned_at' => now()]);

        $this->decline($rider, $parcel->tracking_code, 'Motorcycle flat tire')->assertOk()->assertJsonPath('released', true);

        $parcel->refresh();
        $this->assertNull($parcel->rider_id);
        $this->assertSame('sorted', $parcel->sort_status);
        $this->assertNull($parcel->assigned_at);
        $notes = $this->notified($parcel);
        $this->assertSame([], $notes['buyer']);
        $this->assertSame([], $notes['seller']);
        $this->assertSame(['Rider declined a parcel'], $notes['hub']);
        $this->assertStringContainsString('Motorcycle flat tire', AppNotification::where('user_id', $this->hub->user_id)->value('message'));
    }

    public function test_a_parcel_already_accepted_cannot_be_declined(): void
    {
        $rider = $this->courier(Rider::FIRST_MILE);
        $parcel = $this->parcel();
        $this->accept($rider, $parcel->tracking_code)->assertOk();

        $this->decline($rider, $parcel->tracking_code)->assertStatus(422);
        $this->assertSame('picked_up', $parcel->fresh()->status);
    }

    public function test_only_approved_riders_can_scan(): void
    {
        $parcel = $this->parcel();
        $pending = $this->courier(Rider::FIRST_MILE);
        $pending->update(['status' => 'pending']);

        $this->scan($pending, $parcel->tracking_code)->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));
        $this->postJson('/api/rider/parcels/scan', ['code' => $parcel->tracking_code])->assertForbidden();
    }

    public function test_waybill_qr_code_is_a_real_qr_code_of_the_tracking_code(): void
    {
        $svg = BarcodeService::generateQrSvg('CTZ-ABC123XYZ9', 100);

        $this->assertStringStartsWith('<svg', $svg);
        $this->assertSame($svg, BarcodeService::generateQrSvg('CTZ-ABC123XYZ9', 100));
        $this->assertNotSame($svg, BarcodeService::generateQrSvg('CTZ-OTHER', 100));
    }
}
