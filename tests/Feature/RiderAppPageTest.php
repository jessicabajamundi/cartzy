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
            ->assertSee('Works on most Android phones')
            ->assertSee('Version 1.2.11')
            ->assertSee('Courier')
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

    private const ANDROID_CHROME = 'Mozilla/5.0 (Linux; Android 14; SM-A546E) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36';

    public function test_android_phone_browser_gets_the_app_page_instead_of_the_home_page(): void
    {
        $this->fakeLatestRelease();

        $this->withHeader('User-Agent', self::ANDROID_CHROME)->get('/')
            ->assertOk()
            ->assertSee('CARTZY FOR ANDROID')
            ->assertSee('Download the app (APK)')
            ->assertSee('Courier')
            ->assertSee(route('rider.app.download'))
            ->assertHeader('Vary');
    }

    public function test_computers_iphones_and_crawlers_still_get_the_store(): void
    {
        $this->fakeLatestRelease();
        $agents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36',
            'Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.0 Mobile/15E148 Safari/604.1',
            'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)',
        ];

        foreach ($agents as $agent) {
            $this->withHeader('User-Agent', $agent)->get('/')
                ->assertOk()
                ->assertDontSee('CARTZY FOR ANDROID');
        }
    }

    public function test_only_the_home_page_changes_on_android(): void
    {
        $this->fakeLatestRelease();

        // Product and store links shared to a phone, and the seller login, still open normally.
        $this->withHeader('User-Agent', self::ANDROID_CHROME)->get(route('login.seller'))
            ->assertOk()->assertSee('name="password"', false);
        $this->withHeader('User-Agent', self::ANDROID_CHROME)->get(route('rider.app'))
            ->assertOk()->assertSee('Deliver with the', false);
    }
}
