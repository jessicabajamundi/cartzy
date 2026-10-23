<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'email' => 'admin@example.test',
            'password' => Hash::make('original-admin-pass-123'),
        ]);
    }

    public function test_admin_account_page_displays_forgot_password_options(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('admin.account'));

        $response->assertOk()
            ->assertSee('Forgot password?')
            ->assertSee('Forgot current password?')
            ->assertSee('admin-forgot-pw-container')
            ->assertSee(route('password.send_otp'))
            ->assertSee(route('password.forgot', ['email' => $admin->email, 'portal' => 'admin']));
    }

    public function test_admin_can_send_otp_and_reset_password_from_account(): void
    {
        $admin = $this->admin();

        // 1. Send OTP
        $sendResponse = $this->actingAs($admin)->postJson(route('password.send_otp'), [
            'email' => $admin->email,
        ]);
        $sendResponse->assertOk()->assertJson(['success' => true]);

        $cached = Cache::get('pwd_reset_otp_' . strtolower($admin->email));
        $this->assertNotNull($cached);
        $otp = $cached['otp'];

        // 2. Verify OTP
        $verifyResponse = $this->actingAs($admin)->postJson(route('password.verify_otp'), [
            'email' => $admin->email,
            'otp' => $otp,
        ]);
        $verifyResponse->assertOk()->assertJson(['success' => true]);

        // 3. Reset password via password.reset.submit
        $resetResponse = $this->actingAs($admin)->post(route('password.reset.submit'), [
            'portal' => 'admin',
            'email' => $admin->email,
            'otp' => $otp,
            'password' => 'brand-new-admin-pass-999',
            'password_confirmation' => 'brand-new-admin-pass-999',
        ]);

        $resetResponse->assertRedirect(route('admin.account'));
        $resetResponse->assertSessionHas('success');

        $this->assertTrue(Hash::check('brand-new-admin-pass-999', $admin->fresh()->password));
    }

    public function test_unauthenticated_admin_reset_redirects_to_admin_login(): void
    {
        $admin = $this->admin();

        $this->postJson(route('password.send_otp'), ['email' => $admin->email])->assertOk();
        $cached = Cache::get('pwd_reset_otp_' . strtolower($admin->email));
        $otp = $cached['otp'];

        $this->postJson(route('password.verify_otp'), ['email' => $admin->email, 'otp' => $otp])->assertOk();

        $response = $this->post(route('password.reset.submit'), [
            'portal' => 'admin',
            'email' => $admin->email,
            'otp' => $otp,
            'password' => 'new-secret-password-000',
            'password_confirmation' => 'new-secret-password-000',
        ]);

        $response->assertRedirect(route('login.admin'));
        $this->assertTrue(Hash::check('new-secret-password-000', $admin->fresh()->password));
    }

    public function test_forgot_password_page_accessible_while_logged_in_as_admin(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get(route('password.forgot', [
            'email' => $admin->email,
            'portal' => 'admin',
        ]));

        $response->assertOk()
            ->assertSee($admin->email)
            ->assertSee('Back to My account');
    }
}
