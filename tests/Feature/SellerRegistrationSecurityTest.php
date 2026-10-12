<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SellerRegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function data(array $overrides = []): array
    {
        Cache::put('otp_seller@example.com', ['otp' => '123456']);
        return array_replace([
            'role' => 'seller', 'first_name' => 'Maria Clara', 'last_name' => 'Dela Cruz',
            'birthday' => '1995-06-15',
            'email' => 'seller@example.com', 'phone' => '09189876543',
            'password' => 'Tindahan@2026', 'password_confirmation' => 'Tindahan@2026',
            'email_verification_otp' => '123456',
        ], $overrides);
    }

    public function test_tc_sel_013_and_014_duplicate_mobile_in_local_and_international_formats(): void
    {
        $existing = User::factory()->create(['phone' => '09189876543']);
        foreach (['09189876543', '+639189876543'] as $phone) {
            $this->postJson('/register', $this->data(['phone' => $phone]))
                ->assertUnprocessable()->assertJsonValidationErrors(['phone' => 'This mobile number is already in use.']);
            $this->postJson('/register/request-otp', $this->data(['phone' => $phone]))
                ->assertUnprocessable()->assertJsonValidationErrors(['phone' => 'This mobile number is already in use.']);
        }
        $existing->update(['phone' => '+639189876543']);
        $this->postJson('/register', $this->data())->assertUnprocessable()
            ->assertJsonValidationErrors(['phone' => 'This mobile number is already in use.']);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('sellers', 0);
    }

    public function test_tc_sel_015_seven_characters_are_rejected(): void
    {
        $this->postJson('/register', $this->data(['password' => 'Tind@26', 'password_confirmation' => 'Tind@26']))
            ->assertUnprocessable()->assertJsonValidationErrors(['password' => 'Password must be at least 8 characters long.']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_tc_sel_015_eight_characters_are_accepted_and_hashed(): void
    {
        $this->post('/register', $this->data(['password' => 'Tinda@26', 'password_confirmation' => 'Tinda@26']))
            ->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));
        $this->assertTrue(Hash::check('Tinda@26', User::where('email', 'seller@example.com')->firstOrFail()->password));
        $this->assertDatabaseCount('sellers', 1);
    }

    public function test_tc_sel_016_missing_character_types_are_rejected(): void
    {
        foreach ([
            'tindahan@2026' => 'The password field must contain at least one uppercase and one lowercase letter.',
            'TINDAHAN@2026' => 'The password field must contain at least one uppercase and one lowercase letter.',
            'Tindahan@abcd' => 'The password field must contain at least one number.',
            'Tindahan2026' => 'The password field must contain at least one symbol.',
        ] as $password => $message) {
            $this->postJson('/register', $this->data(['password' => $password, 'password_confirmation' => $password]))
                ->assertUnprocessable()->assertJsonValidationErrors(['password' => $message]);
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_tc_sel_017_confirmation_mismatch_is_rejected(): void
    {
        $this->postJson('/register', $this->data(['password_confirmation' => 'Tindahan@2027']))
            ->assertUnprocessable()->assertJsonValidationErrors(['password' => 'The password confirmation does not match.']);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_tc_sel_018_password_fields_start_masked_and_have_toggle_controls(): void
    {
        $html = $this->get('/register?role=seller')->assertOk()->getContent();
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        foreach (['register_password', 'password_confirmation'] as $id) {
            $this->assertSame('password', $dom->getElementById($id)->getAttribute('type'));
            $this->assertStringContainsString("togglePasswordVisibility('$id', this)", $html);
        }
    }

    public function test_new_international_mobile_is_saved_in_local_format(): void
    {
        $this->post('/register', $this->data(['phone' => '+639189876543']))
            ->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));
        $this->assertDatabaseHas('users', ['email' => 'seller@example.com', 'phone' => '09189876543']);
    }

    public function test_validation_errors_are_rendered_on_the_registration_form(): void
    {
        User::factory()->create(['phone' => '09189876543']);
        $this->from('/register?role=seller')->post('/register', $this->data([
            'password' => 'Tind@26', 'password_confirmation' => 'Tind@26',
        ]))->assertSessionHasErrors(['phone', 'password'])->assertRedirect('/register?role=seller');
        $html = $this->get('/register?role=seller')->assertOk()->getContent();
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        foreach (['phone_error' => 'This mobile number is already in use.', 'register_password_error' => 'Password must be at least 8 characters long.'] as $id => $message) {
            $element = $dom->getElementById($id);
            $this->assertSame($message, $element->textContent);
            $this->assertFalse($element->hasAttribute('hidden'));
        }
    }
}
