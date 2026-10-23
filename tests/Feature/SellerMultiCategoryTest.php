<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerMultiCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_can_create_product_with_multiple_categories(): void
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $shop = Seller::create([
            'user_id' => $user->id,
            'name' => 'Multi Category Store',
            'slug' => 'multi-category-store',
            'status' => 'approved',
        ]);

        $shoes = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true]);
        $sports = Category::create(['name' => 'Sports & Outdoors', 'slug' => 'sports-outdoors', 'is_active' => true]);
        $kids = Category::create(['name' => 'Kids', 'slug' => 'kids', 'is_active' => true]);

        $response = $this->actingAs($user)->post(route('seller.products.add'), [
            'name' => 'Pro Kids Running Shoes',
            'sku' => 'RUN-KIDS-001',
            'price' => 1299.00,
            'stock' => 50,
            'category_ids' => [$shoes->id, $sports->id, $kids->id],
            'low_stock_threshold' => 5,
        ]);

        $response->assertSessionHasNoErrors();

        $product = Product::where('name', 'Pro Kids Running Shoes')->first();
        $this->assertNotNull($product);

        // Primary category is the first one in the list
        $this->assertEquals($shoes->id, $product->category_id);

        // All 3 categories are attached in pivot
        $attachedIds = $product->categories->pluck('id')->all();
        $this->assertEqualsCanonicalizing([$shoes->id, $sports->id, $kids->id], $attachedIds);

        // Reverse relationship works
        $this->assertTrue($sports->allProducts->contains('id', $product->id));
        $this->assertTrue($kids->allProducts->contains('id', $product->id));
    }

    public function test_seller_can_update_multiple_categories_on_edit_product(): void
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $shop = Seller::create([
            'user_id' => $user->id,
            'name' => 'Edit Store',
            'slug' => 'edit-store',
            'status' => 'approved',
        ]);

        $cat1 = Category::create(['name' => 'Men Clothing', 'slug' => 'men-clothing', 'is_active' => true]);
        $cat2 = Category::create(['name' => 'Sports', 'slug' => 'sports', 'is_active' => true]);
        $cat3 = Category::create(['name' => 'Beachwear', 'slug' => 'beachwear', 'is_active' => true]);

        $product = Product::create([
            'seller_id' => $shop->id,
            'category_id' => $cat1->id,
            'name' => 'Classic Tee',
            'slug' => 'classic-tee',
            'is_active' => true,
            'low_stock_threshold' => 5,
        ]);
        $product->categories()->sync([$cat1->id]);

        $variant = $product->variants()->create([
            'sku' => 'TEE-001',
            'name' => 'Standard',
            'price_minor' => 49900,
            'stock' => 20,
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('seller.products.details', $product->id), [
            'name' => 'Classic Beach Sports Tee',
            'sku' => 'TEE-001',
            'price' => 599.00,
            'category_ids' => [$cat2->id, $cat3->id],
            'low_stock_threshold' => 10,
            'is_active' => 1,
        ]);

        $response->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertEquals($cat2->id, $product->category_id);
        $this->assertEqualsCanonicalizing([$cat2->id, $cat3->id], $product->categories->pluck('id')->all());
    }

    public function test_adding_product_requires_at_least_one_category(): void
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        Seller::create([
            'user_id' => $user->id,
            'name' => 'Empty Cat Store',
            'slug' => 'empty-cat-store',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)->post(route('seller.products.add'), [
            'name' => 'No Category Product',
            'sku' => 'NOCAT-001',
            'price' => 100.00,
            'stock' => 10,
            'category_ids' => [],
        ]);

        $response->assertSessionHasErrors('category_ids');
    }
}
