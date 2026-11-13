<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createSeller(): array
    {
        $user = User::factory()->create([
            'role' => 'seller',
            'status' => 'active',
            'id_status' => 'verified',
        ]);

        $seller = Seller::create([
            'user_id' => $user->id,
            'name' => 'History Test Boutique',
            'slug' => 'history-test-boutique',
            'status' => 'approved',
        ]);

        return [$user, $seller];
    }

    private function createOrderForSeller(Seller $seller, array $orderAttrs = [], array $sellerOrderAttrs = [], array $itemAttrs = []): SellerOrder
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        $order = Order::create(array_merge([
            'buyer_id' => $buyer->id,
            'reference' => 'CTZ-ORD-' . strtoupper(fake()->bothify('####??')),
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'subtotal_minor' => 150000,
            'shipping_fee_minor' => 5000,
            'discount_minor' => 0,
            'total_minor' => 155000,
            'shipping_address' => [
                'recipient' => 'Juan Dela Cruz',
                'phone' => '09123456789',
                'address' => '123 Rizal St, Makati City',
            ],
        ], $orderAttrs));

        $sellerOrder = new SellerOrder(array_merge([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'status' => 'pending',
            'subtotal_minor' => 150000,
            'shipping_fee_minor' => 5000,
            'discount_minor' => 0,
            'total_minor' => 155000,
        ], $sellerOrderAttrs));
        $sellerOrder->save();

        $category = \App\Models\Category::firstOrCreate(['slug' => 'apparel'], ['name' => 'Apparel', 'is_active' => true]);

        $product = $seller->products()->create([
            'name' => 'Signature Linen Shirt',
            'slug' => 'signature-linen-shirt-' . uniqid(),
            'category_id' => $category->id,
            'is_active' => true,
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/sample-linen-shirt.jpg',
            'position' => 1,
        ]);

        $variant = $product->variants()->create([
            'sku' => 'SKU-' . uniqid(),
            'name' => 'Navy Blue / Medium',
            'price_minor' => 150000,
            'stock' => 20,
            'is_active' => true,
        ]);

        OrderItem::create(array_merge([
            'seller_order_id' => $sellerOrder->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => $product->name,
            'variant_name' => $variant->name,
            'unit_price_minor' => 150000,
            'quantity' => 1,
        ], $itemAttrs));

        return $sellerOrder;
    }

    public function test_seller_sidebar_contains_order_history_link(): void
    {
        [$sellerUser, $seller] = $this->createSeller();

        $response = $this->actingAs($sellerUser)->get(route('seller.dashboard'));
        $response->assertOk()
            ->assertSee(route('seller.order-history'))
            ->assertSee('Order history');
    }

    public function test_seller_can_view_order_history_with_product_pictures(): void
    {
        [$sellerUser, $seller] = $this->createSeller();

        $sellerOrder = $this->createOrderForSeller($seller, [], [
            'status' => 'delivered',
            'delivered_at' => now(),
        ]);

        $response = $this->actingAs($sellerUser)->get(route('seller.order-history'));

        $response->assertOk()
            ->assertSee('Order history')
            ->assertSee($sellerOrder->order->reference)
            ->assertSee('Signature Linen Shirt')
            ->assertSee('Navy Blue / Medium')
            ->assertSee('Delivered')
            ->assertSee('sample-linen-shirt.jpg'); // verifies product picture is present
    }

    public function test_seller_order_history_filters_by_status(): void
    {
        [$sellerUser, $seller] = $this->createSeller();

        $delivered = $this->createOrderForSeller($seller, [
            'reference' => 'ORD-DELIVERED-001',
        ], [
            'status' => 'delivered',
        ]);

        $cancelled = $this->createOrderForSeller($seller, [
            'reference' => 'ORD-CANCELLED-002',
        ], [
            'status' => 'cancelled',
            'cancellation_reason' => 'Customer requested change of address',
        ]);

        // 1. Filter for delivered only
        $resDelivered = $this->actingAs($sellerUser)->get(route('seller.order-history', ['status' => 'delivered']));
        $resDelivered->assertOk()
            ->assertSee('ORD-DELIVERED-001')
            ->assertDontSee('ORD-CANCELLED-002');

        // 2. Filter for cancelled only
        $resCancelled = $this->actingAs($sellerUser)->get(route('seller.order-history', ['status' => 'cancelled']));
        $resCancelled->assertOk()
            ->assertSee('ORD-CANCELLED-002')
            ->assertSee('Customer requested change of address')
            ->assertDontSee('ORD-DELIVERED-001');
    }

    public function test_seller_order_history_search_by_reference_or_product(): void
    {
        [$sellerUser, $seller] = $this->createSeller();

        $order1 = $this->createOrderForSeller($seller, [
            'reference' => 'MATCH-ME-1234',
        ]);

        $order2 = $this->createOrderForSeller($seller, [
            'reference' => 'IGNORE-ME-5678',
        ]);

        $response = $this->actingAs($sellerUser)->get(route('seller.order-history', ['search' => 'MATCH-ME']));
        $response->assertOk()
            ->assertSee('MATCH-ME-1234')
            ->assertDontSee('IGNORE-ME-5678');
    }
}