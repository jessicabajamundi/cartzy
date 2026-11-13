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

        $this->get('/seller/login')->assertOk()->assertSee('Seller Centre')->assertSee('Welcome, Seller');
    }

    public function test_login_starts_by_choosing_who_you_sign_in_as(): void
    {
        $this->get('/login')->assertOk()
            ->assertSee('data-step="choose"', false)
            ->assertSee('Continue as...')
            ->assertSeeInOrder([
                'Buyer', 'Shop, track orders and save favourites',
                'Seller', 'Manage your store, products and orders',
                'Logistics', 'Run pickups and riders for your hub',
                'Courier', 'In the app', 'First Mile: collect parcels from sellers for the hub',
                'Rider', 'In the app', 'Last Mile: deliver parcels from the hub to buyers',
                'Admin', 'Oversee the platform. Authorized staff only',
            ])
            ->assertSee('Riders sign in on the cartzy app')
            ->assertSee(route('rider.app'), false);
    }

    public function test_a_roles_own_login_page_opens_straight_on_its_form(): void
    {
        $this->get('/seller/login')->assertOk()
            ->assertSee('data-step="login"', false)
            ->assertSee('Sign In to Seller Centre')
            ->assertSee('name="portal" value="seller"', false);

        $this->get('/courier/login')->assertOk()
            ->assertSee('Download the app (APK)')
            ->assertDontSee('name="portal" value="courier"', false);

        $this->get('/rider/login')->assertOk()
            ->assertSee('Download the app (APK)')
            ->assertDontSee('name="portal" value="rider"', false);
    }

    public function test_courier_or_rider_cannot_sign_in_via_web_form(): void
    {
        $courier = User::factory()->create([
            'role' => User::ROLE_COURIER,
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email' => $courier->email,
            'password' => 'secret123',
            'portal' => 'buyer',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
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
