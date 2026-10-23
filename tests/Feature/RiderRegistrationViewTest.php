<?php

namespace Tests\Feature;

use App\Models\LogisticsProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiderRegistrationViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_rider_registration_page_loads_with_details_step_and_fields(): void
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        LogisticsProvider::create([
            'user_id' => $user->id,
            'name' => 'Express Delivery — Metro Manila',
            'slug' => 'express-delivery-metro-manila',
            'status' => 'approved',
        ]);

        $response = $this->get(route('register.rider'));

        $response->assertOk()
            ->assertSee('Become a rider')
            ->assertSee('Apply to deliver for one of CARTZY\'s logistics partners.', false)
            ->assertSee('Your personal details')
            ->assertSee('name="first_name"', false)
            ->assertSee('name="last_name"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('name="birthday"', false)
            ->assertSee('Where you\'ll deliver', false)
            ->assertSee('name="logistics_provider_id"', false)
            ->assertSee('Express Delivery — Metro Manila')
            ->assertSee('Your vehicle')
            ->assertSee('name="vehicle_type"', false)
            ->assertSee('Motorcycle')
            ->assertSee('Bicycle')
            ->assertSee('E-bike')
            ->assertSee('Tricycle')
            ->assertSee('Car')
            ->assertSee('Van')
            ->assertSee('Truck')
            ->assertSee('name="plate_no"', false)
            ->assertSee('Driver\'s license', false)
            ->assertSee('name="license_number"', false)
            ->assertSee('name="license_expiry"', false)
            ->assertSee('Documents')
            ->assertSee('name="license_doc"', false)
            ->assertSee('name="or_cr_doc"', false)
            ->assertSee('CONTINUE TO SIGN-IN DETAILS');
    }

    public function test_register_page_has_become_a_rider_link_in_same_tab(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk()
            ->assertSee('id="linkRiderApply"', false)
            ->assertSee('href="' . route('register.rider') . '"', false)
            ->assertDontSee('id="linkRiderApply" target="_blank"', false);
    }

    public function test_rider_registration_name_inputs_match_buyer_setup(): void
    {
        $response = $this->get(route('register.rider'));

        $response->assertOk()
            ->assertSee('id="first_name"', false)
            ->assertSee('id="last_name"', false)
            ->assertSee('maxlength="50"', false)
            ->assertSee('Special characters and numbers are not allowed.');
    }

    public function test_rider_registration_plate_number_has_strict_attributes_and_placeholder(): void
    {
        $response = $this->get(route('register.rider'));

        $response->assertOk()
            ->assertSee('id="plate_no"', false)
            ->assertSee('maxlength="7"', false)
            ->assertSee('placeholder="123ABC or NBC1234"', false)
            ->assertSee('text-transform: uppercase', false)
            ->assertSee('Must be 6 or 7 characters (e.g. 123ABC or NBC1234)');
    }

    public function test_rider_registration_has_sign_in_and_verify_steps_matching_logistics_registration(): void
    {
        $response = $this->get(route('register.rider'));

        $response->assertOk()
            // Step 2: Sign-in
            ->assertSee('id="riderStepSignIn"', false)
            ->assertSee('Your sign-in details')
            ->assertSee('Set up your login credentials')
            ->assertSee('name="email"', false)
            ->assertSee('name="password"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertSee('Send verification code')
            // Step 3: Verify
            ->assertSee('id="riderStepVerify"', false)
            ->assertSee('Email verification code')
            ->assertSee('otp-boxes-wrapper', false)
            ->assertSee('name="email_verification_otp"', false)
            ->assertSee('VERIFY EMAIL &amp; SUBMIT APPLICATION', false)
            ->assertSee('id="resendOtpBtn"', false)
            ->assertSee('Change email or password');
    }

    public function test_rider_registration_submits_successfully_and_creates_rider_record(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $logisticsUser = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $provider = LogisticsProvider::create([
            'user_id' => $logisticsUser->id,
            'name' => 'FastTrack Express',
            'slug' => 'fasttrack-express',
            'status' => 'approved',
        ]);

        $email = 'rider.applicant@example.com';

        // 1. Request OTP
        $otpRes = $this->postJson(route('register.request_otp'), [
            'email' => $email,
            'name' => 'Juan Dela Cruz',
            'role' => 'courier',
            'phone' => '09171234567',
            'phone_country_code' => '+63',
        ]);

        $otpRes->assertOk()->assertJson(['success' => true]);
        $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $email);
        $this->assertNotNull($cachedOtp);
        $otpCode = $cachedOtp['otp'];

        // 2. Submit Rider Registration
        $file = \Illuminate\Http\UploadedFile::fake()->create('license.pdf', 20, 'application/pdf');
        $orCrFile = \Illuminate\Http\UploadedFile::fake()->create('or_cr.pdf', 20, 'application/pdf');

        $registerRes = $this->post(route('register.submit'), [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'name' => 'Juan Dela Cruz',
            'phone' => '09171234567',
            'phone_country_code' => '+63',
            'birthday' => '1995-05-15',
            'role' => 'courier',
            'logistics_provider_id' => $provider->id,
            'vehicle_type' => 'motorcycle',
            'plate_no' => 'ABC1234',
            'license_number' => 'N01-12-345678',
            'license_expiry' => '2028-10-10',
            'license_doc' => $file,
            'or_cr_doc' => $orCrFile,
            'email' => $email,
            'password' => 'P@ssw0rd123!',
            'password_confirmation' => 'P@ssw0rd123!',
            'email_verification_otp' => $otpCode,
        ]);

        $registerRes->assertRedirect(route('register.pending'));

        // Check user record
        $riderUser = User::where('email', $email)->first();
        $this->assertNotNull($riderUser);
        $this->assertEquals('courier', $riderUser->role);
        $this->assertEquals('pending', $riderUser->status);

        // Check rider record
        $rider = \App\Models\Rider::where('user_id', $riderUser->id)->first();
        $this->assertNotNull($rider);
        $this->assertEquals($provider->id, $rider->logistics_provider_id);
        $this->assertEquals('motorcycle', $rider->vehicle_type);
        $this->assertEquals('ABC1234', $rider->plate_no);
        $this->assertEquals('pending', $rider->status);
    }

    public function test_rider_registration_license_number_has_strict_attributes_and_placeholder(): void
    {
        $response = $this->get(route('register.rider'));

        $response->assertOk()
            ->assertSee('id="license_number"', false)
            ->assertSee('maxlength="13"', false)
            ->assertSee('placeholder="A00-00-000000"', false)
            ->assertSee('text-transform: uppercase', false)
            ->assertSee('Format: A00-00-000000 (e.g. H83-78-937937)')
            ->assertSee('formatLicenseNumber', false)
            ->assertSee('validateLicenseNumber', false);
    }

    public function test_rider_registration_validates_license_number_format(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $logisticsUser = User::factory()->create(['role' => 'logistics', 'status' => 'active']);
        $provider = LogisticsProvider::create([
            'user_id' => $logisticsUser->id,
            'name' => 'License Test Logistics',
            'slug' => 'license-test-logistics',
            'status' => 'approved',
        ]);

        $email = 'rider.license@example.com';
        $otpRes = $this->postJson(route('register.request_otp'), [
            'email' => $email,
            'name' => 'Maria Santos',
            'role' => 'courier',
            'phone' => '09181234567',
            'phone_country_code' => '+63',
        ]);
        $otpRes->assertOk();
        $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $email);
        $otpCode = $cachedOtp['otp'];

        $file = \Illuminate\Http\UploadedFile::fake()->create('license.pdf', 20, 'application/pdf');
        $orCrFile = \Illuminate\Http\UploadedFile::fake()->create('or_cr.pdf', 20, 'application/pdf');

        $baseData = [
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'name' => 'Maria Santos',
            'phone' => '09181234567',
            'phone_country_code' => '+63',
            'birthday' => '1996-06-20',
            'role' => 'courier',
            'logistics_provider_id' => $provider->id,
            'vehicle_type' => 'motorcycle',
            'plate_no' => 'ABC1234',
            'license_expiry' => '2028-10-10',
            'license_doc' => $file,
            'or_cr_doc' => $orCrFile,
            'email' => $email,
            'password' => 'P@ssw0rd123!',
            'password_confirmation' => 'P@ssw0rd123!',
            'email_verification_otp' => $otpCode,
        ];

        // 1. Incomplete / short license number must fail (bawal magkulang)
        $resShort = $this->post(route('register.submit'), [
            ...$baseData,
            'license_number' => 'H83-78-937', // too short (10 chars instead of 13)
        ]);
        $resShort->assertSessionHasErrors(['license_number']);

        // 2. Starting with digit must fail (format must start with letter A-Z)
        $resDigit = $this->post(route('register.submit'), [
            ...$baseData,
            'license_number' => '083-78-937937', // starts with digit instead of letter
        ]);
        $resDigit->assertSessionHasErrors(['license_number']);

        // 3. Exact valid format H83-78-937937 must pass
        $resValid = $this->post(route('register.submit'), [
            ...$baseData,
            'license_number' => 'H83-78-937937',
        ]);
        $resValid->assertRedirect(route('register.pending'));

        $riderUser = User::where('email', $email)->first();
        $this->assertNotNull($riderUser);
        $rider = \App\Models\Rider::where('user_id', $riderUser->id)->first();
        $this->assertNotNull($rider);
        $this->assertEquals($provider->id, $rider->logistics_provider_id);
    }
}

