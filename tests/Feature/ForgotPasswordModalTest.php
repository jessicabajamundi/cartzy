<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForgotPasswordModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_modal_and_standalone_pages_contain_forgot_password_modal(): void
    {
        // 1. Standalone login page (/login)
        $this->get('/login')
            ->assertOk()
            ->assertSee('id="forgot-password-modal"', false)
            ->assertSee('fpmSubmitEmail', false)
            ->assertSee('fpmSubmitOtp', false)
            ->assertSee('fpmSubmitReset', false);

        // 2. Role login pages
        $this->get('/seller/login')
            ->assertOk()
            ->assertSee('id="forgot-password-modal"', false);

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('id="forgot-password-modal"', false);

        // 3. Storefront overlay
        $this->get('/')
            ->assertOk()
            ->assertSee('id="forgot-password-modal"', false);
    }

    public function test_forgot_password_modal_full_ajax_workflow(): void
    {
        $user = User::factory()->create([
            'email' => 'modaluser@example.test',
            'role' => 'buyer',
            'password' => Hash::make('old-secret-password-123'),
        ]);

        // Step 1: Request OTP
        $otpResponse = $this->postJson(route('password.send_otp'), [
            'email' => 'modaluser@example.test',
        ]);
        $otpResponse->assertOk()->assertJson(['success' => true]);

        $cached = Cache::get('pwd_reset_otp_modaluser@example.test');
        $this->assertNotNull($cached);
        $otp = $cached['otp'];

        // Step 2: Verify OTP
        $verifyResponse = $this->postJson(route('password.verify_otp'), [
            'email' => 'modaluser@example.test',
            'otp' => $otp,
        ]);
        $verifyResponse->assertOk()->assertJson(['success' => true]);

        // Step 3: Reset password via JSON request
        $resetResponse = $this->postJson(route('password.reset.submit'), [
            'email' => 'modaluser@example.test',
            'otp' => $otp,
            'password' => 'BrandNewPassword!2026',
            'password_confirmation' => 'BrandNewPassword!2026',
            'portal' => 'buyer',
        ]);

        $resetResponse->assertOk()->assertJson([
            'success' => true,
            'portal' => 'buyer',
        ]);

        // Verify password was indeed updated in database
        $user->refresh();
        $this->assertTrue(Hash::check('BrandNewPassword!2026', $user->password));
    }
}
