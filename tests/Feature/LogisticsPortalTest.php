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

    public function test_logistics_monitoring_and_reports(): void
    {
        $user = $this->getLogisticsUser();
        $mResp = $this->actingAs($user)->get(route('logistics.monitoring'));
        $mResp->assertStatus(200);

        $rResp = $this->actingAs($user)->get(route('logistics.reports'));
        $rResp->assertStatus(200);
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
