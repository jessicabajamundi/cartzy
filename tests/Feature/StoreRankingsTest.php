<?php

namespace Tests\Feature;

use App\Models\{Category, Order, Product, Review, Seller, SellerOrder, User};
use App\Services\StoreRankings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreRankingsTest extends TestCase
{
    use RefreshDatabase;

    private function store(string $name, string $status = 'approved'): Seller
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        return Seller::create(['user_id' => $user->id, 'name' => $name, 'slug' => 'store-'.$user->id, 'status' => $status]);
    }

    private function sell(Seller $store, int $quantity, string $status = 'delivered', ?string $deliveredAt = null, int $subtotal = 1234500): void
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'RANK-'.$buyer->id, 'total_minor' => $subtotal, 'payment_method' => 'cod', 'payment_status' => 'paid', 'shipping_address' => ['recipient' => 'R']]);
        $sellerOrder = SellerOrder::create(['order_id' => $order->id, 'seller_id' => $store->id, 'subtotal_minor' => $subtotal, 'shipping_fee_minor' => 0, 'commission_minor' => 0, 'status' => $status, 'delivered_at' => $deliveredAt ?? now()]);
        $product = $this->product($store);
        $sellerOrder->items()->create(['product_id' => $product->id, 'product_variant_id' => $product->variants()->first()->id, 'product_name' => 'Item', 'variant_name' => 'One', 'unit_price_minor' => 100, 'quantity' => $quantity]);
    }

    private function product(Seller $store): Product
    {
        $category = Category::firstOrCreate(['slug' => 'rank'], ['name' => 'Rank', 'is_active' => true]);
        $product = $store->products()->firstOrCreate(['slug' => 'item-'.$store->id], ['name' => 'Item', 'category_id' => $category->id, 'is_active' => true]);
        $product->variants()->firstOrCreate(['sku' => 'SKU-'.$store->id], ['name' => 'One', 'price_minor' => 100, 'stock' => 5, 'is_active' => true]);

        return $product;
    }

    public function test_stores_rank_by_items_delivered_in_the_last_thirty_days(): void
    {
        $small = $this->store('Small Shop');
        $big = $this->store('Big Shop');
        $this->sell($small, 2);
        $this->sell($big, 5);
        $this->sell($big, 1, 'completed');
        $this->sell($small, 50, 'pending');
        $this->sell($small, 50, 'delivered', now()->subDays(40)->toDateTimeString());

        $rankings = app(StoreRankings::class)->forSeller($small);

        $this->assertSame(['Big Shop', 'Small Shop'], array_column($rankings['top'], 'name'));
        $this->assertSame([1, 2], array_column($rankings['top'], 'rank'));
        $this->assertSame([6, 2], array_column($rankings['top'], 'sold'));
        $this->assertTrue($rankings['top'][1]['is_mine']);
        $this->assertNull($rankings['mine']);
    }

    public function test_ties_go_to_the_better_rated_store(): void
    {
        $plain = $this->store('A Plain Shop');
        $loved = $this->store('Z Loved Shop');
        $this->sell($plain, 3);
        $this->sell($loved, 3);
        $product = $this->product($loved);
        Review::create(['user_id' => User::factory()->create()->id, 'order_item_id' => $product->orderItems()->first()->id, 'product_id' => $product->id, 'rating' => 5, 'comment' => 'Great']);

        $top = app(StoreRankings::class)->forSeller($plain)['top'];

        $this->assertSame(['Z Loved Shop', 'A Plain Shop'], array_column($top, 'name'));
        $this->assertSame(5.0, $top[0]['rating']);
    }

    public function test_only_public_stores_are_listed_and_my_store_shows_when_outside_the_top_ten(): void
    {
        $this->sell($this->store('Hidden Shop', 'suspended'), 99);
        foreach (range(1, 11) as $i) {
            $this->sell($this->store("Shop $i"), 20 + $i);
        }
        $mine = $this->store('My Shop');
        $this->sell($mine, 1);

        $rankings = app(StoreRankings::class)->forSeller($mine);

        $this->assertCount(10, $rankings['top']);
        $this->assertNotContains('Hidden Shop', array_column($rankings['top'], 'name'));
        $this->assertSame(12, $rankings['total']);
        $this->assertSame(12, $rankings['mine']['rank']);
        $this->assertSame('My Shop', $rankings['mine']['name']);
    }

    public function test_a_store_with_no_deliveries_appears_unranked(): void
    {
        $this->sell($this->store('Busy Shop'), 4);
        $mine = $this->store('New Shop');

        $rankings = app(StoreRankings::class)->forSeller($mine);

        $this->assertNull($rankings['mine']['rank']);
        $this->assertSame(0, $rankings['mine']['sold']);
    }

    public function test_dashboard_shows_rankings_instead_of_sales_performance_without_other_stores_earnings(): void
    {
        $mine = $this->store('My Shop');
        $rival = $this->store('Rival Shop');
        $this->sell($rival, 7, 'delivered', null, 9876500);

        $this->actingAs($mine->user)->get('/seller/dashboard')->assertOk()
            ->assertSee('Your ranking on Cartzy')->assertSee('Rival Shop')->assertSee('My Shop')
            ->assertSee('Best-selling products')->assertSee('Not ranked yet')
            ->assertSee(route('store.show', $rival->slug))
            ->assertDontSee('Sales performance')->assertDontSee('98,765');
    }

    public function test_my_best_sellers_show_their_rank_across_all_shops(): void
    {
        $mine = $this->store('My Shop');
        $rival = $this->store('Rival Shop');
        $this->sell($mine, 100);
        $this->sell($rival, 50);
        $category = Category::firstOrCreate(['slug' => 'rank'], ['name' => 'Rank', 'is_active' => true]);
        $second = $mine->products()->create(['name' => 'Second item', 'slug' => 'second-item', 'category_id' => $category->id, 'is_active' => true]);
        $variant = $second->variants()->create(['sku' => 'SECOND', 'name' => 'One', 'price_minor' => 100, 'stock' => 5, 'is_active' => true]);
        $sellerOrder = SellerOrder::where('seller_id', $mine->id)->first();
        $sellerOrder->items()->create(['product_id' => $second->id, 'product_variant_id' => $variant->id, 'product_name' => 'Second item', 'variant_name' => 'One', 'unit_price_minor' => 100, 'quantity' => 8]);

        $products = app(StoreRankings::class)->forSeller($mine)['products'];

        $this->assertSame(['Item', 'Second item'], array_column($products, 'name'));
        $this->assertSame([1, 3], array_column($products, 'rank'));
        $this->assertSame([100, 8], array_column($products, 'sold'));

        $this->actingAs($mine->user)->get('/seller/dashboard')->assertOk()
            ->assertSee('Best shop on Cartzy')->assertSee('Best seller on Cartzy')->assertSee('#3 on Cartzy')
            ->assertSee(route('products.show', $second->id));
    }

    public function test_hidden_products_are_not_ranked(): void
    {
        $mine = $this->store('My Shop');
        $this->sell($mine, 10);
        $this->product($mine)->update(['is_active' => false]);

        $this->assertSame([], app(StoreRankings::class)->forSeller($mine)['products']);
    }
}
