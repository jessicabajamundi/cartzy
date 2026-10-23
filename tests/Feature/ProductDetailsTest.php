<?php

namespace Tests\Feature;

use App\Models\{Category, Product, ProductComment, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailsTest extends TestCase
{
    use RefreshDatabase;

    private function product(): Product
    {
        $owner = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $owner->id, 'name' => 'Shoe Studio', 'slug' => 'shoe-studio', 'status' => 'approved']);
        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true]);
        $product = Product::create(['seller_id' => $seller->id, 'category_id' => $category->id, 'name' => 'Everyday sneaker', 'slug' => 'everyday-sneaker', 'description' => 'Soft everyday shoes.', 'is_active' => true, 'discount_type' => 'percentage', 'discount_value' => 10]);
        $product->variants()->create(['name' => 'White / 35', 'sku' => 'WHITE-35', 'price_minor' => 10000, 'stock' => 4, 'is_active' => true, 'options' => ['color_name' => 'White', 'size' => '35']]);
        $product->variants()->create(['name' => 'Blue / 36', 'sku' => 'BLUE-36', 'price_minor' => 12000, 'stock' => 0, 'is_active' => true]);
        return $product;
    }

    public function test_sale_card_opens_public_details_with_real_options_and_stock(): void
    {
        $product = $this->product();
        $this->get(route('sale'))->assertOk()->assertSee('class="flash-product-link" href="'.route('products.show', $product->id).'" aria-label=', false)
            ->assertDontSee('opens in a new tab');
        $this->get(route('products.show', $product->id))->assertOk()
            ->assertSee('Everyday sneaker')->assertSee('Soft everyday shoes.')->assertSee('Shoe Studio')
            ->assertSee('₱90.00')->assertSee('₱100.00')->assertSee('-10%')->assertSee('id="pd-selected-name">White / 35</strong>', false)
            ->assertSee('data-pick="color" data-value="Blue"', false)->assertSee('data-pick="size" data-value="36"', false)
            ->assertSee('<strong>0</strong> sold', false)->assertSee('4 in stock')->assertSee('Sold out')->assertSee('Reviews &amp; comments', false)->assertSee('No reviews yet.');
        $product->variants()->update(['stock' => 0]);
        $this->get(route('products.show', $product->id))->assertOk()->assertSee('This product is currently sold out.');
    }

    public function test_gallery_contains_only_the_selected_colors_photos(): void
    {
        $product = $this->product();
        $product->variants()->delete();
        foreach (['Black', 'White'] as $color) {
            foreach (['Small', 'Large'] as $size) {
                $product->variants()->create([
                    'name' => "$color / $size", 'sku' => "$color-$size", 'stock' => 10,
                    'price_minor' => 10000, 'is_active' => true,
                    'image_path' => "https://example.com/$color-front.jpg",
                    'gallery' => ["https://example.com/$color-front.jpg", "https://example.com/$color-back.jpg"],
                ]);
            }
        }
        $response = $this->get(route('products.show', $product))->assertOk();
        $response->assertViewHas('selected', fn ($option) => $option['color'] === 'Black' && $option['images'] === [
            'https://example.com/Black-front.jpg', 'https://example.com/Black-back.jpg',
        ]);
        $response->assertViewHas('variants', fn ($options) => $options->every(fn ($option) => $option['images'] === [
            "https://example.com/{$option['color']}-front.jpg", "https://example.com/{$option['color']}-back.jpg",
        ]));
        $response->assertSee('data-photo="https://example.com/White-front.jpg"  hidden', false);
        $response->assertSee('data-photo="https://example.com/White-back.jpg"  hidden', false);
    }

    public function test_slash_inside_a_color_name_is_not_read_as_a_size(): void
    {
        $product = $this->product();
        $product->variants()->delete();
        $product->variants()->create(['name' => 'Black/Hyper Pink/Reflect Silver', 'sku' => 'TOTE-1', 'price_minor' => 10000, 'stock' => 3, 'is_active' => true]);
        $this->get(route('products.show', $product->id))->assertOk()
            ->assertSee('<li>Black/Hyper Pink/Reflect Silver</li>', false)
            ->assertDontSee('<dt>Size', false)
            ->assertSee('No ratings yet');
    }

    public function test_hidden_products_and_unapproved_sellers_are_not_public(): void
    {
        $product = $this->product();
        $product->update(['is_active' => false]);
        $this->get(route('products.show', $product->id))->assertNotFound();
        $product->update(['is_active' => true]);
        $product->seller->update(['status' => 'pending']);
        $this->get(route('products.show', $product->id))->assertNotFound();
        $product->seller->update(['status' => 'approved']);
        $product->seller->user->update(['status' => 'suspended']);
        $this->get(route('products.show', $product->id))->assertNotFound();
    }

    public function test_buyer_comments_persist_are_escaped_and_require_an_active_buyer(): void
    {
        $product = $this->product();
        $url = route('products.comments.store', $product->id);
        $this->post($url, ['body' => 'Question'])->assertRedirect(route('login'));
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->actingAs($buyer)->post($url, ['body' => '<script>alert(1)</script> Is this true to size?'])
            ->assertRedirect(route('products.show', $product->id).'#comment-1')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('product_comments', ['product_id' => $product->id, 'user_id' => $buyer->id]);
        $this->get(route('products.show', $product->id))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->post($url, ['body' => '   '])->assertSessionHasErrors('body');
        $this->post($url, ['body' => str_repeat('a', 2001)])->assertSessionHasErrors('body');
        $this->actingAs($product->seller->user)->post($url, ['body' => 'Seller comment'])->assertForbidden();
        $buyer->update(['status' => 'suspended']);
        $this->actingAs($buyer)->post($url, ['body' => 'Suspended comment'])->assertRedirect(route('login'));
        $this->assertDatabaseCount('product_comments', 1);
    }

    public function test_comments_are_scoped_and_paginated(): void
    {
        $product = $this->product();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        for ($i = 1; $i <= 12; $i++) ProductComment::create(['product_id' => $product->id, 'user_id' => $buyer->id, 'body' => 'Question '.$i]);
        $this->get(route('products.show', $product->id))->assertOk()->assertViewHas('comments', fn ($comments) => $comments->count() === 10 && $comments->total() === 12);
        $this->get(route('products.show', ['id' => $product->id, 'comments_page' => 2]))->assertOk()->assertViewHas('comments', fn ($comments) => $comments->count() === 2);
    }

    public function test_cart_uses_real_price_and_rejects_quantity_above_remaining_stock(): void
    {
        $product = $this->product();
        $variant = $product->variants()->first();
        $this->actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']));
        $data = ['id' => $product->id, 'variant_id' => $variant->id, 'name' => $product->name, 'price' => 1, 'quantity' => 3];
        $this->post(route('cart.add'), $data)->assertSessionHasNoErrors();
        $this->assertEquals(90, app(\App\Services\BuyerCart::class)->all()['variant_'.$variant->id]['price']);
        $this->post(route('cart.add'), $data)->assertSessionHasErrors('quantity');
        $this->assertEquals(3, app(\App\Services\BuyerCart::class)->all()['variant_'.$variant->id]['quantity']);
        $soldOut = $product->variants()->where('stock', 0)->first();
        $this->post(route('cart.add'), array_replace($data, ['variant_id' => $soldOut->id, 'quantity' => 1]))->assertSessionHasErrors('quantity');
    }

    public function test_product_can_be_viewed_by_id_and_slug(): void
    {
        $product = $this->product();
        $this->get(route('products.show', $product->id))->assertOk()->assertSee($product->name);
        $this->get(route('products.show', $product->slug))->assertOk()->assertSee($product->name);
    }
}
