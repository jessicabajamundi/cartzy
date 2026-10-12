<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationPhoneTest extends TestCase
{
    use RefreshDatabase;

    private function registration(string $role, string $phone): array
    {
        return [
            'role' => $role,
            'name' => 'Maria Cruz',
            'first_name' => 'Maria',
            'last_name' => 'Cruz',
            'email' => $role . '@example.com',
            'phone' => $phone,
            'birthday' => '1995-06-15',
            'password' => 'Tindahan@2026',
            'password_confirmation' => 'Tindahan@2026',
            'email_verification_otp' => '123456',
        ];
    }

    public function test_invalid_numbers_are_rejected_for_every_role_before_otp_or_storage(): void
    {
        Mail::fake();
        foreach (['buyer', 'seller', 'logistics', 'courier'] as $role) {
            foreach (['0917ABC4567', 'abcdefghijk', '0917-1234567', '0917123456', '091712345678', '08171234567'] as $phone) {
                foreach (['/register', '/register/request-otp'] as $endpoint) {
                    $this->postJson($endpoint, $this->registration($role, $phone))
                        ->assertUnprocessable()->assertJsonValidationErrors(['phone']);
                }
            }
            $this->assertNull(Cache::get('otp_' . $role . '@example.com'));
        }
        $this->assertDatabaseCount('users', 0);
        Mail::assertNothingSent();
    }

    public function test_valid_numbers_are_saved_with_the_leading_zero_for_all_roles(): void
    {
        foreach (['seller', 'logistics', 'courier', 'buyer'] as $index => $role) {
            $phone = '0917123456' . $index;
            $data = $this->registration($role, $phone);
            Cache::put('otp_' . $data['email'], ['otp' => '123456']);
            $this->post('/register', $data)->assertSessionHasNoErrors()->assertRedirect();
            $this->assertDatabaseHas('users', ['role' => $role, 'phone' => $phone]);
            if ($role === 'buyer') {
                $this->assertDatabaseHas('buyers', ['phone' => $phone]);
            }
            if ($role === 'logistics') {
                $this->assertDatabaseHas('logistics_providers', ['contact_phone' => $phone]);
            }
            Auth::logout();
        }
    }

    public function test_international_mobile_numbers_are_normalized_before_storage(): void
    {
        foreach (['seller', 'logistics', 'courier', 'buyer'] as $index => $role) {
            $data = $this->registration($role, '+63917123456' . $index);
            Cache::put('otp_' . $data['email'], ['otp' => '123456']);
            $this->post('/register', $data)->assertSessionHasNoErrors()->assertRedirect();
            $this->assertDatabaseHas('users', ['role' => $role, 'phone' => '0917123456' . $index]);
            Auth::logout();
        }
    }

    public function test_prefixed_form_saves_ten_digits_and_rejects_extra_digits(): void
    {
        foreach (['seller', 'logistics', 'courier', 'buyer'] as $index => $role) {
            $data = $this->registration($role, '917123456' . $index);
            $data['phone_country_code'] = '+63';
            Cache::put('otp_' . $data['email'], ['otp' => '123456']);
            $this->postJson('/register', array_replace($data, ['phone' => $data['phone'] . '1']))
                ->assertUnprocessable()->assertJsonValidationErrors(['phone']);
            $this->post('/register', $data)->assertSessionHasNoErrors()->assertRedirect();
            $this->assertDatabaseHas('users', ['role' => $role, 'phone' => '0' . $data['phone']]);
            Auth::logout();
        }
    }
}
