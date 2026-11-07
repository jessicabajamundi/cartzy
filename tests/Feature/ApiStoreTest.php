<?php

namespace Tests\Feature;

use App\Models\{Category, Order, OrderItem, Product, Review, Seller, SellerOrder, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiStoreTest extends TestCase
{
    use RefreshDatabase;

    private function seller(string $name = 'Shoe Studio'): Seller
    {
        $owner = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        return Seller::create(['user_id' => $owner->id, 'name' => $name, 'slug' => Str::slug($name), 'status' => 'approved']);
    }

    private function product(Seller $seller, string $name): Product
    {
        $category = Category::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'is_active' => true]);
        $product = Product::create(['seller_id' => $seller->id, 'category_id' => $category->id, 'name' => $name, 'slug' => Str::slug($name).'-'.$seller->id, 'is_active' => true]);
        $product->variants()->create(['name' => 'Default', 'sku' => strtoupper(Str::random(8)), 'price_minor' => 10000, 'stock' => 5, 'is_active' => true]);

        return $product;
    }

    private function review(Product $product, int $rating, ?string $comment = null): Review
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::forceCreate(['buyer_id' => $buyer->id, 'reference' => 'ORD-'.Str::random(8), 'total_minor' => 1000, 'shipping_address' => json_encode(['city' => 'Manila'])]);
        $sellerOrder = SellerOrder::forceCreate(['order_id' => $order->id, 'seller_id' => $product->seller_id, 'status' => 'delivered', 'subtotal_minor' => 1000]);
        $item = OrderItem::forceCreate(['seller_order_id' => $sellerOrder->id, 'product_id' => $product->id, 'product_variant_id' => $product->variants()->first()->id, 'product_name' => $product->name, 'variant_name' => 'Default', 'unit_price_minor' => 1000, 'quantity' => 1]);

        return Review::create(['user_id' => $buyer->id, 'order_item_id' => $item->id, 'product_id' => $product->id, 'rating' => $rating, 'comment' => $comment]);
    }

    public function test_a_store_nobody_has_rated_has_no_rating(): void
    {
        $seller = $this->seller();
        $this->product($seller, 'Everyday sneaker');

        $this->getJson("/api/stores/{$seller->slug}")
            ->assertOk()
            ->assertJsonPath('stats.rating', null)
            ->assertJsonPath('stats.reviews', 0)
            ->assertJsonPath('stats.sold', 0)
            ->assertJsonPath('stats.followers', 0)
            ->assertJsonPath('stats.breakdown.5', 0)
            ->assertJsonPath('reviews', [])
            ->assertJsonPath('products.0.name', 'Everyday sneaker')
            ->assertJsonPath('products.0.rating', null);
    }

    public function test_rating_is_the_real_average_of_this_stores_reviews_only(): void
    {
        $seller = $this->seller();
        $sneaker = $this->product($seller, 'Everyday sneaker');
        $boot = $this->product($seller, 'Trail boot');
        $this->review($sneaker, 5, 'Lovely fit');
        $this->review($sneaker, 4);
        $this->review($boot, 3, 'Okay');
        $this->review($this->product($this->seller('Other Shop'), 'Their sandal'), 1, 'Not this store');

        $response = $this->getJson("/api/stores/{$seller->id}")->assertOk();

        $this->assertEqualsWithDelta(4.0, $response->json('stats.rating'), 0.001);
        $response->assertJsonPath('stats.reviews', 3)
            ->assertJsonPath('stats.breakdown.5', 1)
            ->assertJsonPath('stats.breakdown.4', 1)
            ->assertJsonPath('stats.breakdown.3', 1)
            ->assertJsonPath('stats.breakdown.1', 0)
            ->assertJsonCount(3, 'reviews')
            ->assertJsonCount(2, 'products')
            ->assertJsonMissing(['comment' => 'Not this store'])
            ->assertJsonMissing(['name' => 'Their sandal']);

        $products = collect($response->json('products'))->keyBy('name');
        $this->assertEqualsWithDelta(4.5, $products['Everyday sneaker']['rating'], 0.001);
        $this->assertSame(2, $products['Everyday sneaker']['reviews_count']);
    }

    public function test_star_filter_narrows_the_list_but_not_the_totals(): void
    {
        $seller = $this->seller();
        $sneaker = $this->product($seller, 'Everyday sneaker');
        $this->review($sneaker, 5, 'Lovely fit');
        $this->review($sneaker, 2, 'Too tight');

        $this->getJson("/api/stores/{$seller->slug}?rating=2")
            ->assertOk()
            ->assertJsonCount(1, 'reviews')
            ->assertJsonPath('reviews.0.comment', 'Too tight')
            ->assertJsonPath('reviews.0.product_name', 'Everyday sneaker')
            ->assertJsonPath('stats.reviews', 2);
    }

    public function test_a_store_can_be_found_by_name_and_an_unknown_store_is_not_found(): void
    {
        $seller = $this->seller('Shoe Studio');
        $seller->update(['slug' => 'shoes-1234']);

        $this->getJson('/api/stores/shoe-studio')->assertOk()->assertJsonPath('seller.id', $seller->id);
        $this->getJson('/api/stores/no-such-store')->assertNotFound();
    }

    public function test_a_store_that_is_not_approved_is_not_public(): void
    {
        $seller = $this->seller();
        $seller->update(['status' => 'pending']);

        $this->getJson("/api/stores/{$seller->slug}")->assertNotFound();
    }

    public function test_buyer_can_follow_and_unfollow_and_guests_cannot(): void
    {
        $seller = $this->seller();

        $this->postJson("/api/stores/{$seller->slug}/follow")->assertUnauthorized();

        Sanctum::actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));

        $this->postJson("/api/stores/{$seller->slug}/follow")
            ->assertOk()->assertJsonPath('is_following', true)->assertJsonPath('followers', 1);
        $this->getJson("/api/stores/{$seller->slug}")
            ->assertJsonPath('is_following', true)->assertJsonPath('stats.followers', 1);
        $this->postJson("/api/stores/{$seller->slug}/follow")
            ->assertOk()->assertJsonPath('is_following', false)->assertJsonPath('followers', 0);
    }
}
