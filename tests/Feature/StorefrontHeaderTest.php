<?php

namespace Tests\Feature;

use App\Models\{Category, Product, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontHeaderTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_only_see_cart_and_wishlist_header_icons(): void
    {
        $response = $this->get(route('home'))->assertOk();

        foreach (['cart', 'wishlist'] as $icon) {
            $response->assertSee('data-buyer-nav="'.$icon.'"', false);
        }
        foreach (['orders', 'messages', 'notifications'] as $icon) {
            $response->assertDontSee('data-buyer-nav="'.$icon.'"', false);
        }
    }

    public function test_shopping_pages_keep_all_header_icons_for_buyers_and_sellers(): void
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Header shop', 'slug' => 'header-shop', 'status' => 'approved']);
        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true]);
        $product = Product::create(['seller_id' => $seller->id, 'category_id' => $category->id, 'name' => 'Header product', 'slug' => 'header-product', 'is_active' => true]);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);

        foreach ([$buyer, $sellerUser] as $user) {
            $this->actingAs($user);
            foreach ([route('home'), route('sale'), route('category.show', 'shoes'), route('products.show', $product->id), route('cart.index')] as $url) {
                $response = $this->get($url)->assertOk();
                foreach (['orders', 'messages', 'cart', 'wishlist', 'notifications'] as $icon) {
                    $response->assertSee('data-buyer-nav="'.$icon.'"', false);
                }
                $response->assertDontSee('aria-label="Account shortcuts"', false);
            }
        }
    }
}
