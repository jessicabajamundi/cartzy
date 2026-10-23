<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontWishlistTest extends TestCase
{
    use RefreshDatabase;

    private function product(): array
    {
        return ['ref' => '1', 'name' => 'Smart Fitness Tracker Watch IP68', 'price' => 1299,
            'image' => 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500&h=500&fit=crop&q=80',
            'variation' => 'Standard'];
    }

    private function setupStorefrontProduct(): Product
    {
        $sellerUser = User::factory()->create(['status' => 'active', 'role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name' => 'TechZone Official Mall',
            'slug' => 'techzone-official-mall',
            'status' => 'approved',
        ]);
        $category = Category::firstOrCreate(['slug' => 'smart-wearables'], ['name' => 'Smart Watches & Wearables', 'position' => 1, 'is_active' => true]);
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Smart Fitness Tracker Watch IP68',
            'slug' => 'smart-fitness-tracker-watch-ip68',
            'description' => 'Test',
            'is_active' => true,
        ]);
        $product->variants()->create([
            'name' => 'Standard',
            'sku' => 'TEST-001',
            'price_minor' => 129900,
            'stock' => 10,
            'is_active' => true,
        ]);
        return $product;
    }

    public function test_guest_sees_wishlist_buttons_but_cannot_save(): void
    {
        $this->setupStorefrontProduct();
        $response = $this->get('/')->assertOk()->assertSee('data-authenticated="false"', false);
        $this->assertSame(1, substr_count($response->getContent(), 'class="discovery-wishlist"'));
        $this->postJson(route('wishlist.toggle'), $this->product())->assertUnauthorized();
        $this->assertDatabaseCount('wishlists', 0);
    }

    public function test_saved_heart_survives_reload_and_can_be_removed(): void
    {
        $this->setupStorefrontProduct();
        $user = User::factory()->create();
        $this->actingAs($user)->postJson(route('wishlist.toggle'), $this->product())
            ->assertOk()->assertJson(['wished' => true, 'count' => 1]);
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'ref' => '1', 'price_minor' => 129900]);
        $this->get('/')->assertOk()->assertSee('aria-label="Remove from wishlist: Smart Fitness Tracker Watch IP68"', false);
        $this->postJson(route('wishlist.toggle'), $this->product())
            ->assertOk()->assertJson(['wished' => false, 'count' => 0]);
        $this->get('/')->assertOk()->assertDontSee('aria-label="Remove from wishlist:', false);
    }

    public function test_saved_state_is_private_to_each_account(): void
    {
        $owner = User::factory()->create();
        Wishlist::create(array_merge($this->product(), ['user_id' => $owner->id, 'price_minor' => 129900]));
        $this->actingAs(User::factory()->create())->get('/')
            ->assertOk()->assertDontSee('aria-label="Remove from wishlist:', false);
        $this->postJson(route('wishlist.toggle'), $this->product())->assertOk()->assertJson(['wished' => true]);
        $this->assertDatabaseCount('wishlists', 2);
    }

    public function test_wishlist_form_uses_the_current_origin_even_behind_an_https_proxy(): void
    {
        $this->setupStorefrontProduct();
        \Illuminate\Support\Facades\URL::forceRootUrl('http://internal-app.test');
        try {
            $response = $this->get('/')->assertOk();
            $response->assertSee('action="/wishlist/toggle"', false)
                ->assertSee('data-login-url="/login"', false);
            $dom = new \DOMDocument();
            @$dom->loadHTML($response->getContent());
            $xpath = new \DOMXPath($dom);
            $forms = $xpath->query('//form[@class="discovery-wishlist-form"]');
            $this->assertCount(1, $forms);
            $payload = [];
            foreach ($xpath->query('.//input', $forms->item(0)) as $input) {
                $payload[$input->getAttribute('name')] = $input->getAttribute('value');
            }
            $this->assertArrayHasKey('_token', $payload);
            $this->actingAs(User::factory()->create())
                ->postJson('/wishlist/toggle', $payload)
                ->assertOk()->assertJson(['success' => true, 'wished' => true]);
        } finally {
            \Illuminate\Support\Facades\URL::forceRootUrl(null);
        }
    }

    public function test_wishlist_can_be_saved_and_removed_without_javascript(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('wishlist.toggle'), $this->product())
            ->assertRedirect(route('home').'#daily-discover-section')
            ->assertSessionHas('wishlist_status', 'Saved to your wishlist.');
        $this->assertDatabaseHas('wishlists', ['user_id' => $user->id, 'ref' => '1']);
        $this->get(route('buyer.dashboard', ['tab' => 'wishlist']))
            ->assertOk()->assertSee($this->product()['name']);
        $this->post(route('wishlist.toggle'), $this->product())
            ->assertRedirect(route('home').'#daily-discover-section')
            ->assertSessionHas('wishlist_status', 'Removed from your wishlist.');
        $this->assertDatabaseCount('wishlists', 0);
    }
}
