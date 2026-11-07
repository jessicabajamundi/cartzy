<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginModalTest extends TestCase
{
    use RefreshDatabase;

    private function buyer(): User
    {
        return User::factory()->create(['role' => 'buyer', 'status' => 'active', 'password' => bcrypt('secret-pass')]);
    }

    public function test_login_pages_render_the_modal_open(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('id="login-modal"', false)
            ->assertSee('is-open is-standalone', false)
            ->assertSee('Welcome back');

        $this->get('/seller/login')->assertOk()->assertSee('Seller Centre');
    }

    public function test_people_choose_who_they_sign_in_as(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('Sign in as')
            ->assertSeeInOrder(['Buyer', 'Seller', 'Logistics', 'Rider', 'In the app', 'Admin'])
            ->assertSee('data-lm-role="buyer"'."\n".'                                aria-checked="true"', false)
            ->assertSee('Riders sign in on the cartzy app')
            ->assertSee(route('login.courier'), false);

        // The seller page opens with Seller already picked.
        $this->get('/seller/login')->assertOk()
            ->assertSee('data-lm-role="seller"'."\n".'                                aria-checked="true"', false)
            ->assertSee('name="portal" value="seller"', false);
    }

    public function test_storefront_includes_the_modal_for_guests_only(): void
    {
        $this->get('/')->assertOk()->assertSee('id="login-modal"', false)->assertSee('name="redirect_to" value="/"', false);

        $this->actingAs($this->buyer())->get('/')->assertOk()->assertDontSee('id="login-modal"', false);
    }

    public function test_buyer_returns_to_the_page_the_modal_was_opened_on(): void
    {
        $buyer = $this->buyer();

        $this->post('/login', [
            'email' => $buyer->email, 'password' => 'secret-pass', 'portal' => 'buyer', 'redirect_to' => '/sale?page=2',
        ])->assertRedirect(url('/sale?page=2'));
    }

    public function test_offsite_redirect_targets_are_ignored(): void
    {
        $buyer = $this->buyer();

        $this->post('/login', [
            'email' => $buyer->email, 'password' => 'secret-pass', 'portal' => 'buyer', 'redirect_to' => '//evil.example/steal',
        ])->assertRedirect(route('home'));
    }
}
