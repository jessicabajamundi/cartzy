<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartSortingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cart_items_are_returned_most_recently_added_first_on_web_and_api(): void
    {
        $buyer = User::factory()->create(['role' => User::ROLE_BUYER]);
        $sellerUser = User::factory()->create(['role' => User::ROLE_SELLER]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name' => 'Demo Shop',
            'slug' => 'demo-shop',
            'status' => 'approved',
        ]);
        $category = Category::create(['name' => 'Gadgets', 'slug' => 'gadgets']);

        $variants = [];
        foreach (['Product A', 'Product B', 'Product C'] as $idx => $name) {
            $product = Product::create([
                'seller_id' => $seller->id,
                'category_id' => $category->id,
                'name' => $name,
                'slug' => 'product-' . $idx,
                'price_minor' => 10000,
                'stock' => 10,
                'status' => 'active',
            ]);
            $variants[$name] = ProductVariant::create([
                'product_id' => $product->id,
                'name' => 'Default',
                'sku' => 'SKU-' . $idx,
                'price_minor' => 10000,
                'stock' => 10,
            ]);
        }

        $cart = Cart::create(['user_id' => $buyer->id]);
        $base = now()->subMinutes(10);
        foreach (['Product A' => 0, 'Product B' => 2, 'Product C' => 5] as $name => $offsetMinutes) {
            $variant = $variants[$name];
            $item = $cart->items()->create([
                'cart_key' => 'variant_' . $variant->id,
                'product_variant_id' => $variant->id,
                'quantity' => 1,
                'selected' => true,
                'snapshot' => [
                    'id' => $variant->product_id,
                    'product_id' => $variant->product_id,
                    'variant_id' => $variant->id,
                    'name' => $name,
                    'price' => 100,
                    'image' => '',
                    'variation' => 'Default',
                    'store' => 'Demo Shop',
                    'seller_id' => $seller->id,
                ],
            ]);
            $item->forceFill(['created_at' => $base->copy()->addMinutes($offsetMinutes)])->saveQuietly();
        }

        // API check
        $token = $buyer->createToken('mobile')->plainTextToken;
        $apiNames = collect($this->withToken($token)->getJson('/api/cart')->assertOk()->json('items'))
            ->pluck('name')
            ->values()
            ->all();
        $this->assertSame(['Product C', 'Product B', 'Product A'], $apiNames);

        // Web check
        $this->actingAs($buyer, 'web')
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSeeInOrder(['Product C', 'Product B', 'Product A']);
    }
}
