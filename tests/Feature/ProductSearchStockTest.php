<?php

namespace Tests\Feature;

use App\Models\{Category, Product, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchStockTest extends TestCase
{
    use RefreshDatabase;

    private function createSellerWithProduct(int $stock = 0, bool $isActive = true): array
    {
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name' => 'Tech Boutique',
            'slug' => 'tech-boutique-'.$sellerUser->id,
            'status' => 'approved',
        ]);
        $category = Category::firstOrCreate(['slug' => 'electronics'], ['name' => 'Electronics', 'is_active' => true]);
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Mechanical Wireless Keyboard',
            'slug' => 'mechanical-wireless-keyboard-'.$sellerUser->id,
            'price_minor' => 250000,
            'is_active' => $isActive,
        ]);
        $variant = $product->variants()->create([
            'name' => 'Standard Edition',
            'sku' => 'KB-ZERO-'.$sellerUser->id,
            'price_minor' => 250000,
            'stock' => $stock,
            'is_active' => true,
        ]);

        return [$sellerUser, $seller, $product, $variant];
    }

    public function test_sold_out_product_can_be_saved_but_only_added_to_cart_after_restock(): void
    {
        [, , $product, $variant] = $this->createSellerWithProduct();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $payload = ['id' => (string) $product->id, 'name' => $product->name,
            'price' => 2500, 'variation' => $variant->name, 'quantity' => 1];

        $this->actingAs($buyer)->postJson(route('wishlist.toggle'),
            array_merge($payload, ['ref' => (string) $product->id]))
            ->assertOk()->assertJsonPath('wished', true);
        $this->postJson('/cart/add', $payload)->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->assertDatabaseCount('cart_items', 0);
        $this->assertDatabaseCount('wishlists', 1);
        $this->get(route('buyer.dashboard', ['tab' => 'wishlist']))
            ->assertOk()->assertSee('Out of Stock');

        $variant->update(['stock' => 5]);
        $this->get(route('buyer.dashboard', ['tab' => 'wishlist']))
            ->assertOk()->assertSee('Move to cart');
        $this->postJson('/cart/add', $payload)->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_product_card_remains_in_search_when_stock_hits_zero(): void
    {
        [$sellerUser, $seller, $product, $variant] = $this->createSellerWithProduct(stock: 0, isActive: true);

        // Product with 0 stock MUST still be present in homepage search / discovery
        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('Mechanical Wireless Keyboard');
        $response->assertSee('Out of Stock');

        // Can still view the product details page
        $detailResponse = $this->get(route('products.show', $product->id));
        $detailResponse->assertOk();
        $detailResponse->assertSee('Mechanical Wireless Keyboard');
        $detailResponse->assertSee('Sold out');
    }

    public function test_product_is_unsearchable_only_when_seller_archives_it(): void
    {
        [$sellerUser, $seller, $product, $variant] = $this->createSellerWithProduct(stock: 0, isActive: true);

        // Before archiving: visible in search
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mechanical Wireless Keyboard');

        // Seller archives the product
        $this->actingAs($sellerUser)
            ->post(route('seller.products.archive', $product->id))
            ->assertRedirect();

        $this->assertFalse($product->fresh()->is_active);

        // After archiving: MUST NOT appear in search or storefront discovery
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Mechanical Wireless Keyboard');

        // Seller restores / unarchives the product
        $this->actingAs($sellerUser)
            ->post(route('seller.products.archive', $product->id))
            ->assertRedirect();

        $this->assertTrue($product->fresh()->is_active);

        // After restoring: reappears in search, even with 0 stock
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Mechanical Wireless Keyboard');
    }
}
