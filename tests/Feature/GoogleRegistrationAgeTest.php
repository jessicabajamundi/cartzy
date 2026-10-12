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
}
