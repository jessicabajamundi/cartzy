<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LogisticsRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_logistics_registration_creates_pending_company_account(): void
    {
        Cache::put('otp_partner@example.com', ['otp' => '123456']);
        $this->post(route('register'), [
            'role' => 'logistics', 'first_name' => 'Maria', 'last_name' => 'Santos',
            'email' => 'partner@example.com',
            'phone' => '9535333656', 'phone_country_code' => '+63',
            'business_name' => 'Cavite Delivery', 'service_area' => 'Cavite',
            'password' => 'Delivery@2026', 'password_confirmation' => 'Delivery@2026',
            'email_verification_otp' => '123456',
        ])->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));

        $user = User::where('email', 'partner@example.com')->firstOrFail();
        $this->assertSame('logistics', $user->role);
        $this->assertSame('pending', $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->birthday);
        $this->assertDatabaseHas('logistics_providers', [
            'user_id' => $user->id, 'name' => 'Cavite Delivery', 'status' => 'pending',
        ]);
        $this->assertGuest();
    }

    public function test_logistics_details_hide_birthday_and_use_shared_wizard(): void
    {
        $this->get(route('register', ['role' => 'logistics']))->assertOk()
            ->assertSee('Become a Logistics Partner')
            ->assertSee('id="birthdayFieldWrapper" style="display: none; margin-bottom: 26px;"', false)
            ->assertSee('js/auth/register.js')
            ->assertDontSee('window.selectRole = function(role)', false);
    }
}
