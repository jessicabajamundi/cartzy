<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, Order, Rider, Seller, SellerOrder, Shipment, User};
use App\Services\{DeliveryAssignment, RiderAttendanceService};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeliveryAssignmentTest extends TestCase
{
    use RefreshDatabase;

    private function hub(): LogisticsProvider
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        return LogisticsProvider::create(['user_id' => $user->id, 'name' => 'Hub '.$user->id, 'slug' => 'hub-'.$user->id, 'status' => 'approved']);
    }

    private function rider(LogisticsProvider $hub, string $barangay = 'Bubukal', array $attributes = []): Rider
    {
        $user = User::factory()->create(['role' => 'courier', 'status' => 'active', 'barangay' => $barangay, 'city' => 'Santa Cruz', 'province' => 'Laguna']);
        return Rider::create($attributes + ['user_id' => $user->id, 'logistics_provider_id' => $hub->id,
            'courier_type' => Rider::LAST_MILE, 'status' => 'approved', 'is_active' => true, 'online_until' => now()->addHours(1)]);
    }

    private function parcel(LogisticsProvider $hub, array $shipping = []): Shipment
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Shop', 'slug' => 'shop-'.$sellerUser->id, 'status' => 'approved']);
        $order = Order::create(['buyer_id' => User::factory()->create()->id, 'reference' => 'TEST-'.$seller->id,
            'total_minor' => 10000, 'payment_method' => 'cod', 'payment_status' => 'pending',
            'shipping_address' => $shipping ?: ['brgy' => 'Brgy. Bubukal', 'city' => 'Sta. Cruz', 'province' => 'Laguna']]);
        $so = SellerOrder::create(['seller_id' => $seller->id, 'order_id' => $order->id, 'subtotal_minor' => 10000, 'commission_minor' => 0, 'status' => 'ready_to_ship']);
        return Shipment::create(['seller_order_id' => $so->id, 'logistics_provider_id' => $hub->id,
            'tracking_code' => 'CTZ-TEST-'.$so->id, 'status' => 'unassigned', 'received_at' => now(), 'sort_status' => 'sorted']);
    }

    public function test_barangay_priority_fallback_eligibility_and_duplicate_assignment(): void
    {
        $hub = $this->hub();
        $fallback = $this->rider($hub, 'Poblacion');
        $primary = $this->rider($hub);
        $this->rider($hub, 'Bubukal', ['courier_type' => Rider::FIRST_MILE]);
        $this->rider($hub, 'Bubukal', ['is_active' => false]);
        $this->rider($hub, 'Bubukal', ['status' => 'pending']);
        $suspended = $this->rider($hub);
        $suspended->user->update(['is_suspended' => true]);
        $this->rider($this->hub());
        $parcel = $this->parcel($hub);
        $service = app(DeliveryAssignment::class);
        $this->assertSame($primary->id, $service->assign($parcel)->rider_id);
        $service->assign($parcel);
        $this->assertSame(1, $parcel->deliveryEvents()->count());
        $this->assertStringContainsString('(barangay)', $parcel->deliveryEvents()->first()->note);
        $primary->update(['online_until' => now()->subSecond()]);
        $second = $this->parcel($hub);
        $this->assertSame($fallback->id, $service->assign($second)->rider_id);
        $this->assertStringContainsString('(fallback)', $second->deliveryEvents()->first()->note);
        $fallback->update(['online_until' => null]);
        $third = $this->parcel($hub);
        $this->assertNull($service->assign($third));
        $this->assertNull($third->fresh()->rider_id);
    }

    public function test_designated_barangay_and_legacy_address_are_supported(): void
    {
        $hub = $this->hub();
        $this->rider($hub, 'Poblacion');
        $designated = $this->rider($hub, 'Other', ['designated_place' => ['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna']]);
        $parcel = $this->parcel($hub, ['address' => '45 Main Street, Bubukal, Santa Cruz, Laguna']);
        $this->assertSame($designated->id, app(DeliveryAssignment::class)->assign($parcel)->rider_id);
    }

    public function test_manual_override_is_scoped_online_only_audited_and_preserved(): void
    {
        $hub = $this->hub();
        $primary = $this->rider($hub);
        $other = $this->rider($hub, 'Poblacion');
        $offline = $this->rider($hub, 'Bubukal', ['online_until' => null]);
        $foreignHub = $this->hub();
        $foreign = $this->rider($foreignHub);
        $parcel = $this->parcel($hub);
        Sanctum::actingAs($hub->user);
        $base = '/api/logistics/shipments/'.$parcel->id;
        $this->getJson($base.'/riders')->assertOk()->assertJsonPath('recommended_rider_id', $primary->id)->assertJsonCount(2, 'riders');
        $this->postJson($base.'/auto-assign')->assertOk()->assertJsonPath('shipment.rider_id', $primary->id);
        $this->putJson($base.'/rider', ['rider_id' => $other->id])->assertOk()->assertJsonPath('shipment.rider_id', $other->id);
        $this->postJson($base.'/auto-assign')->assertJsonPath('shipment.rider_id', $other->id);
        $this->assertDatabaseHas('delivery_events', ['shipment_id' => $parcel->id, 'user_id' => $hub->user_id,
            'note' => "Assignment (manual): rider #{$other->id}; previous rider: {$primary->id}."]);
        foreach ([$offline, $foreign] as $invalid) {
            $this->putJson($base.'/rider', ['rider_id' => $invalid->id])->assertUnprocessable();
        }
        Sanctum::actingAs($foreignHub->user);
        $this->getJson($base.'/riders')->assertNotFound();
        $this->putJson($base.'/rider', ['rider_id' => $foreign->id])->assertNotFound();
        Sanctum::actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));
        $this->postJson($base.'/auto-assign')->assertForbidden();
        Sanctum::actingAs(User::factory()->create(['role' => 'admin', 'status' => 'active']));
        $this->putJson($base.'/rider', ['rider_id' => $primary->id])->assertOk();
        foreach (['out_for_delivery', 'delivered', 'cancelled', 'returned'] as $status) {
            $parcel->update(['status' => $status]);
            $this->putJson($base.'/rider', ['rider_id' => $other->id])->assertUnprocessable();
        }
    }

    public function test_receipt_assigns_automatically_and_online_transition_retries_waiting_parcels(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub);
        $parcel = $this->parcel($hub);
        $parcel->update(['received_at' => null, 'sort_status' => 'unsorted']);
        $this->actingAs($hub->user)->post(route('logistics.parcels.receive', $parcel->id))->assertRedirect();
        $this->assertSame($rider->id, $parcel->fresh()->rider_id);
        $this->get(route('logistics.hub', ['tab' => 'assigned']))->assertOk()->assertSee('Change rider');
        $rider->update(['online_until' => null]);
        $waiting = $this->parcel($hub);
        Sanctum::actingAs($rider->user);
        $this->putJson('/api/rider/availability', ['status' => 'online'])->assertOk();
        $this->assertSame($rider->id, $waiting->fresh()->rider_id);
        $this->putJson('/api/rider/availability', ['status' => 'offline'])->assertOk();
        $this->assertFalse(Rider::availableForDelivery()->whereKey($rider->id)->exists());
    }

    public function test_time_in_and_out_control_online_availability(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub, 'Bubukal', ['online_until' => null]);
        $parcel = $this->parcel($hub);
        $attendance = app(RiderAttendanceService::class);
        $attendance->scan($rider, $attendance->code($hub));
        $this->assertSame($rider->id, $parcel->fresh()->rider_id);
        $this->assertTrue(Rider::availableForDelivery()->whereKey($rider->id)->exists());
        $this->travel(30)->seconds();
        $attendance->scan($rider, $attendance->code($hub));
        $this->assertNull($rider->fresh()->online_until);
    }

    public function test_batch_balances_equal_matches_and_skips_unreceived_or_cancelled_parcels(): void
    {
        $hub = $this->hub();
        $a = $this->rider($hub);
        $b = $this->rider($hub);
        $first = $this->parcel($hub);
        $second = $this->parcel($hub);
        $notReceived = $this->parcel($hub);
        $notReceived->update(['received_at' => null]);
        $cancelled = $this->parcel($hub);
        $cancelled->sellerOrder->update(['status' => 'cancelled']);
        $results = app(DeliveryAssignment::class)->assignWaiting($hub->id);
        $this->assertCount(2, $results);
        $this->assertSame($a->id, $first->fresh()->rider_id);
        $this->assertSame($b->id, $second->fresh()->rider_id);
        $this->assertNull($notReceived->fresh()->rider_id);
        $this->assertNull($cancelled->fresh()->rider_id);
    }

    public function test_designation_api_and_web_override_use_the_same_authorization_and_availability(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub, 'Other');
        $other = $this->rider($hub, 'Poblacion');
        $foreign = $this->rider($this->hub());
        $place = ['barangay' => 'Bubukal', 'city' => 'Santa Cruz', 'province' => 'Laguna'];
        Sanctum::actingAs($hub->user);
        $this->putJson('/api/logistics/riders/'.$rider->id.'/designation', $place)->assertOk();
        $this->putJson('/api/logistics/riders/'.$foreign->id.'/designation', $place)->assertNotFound();
        $parcel = $this->parcel($hub);
        $this->assertSame($rider->id, app(DeliveryAssignment::class)->assign($parcel)->rider_id);
        $this->actingAs($hub->user, 'web')->post(route('logistics.assignments.assign', $parcel->id), ['rider_id' => $other->id])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($other->id, $parcel->fresh()->rider_id);
        $rider->update(['online_until' => null]);
        $this->post(route('logistics.assignments.assign', $parcel->id), ['rider_id' => $rider->id])->assertSessionHasErrors('rider_id');
        $this->assertSame($other->id, $parcel->fresh()->rider_id);
    }
}
