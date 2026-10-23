<?php

namespace Tests\Feature;

use App\Models\{Category, Product, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlashSaleTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, array $attributes = []): Product
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $user->id, 'name' => 'Sale shop', 'slug' => 'shop-'.$user->id, 'status' => 'approved']);
        $category = Category::firstOrCreate(['slug' => 'sale-test'], ['name' => 'Sale test', 'is_active' => true]);
        $product = Product::create(array_merge(['seller_id' => $seller->id, 'category_id' => $category->id, 'name' => $name, 'slug' => $name, 'is_active' => true, 'discount_type' => 'percentage', 'discount_value' => 10], $attributes));
        $product->variants()->create(['name' => 'Standard', 'sku' => $name, 'price_minor' => 10000, 'stock' => 10, 'is_active' => true]);
        return $product;
    }

    public function test_sale_lists_only_live_available_discounts(): void
    {
        $this->travelTo(now()->startOfSecond());
        $active = $this->product('live-discount', ['discount_starts_at' => now(), 'discount_ends_at' => now()->addDay()]);
        $fixed = $this->product('fixed-discount', ['discount_type' => 'fixed', 'discount_value' => 25]);
        $this->product('regular-price', ['discount_type' => null]);
        $this->product('expired-offer', ['discount_ends_at' => now()]);
        $this->product('future-offer', ['discount_starts_at' => now()->addMinute()]);
        $this->product('archived-offer', ['is_active' => false]);
        $this->product('empty-stock')->variants()->update(['stock' => 0]);
        $this->product('inactive-variant')->variants()->update(['is_active' => false]);
        $this->product('unapproved-shop')->seller->update(['status' => 'pending']);
        $this->product('inactive-seller')->seller->user->update(['status' => 'suspended']);
        $this->product('no-price-reduction', ['discount_value' => 0.01])->variants()->update(['price_minor' => 2]);
        $this->get(route('sale'))->assertOk()->assertSee('Flash')->assertSee('Sale')
            ->assertSee('</span>90.00', false)->assertSee('</span>75.00', false)
            ->assertSee('-10%')->assertSee('-25%')->assertDontSee('25.00 OFF')->assertSee('class="flash-stock', false)
            ->assertViewHas('products', fn ($products) => $products->total() === 2 && $products->pluck('id')->sort()->values()->all() === collect([$active->id, $fixed->id])->sort()->values()->all());
        $this->get(route('home'))->assertOk()->assertSee('href="'.route('sale').'"', false);
    }

    public function test_sale_paginates_five_rows_and_preserves_page_size(): void
    {
        for ($i = 1; $i <= 26; $i++) {
            $this->product('paged-offer-'.$i);
        }

        foreach ([10, 15, 20, 25] as $size) {
            $this->get(route('sale', ['per_page' => $size]))->assertOk()
                ->assertViewHas('products', fn ($products) => $products->count() === $size && $products->total() === 26)
                ->assertSee('aria-label="Sale pagination"', false)
                ->assertSee('per_page='.$size.'&amp;page=2#sale-products', false);
        }
        $this->get(route('sale', ['per_page' => 10, 'page' => 3]))->assertOk()
            ->assertViewHas('products', fn ($products) => $products->count() === 6 && $products->firstItem() === 21);
        $this->get(route('sale', ['per_page' => 999]))->assertOk()
            ->assertViewHas('products', fn ($products) => $products->perPage() === 25);
    }

    public function test_sale_wishlist_reference_uses_real_variant_when_moved_to_cart(): void
    {
        $product = $this->product('saved-sale-item');
        $variant = $product->variants()->first();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $ref = 'variant_'.$variant->id;
        $this->actingAs($buyer)->postJson(route('wishlist.toggle'), ['ref' => $ref, 'name' => $product->name, 'price' => 90, 'variation' => $variant->name])->assertOk()->assertJson(['wished' => true]);
        $this->get(route('sale'))->assertOk()->assertSee('value="'.$ref.'"', false)->assertSee('aria-pressed="true"', false)->assertSee('&#9733;</span> 0</span>', false)->assertSee('0 sold')->assertDontSee('class="flash-free-shipping"', false);
        $this->post(route('cart.add'), ['id' => $ref, 'name' => $product->name, 'price' => 1, 'quantity' => 1, 'variation' => $variant->name])->assertSessionHasNoErrors();
        $this->assertEquals(90, app(\App\Services\BuyerCart::class)->all()[$ref]['price']);
    }

    public function test_sale_empty_state_and_selected_variant_cart_price(): void
    {
        $this->get(route('sale'))->assertOk()->assertSee('No active sale products right now');
        $product = $this->product('discounted-cart-item');
        $variant = $product->variants()->first();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->actingAs($buyer)->post(route('cart.add'), ['id' => $product->id, 'variant_id' => $variant->id, 'name' => $product->name, 'price' => 1, 'quantity' => 1])->assertSessionHasNoErrors();
        $this->assertEquals(90, app(\App\Services\BuyerCart::class)->all()['variant_'.$variant->id]['price']);
    }
}
