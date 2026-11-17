<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GoogleAccountPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => 'buyer', 'status' => 'active',
            'google_id' => 'google-'.uniqid(), 'has_custom_password' => false,
            'password' => Hash::make('unknown-generated-secret')]);
    }

    private function resetData(User $buyer): array
    {
        Cache::put('pwd_reset_otp_'.$buyer->email, ['otp' => '123456'], 600);
        return ['email' => $buyer->email, 'otp' => '123456', 'password' => 'NewCartzyPass123!', 'password_confirmation' => 'NewCartzyPass123!'];
    }

    public function test_api_reset_closes_passwordless_change_flow_and_retains_only_the_verified_device(): void
    {
        $buyer = $this->buyer();
        $current = $buyer->createToken('current');
        $other = $buyer->createToken('other');
        $this->withToken($current->plainTextToken)->postJson('/api/auth/password/reset', $this->resetData($buyer))
            ->assertOk()->assertJsonPath('session_preserved', true)->assertJsonPath('user.has_custom_password', true);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $current->accessToken->id]);
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $other->accessToken->id]);
        $this->assertTrue(Hash::check('NewCartzyPass123!', $buyer->fresh()->password));
        $this->withToken($current->plainTextToken)->postJson('/api/account/password', [
            'password' => 'OverrideWithoutCurrent123!', 'password_confirmation' => 'OverrideWithoutCurrent123!',
        ])->assertUnprocessable()->assertJsonValidationErrors('current_password');
        $this->withToken($current->plainTextToken)->postJson('/api/account/delete', ['password' => 'NewCartzyPass123!'])->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $buyer->id]);
    }

    public function test_legacy_google_account_can_verify_email_set_password_then_delete(): void
    {
        // Existing accounts conservatively retain true: their password origin cannot be inferred from a hash.
        $buyer = $this->buyer(['has_custom_password' => true]);
        $token = $buyer->createToken('current')->plainTextToken;
        $this->withToken($token)->postJson('/api/auth/password/reset', $this->resetData($buyer))->assertOk();
        $this->withToken($token)->postJson('/api/account/delete', ['password' => 'NewCartzyPass123!'])->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $buyer->id]);
    }

    public function test_guest_reset_revokes_all_existing_tokens(): void
    {
        $buyer = $this->buyer();
        $buyer->createToken('old');
        $this->postJson('/api/auth/password/reset', $this->resetData($buyer))->assertOk()->assertJsonPath('session_preserved', false);
        $this->assertSame(0, $buyer->tokens()->count());
        $this->assertTrue($buyer->fresh()->has_custom_password);
    }

    public function test_web_reset_returns_to_profile_and_requires_new_password_for_deletion(): void
    {
        $buyer = $this->buyer(['has_custom_password' => true]);
        $this->actingAs($buyer, 'web')->post(route('password.reset.submit'), $this->resetData($buyer) + ['context' => 'account-deletion'])
            ->assertRedirect(route('buyer.dashboard', ['tab' => 'settings', 'section' => 'password']));
        $this->assertTrue($buyer->fresh()->has_custom_password);
        $this->get('/dashboard?tab=settings&section=password')->assertOk()->assertSee('Cartzy password');
        $this->delete(route('account.delete'), ['password' => 'NewCartzyPass123!'])->assertRedirect(route('login'));
        $this->assertDatabaseMissing('users', ['id' => $buyer->id]);
    }

    public function test_reset_requires_the_actual_otp_even_after_a_prior_code_verification(): void
    {
        $buyer = $this->buyer();
        $data = $this->resetData($buyer);
        Cache::put('pwd_reset_verified_'.$buyer->email, true, 600);
        $data['otp'] = '999999';
        $this->postJson(route('password.reset.submit'), $data)->assertUnprocessable();
        $this->postJson('/api/auth/password/reset', $data)->assertUnprocessable();
        $this->assertFalse($buyer->fresh()->has_custom_password);
        $this->assertTrue(Hash::check('unknown-generated-secret', $buyer->fresh()->password));
    }

    public function test_initial_password_can_only_be_set_once_and_only_for_google_accounts(): void
    {
        $buyer = $this->buyer();
        $token = $buyer->createToken('current')->plainTextToken;
        $payload = ['password' => 'InitialCartzy123!', 'password_confirmation' => 'InitialCartzy123!'];
        $this->withToken($token)->postJson('/api/account/password', $payload)->assertOk();
        $this->withToken($token)->postJson('/api/account/password', $payload)->assertUnprocessable();
        $regular = $this->buyer(['google_id' => null]);
        $regularToken = $regular->createToken('regular')->plainTextToken;
        auth()->forgetGuards();
        $this->withToken($regularToken)->postJson('/api/account/password', $payload)->assertUnprocessable();
    }

    public function test_google_linked_account_with_known_password_still_requires_current_password(): void
    {
        $buyer = $this->buyer(['has_custom_password' => true, 'password' => Hash::make('ExistingPass123!')]);
        $token = $buyer->createToken('current')->plainTextToken;
        $payload = ['password' => 'NewCartzy123!', 'password_confirmation' => 'NewCartzy123!'];
        $this->withToken($token)->postJson('/api/account/password', $payload)->assertUnprocessable();
        $this->withToken($token)->postJson('/api/account/password', $payload + ['current_password' => 'WrongPassword'])->assertUnprocessable();
        $this->withToken($token)->postJson('/api/account/password', $payload + ['current_password' => 'ExistingPass123!'])->assertOk();
    }

    public function test_web_google_account_shows_email_shortcut_without_claiming_google_password_is_saved(): void
    {
        $buyer = $this->buyer(['has_custom_password' => true]);
        $this->actingAs($buyer, 'web')->get('/dashboard?tab=settings')->assertOk()
            ->assertSee('Set or reset Cartzy password by email')
            ->assertSee('Confirm with your Cartzy password')
            ->assertSee('context=account-deletion');
        $this->get(route('password.forgot', ['context' => 'account-deletion', 'email' => $buyer->email]))->assertOk()
            ->assertSee('Set your Cartzy password')->assertSee('Your Google password will not change.');
    }
}
