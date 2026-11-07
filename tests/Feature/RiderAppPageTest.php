<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RiderAppPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function fakeLatestRelease(): void
    {
        Http::fake([
            'api.github.com/repos/jessicabajamundi/cartzy/releases/latest' => Http::response([
                'tag_name' => 'v1.2.11',
                'assets' => [
                    ['name' => 'notes.txt', 'browser_download_url' => 'https://github.com/x/notes.txt'],
                    ['name' => 'cartzy-mobile-v1.2.11.apk', 'browser_download_url' => 'https://github.com/jessicabajamundi/cartzy/releases/download/v1.2.11/cartzy-mobile-v1.2.11.apk'],
                ],
            ]),
        ]);
    }

    public function test_rider_login_page_is_the_app_download_page(): void
    {
        $this->fakeLatestRelease();

        $this->get(route('login.courier'))
            ->assertOk()
            ->assertSee('Deliver with the', false)
            ->assertSee('Download the app (APK)')
            ->assertSee('Android 7.0 or newer')
            ->assertSee('Version 1.2.11')
            ->assertSee('Login as Rider')
            ->assertSee('cartzy-mobile-v1.2.11.apk')
            ->assertSee(route('rider.app.download'))
            ->assertSee(route('register.rider'))
            ->assertDontSee('name="password"', false);
    }

    public function test_download_goes_to_the_newest_apk(): void
    {
        $this->fakeLatestRelease();

        $this->get(route('rider.app.download'))
            ->assertRedirect('https://github.com/jessicabajamundi/cartzy/releases/download/v1.2.11/cartzy-mobile-v1.2.11.apk');
    }

    public function test_download_falls_back_to_the_releases_page_when_github_is_down(): void
    {
        Http::fake(['api.github.com/*' => Http::response([], 503)]);

        $this->get(route('rider.app.download'))
            ->assertRedirect('https://github.com/jessicabajamundi/cartzy/releases/latest');
        $this->get(route('rider.app'))->assertOk()->assertDontSee('Version ');
    }

    public function test_a_signed_in_rider_opening_the_hub_in_a_browser_gets_the_app_page(): void
    {
        $this->fakeLatestRelease();
        $rider = User::factory()->create(['role' => User::ROLE_COURIER, 'status' => 'active', 'name' => 'Arnel Gomez']);

        $this->actingAs($rider)->get('/courier/dashboard')->assertRedirect(route('rider.app'));
        $this->actingAs($rider)->get(route('rider.app'))
            ->assertOk()
            ->assertSee('signed in on the website as Arnel Gomez')
            ->assertSee('Log out');
    }

    public function test_other_accounts_still_use_the_website(): void
    {
        $this->fakeLatestRelease();

        $this->get(route('login.seller'))->assertOk()->assertSee('name="password"', false);
        $this->get(route('rider.app'))->assertSee(route('login.seller'))->assertSee(route('login.logistics'));
    }
}
