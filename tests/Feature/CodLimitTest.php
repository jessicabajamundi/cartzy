<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\LogisticsProvider;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Seller;
use App\Models\User;
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_cod_order_above_limit_is_blocked_to_protect_seller(): void
    {
        $buyer = User::factory()->create(['role' => User::ROLE_BUYER]);
        $hubUser = User::factory()->create(['role' => User::ROLE_LOGISTICS]);
        LogisticsProvider::create([
            'user_id' => $hubUser->id,
            'name' => 'Main Hub',
            'slug' => 'main-hub',
            'status' => 'approved',
        ]);
        $sellerUser = User::factory()->create(['role' => User::ROLE_SELLER]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name' => 'Luxury Shop',
            'slug' => 'luxury-shop',
            'status' => 'approved',
        ]);
        $category = Category::create(['name' => 'Watches', 'slug' => 'watches']);
        $product = Product::create(['package_length_cm' => 20, 'package_width_cm' => 15, 'package_height_cm' => 10,
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Luxury Watch',
            'slug' => 'luxury-watch',
            'price_minor' => 400_000_000, // ₱4,000,000.00
            'stock' => 5,
            'status' => 'active',
        ]);
        $variant = ProductVariant::create(['weight_grams' => 500,
            'product_id' => $product->id,
            'name' => 'Gold',
            'sku' => 'WATCH-GLD',
            'price_minor' => 400_000_000,
            'stock' => 5,
        ]);
        $address = Address::create([
            'user_id' => $buyer->id,
            'recipient' => 'Buyer Test',
            'phone' => '09171234567',
            'line1' => '123 Main St',
            'barangay' => 'San Jose',
            'city' => 'Manila',
            'province' => 'Metro Manila',
            'region' => 'NCR',
            'postal_code' => '1000',
            'is_default' => true,
        ]);

        $token = $buyer->createToken('mobile')->plainTextToken;
        $this->withToken($token)
            ->postJson('/api/orders', [
                'address_id' => $address->id,
                'payment_method' => 'COD',
                'checkout_key' => 'test-cod-limit-1',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonFragment([
                'message' => 'Cash on Delivery is limited to ₱50,000.00 per order to protect sellers. Please use GCash or Cartzy Wallet for larger orders.',
            ]);

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(5_000_000, OrderPlacement::COD_MAX_MINOR);
    }
}
