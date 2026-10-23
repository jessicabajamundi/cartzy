<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationOtpTransportTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\Support\SellerSignupFields;

    public function test_mail_is_addressed_to_applicant_and_code_is_not_exposed(): void
    {
        config(['mail.default' => 'smtp', 'app.debug' => true]);
        Mail::shouldReceive('send')->once()->withArgs(function ($view, $data, $callback) {
            $message = new \Illuminate\Mail\Message(new \Symfony\Component\Mime\Email());
            $callback($message);
            $this->assertSame('recipient@example.com', $message->getSymfonyMessage()->getTo()[0]->getAddress());
            $this->assertSame('emails.otp-verification', $view);
            $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $data['otp']);
            return true;
        });
        $this->postJson('/register/request-otp', ['email' => 'recipient@example.com'])
            ->assertOk()->assertJsonPath('mail_sent', true)->assertJsonMissingPath('otp_preview');
        $this->assertNotNull(Cache::get('otp_recipient@example.com'));
    }

    public function test_smtp_failure_does_not_report_success_or_activate_a_code(): void
    {
        config(['mail.default' => 'smtp', 'app.debug' => true]);
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP unavailable'));
        $this->postJson('/register/request-otp', ['email' => 'recipient@example.com'])
            ->assertStatus(503)->assertJsonPath('success', false)->assertJsonPath('mail_sent', false)
            ->assertJsonMissingPath('otp_preview');
        $this->assertNull(Cache::get('otp_recipient@example.com'));
    }

    public function test_log_mailer_cannot_claim_email_delivery(): void
    {
        config(['mail.default' => 'log']);
        $this->postJson('/register/request-otp', ['email' => 'recipient@example.com'])
            ->assertStatus(503)->assertJsonPath('success', false);
        $this->assertNull(Cache::get('otp_recipient@example.com'));
    }

    public function test_expired_code_cannot_be_recovered_from_old_history(): void
    {
        Mail::fake();
        $this->postJson('/register/request-otp', ['email' => 'recipient@example.com'])->assertOk();
        $item = Cache::get('otp_recipient@example.com');
        Cache::put('registration_otps', [$item], now()->addDay());
        $this->travel(11)->minutes();
        $this->postJson('/register/verify-otp', ['email' => 'recipient@example.com', 'otp' => $item['otp']])
            ->assertUnprocessable()->assertJsonPath('success', false);
    }

    public function test_registration_endpoints_remain_same_origin_behind_an_https_proxy(): void
    {
        // TLS terminates at the proxy; Laravel sees an HTTP request internally.
        $this->withHeaders(['X-Forwarded-Proto' => 'https'])
            ->get('http://cartzyy.shop/register')
            ->assertOk()
            ->assertSee('action="/register"', false)
            ->assertSee('data-request-otp-url="/register/request-otp"', false)
            ->assertSee('data-verify-otp-url="/register/verify-otp"', false)
            ->assertDontSee('data-request-otp-url="http:', false);
    }

    public function test_seller_can_request_verify_and_register_with_a_code(): void
    {
        Mail::fake();
        $email = 'signup-test@example.com';
        $this->postJson('/register/request-otp', [
            'email' => $email, 'name' => 'Maria Santos', 'role' => 'seller',
            'phone' => '9171234567', 'phone_country_code' => '+63',
        ])->assertOk()->assertJsonPath('success', true);

        $code = Cache::get('otp_'.$email)['otp'];
        $this->postJson('/register/verify-otp', ['email' => $email, 'otp' => $code])
            ->assertOk()->assertJsonPath('success', true);

        $this->post('/register', [
            'first_name' => 'Maria', 'last_name' => 'Santos',
            ...$this->sellerSignupFields(),
            'email' => $email, 'role' => 'seller', 'birthday' => '1995-06-15',
            'phone' => '9171234567', 'phone_country_code' => '+63',
            'password' => 'Account@2026', 'password_confirmation' => 'Account@2026',
            'email_verification_otp' => $code,
        ])->assertSessionHasNoErrors()->assertRedirect(route('register.pending'));

        $this->assertDatabaseHas('users', ['email' => $email, 'role' => 'seller', 'status' => 'pending']);
        $this->assertNotNull(User::where('email', $email)->firstOrFail()->email_verified_at);
    }
}
