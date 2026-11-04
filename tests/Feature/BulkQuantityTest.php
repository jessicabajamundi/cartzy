<?php

namespace Tests\Feature;

use App\Models\{Category, Product, ProductVariant, Seller, User};
use App\Support\WarehouseShipping;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkQuantityTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private Product $product;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Mug Shop', 'slug' => 'mug-shop', 'status' => 'approved']);
        $category = Category::create(['name' => 'Home', 'slug' => 'home', 'position' => 0, 'is_active' => true]);
        $this->product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id, 'name' => 'Coffee mug', 'slug' => 'coffee-mug',
            'is_active' => true, 'approval_status' => Product::APPROVAL_APPROVED,
        ]);
        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id, 'sku' => 'MUG-RED', 'name' => 'Red', 'price_minor' => 28000, 'stock' => 250, 'is_active' => true,
        ]);
    }

    private function addOne(): string
    {
        $this->actingAs($this->buyer)->postJson(route('cart.add'), [
            'id' => $this->product->id, 'variant_id' => $this->variant->id, 'name' => 'Coffee mug', 'price' => 1, 'quantity' => 1,
        ])->assertOk();

        return 'variant_'.$this->variant->id;
    }

    public function test_cart_quantity_can_go_past_99_up_to_the_stock_but_not_beyond(): void
    {
        $key = $this->addOne();

        $this->postJson(route('cart.update'), ['cart_key' => $key, 'quantity' => 150])->assertOk();
        $this->assertDatabaseHas('cart_items', ['cart_key' => $key, 'quantity' => 150]);

        $this->postJson(route('cart.update'), ['cart_key' => $key, 'quantity' => 250])->assertOk();
        $this->assertDatabaseHas('cart_items', ['cart_key' => $key, 'quantity' => 250]);

        $this->postJson(route('cart.update'), ['cart_key' => $key, 'quantity' => 251])
            ->assertUnprocessable()
            ->assertJsonPath('errors.quantity.0', 'Only 250 item(s) are available for this option.');
        $this->assertDatabaseHas('cart_items', ['cart_key' => $key, 'quantity' => 250]);
    }

    public function test_warehouse_note_shows_only_when_quantity_is_over_100(): void
    {
        $key = $this->addOne();
        $hiddenNote = 'data-threshold="100"  hidden';

        $this->postJson(route('cart.update'), ['cart_key' => $key, 'quantity' => 100])->assertOk();
        $this->get(route('cart.index'))->assertOk()
            ->assertSee(WarehouseShipping::NOTE)                 // in the page so it can appear while typing
            ->assertSee($hiddenNote, false)
            ->assertSee('max="250"', false);

        $this->postJson(route('cart.update'), ['cart_key' => $key, 'quantity' => 101])->assertOk();
        $this->get(route('cart.index'))->assertOk()->assertDontSee($hiddenNote, false);

        $this->assertFalse(WarehouseShipping::applies(100));
        $this->assertTrue(WarehouseShipping::applies(101));
    }
}
