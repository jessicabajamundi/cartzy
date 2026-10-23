<?php

namespace Tests\Feature;

use App\Models\HeroSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeroCarouselTest extends TestCase
{
    use RefreshDatabase;

    public function test_halloween_is_persisted_and_shown_first(): void
    {
        $this->assertDatabaseHas('hero_slides', [
            'key' => 'halloween',
            'image_path' => 'images/banners/cartzy-halloween-web.png',
            'position' => 0,
            'is_active' => true,
        ]);
        $this->assertFileExists(public_path(HeroSlide::where('key', 'halloween')->firstOrFail()->image_path));
        $this->get('/')->assertOk()->assertSeeInOrder([
            'Spooky season.', 'Good finds.', 'Tune into more.',
        ])->assertSee('/category/electronics', false)
            ->assertSee('images/banners/cartzy-halloween-web.png', false)
            ->assertDontSee('images/banners/cartzy-halloween.jpg', false);
    }

    public function test_carousel_reads_database_content_and_visibility(): void
    {
        HeroSlide::where('key', 'halloween')->update(['title' => 'Updated seasonal collection']);
        $this->get('/')->assertOk()->assertSee('Updated seasonal collection')->assertDontSee('Spooky season.');

        HeroSlide::where('key', 'halloween')->update(['is_active' => false]);
        $this->get('/')->assertOk()->assertDontSee('Updated seasonal collection')->assertSee('Good finds.');
    }

    public function test_homepage_handles_no_active_slides(): void
    {
        HeroSlide::query()->update(['is_active' => false]);
        $this->get('/')->assertOk()->assertDontSee('data-market-hero', false);
    }
}
