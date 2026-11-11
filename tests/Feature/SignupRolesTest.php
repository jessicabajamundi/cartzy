<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** The sign-up modal starts with "Sign up as..." Buyer, Seller, Logistics, Rider or Courier. */
class SignupRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_sign_up_starts_by_choosing_who_you_are(): void
    {
        $response = $this->get(route('register'))->assertOk()
            ->assertSee('Sign up as...')
            ->assertSeeInOrder(['Buyer', 'Seller', 'Logistics', 'Rider', 'Courier'])
            ->assertSee('data-su-role="buyer"', false)
            ->assertSee('data-su-role="seller"', false)
            ->assertSee('data-su-role="logistics"', false)
            ->assertSee(route('register.rider', ['type' => 'last_mile']), false)
            ->assertSee(route('register.rider', ['type' => 'first_mile']), false)
            ->assertSee('aria-labelledby="su-choose-title"', false);

        // The form waits behind the role picker, in the same 540px dialog as the login.
        $html = $response->getContent();
        $this->assertMatchesRegularExpression('/data-su-step="choose"\s+data-su-roles/', $html);
        $this->assertMatchesRegularExpression('/data-su-step="form"\s+hidden/', $html);
        $this->assertStringNotContainsString('lm-dialog-wide', $html);
    }

    public function test_a_link_that_names_the_role_opens_its_form_directly(): void
    {
        $html = $this->get(route('register', ['role' => 'seller']))->assertOk()
            ->assertSee('aria-labelledby="authTitle"', false)
            ->assertSee('<span data-su-badge-label>Seller</span>', false)
            ->getContent();
        $this->assertMatchesRegularExpression('/data-su-step="choose"\s+hidden/', $html);
        $this->assertDoesNotMatchRegularExpression('/data-su-step="form"\s+hidden/', $html);

        // Shoppers sent from the cart are buyers.
        $this->get(route('register', ['from' => 'cart']))->assertOk()
            ->assertSee('<span data-su-badge-label>Buyer</span>', false)
            ->assertSee('aria-labelledby="authTitle"', false);
    }

    public function test_rider_and_courier_apply_as_last_mile_and_first_mile(): void
    {
        $this->get(route('register.rider', ['type' => 'first_mile']))->assertOk()
            ->assertSee('Become a courier')
            ->assertSee('Courier · First Mile')
            ->assertSee('name="courier_type" value="first_mile"', false)
            ->assertSee('class="lm-dialog lm-dialog-form"', false)
            ->assertSee(route('register'), false);

        $this->get(route('register.rider'))->assertOk()
            ->assertSee('Become a rider')
            ->assertSee('Rider · Last Mile')
            ->assertSee('name="courier_type" value="last_mile"', false);
    }
}
