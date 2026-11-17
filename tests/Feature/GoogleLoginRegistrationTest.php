<?php

namespace Tests\Feature;

use App\Models\User;
use Firebase\JWT\JWT;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Hash, Http};
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleLoginRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private function identity(string $email = 'buyer@gmail.com', string $sub = 'google-buyer'): void
    {
        $google = (new GoogleUser)->map(['id' => $sub, 'email' => $email, 'name' => 'Google Buyer']);
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($google);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    private function token(array $overrides = []): string
    {
        config(['services.google.client_id' => 'cartzy-test-client', 'services.google.mobile_client_id' => null]);
        $options = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];
        $localConfig = dirname(PHP_BINARY).'/extras/ssl/openssl.cnf';
        if (is_file($localConfig)) $options['config'] = $localConfig;
        $key = openssl_pkey_new($options);
        openssl_pkey_export($key, $private, null, $options);
        $rsa = openssl_pkey_get_details($key)['rsa'];
        Http::fake(['www.googleapis.com/oauth2/v3/certs' => Http::response(['keys' => [[
            'kty' => 'RSA', 'kid' => 'test-key', 'alg' => 'RS256', 'use' => 'sig',
            'n' => JWT::urlsafeB64Encode($rsa['n']), 'e' => JWT::urlsafeB64Encode($rsa['e']),
        ]]])]);
        return JWT::encode($overrides + ['iss' => 'https://accounts.google.com', 'aud' => 'cartzy-test-client',
            'iat' => time() - 5, 'exp' => time() + 3600, 'sub' => 'google-buyer',
            'email' => 'buyer@gmail.com', 'email_verified' => true, 'name' => 'Google Buyer'], $private, 'RS256', 'test-key');
    }

    public function test_web_login_unknown_google_email_does_not_register(): void
    {
        $this->identity();
        $this->get('/auth/google/callback')->assertRedirect(route('login'))
            ->assertSessionHasErrors(['email' => 'No Cartzy account found for this Google email. Please register first.']);
        $this->assertGuest();
        $this->assertSame(0, User::count());
    }

    public function test_web_registration_existing_email_does_not_link_or_login(): void
    {
        $buyer = User::factory()->create(['email' => 'buyer@gmail.com', 'role' => 'buyer', 'status' => 'active', 'google_id' => null]);
        $originalPassword = $buyer->password;
        $this->identity();
        $this->withSession(['auth_google_intent' => 'register'])->get('/auth/google/callback')->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email' => 'This Google email already has a Cartzy account. Please log in.']);
        $this->assertGuest();
        $this->assertSame(1, User::count());
        $this->assertNull($buyer->fresh()->google_id);
        $this->assertSame($originalPassword, $buyer->fresh()->password);
    }

    public function test_web_login_existing_email_links_google_and_keeps_password(): void
    {
        $buyer = User::factory()->create(['email' => 'buyer@gmail.com', 'role' => 'buyer', 'status' => 'active',
            'password' => Hash::make('ExistingPass123!'), 'has_custom_password' => true]);
        $this->identity();
        $this->get('/auth/google/callback')->assertRedirect();
        $this->assertAuthenticatedAs($buyer);
        $this->assertSame('google-buyer', $buyer->fresh()->google_id);
        $this->assertTrue(Hash::check('ExistingPass123!', $buyer->fresh()->password));
        $this->assertTrue($buyer->fresh()->has_custom_password);
    }

    public function test_api_login_unknown_email_cannot_create_an_account(): void
    {
        $this->postJson('/api/auth/google', ['intent' => 'login', 'id_token' => $this->token()])->assertUnprocessable()
            ->assertJsonValidationErrors('email');
        $this->assertSame(0, User::count());
    }

    public function test_api_registration_creates_new_buyer_and_retry_does_not_log_in(): void
    {
        $token = $this->token();
        $payload = ['intent' => 'register', 'id_token' => $token];
        $this->postJson('/api/auth/google', $payload)->assertOk()->assertJsonPath('user.email', 'buyer@gmail.com')
            ->assertJsonPath('user.has_custom_password', false)->assertJsonStructure(['token']);
        $buyer = User::firstOrFail();
        $this->postJson('/api/auth/google', $payload)->assertUnprocessable();
        $this->assertSame(1, User::count());
        $this->assertSame(1, $buyer->tokens()->count());
    }

    public function test_api_login_existing_account_uses_verified_token_email_not_supplied_email(): void
    {
        $buyer = User::factory()->create(['email' => 'buyer@gmail.com', 'role' => 'buyer', 'status' => 'active']);
        $other = User::factory()->create(['email' => 'other@gmail.com', 'role' => 'buyer', 'status' => 'active']);
        $this->postJson('/api/auth/google', ['intent' => 'login', 'id_token' => $this->token(), 'email' => $other->email])
            ->assertOk()->assertJsonPath('user.id', $buyer->id);
        $this->assertSame(0, $other->tokens()->count());
    }

    public function test_api_rejects_email_only_login_and_forged_or_wrong_audience_tokens(): void
    {
        $wrongAudience = $this->token(['aud' => 'another-app']);
        $this->postJson('/api/auth/google', ['intent' => 'login', 'email' => 'buyer@gmail.com'])->assertUnprocessable();
        $this->postJson('/api/auth/google', ['intent' => 'register', 'id_token' => 'forged'])->assertUnprocessable();
        $this->postJson('/api/auth/google', ['intent' => 'register', 'id_token' => $wrongAudience])->assertUnprocessable();
        $this->assertSame(0, User::count());
    }

    public function test_api_rejects_expired_tokens(): void
    {
        $this->postJson('/api/auth/google', ['intent' => 'register', 'id_token' => $this->token(['exp' => time() - 60])])->assertUnprocessable();
        $this->assertSame(0, User::count());
    }

    public function test_google_login_cannot_access_removed_account_or_replace_existing_google_id(): void
    {
        $buyer = User::factory()->create(['email' => 'buyer@gmail.com', 'role' => 'buyer', 'status' => 'removed', 'google_id' => 'different-google']);
        $this->postJson('/api/auth/google', ['intent' => 'login', 'id_token' => $this->token()])->assertUnprocessable();
        $this->assertSame('different-google', $buyer->fresh()->google_id);
        $this->assertSame(0, $buyer->tokens()->count());
    }
}
