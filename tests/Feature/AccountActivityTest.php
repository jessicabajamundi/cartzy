<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AccountActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_account_changes_are_logged_and_listed_on_the_activity_section(): void
    {
        $user = User::factory()->create(['role' => 'buyer', 'password' => Hash::make('original-password')]);

        $this->actingAs($user)->from('/dashboard?tab=settings')->post(route('account.password.update'), [
            'current_password' => 'original-password', 'password' => 'new-password', 'password_confirmation' => 'new-password',
        ])->assertSessionHasNoErrors();
        $this->post(route('account.profile.update'), ['name' => 'Renamed Buyer'])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'event' => 'password_changed', 'channel' => 'web']);
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'event' => 'profile_updated', 'channel' => 'web']);

        $this->get('/dashboard?tab=settings&section=activity')->assertOk()
            ->assertSee('Password changed')->assertSee('Personal details updated')->assertSee('Website');
    }

    public function test_failed_web_sign_in_is_shown_to_the_account_owner(): void
    {
        $user = User::factory()->create(['role' => 'buyer', 'email' => 'owner@example.com']);

        $this->post(route('login'), ['email' => 'owner@example.com', 'password' => 'wrong-password']);

        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'event' => ActivityLog::FAILED_LOGIN]);
    }

    public function test_mobile_sign_in_is_logged_and_the_api_lists_only_your_own_activity(): void
    {
        $user = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'email' => 'app@example.com', 'password' => Hash::make('secret-password')]);
        $other = User::factory()->create(['role' => 'buyer']);
        ActivityLog::create(['user_id' => $other->id, 'event' => 'login', 'channel' => 'web']);

        $this->postJson('/api/auth/login', ['email' => 'app@example.com', 'password' => 'secret-password'])->assertOk();
        $this->assertDatabaseHas('activity_logs', ['user_id' => $user->id, 'event' => 'login', 'channel' => 'mobile']);

        Sanctum::actingAs($user);
        $this->postJson('/api/addresses', [
            'label' => 'Home', 'name' => 'App Buyer', 'phone' => '09171234567', 'street' => '10 Main Street',
            'barangay' => 'San Jose', 'municipality' => 'Quezon City', 'province' => 'Metro Manila', 'postal_code' => '1100',
        ])->assertCreated();

        $response = $this->getJson('/api/account/activity')->assertOk()->assertJsonPath('has_more', false);
        $this->assertSame(['address_added', 'login'], collect($response->json('activity'))->pluck('event')->all());
        $response->assertJsonPath('activity.1.title', 'Signed in')->assertJsonPath('activity.1.channel', 'mobile');

        $this->getJson('/api/account/activity?group=security')->assertOk()->assertJsonCount(1, 'activity');
        $this->getJson('/api/account/activity?group=nope')->assertStatus(422);
    }

    public function test_activity_is_paged_ten_at_a_time_on_web_and_mobile(): void
    {
        $user = User::factory()->create(['role' => 'buyer']);
        foreach (range(1, 23) as $i) {
            ActivityLog::create(['user_id' => $user->id, 'event' => 'login', 'channel' => 'web', 'description' => "Entry {$i}", 'created_at' => now()->subMinutes(30 - $i)]);
        }

        $this->actingAs($user)->get('/dashboard?tab=settings&section=activity')->assertOk()
            ->assertSee('Showing 1–10 of 23')->assertSee('Page 1 of 3')->assertSee('Entry 23')->assertDontSee('Entry 13')
            ->assertSee('activity_page=2', false);
        $this->get('/dashboard?tab=settings&section=activity&activity_page=3')->assertOk()
            ->assertSee('Showing 21–23 of 23')->assertSee('Entry 1')->assertDontSee('Entry 4<', false);
        $this->get('/dashboard?tab=settings&section=activity&activity_group=orders')->assertOk()
            ->assertSee('No activity of this kind yet.');

        Sanctum::actingAs($user);
        $this->getJson('/api/account/activity')->assertJsonCount(10, 'activity')->assertJsonPath('next_page', 2);
        $this->getJson('/api/account/activity?page=2')->assertJsonCount(10, 'activity')->assertJsonPath('next_page', 3);
        $this->getJson('/api/account/activity?page=3')->assertJsonCount(3, 'activity')->assertJsonPath('has_more', false);
    }

    public function test_activity_requires_sign_in(): void
    {
        $this->getJson('/api/account/activity')->assertUnauthorized();
    }
}
