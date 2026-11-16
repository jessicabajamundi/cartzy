<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, Rider, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VehicleClassificationTest extends TestCase
{
    use RefreshDatabase;

    private function hub(): LogisticsProvider
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        return LogisticsProvider::create(['user_id' => $user->id, 'name' => 'Hub '.$user->id,
            'slug' => 'hub-'.$user->id, 'status' => 'approved']);
    }

    private function driver(LogisticsProvider $hub, string $vehicle, string $type): Rider
    {
        $user = User::factory()->create(['role' => 'courier', 'status' => 'active', 'is_suspended' => false]);
        return Rider::create(['user_id' => $user->id, 'logistics_provider_id' => $hub->id,
            'vehicle_type' => $vehicle, 'courier_type' => $type, 'status' => 'approved', 'is_active' => true]);
    }

    public function test_registered_vehicle_overrides_a_conflicting_classification_for_all_six_types(): void
    {
        $hub = $this->hub();
        foreach (['motorcycle', 'tricycle', 'E-bike'] as $vehicle) {
            $rider = $this->driver($hub, $vehicle, Rider::FIRST_MILE);
            $this->assertTrue($rider->fresh()->isLastMile());
            $this->assertSame(Rider::LAST_MILE, $rider->fresh()->getRawOriginal('courier_type'));
            $this->assertFalse($rider->canCarryLinehaul());
        }
        foreach (['car', 'van', 'truck'] as $vehicle) {
            $courier = $this->driver($hub, $vehicle, Rider::LAST_MILE);
            $this->assertTrue($courier->fresh()->isFirstMile());
            $this->assertTrue($courier->canCarryLinehaul());
        }
        $this->assertSame(3, Rider::lastMile()->count());
        $this->assertSame(3, Rider::vehicleCouriers()->count());
    }

    public function test_hub_cannot_switch_registered_vehicle_classification_even_with_direct_requests(): void
    {
        $hub = $this->hub();
        $rider = $this->driver($hub, 'motorcycle', Rider::LAST_MILE);
        $courier = $this->driver($hub, 'van', Rider::FIRST_MILE);
        $this->actingAs($hub->user)->post(route('logistics.riders.type', $rider->id), ['courier_type' => Rider::FIRST_MILE])
            ->assertRedirect()->assertSessionHasErrors('courier_type');
        $this->post(route('logistics.riders.type', $courier->id), ['courier_type' => Rider::LAST_MILE])
            ->assertRedirect()->assertSessionHasErrors('courier_type');
        $this->assertTrue($rider->fresh()->isLastMile());
        $this->assertTrue($courier->fresh()->isFirstMile());
        $this->get(route('logistics.riders'))->assertOk()->assertDontSee('name="courier_type"', false)
            ->assertSee('Based on registered vehicle')->assertViewHas('typeCounts', ['all' => 2, 'rider' => 1, 'courier' => 1]);
        $this->get(route('logistics.riders', ['type' => 'rider']))->assertOk()->assertViewHas('riders', fn ($r) => $r->total() === 1);
        $otherHub = $this->hub();
        $this->actingAs($otherHub->user)->post(route('logistics.riders.type', $rider->id), ['courier_type' => Rider::FIRST_MILE])->assertNotFound();
    }

    public function test_legacy_wrong_assignments_are_classified_by_vehicle_and_repaired_without_losing_linehaul(): void
    {
        $hub = $this->hub();
        $bike = $this->driver($hub, 'Motorcycle', Rider::LAST_MILE);
        $car = $this->driver($hub, 'Car', Rider::FIRST_MILE);
        $linehaul = $this->driver($hub, 'Truck', Rider::LINEHAUL);
        DB::table('riders')->where('id', $bike->id)->update(['courier_type' => Rider::FIRST_MILE]);
        DB::table('riders')->where('id', $car->id)->update(['courier_type' => Rider::LAST_MILE]);
        $this->assertTrue($bike->fresh()->isLastMile());
        $this->assertTrue($car->fresh()->canCarryLinehaul());
        $this->assertEquals([$bike->id], Rider::lastMile()->pluck('id')->all());
        $migration = require database_path('migrations/2026_11_16_000005_align_rider_classification_with_registered_vehicle.php');
        $migration->up();
        $this->assertSame(Rider::LAST_MILE, $bike->fresh()->getRawOriginal('courier_type'));
        $this->assertSame(Rider::FIRST_MILE, $car->fresh()->getRawOriginal('courier_type'));
        $this->assertSame(Rider::LINEHAUL, $linehaul->fresh()->getRawOriginal('courier_type'));
    }

    public function test_profile_updates_cannot_replace_the_registered_vehicle(): void
    {
        $hub = $this->hub();
        $rider = $this->driver($hub, 'Motorcycle', Rider::LAST_MILE);
        $this->actingAs($rider->user)->post(route('courier.account.profile'), ['phone' => '09171234567', 'vehicle_type' => 'Van'])
            ->assertRedirect()->assertSessionHasErrors('vehicle_type');
        $this->assertSame('Motorcycle', $rider->fresh()->vehicle_type);
        $this->post(route('courier.account.profile'), ['phone' => '09171234567', 'vehicle_type' => 'motorcycle'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertTrue($rider->fresh()->isLastMile());
    }

    public function test_registration_validation_derives_role_from_the_vehicle(): void
    {
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        foreach (['motorcycle', 'tricycle', 'E-bike', 'car', 'van', 'truck'] as $vehicle) {
            $this->postJson('/api/auth/register/rider', ['vehicle_type' => $vehicle,
                'courier_type' => in_array($vehicle, ['car', 'van', 'truck']) ? Rider::LAST_MILE : Rider::FIRST_MILE])
                ->assertUnprocessable()->assertJsonMissingValidationErrors(['vehicle_type', 'courier_type']);
        }
    }

    public function test_mobile_login_respects_registered_vehicle_including_hub_to_hub_couriers(): void
    {
        $hub = $this->hub();
        foreach (['motorcycle' => Rider::LAST_MILE, 'van' => Rider::FIRST_MILE, 'truck' => Rider::LINEHAUL] as $vehicle => $type) {
            $rider = $this->driver($hub, $vehicle, $type);
            $correct = $vehicle === 'motorcycle' ? 'rider' : 'courier';
            $this->postJson('/api/auth/login', ['email' => $rider->user->email, 'password' => 'password', 'role' => $correct])
                ->assertOk()->assertJsonPath('user.courier_type', $type);
            $this->postJson('/api/auth/login', ['email' => $rider->user->email, 'password' => 'password', 'role' => $correct === 'rider' ? 'courier' : 'rider'])
                ->assertForbidden();
        }
    }
}
