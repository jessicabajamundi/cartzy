<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SellerPendingDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_seller_can_log_in_view_dashboard_and_has_add_product_locked_until_approved(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        $category = Category::create(['name' => 'General', 'slug' => 'general', 'is_active' => true]);

        // 1. Create a seller whose registration is pending admin approval
        $user = User::factory()->create([
            'name' => 'Juan Dela Cruz',
            'email' => 'seller.pending@example.com',
            'password' => Hash::make('SellerPass@2026'),
            'role' => 'seller',
            'status' => 'pending',
            'id_photo' => 'ids/test.jpg',
        ]);
        $shop = Seller::create([
            'user_id' => $user->id,
            'name' => 'Juan Store',
            'slug' => 'juan-store',
            'status' => 'pending',
        ]);

        // 2. Pending seller can log in through the login form
        $response = $this->post('/login', [
            'email' => 'seller.pending@example.com',
            'password' => 'SellerPass@2026',
        ]);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('seller.dashboard'));
        $this->assertAuthenticatedAs($user);

        // 3. Pending seller can view the dashboard
        $dashResponse = $this->actingAs($user)->get('/seller/dashboard');
        $dashResponse->assertOk();
        $dashResponse->assertSee('Store overview');
        $dashResponse->assertSee('Account Pending Administrator Approval');
        $dashResponse->assertSee('Add product (Locked)');

        // 4. Pending seller also sees the locked button on the products page
        $productsResponse = $this->actingAs($user)->get('/seller/products');
        $productsResponse->assertOk();
        $productsResponse->assertSee('Add product (Pending approval)');

        // 5. Attempting to add a product while pending is blocked
        $addAttempt = $this->actingAs($user)->post('/seller/products/add', [
            'name' => 'New Awesome Product',
            'category_id' => $category->id,
            'price' => 150.00,
            'stock' => 10,
        ]);
        $addAttempt->assertSessionHasErrors(['name' => 'Your seller account is currently pending administrator approval. You cannot sell or add products until approved by the admin.']);
        $this->assertDatabaseCount('products', 0);

        // 6. Admin reviews and approves the seller registration
        $this->flushSession();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $adminRes = $this->actingAs($admin)->post('/admin/registrations/'.$user->id.'/status', [
            'status' => 'approved',
        ]);
        $adminRes->assertRedirect();

        $this->assertSame('active', $user->fresh()->status);
        $this->assertSame('approved', $shop->fresh()->status);

        // 7. Now approved seller dashboard shows unlocked "＋ Add product"
        $this->flushSession();
        $approvedDash = $this->actingAs($user->fresh())->get('/seller/dashboard');
        $approvedDash->assertOk();
        $approvedDash->assertDontSee('Add product (Locked)');
        $approvedDash->assertSee('＋ Add product');

        // 8. Approved seller can now successfully add a product
        $addSuccess = $this->actingAs($user->fresh())->post('/seller/products/add', [
            'name' => 'New Awesome Product',
            'sku' => 'SKU-AWESOME-01',
            'category_id' => $category->id,
            'price' => 150.00,
            'stock' => 10,
        ]);
        $addSuccess->assertSessionHasNoErrors();
        $this->assertDatabaseHas('products', [
            'seller_id' => $shop->id,
            'name' => 'New Awesome Product',
        ]);
    }
}
