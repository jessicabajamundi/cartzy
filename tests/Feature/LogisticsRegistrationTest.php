<?php

namespace Tests\Feature;

use App\Models\LogisticsProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class LogisticsRegistrationTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Support\LogisticsSignupFields;

    private function application(array $overrides = []): array
    {
        return [
            'role' => 'logistics', 'first_name' => 'Maria', 'last_name' => 'Santos',
            'email' => 'partner@example.com',
            'phone' => '9535333656', 'phone_country_code' => '+63',
            ...$this->logisticsSignupFields(),
            'password' => 'Delivery@2026', 'password_confirmation' => 'Delivery@2026',
            'email_verification_otp' => '123456',
            ...$overrides,
        ];
    }

    public function test_verified_logistics_registration_creates_pending_company_account(): void
    {
        Cache::put('otp_partner@example.com', ['otp' => '123456']);

        $this->post(route('register'), $this->application())
            ->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));

        $user = User::where('email', 'partner@example.com')->firstOrFail();
        $this->assertSame('logistics', $user->role);
        $this->assertSame('pending', $user->status);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->birthday);
        $this->assertSame('123456789000', $user->tin_number);
        $this->assertSame('Cavite', $user->province);
        $this->assertSame('Imus', $user->city);
        $this->assertSame('4103', $user->postal_code);
        $this->assertNotNull($user->sec_certificate);
        $this->assertNotNull($user->bir_certificate);
        $this->assertNotNull($user->dti_permit);
        $this->assertNotNull($user->id_photo);

        $provider = LogisticsProvider::where('user_id', $user->id)->firstOrFail();
        $this->assertSame('Cavite Delivery', $provider->name);
        $this->assertSame('pending', $provider->status);
        $this->assertSame('CS202012345', $provider->sec_registration_no);
        $this->assertSame('Operations Manager', $provider->contact_position);
        $this->assertSame(['NCR', 'IV-A'], $provider->service_regions);
        $this->assertSame('NCR, Region IV-A', $provider->serviceRegionsSummary());
        $this->assertGuest();
    }

    public function test_logistics_registration_requires_company_details_regions_and_documents(): void
    {
        Cache::put('otp_partner@example.com', ['otp' => '123456']);

        $this->post(route('register'), $this->application([
            'sec_registration_no' => '', 'tin_number' => '12345', 'contact_position' => '',
            'service_regions' => [], 'office_postal_code' => '41', 'business_permit' => null, 'id_photo' => null,
        ]))->assertSessionHasErrors([
            'sec_registration_no', 'tin_number', 'contact_position', 'service_regions',
            'office_postal_code', 'business_permit', 'id_photo',
        ]);

        $this->post(route('register'), $this->application(['service_regions' => ['ATLANTIS']]))
            ->assertSessionHasErrors('service_regions.0');
        $this->assertDatabaseMissing('users', ['email' => 'partner@example.com']);
    }

    public function test_company_name_must_not_be_used_by_another_partner(): void
    {
        $owner = User::factory()->create(['role' => 'logistics']);
        LogisticsProvider::create(['user_id' => $owner->id, 'name' => 'Cavite Delivery', 'slug' => 'cavite-delivery', 'status' => 'approved']);
        Cache::put('otp_partner@example.com', ['otp' => '123456']);

        $this->post(route('register'), $this->application(['business_name' => 'cavite delivery']))
            ->assertSessionHasErrors('business_name');
    }

    public function test_logistics_signup_shows_seven_steps(): void
    {
        $this->get(route('register', ['role' => 'logistics']))->assertOk()
            ->assertSee('Become a Logistics Partner')
            ->assertSee('id="birthdayFieldWrapper" style="display: none; margin-bottom: 26px;"', false)
            ->assertSee('id="logisticsProgress"', false)
            ->assertSee('of 7', false)
            ->assertSee('data-signup-page="5"', false)
            ->assertSee('SEC registration no.')
            ->assertSee('Region IV-A (CALABARZON)')
            ->assertSee('name="office_region"', false)
            ->assertSee("Mayor's / Business Permit")
            ->assertSee('Valid ID of Authorized Representative')
            ->assertSee('js/auth/register.js')
            ->assertDontSee('name="service_area"', false)
            ->assertDontSee('window.selectRole = function(role)', false);
    }
}
