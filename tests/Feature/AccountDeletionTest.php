<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\LogisticsProvider;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Rider;
use App\Models\Seller;
use App\Models\SellerOrder;
use App\Models\Shipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_own_account_on_web_or_api(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make('secret123'),
        ]);

        $this->actingAs($admin)
            ->delete(route('account.delete'), ['password' => 'secret123'])
            ->assertSessionHasErrorsIn('deleteAccount', 'password');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        $token = $admin->createToken('test')->plainTextToken;
        $this->withToken($token)
            ->postJson('/api/account/delete', ['password' => 'secret123'])
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_wrong_password_blocks_account_deletion(): void
    {
        $buyer = User::factory()->create([
            'role' => User::ROLE_BUYER,
            'password' => Hash::make('secret123'),
        ]);

        $this->actingAs($buyer)
            ->delete(route('account.delete'), ['password' => 'wrong-pass'])
            ->assertSessionHasErrorsIn('deleteAccount', 'password');

        $this->assertDatabaseHas('users', ['id' => $buyer->id]);
    }

    public function test_buyer_with_completed_order_is_erased_from_users_while_order_history_is_preserved(): void
    {
        $buyer = User::factory()->create([
            'role' => User::ROLE_BUYER,
            'password' => Hash::make('secret123'),
        ]);
        $sellerUser = User::factory()->create(['role' => User::ROLE_SELLER]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name' => 'Test Store',
            'slug' => 'test-store',
            'status' => 'approved',
        ]);
        $category = Category::create([
            'name' => 'Shoes',
            'slug' => 'shoes',
        ]);
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Sample Shoe',
            'slug' => 'sample-shoe',
            'price_minor' => 50000,
            'stock' => 5,
            'status' => 'active',
        ]);
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'name' => 'Black / 42',
            'sku' => 'SHOE-BLK-42',
            'price_minor' => 50000,
            'stock' => 5,
        ]);
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'reference' => 'ORD-DEL-1001',
            'total_minor' => 50000,
            'payment_method' => 'cod',
            'payment_status' => 'paid',
            'shipping_address' => ['address' => '123 Test St'],
        ]);
        $sellerOrder = SellerOrder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'subtotal_minor' => 50000,
            'shipping_fee_minor' => 0,
            'commission_minor' => 0,
            'status' => 'delivered',
        ]);
        $item = OrderItem::create([
            'seller_order_id' => $sellerOrder->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'product_name' => 'Sample Shoe',
            'variant_name' => 'Black / 42',
            'unit_price_minor' => 50000,
            'quantity' => 1,
        ]);

        $this->actingAs($buyer)
            ->delete(route('account.delete'), ['password' => 'secret123'])
            ->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['id' => $buyer->id]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'buyer_id' => null,
            'reference' => 'ORD-DEL-1001',
        ]);
        $this->assertDatabaseHas('seller_orders', ['id' => $sellerOrder->id, 'status' => 'delivered']);
        $this->assertDatabaseHas('order_items', ['id' => $item->id, 'product_name' => 'Sample Shoe']);
    }

    public function test_buyer_with_active_order_cannot_delete_account(): void
    {
        $buyer = User::factory()->create([
            'role' => User::ROLE_BUYER,
            'password' => Hash::make('secret123'),
        ]);
        $sellerUser = User::factory()->create(['role' => User::ROLE_SELLER]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name' => 'Active Store',
            'slug' => 'active-store',
            'status' => 'approved',
        ]);
        $order = Order::create([
            'buyer_id' => $buyer->id,
            'reference' => 'ORD-ACT-1002',
            'total_minor' => 30000,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'shipping_address' => ['address' => '123 Test St'],
        ]);
        SellerOrder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'subtotal_minor' => 30000,
            'shipping_fee_minor' => 0,
            'commission_minor' => 0,
            'status' => 'pending',
        ]);

        $token = $buyer->createToken('mobile')->plainTextToken;
        $this->withToken($token)
            ->postJson('/api/account/delete', ['password' => 'secret123'])
            ->assertStatus(422);

        $this->assertDatabaseHas('users', ['id' => $buyer->id]);
    }

    public function test_courier_can_delete_account_via_api_when_no_active_deliveries_or_unremitted_cod(): void
    {
        $hubUser = User::factory()->create(['role' => User::ROLE_LOGISTICS]);
        $hub = LogisticsProvider::create([
            'user_id' => $hubUser->id,
            'name' => 'Test Hub',
            'slug' => 'test-hub',
            'status' => 'approved',
        ]);
        $courierUser = User::factory()->create([
            'role' => User::ROLE_COURIER,
            'password' => Hash::make('secret123'),
        ]);
        $rider = Rider::create([
            'user_id' => $courierUser->id,
            'logistics_provider_id' => $hub->id,
            'vehicle_type' => 'motorcycle',
            'status' => 'approved',
        ]);

        $token = $courierUser->createToken('mobile')->plainTextToken;
        $this->withToken($token)
            ->postJson('/api/account/delete', ['password' => 'secret123'])
            ->assertOk()
            ->assertJson(['message' => 'Your account has been permanently deleted.']);

        $this->assertDatabaseMissing('users', ['id' => $courierUser->id]);
        $this->assertDatabaseMissing('riders', ['id' => $rider->id]);
    }

    public function test_google_buyer_without_custom_password_can_set_password_then_delete_account(): void
    {
        $googleBuyer = User::factory()->create([
            'role' => User::ROLE_BUYER,
            'google_id' => 'google-12345',
            'password' => Hash::make('random-generated-secret'),
            'has_custom_password' => false,
        ]);

        // Set initial password via API without current_password
        $token = $googleBuyer->createToken('mobile')->plainTextToken;
        $this->withToken($token)
            ->postJson('/api/account/password', [
                'password' => 'MyNewPass123!',
                'password_confirmation' => 'MyNewPass123!',
            ])
            ->assertOk()
            ->assertJsonPath('user.has_custom_password', true);

        $this->assertTrue((bool) $googleBuyer->fresh()->has_custom_password);

        // Now delete account using the newly set password
        $this->withToken($token)
            ->postJson('/api/account/delete', ['password' => 'MyNewPass123!'])
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $googleBuyer->id]);
    }
}
