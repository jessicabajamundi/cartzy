<?php

namespace Tests\Feature;

use App\Models\{Category, Order, OrderItem, Product, Review, Seller, SellerOrder, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerStorePageTest extends TestCase
{
    use RefreshDatabase;

    private function seller(string $name = 'Shoe Studio'): Seller
    {
        $owner = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        return Seller::create(['user_id' => $owner->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'description' => 'Handmade shoes since 2020.', 'status' => 'approved']);
    }

    private function product(Seller $seller, string $name, int $price, array $attributes = []): Product
    {
        $attributes['category_id'] ??= Category::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'is_active' => true])->id;
        $product = Product::create($attributes + ['seller_id' => $seller->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name).'-'.$seller->id, 'is_active' => true]);
        $product->variants()->create(['name' => 'Default', 'sku' => strtoupper(\Illuminate\Support\Str::random(8)), 'price_minor' => $price, 'stock' => 5, 'is_active' => true]);
        return $product;
    }

    private function review(Product $product, int $rating, ?string $comment = null): Review
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $order = Order::forceCreate(['buyer_id' => $buyer->id, 'reference' => 'ORD-'.\Illuminate\Support\Str::random(8), 'total_minor' => 1000, 'shipping_address' => json_encode(['city' => 'Manila'])]);
        $sellerOrder = SellerOrder::forceCreate(['order_id' => $order->id, 'seller_id' => $product->seller_id, 'status' => 'delivered', 'subtotal_minor' => 1000]);
        $item = OrderItem::forceCreate(['seller_order_id' => $sellerOrder->id, 'product_id' => $product->id, 'product_variant_id' => $product->variants()->first()->id, 'product_name' => $product->name, 'variant_name' => 'Default', 'unit_price_minor' => 1000, 'quantity' => 2]);
        return Review::create(['user_id' => $buyer->id, 'order_item_id' => $item->id, 'product_id' => $product->id, 'rating' => $rating, 'comment' => $comment]);
    }

    public function test_store_page_shows_profile_products_rating_and_followers(): void
    {
        $seller = $this->seller();
        $sneaker = $this->product($seller, 'Everyday sneaker', 10000);
        $this->product($seller, 'Hidden boot', 9000, ['is_active' => false]);
        $this->product($this->seller('Other Shop'), 'Someone else sandal', 5000);
        $this->review($sneaker, 5, 'Lovely fit');
        $this->review($sneaker, 4);
        $seller->followers()->attach(User::factory()->create(['role' => 'buyer'])->id);

        $this->get(route('store.show', $seller->slug))->assertOk()
            ->assertSee('Shoe Studio')->assertSee('Everyday sneaker')
            ->assertDontSee('Hidden boot')->assertDontSee('Someone else sandal')
            ->assertViewHas('stats', fn ($stats) => $stats['followers'] === 1 && $stats['products'] === 1
                && $stats['reviews'] === 2 && abs($stats['rating'] - 4.5) < 0.01 && $stats['sold'] === 4 && $stats['breakdown'][5] === 1);

        $this->get(route('store.show', ['slug' => $seller->slug, 'tab' => 'reviews']))->assertOk()->assertSee('Lovely fit');
        $this->get(route('store.show', ['slug' => $seller->slug, 'tab' => 'reviews', 'rating' => 4]))->assertOk()->assertDontSee('Lovely fit');
        $this->get(route('store.show', ['slug' => $seller->slug, 'tab' => 'about']))->assertOk()->assertSee('Handmade shoes since 2020.');
        $this->get(route('products.show', $sneaker->id))->assertOk()->assertSee(route('store.show', $seller->slug), false);
    }

    public function test_store_products_can_be_searched_filtered_and_sorted(): void
    {
        $seller = $this->seller();
        $shoes = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true]);
        $this->product($seller, 'Canvas sneaker', 30000, ['category_id' => $shoes->id]);
        $this->product($seller, 'Leather wallet', 10000);
        $this->product($seller, 'Running sneaker', 20000, ['category_id' => $shoes->id]);

        $names = fn ($response) => $response->viewData('products')->pluck('name')->all();
        $this->assertSame(['Canvas sneaker', 'Running sneaker'], $names($this->get(route('store.show', ['slug' => $seller->slug, 'q' => 'sneaker', 'sort' => 'price_desc']))));
        $this->assertSame(['Leather wallet', 'Running sneaker', 'Canvas sneaker'], $names($this->get(route('store.show', ['slug' => $seller->slug, 'sort' => 'price_asc']))));
        $this->assertEqualsCanonicalizing(['Canvas sneaker', 'Running sneaker'], $names($this->get(route('store.show', ['slug' => $seller->slug, 'category' => 'shoes']))));
        $this->get(route('store.show', ['slug' => $seller->slug, 'sort' => ['bad'], 'tab' => ['x']]))->assertOk();
    }

    public function test_unapproved_or_suspended_stores_are_not_public(): void
    {
        $seller = $this->seller();
        $seller->update(['status' => 'pending']);
        $this->get(route('store.show', $seller->slug))->assertNotFound();
        $seller->update(['status' => 'approved']);
        $seller->user->update(['status' => 'suspended']);
        $this->get(route('store.show', $seller->slug))->assertNotFound();
        $this->get(route('store.show', 'missing-store'))->assertNotFound();
    }

    public function test_buyers_can_follow_and_unfollow_a_store(): void
    {
        $seller = $this->seller();
        $url = route('store.follow', $seller->slug);
        $this->post($url)->assertRedirect(route('login'));

        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->actingAs($buyer)->postJson($url)->assertOk()->assertJson(['following' => true, 'followers' => 1]);
        $this->assertDatabaseHas('seller_followers', ['seller_id' => $seller->id, 'user_id' => $buyer->id]);
        $this->get(route('store.show', $seller->slug))->assertSee('Following');

        $this->postJson($url)->assertOk()->assertJson(['following' => false, 'followers' => 0]);
        $this->assertDatabaseCount('seller_followers', 0);

        $this->actingAs($seller->user)->post($url)->assertForbidden();
        $this->assertDatabaseCount('seller_followers', 0);
    }

    public function test_chat_bubble_opens_a_store_conversation_for_buyers(): void
    {
        $seller = $this->seller();
        $product = $this->product($seller, 'Everyday sneaker', 10000);
        $url = route('store.message', $seller->slug);

        $this->get(route('store.show', $seller->slug))->assertOk()->assertSee('Log in to chat with Shoe Studio')->assertDontSee('id="pd-chat"', false);
        $this->post($url)->assertRedirect(route('login'));

        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->actingAs($buyer)->get(route('store.show', $seller->slug))->assertOk()
            ->assertSee('class="chat-bubble"', false)->assertSee('id="pd-chat"', false)->assertSee($url, false)
            ->assertSee('id="pd-chat-minimize"', false)->assertSee('js/chat-dock.js', false);

        $data = $this->postJson($url)->assertOk()->assertJsonStructure(['send_url', 'inbox_url', 'messages'])->json();
        $this->postJson($url)->assertOk();
        $this->assertDatabaseCount('product_inquiries', 1);
        $this->assertDatabaseHas('product_inquiries', ['buyer_id' => $buyer->id, 'seller_id' => $seller->id, 'product_id' => null]);

        $this->postJson($data['send_url'], ['message' => 'Do you ship to Cebu?'])->assertCreated()->assertJsonPath('message.body', 'Do you ship to Cebu?');
        $this->get($data['inbox_url'])->assertOk()->assertSee('General store chat')->assertSee('Do you ship to Cebu?');

        // A product question stays a separate conversation.
        $this->postJson(route('products.inquire', $product->id))->assertOk();
        $this->assertDatabaseCount('product_inquiries', 2);

        $this->actingAs($seller->user)->get(route('store.show', $seller->slug))->assertOk()->assertDontSee('class="chat-bubble"', false);
        $this->post($url)->assertForbidden();

        $seller->update(['status' => 'suspended']);
        $this->actingAs($buyer)->postJson($url)->assertNotFound();
    }
}
