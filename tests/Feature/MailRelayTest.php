<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MailRelayTest extends TestCase
{
    use RefreshDatabase;

    private function useRelay(): void
    {
        config([
            'services.mail_webhook.url' => 'https://script.google.com/macros/s/test/exec',
            'services.mail_webhook.secret' => 'test-secret',
            'mail.default' => 'relay',
        ]);
    }

    public function test_registration_code_is_emailed_through_the_relay(): void
    {
        $this->useRelay();
        Http::fake(['script.google.com/*' => Http::response(['ok' => true])]);

        $this->postJson(route('register.request_otp'), [
            'email' => 'partner@example.com', 'name' => 'Maria Santos', 'role' => 'logistics',
            'phone' => '9535333656', 'phone_country_code' => '+63',
        ])->assertOk()->assertJson(['success' => true, 'mail_sent' => true]);

        $code = Cache::get('otp_partner@example.com')['otp'];
        Http::assertSent(fn ($request) => $request['secret'] === 'test-secret'
            && $request['to'] === 'partner@example.com'
            && $request['subject'] === 'Your Cartzy email verification code'
            && str_contains($request['html'], $code));
    }

    public function test_a_relay_failure_keeps_the_code_inactive_and_reports_it(): void
    {
        $this->useRelay();
        Http::fake(['script.google.com/*' => Http::response(['ok' => false, 'error' => 'Unauthorized.'])]);

        $this->postJson(route('register.request_otp'), [
            'email' => 'partner@example.com', 'name' => 'Maria Santos', 'role' => 'logistics',
            'phone' => '9535333656', 'phone_country_code' => '+63',
        ])->assertStatus(503)->assertJson(['success' => false, 'mail_sent' => false]);

        $this->assertNull(Cache::get('otp_partner@example.com'));
    }
}
