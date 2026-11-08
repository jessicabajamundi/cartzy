<?php

namespace Tests\Feature;

use App\Models\LogisticsProvider;
use App\Models\Rider;
use App\Models\RiderAttendance;
use App\Models\User;
use App\Services\RiderAttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RiderAttendanceTest extends TestCase
{
    use RefreshDatabase;

    private const SCAN = '/api/rider/attendance/scan';

    protected function setUp(): void
    {
        parent::setUp();
        // Start every test 2 seconds into a 25-second code window.
        Carbon::setTestNow(Carbon::createFromTimestamp(intdiv(1_800_000_000, 25) * 25 + 2));
    }

    private function hub(string $name = 'Metro South Hub'): LogisticsProvider
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);

        return LogisticsProvider::create(['user_id' => $user->id, 'name' => $name, 'slug' => str()->slug($name), 'status' => 'approved']);
    }

    private function rider(LogisticsProvider $hub, string $status = 'approved'): Rider
    {
        $user = User::factory()->create(['role' => 'courier', 'status' => 'active']);

        return Rider::create(['user_id' => $user->id, 'logistics_provider_id' => $hub->id, 'vehicle_type' => 'Motorcycle', 'plate_no' => 'NQ-1234', 'status' => $status, 'is_active' => true, 'area' => 'Calamba']);
    }

    private function code(LogisticsProvider $hub): string
    {
        return app(RiderAttendanceService::class)->code($hub);
    }

    public function test_first_scan_times_the_rider_in_and_the_next_code_times_them_out(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub);
        Sanctum::actingAs($rider->user);

        $this->postJson(self::SCAN, ['code' => $this->code($hub)])
            ->assertCreated()->assertJson(['action' => 'time_in', 'on_shift' => true]);
        $this->getJson('/api/rider/attendance')->assertOk()->assertJson(['on_shift' => true]);

        $this->travel(2)->hours();
        $this->postJson(self::SCAN, ['code' => $this->code($hub)])
            ->assertOk()->assertJson(['action' => 'time_out', 'on_shift' => false, 'shift' => ['worked_minutes' => 120]])
            ->assertJsonPath('message', fn ($message) => str_contains($message, '2h 0m'));

        $shift = RiderAttendance::sole();
        $this->assertSame($hub->id, $shift->logistics_provider_id);
        $this->assertNotNull($shift->time_out_at);
        $this->getJson('/api/rider/attendance')->assertOk()->assertJson(['on_shift' => false])->assertJsonCount(1, 'recent');
    }

    public function test_a_code_only_works_in_its_own_25_seconds(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub);
        Sanctum::actingAs($rider->user);
        $screenshot = $this->code($hub);

        // 2s into the next window the old code still counts (grace for a scan right at the changeover)...
        $this->travel(25)->seconds();
        $this->postJson(self::SCAN, ['code' => $screenshot])->assertCreated();

        // ...but a code from one window ago, past the grace, is refused.
        $this->travel(28)->seconds();
        $this->postJson(self::SCAN, ['code' => $screenshot])->assertStatus(422)
            ->assertJsonPath('message', fn ($message) => str_contains($message, 'expired'));
        $this->assertNull(RiderAttendance::sole()->time_out_at);
    }

    public function test_the_same_code_cannot_time_in_and_out(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub);
        Sanctum::actingAs($rider->user);
        $code = $this->code($hub);

        $this->postJson(self::SCAN, ['code' => $code])->assertCreated();
        $this->postJson(self::SCAN, ['code' => $code])->assertStatus(409);

        $this->assertNull(RiderAttendance::sole()->time_out_at);
    }

    public function test_forged_codes_and_other_hubs_codes_are_refused(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub);
        Sanctum::actingAs($rider->user);
        $window = app(RiderAttendanceService::class)->currentWindow();

        $this->postJson(self::SCAN, ['code' => "CARTZY-ATT.{$hub->id}.{$window}.000000000000000000000000"])->assertStatus(422);
        $this->postJson(self::SCAN, ['code' => 'CTZ-ABC123'])->assertStatus(422);
        $this->postJson(self::SCAN, ['code' => $this->code($this->hub('Other Hub'))])->assertForbidden();

        $this->assertSame(0, RiderAttendance::count());
    }

    public function test_riders_who_are_not_approved_and_other_accounts_cannot_time_in(): void
    {
        $hub = $this->hub();
        Sanctum::actingAs($this->rider($hub, 'pending')->user);
        $this->postJson(self::SCAN, ['code' => $this->code($hub)])->assertForbidden();

        Sanctum::actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));
        $this->postJson(self::SCAN, ['code' => $this->code($hub)])->assertForbidden();

        $this->assertSame(0, RiderAttendance::count());
    }

    public function test_a_shift_without_a_time_out_does_not_block_the_next_day(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub);
        Sanctum::actingAs($rider->user);

        $this->postJson(self::SCAN, ['code' => $this->code($hub)])->assertCreated();
        $this->travel(20)->hours();
        $this->postJson(self::SCAN, ['code' => $this->code($hub)])->assertCreated()->assertJson(['action' => 'time_in']);

        $this->assertSame(2, RiderAttendance::count());
        $this->assertSame(1, RiderAttendance::whereNull('time_out_at')->where('time_in_at', '<', now()->subHours(16))->count());
    }

    public function test_hub_page_shows_a_scannable_code_and_the_attendance_log(): void
    {
        $hub = $this->hub();
        $rider = $this->rider($hub);
        $rider->user->update(['name' => 'Arnel Gomez']);
        app(RiderAttendanceService::class)->scan($rider, $this->code($hub));

        $this->actingAs($hub->user)->get(route('logistics.attendance'))->assertOk()
            ->assertSee('Time in / time out')->assertSee('<svg', false)
            ->assertSee('Arnel Gomez')->assertSee('On shift');

        $first = $this->getJson(route('logistics.attendance.qr'))->assertOk()
            ->assertJsonStructure(['svg', 'seconds_left', 'version'])->assertJson(['seconds_left' => 23]);
        $this->travel(25)->seconds();
        $second = $this->getJson(route('logistics.attendance.qr'))->assertOk();
        $this->assertNotSame($first->json('svg'), $second->json('svg'));

        $this->get(route('logistics.attendance', ['date' => now('Asia/Manila')->subDays(3)->format('Y-m-d')]))
            ->assertOk()->assertSee('No rider timed in on this day.');
    }
}
