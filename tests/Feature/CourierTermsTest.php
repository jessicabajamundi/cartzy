<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Cache, Mail, Storage};
use Tests\TestCase;

class CourierTermsTest extends TestCase
{
    use RefreshDatabase;

    private function payload(): array
    {
        Mail::fake();
        Storage::fake('public');
        $hub = LogisticsProvider::create(['user_id' => User::factory()->create(['role' => 'logistics', 'status' => 'active'])->id,
            'name' => 'Test Hub', 'slug' => 'test-hub', 'status' => 'approved']);
        Cache::put('otp_courier@example.test', ['otp' => '123456'], 600);
        return ['first_name' => 'Juan', 'last_name' => 'Cruz', 'name' => 'Juan Cruz',
            'email' => 'courier@example.test', 'phone' => '09171234567', 'birthday' => '1995-05-15',
            'role' => 'courier', 'courier_type' => 'first_mile', 'vehicle_type' => 'van',
            'logistics_provider_id' => $hub->id, 'logistics_partner' => $hub->name,
            'province' => 'Laguna', 'city' => 'Santa Cruz', 'barangay' => 'Bubukal',
            'plate_no' => 'ABC1234', 'license_number' => 'N01-12-345678', 'license_expiry' => '2028-10-10',
            'license_doc' => UploadedFile::fake()->create('license.jpg', 10, 'image/jpeg'),
            'or_cr_doc' => UploadedFile::fake()->create('orcr.jpg', 10, 'image/jpeg'),
            'password' => 'CourierPass123!', 'password_confirmation' => 'CourierPass123!',
            'email_verification_otp' => '123456', 'otp' => '123456'];
    }

    public function test_courier_terms_show_every_pdf_section_and_use_logistics_modal_styles_in_signin_step(): void
    {
        $response = $this->get(route('register.rider', ['type' => 'first_mile']))->assertOk();
        $policy = json_decode(file_get_contents(resource_path('legal/courier-terms.json')), true);
        $this->assertCount(23, array_filter($policy['blocks'], fn ($block) => $block['type'] === 'heading'));
        foreach ($policy['blocks'] as $block) {
            foreach ($block['items'] ?? [$block['text']] as $text) $response->assertSee($text);
        }
        $response->assertSee($policy['agreement'])->assertSee('class="logistics-terms-dialog"', false)
            ->assertSee('id="courierTermsAgreedBadge"', false)->assertSee('I Agree');
        $html = $response->getContent();
        $this->assertLessThan(strpos($html, 'id="courierTermsWrapper"'), strpos($html, 'id="riderStepSignIn"'));
        $this->assertLessThan(strpos($html, 'id="riderStepVerify"'), strpos($html, 'id="courierTermsWrapper"'));
        $this->get(route('register.rider'))->assertOk()->assertDontSee('id="courierTermsDialog"', false);
    }

    public function test_web_courier_cannot_register_until_terms_are_accepted(): void
    {
        $payload = $this->payload();
        $this->post(route('register.submit'), $payload)->assertSessionHasErrors('terms_agreed');
        $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
        $this->post(route('register.submit'), $payload + ['terms_agreed' => '1'])
            ->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));
        $this->assertDatabaseHas('users', ['email' => $payload['email'], 'status' => 'pending']);
    }

    public function test_api_checks_acceptance_after_vehicle_classification_and_registers_only_after_agreement(): void
    {
        $payload = $this->payload();
        unset($payload['license_doc'], $payload['or_cr_doc']);
        // A van remains a courier even if a client tries to call it a last-mile rider.
        $payload['courier_type'] = 'last_mile';
        $this->postJson('/api/auth/register/rider', $payload)->assertUnprocessable()->assertJsonValidationErrors('terms_agreed');
        $this->assertDatabaseMissing('users', ['email' => $payload['email']]);
        $this->postJson('/api/auth/register/rider', $payload + ['terms_agreed' => 1])->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => $payload['email'], 'status' => 'pending']);
    }
}
