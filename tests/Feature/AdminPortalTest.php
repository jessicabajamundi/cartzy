<?php

namespace Tests\Feature;

use App\Models\{AppNotification, Order, Product, Seller, SellerOrder, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Hash, Mail};
use Tests\TestCase;

class AdminPortalTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'status' => 'active']);
    }

    private function order(string $status = 'completed', string $payment = 'paid', int $subtotal = 10000, int $commission = 800): SellerOrder
    {
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Database Store '.$sellerUser->id, 'slug' => 'store-'.$sellerUser->id, 'status' => 'approved', 'commission_bps' => 800]);
        $order = Order::create(['buyer_id' => User::factory()->create()->id, 'reference' => 'DB-ORDER-'.$sellerUser->id, 'total_minor' => $subtotal + 1000, 'payment_method' => 'cod', 'payment_status' => $payment, 'shipping_address' => []]);
        return SellerOrder::create(['order_id' => $order->id, 'seller_id' => $seller->id, 'subtotal_minor' => $subtotal, 'commission_minor' => $commission, 'status' => $status]);
    }

    public function test_admin_requires_an_existing_active_admin_account(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 0);
        $this->actingAs(User::factory()->create())->get('/admin/dashboard')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'admin', 'status' => 'suspended']))->get('/admin/dashboard')->assertRedirect(route('login'));
    }

    public function test_all_admin_pages_render_empty_database_states_without_demo_data(): void
    {
        $this->actingAs($this->admin());
        foreach (['dashboard', 'registrations', 'users', 'compliance', 'disputes', 'commission', 'reports', 'settings', 'chat', 'account'] as $page) {
            $this->get('/admin/'.$page)->assertOk()->assertDontSee('TechZone')->assertDontSee('1,845,920')->assertDontSee('GlowBeauty');
        }
        $this->get('/admin/dashboard')->assertViewHas('stats', fn ($s) => $s['sales'] === 0 && $s['buyers'] === 0 && $s['pending'] === 0);
    }

    public function test_totals_use_saved_commission_and_exclude_unpaid_cancelled_and_refunded_orders(): void
    {
        $this->order();
        $this->order('delivered', 'paid', 20000, 1200);
        $this->order('cancelled', 'paid', 90000, 9000);
        $this->order('completed', 'pending', 90000, 9000);
        $this->order('completed', 'refunded', 90000, 9000);
        $this->actingAs($this->admin())->get('/admin/dashboard')->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['sales'] == 300 && $s['commission'] == 20 && $s['orders'] === 5);
        $this->get('/admin/commission')->assertOk()->assertViewHas('transactions', fn ($rows) => $rows->total() === 2);
    }

    public function test_registration_decisions_validate_and_persist_to_real_accounts(): void
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'pending', 'id_status' => 'pending', 'id_photo' => 'ids/test.jpg']);
        $seller = Seller::create(['user_id' => $user->id, 'name' => 'New Store', 'slug' => 'new-store']);
        $this->actingAs($this->admin());
        $this->post('/admin/registrations/'.$user->id.'/status', ['status' => 'invalid'])->assertSessionHasErrors('status');
        $this->post('/admin/registrations/'.$user->id.'/status', ['status' => 'rejected'])->assertSessionHasErrors('reason');
        $this->post('/admin/registrations/'.$user->id.'/status', ['status' => 'approved'])->assertRedirect();
        $this->assertSame('verified', $user->fresh()->id_status);
        $this->assertSame('approved', $seller->fresh()->status);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'Registration reviewed']);
        $this->post('/admin/registrations/99999/status', ['status' => 'approved'])->assertNotFound();
        $this->post('/admin/registrations/'.$user->id.'/status', ['status' => 'rejected', 'reason' => 'Unreadable document'])->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'id_status' => 'rejected', 'id_verified_at' => null]);
    }

    public function test_user_search_and_access_updates_use_database_rows(): void
    {
        $user = User::factory()->create(['name' => 'Database Buyer', 'role' => 'buyer']);
        $admin = $this->admin();
        $this->actingAs($admin)->get('/admin/users?role=buyer&search=Database')->assertOk()->assertSee('Database Buyer')
            ->assertViewHas('users', fn ($rows) => $rows->total() === 1);
        $this->post('/admin/users/'.$user->id.'/status', ['status' => 'suspended'])->assertRedirect();
        $this->assertTrue($user->fresh()->is_suspended);
        $this->post('/admin/users/'.$admin->id.'/status', ['status' => 'deactivated'])->assertUnprocessable();
        $this->post('/admin/users/'.$user->id.'/status', ['status' => 'active'])->assertRedirect();
        $this->assertFalse($user->fresh()->is_suspended);
    }

    public function test_profile_password_and_real_login_are_persisted(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'password' => Hash::make('original-password')]);
        $this->actingAs($admin)->post('/admin/account/update', ['name' => 'Updated Admin', 'email' => 'updated@example.test', 'phone' => '09123456789'])->assertRedirect();
        $this->assertSame('Updated Admin', $admin->fresh()->name);
        $this->post('/admin/account/update', ['section' => 'password', 'current_password' => 'incorrect', 'password' => 'new-password-long', 'password_confirmation' => 'new-password-long'])->assertSessionHasErrors('current_password');
        $this->post('/admin/account/update', ['section' => 'password', 'current_password' => 'original-password', 'password' => 'new-password-long', 'password_confirmation' => 'new-password-long'])->assertRedirect();
        $this->assertTrue(Hash::check('new-password-long', $admin->fresh()->password));
        $this->post('/logout');
        $this->post('/login', ['email' => 'updated@example.test', 'password' => 'new-password-long'])->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin->fresh());
    }

    public function test_settings_messages_and_disputes_persist_without_fake_financial_actions(): void
    {
        $order = $this->order();
        $buyer = $order->order->buyer;
        $this->actingAs($this->admin());
        $this->post('/admin/settings/policies', ['terms_of_service' => 'Actual terms'])->assertRedirect();
        $this->assertDatabaseHas('platform_settings', ['key' => 'terms_of_service', 'value' => 'Actual terms']);
        $this->post('/admin/settings/announcement', ['title' => 'Database notice', 'target' => 'buyer', 'content' => 'A real announcement'])->assertRedirect();
        $this->assertDatabaseHas('app_notifications', ['user_id' => $buyer->id, 'type' => 'announcement']);
        $this->assertDatabaseMissing('app_notifications', ['user_id' => $order->seller->user_id, 'type' => 'announcement']);
        $this->post('/admin/chat/'.$buyer->id.'/send', ['message' => 'A real message'])->assertRedirect();
        $this->get('/admin/chat?contact='.$buyer->id)->assertOk()->assertSee('A real message');
        $this->post('/admin/disputes', ['reference' => $order->order->reference, 'subject' => 'Item condition', 'description' => 'Please inspect this order.'])->assertRedirect();
        $dispute = DB::table('admin_disputes')->first();
        $this->get('/admin/disputes')->assertOk()->assertSee('Item condition');
        $this->post('/admin/disputes/'.$dispute->id.'/resolve', ['notes' => 'Resolved with the buyer.'])->assertRedirect();
        $this->assertDatabaseHas('admin_disputes', ['id' => $dispute->id, 'status' => 'resolved']);
        $this->assertSame('paid', $order->order->fresh()->payment_status);
        $this->post('/admin/disputes/'.$dispute->id.'/resolve', ['notes' => 'Duplicate'])->assertUnprocessable();
    }

    public function test_reports_filter_dates_and_export_all_matching_rows(): void
    {
        $current = $this->order();
        $older = $this->order();
        $older->created_at = now()->subMonths(2);
        $older->save();
        $this->actingAs($this->admin())->get('/admin/reports?from='.now()->startOfMonth()->toDateString().'&to='.now()->toDateString())
            ->assertOk()->assertViewHas('summary', fn ($s) => $s['orders'] === 1);
        $response = $this->get('/admin/reports?export=csv');
        $response->assertOk()->assertDownload();
        $this->assertStringContainsString($current->order->reference, $response->streamedContent());
        $this->assertStringNotContainsString($older->order->reference, $response->streamedContent());
    }

    public function test_product_visibility_changes_are_saved_and_validated(): void
    {
        $order = $this->order();
        $category = \App\Models\Category::create(['name' => 'Database category', 'slug' => 'database-category']);
        $product = Product::create(['seller_id' => $order->seller_id, 'category_id' => $category->id, 'name' => 'Database product', 'slug' => 'database-product', 'is_active' => true]);
        $this->actingAs($this->admin())->get('/admin/compliance')->assertOk()->assertSee('Database product');
        $this->post('/admin/compliance/'.$product->id.'/action', ['action' => 'suspend_product'])->assertRedirect();
        $this->assertFalse($product->fresh()->is_active);
        $this->post('/admin/compliance/'.$product->id.'/action', ['action' => 'invalid'])->assertSessionHasErrors('action');
        $this->post('/admin/compliance/'.$product->id.'/action', ['action' => 'activate'])->assertRedirect();
        $this->assertTrue($product->fresh()->is_active);
    }

    public function test_demo_links_cannot_create_accounts_and_disabled_sessions_are_revoked(): void
    {
        $this->get('/demo-login/admin')->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 0);
        $user = User::factory()->create(['role' => 'buyer', 'status' => 'deactivated']);
        $this->actingAs($user)->get('/')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_registration_decision_notifies_applicant_via_email_and_in_app(): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => 'seller', 'status' => 'pending', 'id_status' => 'pending', 'id_photo' => 'ids/test.jpg']);
        $this->actingAs($this->admin())
            ->post('/admin/registrations/'.$user->id.'/status', ['status' => 'approved'])
            ->assertRedirect();

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $user->id,
            'type' => 'registration_approved',
        ]);
        Mail::assertSent(\App\Mail\RegistrationDecisionMail::class, function ($mail) use ($user) {
            return $mail->user->id === $user->id && $mail->status === 'approved';
        });
    }

    public function test_seller_compliance_warning_and_suspension_actions(): void
    {
        Mail::fake();
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Flagged Store', 'slug' => 'flagged-store', 'status' => 'approved']);
        $category = \App\Models\Category::create(['name' => 'Fashion', 'slug' => 'fashion']);
        $product = Product::create(['seller_id' => $seller->id, 'category_id' => $category->id, 'name' => 'Prohibited Item', 'slug' => 'prohibited-item', 'is_active' => true]);

        $this->actingAs($this->admin());

        // 1. Issue warning
        $this->post('/admin/compliance/sellers/'.$seller->id.'/warning', ['reason' => 'Prohibited product detected'])
            ->assertRedirect();
        $this->assertDatabaseHas('app_notifications', ['user_id' => $sellerUser->id, 'type' => 'seller_warning']);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'Seller warning issued']);

        // 2. Suspend seller
        $this->post('/admin/compliance/sellers/'.$seller->id.'/suspend', ['reason' => 'Repeated violations'])
            ->assertRedirect();
        $this->assertTrue($sellerUser->fresh()->is_suspended);
        $this->assertSame('suspended', $sellerUser->fresh()->status);
        $this->assertSame('suspended', $seller->fresh()->status);
        $this->assertFalse($product->fresh()->is_active);
        $this->assertDatabaseHas('app_notifications', ['user_id' => $sellerUser->id, 'type' => 'seller_suspended']);
        $this->assertDatabaseHas('admin_audit_logs', ['action' => 'Seller suspended for violations']);
    }

    public function test_disputes_display_coordination_with_buyer_seller_and_courier(): void
    {
        $order = $this->order();
        $buyer = $order->order->buyer;
        $this->actingAs($this->admin());
        $this->post('/admin/disputes', [
            'reference' => $order->order->reference,
            'subject' => 'Package delivery dispute',
            'description' => 'Buyer reports empty parcel delivered.',
        ])->assertRedirect();

        $response = $this->get('/admin/disputes');
        $response->assertOk();
        $response->assertSee('Package delivery dispute');
        $response->assertSee($buyer->name);
        $response->assertSee($order->seller->name);
        $response->assertSee(route('admin.chat', ['contact' => $buyer->id]));
        $response->assertSee(route('admin.chat', ['contact' => $order->seller->user_id]));
    }
}
