<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email): User
    {
        return User::factory()->create([
            'role' => $role,
            'status' => 'active',
            'email' => $email,
            'password' => Hash::make('old-password-123'),
        ]);
    }

    public function test_buyer_and_rider_can_reset_password_from_the_app(): void
    {
        foreach (['buyer' => 'buyer', 'rider' => 'courier'] as $mobileRole => $role) {
            Cache::flush(); // Both runs share one IP; start each under the rate limit.
            $user = $this->user($role, "{$mobileRole}@example.test");
            $user->createToken('old-device');

            $this->postJson('/api/auth/password/forgot', ['email' => $user->email, 'role' => $mobileRole])
                ->assertOk()->assertJson(['success' => true]);
            $otp = Cache::get('pwd_reset_otp_'.$user->email)['otp'];

            $this->postJson('/api/auth/password/verify', ['email' => $user->email, 'otp' => $otp])
                ->assertOk()->assertJson(['success' => true]);

            $this->postJson('/api/auth/password/reset', [
                'email' => $user->email,
                'otp' => $otp,
                'password' => 'new-password-456',
                'password_confirmation' => 'new-password-456',
            ])->assertOk();

            $user->refresh();
            $this->assertTrue(Hash::check('new-password-456', $user->password));
            $this->assertCount(0, $user->tokens);
            $this->assertNull(Cache::get('pwd_reset_otp_'.$user->email));

            $this->postJson('/api/auth/login', [
                'email' => $user->email,
                'password' => 'new-password-456',
                'role' => $mobileRole,
            ])->assertOk();
        }
    }

    public function test_reset_is_rejected_for_the_wrong_login_or_a_bad_code(): void
    {
        $buyer = $this->user('buyer', 'buyer@example.test');

        $this->postJson('/api/auth/password/forgot', ['email' => $buyer->email, 'role' => 'rider'])
            ->assertStatus(403)->assertJson(['account_role' => 'buyer']);
        $this->assertNull(Cache::get('pwd_reset_otp_'.$buyer->email));

        $this->postJson('/api/auth/password/forgot', ['email' => 'nobody@example.test', 'role' => 'buyer'])
            ->assertStatus(404);

        $this->postJson('/api/auth/password/forgot', ['email' => $buyer->email, 'role' => 'buyer'])->assertOk();

        $this->postJson('/api/auth/password/reset', [
            'email' => $buyer->email,
            'otp' => '000000',
            'password' => 'new-password-456',
            'password_confirmation' => 'new-password-456',
        ])->assertStatus(422);

        $this->assertTrue(Hash::check('old-password-123', $buyer->fresh()->password));
    }
}
