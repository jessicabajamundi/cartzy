<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Tests\TestCase;

class GoogleRegistrationAgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_google_users_must_complete_age_checked_registration(): void
    {
        $googleUser = (new GoogleUser)->map([
            'id' => 'google-new-buyer',
            'name' => 'New Buyer',
            'email' => 'new.buyer@example.com',
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['birthday'])
            ->assertSessionHasInput('email', 'new.buyer@example.com');
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_seller_cannot_sign_in_via_google(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'test_seller@example.com',
            'role' => \App\Models\User::ROLE_SELLER,
            'status' => 'active',
        ]);

        $googleUser = (new GoogleUser)->map([
            'id' => 'google-id-seller',
            'name' => 'Seller User',
            'email' => $user->email,
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login', ['portal' => 'seller']))
            ->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_logistics_cannot_sign_in_via_google(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'test_logistics@example.com',
            'role' => \App\Models\User::ROLE_LOGISTICS,
            'status' => 'active',
        ]);

        $googleUser = (new GoogleUser)->map([
            'id' => 'google-id-logistics',
            'name' => 'Logistics User',
            'email' => $user->email,
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login', ['portal' => 'logistics']))
            ->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_rider_cannot_sign_in_via_google(): void
    {
        $user = \App\Models\User::factory()->create([
            'email' => 'test_courier@example.com',
            'role' => \App\Models\User::ROLE_COURIER,
            'status' => 'active',
        ]);

        $googleUser = (new GoogleUser)->map([
            'id' => 'google-id-courier',
            'name' => 'Courier User',
            'email' => $user->email,
        ]);
        $provider = Mockery::mock();
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login', ['portal' => 'courier']))
            ->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_register_page_google_button_visibility(): void
    {
        // Buyer: Google button displayed
        $this->get(route('register', ['role' => 'buyer']))
            ->assertOk()
            ->assertSee('id="googleSignupWrapper" style="display: block; margin-bottom: 20px;"', false)
            ->assertSee('id="googleSignupDivider" style="display: flex; margin: 0 0 24px 0;"', false);

        // Seller: Google button hidden
        $this->get(route('register', ['role' => 'seller']))
            ->assertOk()
            ->assertSee('id="googleSignupWrapper" style="display: none; margin-bottom: 20px;"', false)
            ->assertSee('id="googleSignupDivider" style="display: none; margin: 0 0 24px 0;"', false);

        // Logistics: Google button hidden
        $this->get(route('register', ['role' => 'logistics']))
            ->assertOk()
            ->assertSee('id="googleSignupWrapper" style="display: none; margin-bottom: 20px;"', false)
            ->assertSee('id="googleSignupDivider" style="display: none; margin: 0 0 24px 0;"', false);
    }
}
