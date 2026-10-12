<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthSessionEndTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_routes_have_prevent_back_history_no_cache_headers(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $this->assertStringContainsString('no-store', (string)$response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-cache', (string)$response->headers->get('Cache-Control'));
        $this->assertEquals('no-cache', $response->headers->get('Pragma'));
    }

    public function test_logout_ends_session_and_redirects_with_no_cache_headers(): void
    {
        $user = User::where('role', 'buyer')->first() ?? User::factory()->create([
            'role' => 'buyer',
            'status' => 'active',
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $this->assertGuest();
        $this->assertStringContainsString('no-store', (string)$response->headers->get('Cache-Control'));
    }

    public function test_guest_cannot_access_protected_pages_and_gets_redirected_to_login(): void
    {
        $response = $this->get('/user/account');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/seller/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/logistics/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_navigating_back_to_protected_page_after_logout_is_blocked_and_redirects_to_login(): void
    {
        $user = User::factory()->create([
            'role' => 'buyer',
            'status' => 'active',
        ]);

        // User logs in and accesses their account / dashboard
        $this->actingAs($user);
        $dashboardPage = $this->get('/dashboard');
        $dashboardPage->assertStatus(200);

        // User logs out
        $logout = $this->post('/logout');
        $logout->assertRedirect('/login');
        $this->assertGuest();

        // Browser attempts to request the previous authenticated dashboard (e.g. clicking back button)
        $backAttempt = $this->get('/dashboard');
        $backAttempt->assertRedirect('/login');
        $this->assertStringContainsString('no-store', (string)$backAttempt->headers->get('Cache-Control'));
    }
}
