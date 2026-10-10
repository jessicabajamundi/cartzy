<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_registration_wizard_page_renders_steps(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
        $response->assertSee('Your personal details');
        $response->assertSee('Your sign-in details');
        $response->assertSee('Your verification code has been sent.');
        $response->assertSee('VERIFY EMAIL & CONTINUE');
    }
}

