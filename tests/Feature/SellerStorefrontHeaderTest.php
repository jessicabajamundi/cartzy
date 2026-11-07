<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStorefrontHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_sees_guest_style_header_with_dashboard_link_on_the_landing_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'seller']))
            ->get(route('home'))
            ->assertOk()
            ->assertSee('data-buyer-nav="cart"', false)
            ->assertSee('data-buyer-nav="wishlist"', false)
            ->assertDontSee('data-buyer-nav="orders"', false)
            ->assertDontSee('data-buyer-nav="messages"', false)
            ->assertDontSee('data-buyer-nav="notifications"', false)
            ->assertSee('<a href="'.route('seller.dashboard').'"', false)
            ->assertDontSee('id="header-profile-menu"', false);
    }

    public function test_buyer_keeps_the_full_header_on_the_landing_page(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'buyer']))
            ->get(route('home'))
            ->assertOk()
            ->assertSee('data-buyer-nav="orders"', false)
            ->assertSee('data-buyer-nav="notifications"', false)
            ->assertDontSee('<a href="'.route('seller.dashboard').'"', false);
    }
}
