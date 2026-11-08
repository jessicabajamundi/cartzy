<?php

namespace Tests\Feature;

use App\Models\{Address, Category, LogisticsProvider, Order, Product, ProductVariant, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiPlaceOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private Address $address;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->address = Address::create([
            'user_id' => $this->buyer->id, 'label' => 'Home', 'recipient' => 'Ana Buyer', 'phone' => '09171234567',
            'line1' => '12 Rose St', 'barangay' => 'San Isidro', 'city' => 'Quezon City', 'province' => 'Metro Manila',
            'region' => 'NCR', 'postal_code' => '1100', 'is_default' => false,
        ]);

        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Mug Shop', 'slug' => 'mug-shop', 'status' => 'approved']);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
        $category = Category::create(['name' => 'Home', 'slug' => 'home', 'position' => 0, 'is_active' => true]);
        $product = Product::create([
            'seller_id' => $seller->id, 'category_id' => $category->id, 'name' => 'Coffee mug', 'slug' => 'coffee-mug',
            'is_active' => true, 'approval_status' => Product::APPROVAL_APPROVED,
        ]);
        $this->variant = ProductVariant::create([
            'product_id' => $product->id, 'sku' => 'MUG-RED', 'name' => 'Red', 'price_minor' => 28000, 'stock' => 3, 'is_active' => true,
        ]);

        Sanctum::actingAs($this->buyer);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'checkout_key' => 'tap-1',
            'payment_method' => 'COD',
            'address_id' => $this->address->id,
            'items' => [[
                'product_id' => $this->variant->product_id, 'variant_id' => $this->variant->id,
                'name' => 'Coffee mug', 'variation' => 'Red', 'quantity' => 2,
            ]],
        ], $overrides);
    }

    public function test_buyer_places_a_real_order_with_database_prices_and_stock(): void
    {
        $response = $this->postJson('/api/orders', $this->payload())->assertCreated();

        $order = Order::firstOrFail();
        $response->assertJsonPath('order.reference', $order->reference)
            ->assertJsonPath('order.total', $order->total_minor / 100);
        $this->assertSame($this->buyer->id, $order->buyer_id);
        $this->assertSame('Ana Buyer', $order->shipping_address['recipient']);
        $this->assertSame('09171234567', $order->shipping_address['phone']);

        $item = $order->sellerOrders()->firstOrFail()->items()->firstOrFail();
        $this->assertSame(28000, $item->unit_price_minor); // price from the database, not the app
        $this->assertSame(2, $item->quantity);
        $this->assertSame(1, $this->variant->fresh()->stock);

        $this->getJson('/api/orders')->assertOk()->assertJsonPath('orders.0.id', $order->reference);
    }

    public function test_retrying_the_same_tap_does_not_create_a_second_order(): void
    {
        $first = $this->postJson('/api/orders', $this->payload())->assertCreated();
        $this->postJson('/api/orders', $this->payload())
            ->assertOk()
            ->assertJsonPath('order.reference', $first->json('order.reference'));

        $this->assertSame(1, Order::count());
        $this->assertSame(1, $this->variant->fresh()->stock);
    }

    public function test_ordering_more_than_the_stock_is_refused(): void
    {
        $payload = $this->payload();
        $payload['items'][0]['quantity'] = 5;

        $this->postJson('/api/orders', $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'There is not enough stock for Coffee mug.');
        $this->assertSame(0, Order::count());
        $this->assertSame(3, $this->variant->fresh()->stock);
    }

    public function test_another_buyers_address_and_unknown_items_are_refused(): void
    {
        $other = Address::create([
            'user_id' => User::factory()->create(['role' => 'buyer'])->id, 'recipient' => 'X', 'phone' => '09170000000',
            'line1' => '1 St', 'barangay' => 'Poblacion', 'city' => 'Makati', 'province' => 'Metro Manila',
            'region' => 'NCR', 'postal_code' => '1210',
        ]);
        $this->postJson('/api/orders', $this->payload(['address_id' => $other->id]))->assertStatus(422);

        $this->postJson('/api/orders', $this->payload([
            'checkout_key' => 'tap-2',
            'items' => [['name' => 'Mystery', 'quantity' => 1]],
        ]))->assertStatus(422);

        $this->assertSame(0, Order::count());
    }
}
