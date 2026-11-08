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

    public function test_logistics_sign_up_is_seven_short_steps(): void
    {
        $html = $this->get(route('register', ['role' => 'logistics']))->assertOk()
            ->assertSee('id="logisticsProgress"', false)
            ->assertSee('Step <strong data-lg-number>1</strong> of 7', false)
            ->assertSee('js/auth/logistics-steps.js')
            ->getContent();

        // Steps 1 to 5 are pages of the details section; 6 and 7 are the sign-in and verify sections.
        foreach (range(1, 5) as $page) {
            $this->assertStringContainsString('data-lg-page="'.$page.'"', $html);
        }
        foreach (['hub_region', 'hub_province', 'hub_city', 'hub_barangay', 'hub_street_address', 'hub_postal_code'] as $field) {
            $this->assertStringContainsString('name="'.$field.'"', $html);
        }
        $this->assertStringContainsString('id="lg_id_photo" name="id_photo"', $html);
        $this->assertStringContainsString('id="lg_dti_permit" name="dti_permit"', $html);
    }

    public function test_logistics_sign_up_saves_the_hub_address_and_documents(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        Cache::put('otp_hub@example.com', ['otp' => '654321']);

        $this->post(route('register'), [
            'role' => 'logistics', 'first_name' => 'Ramon', 'last_name' => 'Cruz',
            'email' => 'hub@example.com', 'phone' => '9171112222', 'phone_country_code' => '+63',
            'business_name' => 'Laguna Swift Couriers', 'service_area' => 'Laguna',
            'hub_region' => 'IV-A', 'hub_province' => 'Laguna', 'hub_city' => 'Calamba', 'hub_barangay' => 'Real',
            'hub_street_address' => '88 National Highway', 'hub_postal_code' => '4027',
            'id_photo' => \Illuminate\Http\UploadedFile::fake()->create('id.pdf', 30, 'application/pdf'),
            'dti_permit' => \Illuminate\Http\UploadedFile::fake()->create('permit.pdf', 40, 'application/pdf'),
            'password' => 'Delivery@2026', 'password_confirmation' => 'Delivery@2026',
            'email_verification_otp' => '654321',
        ])->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));

        $user = User::where('email', 'hub@example.com')->firstOrFail();
        $this->assertSame(['IV-A', 'Laguna', 'Calamba', 'Real', '88 National Highway', '4027'],
            [$user->region, $user->province, $user->city, $user->barangay, $user->street_address, $user->postal_code]);
        $this->assertNotNull($user->id_photo);
        $this->assertNotNull($user->dti_permit);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists([$user->id_photo, $user->dti_permit]);
    }

    public function test_hub_postal_code_must_be_four_digits(): void
    {
        Cache::put('otp_hub2@example.com', ['otp' => '111222']);
        $this->post(route('register'), [
            'role' => 'logistics', 'first_name' => 'Ramon', 'last_name' => 'Cruz', 'email' => 'hub2@example.com',
            'business_name' => 'Laguna Swift Couriers', 'hub_postal_code' => '40A7',
            'password' => 'Delivery@2026', 'password_confirmation' => 'Delivery@2026', 'email_verification_otp' => '111222',
        ])->assertSessionHasErrors('hub_postal_code');
    }
}
