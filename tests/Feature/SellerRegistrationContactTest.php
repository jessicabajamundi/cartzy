<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SellerRegistrationContactTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Support\SellerSignupFields;

    private function registration(array $overrides = []): array
    {
        return array_replace([
            ...$this->sellerSignupFields(),
            'role' => 'seller',
            'birthday' => '1995-06-15',
            'first_name' => 'Maria Clara',
            'last_name' => 'Dela Cruz',
            'email' => 'seller.test01@example.com',
            'phone' => '09170000001',
            'password' => 'Tindahan@2026',
            'password_confirmation' => 'Tindahan@2026',
            'email_verification_otp' => '123456',
        ], $overrides);
    }

    public function test_tc_sel_007_invalid_emails_are_rejected_before_otp_and_registration(): void
    {
        Mail::fake();
        foreach (['mariaclara.santosexample.com', 'mariaclara.santos@', '@example.com', 'mariaclara.santos@example', 'mariaclara santos@example.com'] as $email) {
            foreach (['/register/request-otp', '/register'] as $url) {
                $this->postJson($url, $this->registration(['email' => $email]))
                    ->assertUnprocessable()->assertJsonValidationErrors(['email' => 'Please enter a valid email address.']);
            }
            $this->assertNull(Cache::get('otp_' . $email));
        }
        Mail::assertNothingSent();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_tc_sel_008_and_009_duplicate_emails_are_case_insensitive(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'mariaclara.santos@example.com']);
        foreach (['mariaclara.santos@example.com', 'MARIACLARA.SANTOS@EXAMPLE.COM', '  MARIACLARA.SANTOS@EXAMPLE.COM  '] as $email) {
            foreach (['/register/request-otp', '/register'] as $url) {
                $this->postJson($url, $this->registration(['email' => $email]))
                    ->assertUnprocessable()->assertJsonValidationErrors(['email' => 'This email is already in use. Please log in instead.']);
            }
        }
        // Existing mixed-case records must also count as duplicates.
        $user->update(['email' => 'MariaClara.Santos@Example.Com']);
        $this->postJson('/register', $this->registration(['email' => 'mariaclara.santos@example.com']))
            ->assertUnprocessable()->assertJsonValidationErrors(['email']);
        Mail::assertNothingSent();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('sellers', 0);
    }

    public function test_tc_sel_010_and_012_trimmed_email_and_eleven_digit_mobile_are_saved(): void
    {
        Mail::fake();
        $data = $this->registration(['email' => '  seller.test01@example.com  ']);
        $this->postJson('/register/request-otp', $data)->assertOk()->assertJson(['success' => true]);
        $otp = Cache::get('otp_seller.test01@example.com');
        $this->assertNotNull($otp);
        $data['email_verification_otp'] = $otp['otp'];
        $this->post('/register', $data)->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));
        $this->assertDatabaseHas('users', [
            'email' => 'seller.test01@example.com', 'phone' => '09170000001', 'role' => 'seller', 'status' => 'pending',
        ]);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('sellers', 1);
    }

    public function test_tc_sel_011_invalid_mobile_formats_are_rejected_without_creating_an_account(): void
    {
        foreach (['0917ABC4567', '0917-123-456', '12345678901', '02-8123-4567'] as $phone) {
            $this->postJson('/register', $this->registration(['phone' => $phone]))
                ->assertUnprocessable()->assertJsonValidationErrors([
                    'phone' => 'Please enter a valid mobile number starting with 09 and containing only digits.',
                ]);
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('sellers', 0);
    }

    public function test_tc_sel_012_ten_and_twelve_digits_show_a_length_error(): void
    {
        foreach (['0917123456', '091712345678'] as $phone) {
            $this->postJson('/register', $this->registration(['phone' => $phone]))
                ->assertUnprocessable()->assertJsonValidationErrors(['phone' => 'Mobile number must be exactly 11 digits.']);
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_buyer_registration_and_check_phone_reject_duplicate_mobile(): void
    {
        User::factory()->create(['phone' => '09170000001']);

        // Check phone endpoint
        $this->postJson('/register/check-phone', ['phone' => '9170000001', 'phone_country_code' => '+63'])
            ->assertOk()->assertJson(['available' => false, 'message' => 'This mobile number is already in use.']);

        $this->postJson('/register/check-phone', ['phone' => '9179999999', 'phone_country_code' => '+63'])
            ->assertOk()->assertJson(['available' => true]);

        // Buyer registration fails with duplicate mobile
        $buyerData = [
            'role' => 'buyer',
            'first_name' => 'Maria',
            'last_name' => 'Clara',
            'birthday' => '1995-06-15',
            'email' => 'buyer.test@example.com',
            'phone' => '09170000001',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'email_verification_otp' => '123456',
        ];
        $this->postJson('/register', $buyerData)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['phone' => 'This mobile number is already in use.']);
    }
}
