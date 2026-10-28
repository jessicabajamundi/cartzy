<?php

namespace Tests\Feature;

use App\Models\LogisticsProvider;
use App\Models\PickupRequest;
use App\Models\Rider;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogisticsPortalTest extends TestCase
{
    use RefreshDatabase;

    private function getLogisticsUser(): User
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        return User::where('email', 'logistics@cartzy.ph')->first()
            ?? User::where('role', 'logistics')->first();
    }

    public function test_logistics_dashboard_loads(): void
    {
        $user = $this->getLogisticsUser();
        $response = $this->actingAs($user)->get(route('logistics.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Logistics Hub Dashboard');
        $response->assertSee('OPERATIONS DATE FILTER');
    }

    public function test_logistics_dashboard_date_filter_boundary_value_analysis(): void
    {
        $user = $this->getLogisticsUser();

        // 1. Filter today (preset)
        $responseToday = $this->actingAs($user)->get(route('logistics.dashboard', ['preset' => 'today']));
        $responseToday->assertOk()
            ->assertSee('OPERATIONS DATE FILTER')
            ->assertSee('Today');

        // 2. Filter a boundary range that starts and ends on a specific shipment date
        $targetDate = '2026-10-11';
        $responseBoundary = $this->actingAs($user)->get(route('logistics.dashboard', [
            'start_date' => $targetDate,
            'end_date'   => $targetDate,
        ]));
        $responseBoundary->assertOk()
            ->assertViewHas('activePreset', 'custom')
            ->assertViewHas('filterLabel', 'Oct 11, 2026')
            ->assertSee('Oct 11, 2026')
            ->assertSee('Reset');

        // 3. Multi-day date range
        $responseMulti = $this->actingAs($user)->get(route('logistics.dashboard', [
            'start_date' => '2026-10-01',
            'end_date'   => '2026-10-16',
        ]));
        $responseMulti->assertOk()
            ->assertViewHas('activePreset', 'custom')
            ->assertViewHas('filterLabel', 'Oct 01, 2026 – Oct 16, 2026');
    }

    public function test_logistics_riders_page_loads_and_updates(): void
    {
        $user = $this->getLogisticsUser();
        $response = $this->actingAs($user)->get(route('logistics.riders'));
        $response->assertStatus(200);
        $response->assertSee('Rider Management');

        $rider = Rider::first();
        if ($rider) {
            $postResp = $this->actingAs($user)->post(route('logistics.riders.status', $rider->id), [
                'status' => 'approved',
            ]);
            $postResp->assertRedirect();
            $this->assertDatabaseHas('riders', ['id' => $rider->id, 'status' => 'approved']);
        }
    }

    public function test_rider_directory_search_counts_and_inactive_filter(): void
    {
        $user = $this->getLogisticsUser();
        $provider = $user->logisticsProvider;
        $rider = Rider::where('logistics_provider_id', $provider->id)->firstOrFail();
        $rider->update(['area' => 'Unique Rider Search Area', 'status' => 'pending', 'is_active' => false]);
        $total = Rider::where('logistics_provider_id', $provider->id)->count();

        $this->actingAs($user)->get(route('logistics.riders', ['search' => 'Unique Rider Search']))
            ->assertOk()
            ->assertViewHas('riders', fn ($items) => $items->total() === 1 && $items->first()['id'] === $rider->id)
            ->assertViewHas('counts', fn ($counts) => $counts['all'] === $total);
        $this->get(route('logistics.riders', ['search' => 'Unique Rider Search', 'filter' => 'inactive']))
            ->assertOk()->assertSee('No riders found');

        $rider->update(['status' => 'approved']);
        $this->get(route('logistics.riders', ['search' => 'Unique Rider Search', 'filter' => 'inactive']))
            ->assertOk()->assertViewHas('riders', fn ($items) => $items->total() === 1);
        $this->get(route('logistics.riders', ['filter' => 'invalid']))
            ->assertOk()->assertViewHas('filter', 'all');
        $this->get(route('logistics.riders', ['search' => $rider->user->email]))
            ->assertOk()->assertViewHas('riders', fn ($items) => collect($items->items())->contains('id', $rider->id));

        // Test suspending rider
        $this->actingAs($user)->post(route('logistics.riders.status', $rider->id), ['status' => 'suspended'])
            ->assertRedirect();
        $this->assertDatabaseHas('riders', ['id' => $rider->id, 'status' => 'suspended', 'is_active' => false]);
        $this->assertTrue((bool) $rider->fresh()->user->is_suspended);

        $this->get(route('logistics.riders', ['filter' => 'suspended']))
            ->assertOk()
            ->assertViewHas('riders', fn ($items) => $items->total() === 1)
            ->assertSee('rider-badge-suspended')
            ->assertSee('Unsuspend');
    }

    public function test_rider_directory_prioritizes_pending_and_paginates(): void
    {
        $user = $this->getLogisticsUser();
        $provider = $user->logisticsProvider;
        for ($i = 0; $i < 14; $i++) {
            Rider::create([
                'user_id' => User::factory()->create()->id,
                'logistics_provider_id' => $provider->id,
                'vehicle_type' => 'Motorcycle',
                'area' => 'Pagination Test Area',
                'status' => $i === 0 ? 'pending' : 'approved',
                'is_active' => false,
            ]);
        }
        $this->actingAs($user)->get(route('logistics.riders', ['search' => 'Pagination Test Area']))
            ->assertOk()->assertViewHas('riders', fn ($items) => $items->total() === 14
                && $items->count() === 12 && $items->first()['status'] === 'pending'
                && str_contains($items->nextPageUrl(), 'search=Pagination'));
        $this->get(route('logistics.riders', ['search' => 'Pagination Test Area', 'page' => 2]))
            ->assertOk()->assertViewHas('riders', fn ($items) => $items->count() === 2);
    }

    public function test_logistics_pickups_page_loads_and_confirms(): void
    {
        $user = $this->getLogisticsUser();
        $response = $this->actingAs($user)->get(route('logistics.pickups'));
        $response->assertStatus(200);

        $pickup = PickupRequest::first();
        if ($pickup) {
            $postResp = $this->actingAs($user)->post(route('logistics.pickups.confirm', $pickup->id));
            $postResp->assertRedirect();
            $this->assertDatabaseHas('pickup_requests', ['id' => $pickup->id, 'status' => 'confirmed']);
        }
    }

    public function test_pickup_search_filters_and_pending_priority(): void
    {
        $user = $this->getLogisticsUser();
        $provider = $user->logisticsProvider;
        $pickup = PickupRequest::where('logistics_provider_id', $provider->id)->firstOrFail();
        $pickup->update(['address' => 'Unique Pickup Search Street', 'status' => 'pending']);
        $this->actingAs($user)->get(route('logistics.pickups', ['q' => 'Unique Pickup Search', 'status' => 'pending']))
            ->assertOk()->assertViewHas('pickups', fn ($items) => $items->total() === 1 && $items->first()['id'] === $pickup->id);
        $this->get(route('logistics.pickups', ['q' => 'Unique Pickup Search', 'status' => 'confirmed']))
            ->assertOk()->assertSee('No pickup requests found.');
        $this->get(route('logistics.pickups', ['q' => $pickup->seller->user->email]))
            ->assertOk()->assertViewHas('pickups', fn ($items) => collect($items->items())->contains('id', $pickup->id));
        $this->get(route('logistics.pickups'))->assertOk()
            ->assertViewHas('pickups', fn ($items) => $items->first()['status'] === 'pending')
            ->assertViewHas('counts', fn ($counts) => $counts['all'] === PickupRequest::where('logistics_provider_id', $provider->id)->count());
        $this->get(route('logistics.pickups', ['status' => 'invalid']))->assertOk()->assertViewHas('filter', 'all');
    }

    public function test_logistics_pickups_nearest_rider_matching_and_auto_assign(): void
    {
        $user = $this->getLogisticsUser();
        $provider = $user->logisticsProvider;

        // Ensure a rider exists in Santa Cruz Laguna
        $riderUser = User::factory()->create(['role' => 'courier', 'status' => 'active']);
        $nearRider = Rider::create([
            'user_id'               => $riderUser->id,
            'logistics_provider_id' => $provider->id,
            'vehicle_type'          => 'Motorcycle',
            'plate_no'              => 'SC-9988',
            'status'                => 'approved',
            'is_active'             => true,
            'area'                  => 'Laguna - Santa Cruz',
            'rating'                => 5.0,
            'deliveries_today'      => 1,
        ]);

        $pickup = PickupRequest::where('logistics_provider_id', $provider->id)->firstOrFail();
        $pickup->update([
            'address'     => '123 Rizal Street, Santa Cruz, Laguna',
            'rider_id'    => null,
            'assigned_at' => null,
            'status'      => 'pending',
        ]);

        // Visit pickups page and assert nearest rider was detected as Santa Cruz
        $resp = $this->actingAs($user)->get(route('logistics.pickups'));
        $resp->assertOk();
        $resp->assertViewHas('pickups', function ($items) use ($pickup, $nearRider) {
            $matched = collect($items->items())->firstWhere('id', $pickup->id);
            return $matched && $matched['nearest_rider']['id'] === $nearRider->id;
        });

        // 1-Click Auto Dispatch Nearest Rider
        $autoResp = $this->actingAs($user)->post(route('logistics.pickups.auto-assign', $pickup->id));
        $autoResp->assertRedirect();
        $pickup->refresh();

        $this->assertEquals($nearRider->id, $pickup->rider_id);
        $this->assertEquals('confirmed', $pickup->status);
        $this->assertNotNull($pickup->assigned_at);
    }

    public function test_logistics_pickups_manual_rider_assignment_and_unassign(): void
    {
        $user = $this->getLogisticsUser();
        $provider = $user->logisticsProvider;

        $rider = Rider::where('logistics_provider_id', $provider->id)->where('status', 'approved')->firstOrFail();
        $pickup = PickupRequest::where('logistics_provider_id', $provider->id)->firstOrFail();

        // Assign specific rider
        $assignResp = $this->actingAs($user)->post(route('logistics.pickups.assign', $pickup->id), [
            'rider_id' => $rider->id,
        ]);
        $assignResp->assertRedirect();
        $pickup->refresh();

        $this->assertEquals($rider->id, $pickup->rider_id);
        $this->assertNotNull($pickup->assigned_at);

        // Unassign rider
        $unassignResp = $this->actingAs($user)->post(route('logistics.pickups.unassign', $pickup->id));
        $unassignResp->assertRedirect();
        $pickup->refresh();

        $this->assertNull($pickup->rider_id);
        $this->assertNull($pickup->assigned_at);
    }

    public function test_incoming_parcels_search_filter_and_receipt(): void
    {
        $user = $this->getLogisticsUser();
        $shipment = Shipment::where('logistics_provider_id', $user->logisticsProvider->id)->firstOrFail();
        $shipment->update(['tracking_code' => 'RECEIVING-TEST-001', 'received_at' => null, 'status' => 'unassigned']);
        $this->actingAs($user)->get(route('logistics.parcels', ['search' => 'RECEIVING-TEST', 'status' => 'pending_receipt']))
            ->assertOk()->assertSee('Awaiting receipt')->assertSee('Mark received')
            ->assertViewHas('parcels', fn ($items) => $items->total() === 1 && $items->first()['id'] === $shipment->id);
        $this->get(route('logistics.parcels', ['search' => 'RECEIVING-TEST', 'status' => 'at_hub']))
            ->assertOk()->assertViewHas('parcels', fn ($items) => $items->total() === 0);
        $this->get(route('logistics.parcels'))->assertOk()
            ->assertViewHas('parcels', fn ($items) => $items->first()['status'] === 'pending_receipt');
        $this->post(route('logistics.parcels.receive', $shipment->id))->assertRedirect();
        $receivedAt = $shipment->fresh()->received_at;
        $this->assertNotNull($receivedAt);
        $this->post(route('logistics.parcels.receive', $shipment->id))->assertRedirect();
        $this->assertTrue($receivedAt->equalTo($shipment->fresh()->received_at));
        $this->assertSame(1, $shipment->deliveryEvents()->where('status', 'received_at_hub')->count());
        $this->get(route('logistics.parcels', ['search' => 'RECEIVING-TEST', 'status' => 'at_hub']))
            ->assertOk()->assertSee('Receipt confirmed')
            ->assertViewHas('parcels', fn ($items) => $items->total() === 1);
        $this->get(route('logistics.parcels', ['status' => 'unknown']))->assertOk()->assertViewHas('filter', 'all');
    }

    public function test_sorting_search_priority_and_area_validation(): void
    {
        $user = $this->getLogisticsUser();
        $shipment = Shipment::where('logistics_provider_id', $user->logisticsProvider->id)->firstOrFail();
        $shipment->update(['tracking_code' => 'SORT-SEARCH-001', 'sort_status' => 'unsorted']);
        $this->actingAs($user)->get(route('logistics.sorting'))
            ->assertOk()->assertViewHas('parcels', fn ($items) => $items[0]['sort_status'] === 'unsorted');
        $this->get(route('logistics.sorting', ['search' => 'SORT-SEARCH', 'status' => 'unsorted']))
            ->assertOk()->assertViewHas('parcels', fn ($items) => count($items) === 1 && $items[0]['id'] === $shipment->id);
        $this->get(route('logistics.sorting', ['search' => 'SORT-SEARCH', 'status' => 'assigned']))
            ->assertOk()->assertSee('No parcels found');
        $this->post(route('logistics.sorting.sort', $shipment->id), ['area' => ''])
            ->assertSessionHasErrors('area');
        $this->assertSame('unsorted', $shipment->fresh()->sort_status);
        $this->get(route('logistics.sorting', ['status' => 'invalid']))->assertOk()->assertViewHas('filter', 'all');
    }

    public function test_logistics_parcels_sorting_and_assignments(): void
    {
        $user = $this->getLogisticsUser();

        // Parcels
        $pResp = $this->actingAs($user)->get(route('logistics.parcels'));
        $pResp->assertStatus(200);

        // Sorting
        $sResp = $this->actingAs($user)->get(route('logistics.sorting'));
        $sResp->assertStatus(200);

        $shipment = Shipment::first();
        if ($shipment) {
            $sortResp = $this->actingAs($user)->post(route('logistics.sorting.sort', $shipment->id), [
                'area' => 'Laguna - Santa Cruz',
            ]);
            $sortResp->assertRedirect();
            $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'delivery_area' => 'Laguna - Santa Cruz', 'sort_status' => 'sorted']);
        }

        // Assignments
        $aResp = $this->actingAs($user)->get(route('logistics.assignments'));
        $aResp->assertStatus(200);

        $rider = Rider::where('status', 'approved')->first();
        if ($shipment && $rider) {
            $assignResp = $this->actingAs($user)->post(route('logistics.assignments.assign', $shipment->id), [
                'rider_id' => $rider->id,
            ]);
            $assignResp->assertRedirect();
            $this->assertDatabaseHas('shipments', ['id' => $shipment->id, 'rider_id' => $rider->id, 'sort_status' => 'assigned']);
        }
    }

    public function test_monitoring_search_status_counts_and_failed_priority(): void
    {
        $user = $this->getLogisticsUser();
        $shipment = Shipment::where('logistics_provider_id', $user->logisticsProvider->id)->firstOrFail();
        $shipment->update(['tracking_code' => 'MONITOR-SEARCH-001', 'status' => 'failed']);
        $total = Shipment::where('logistics_provider_id', $user->logisticsProvider->id)->count();
        $this->actingAs($user)->get(route('logistics.monitoring'))
            ->assertOk()->assertViewHas('parcels', fn ($items) => $items->first()['delivery_status'] === 'failed');
        $this->get(route('logistics.monitoring', ['search' => 'MONITOR-SEARCH', 'status' => 'failed']))
            ->assertOk()->assertSee('MONITOR-SEARCH-001')
            ->assertViewHas('parcels', fn ($items) => $items->total() === 1
                && $items->first()['updated_at'] === $shipment->updated_at->timezone('Asia/Manila')->format('M d, Y g:i A'))
            ->assertViewHas('counts', fn ($counts) => $counts['all'] === $total);
        $this->get(route('logistics.monitoring', ['search' => 'MONITOR-SEARCH', 'status' => 'delivered']))
            ->assertOk()->assertSee('No deliveries found');
        $this->get(route('logistics.monitoring', ['status' => 'invalid']))
            ->assertOk()->assertViewHas('filter', 'all');
    }

    public function test_logistics_monitoring_and_reports(): void
    {
        $user = $this->getLogisticsUser();
        $mResp = $this->actingAs($user)->get(route('logistics.monitoring'));
        $mResp->assertStatus(200);

        $rResp = $this->actingAs($user)->get(route('logistics.reports'));
        $rResp->assertStatus(200);
    }

    public function test_logistics_chat_preserves_emoji_messages(): void
    {
        $user = $this->getLogisticsUser();
        $rider = Rider::where('logistics_provider_id', $user->logisticsProvider->id)->firstOrFail();
        $text = "On the way! 🛵📦\nThank you! 💜";
        $this->actingAs($user)->post(route('logistics.chat.send', $rider->user_id), [
            'message' => $text,
        ])->assertRedirect();
        $this->assertDatabaseHas('logistics_messages', [
            'logistics_provider_id' => $user->logisticsProvider->id,
            'contact_user_id' => $rider->user_id,
            'message' => $text,
        ]);
        $this->get(route('logistics.chat', ['contact' => $rider->user_id]))
            ->assertOk()->assertSee($text)->assertSee('Add an emoji')->assertSee('Search conversations');
    }

    public function test_logistics_chat_and_account(): void
    {
        $user = $this->getLogisticsUser();
        $cResp = $this->actingAs($user)->get(route('logistics.chat'));
        $cResp->assertStatus(200);

        $sendResp = $this->actingAs($user)->post(route('logistics.chat.send', 1), [
            'message' => 'Test message from test runner',
        ]);
        $sendResp->assertRedirect();

        $accResp = $this->actingAs($user)->get(route('logistics.account'));
        $accResp->assertStatus(200);
    }
}
