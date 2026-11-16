<?php

namespace Tests\Feature;

use App\Models\{AdminDispute, LogisticsProvider, Order, Payment, Seller, SellerOrder, Shipment, User};
use App\Services\{BuyerCart, BuyerOrders, OrderPlacement, ReturnWorkflow};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class OnlinePaymentRecordsTest extends TestCase
{
    use RefreshDatabase;

    private function hub(): LogisticsProvider
    {
        $user = User::factory()->create(['role' => 'logistics', 'status' => 'active', 'is_suspended' => false]);
        return LogisticsProvider::create(['user_id' => $user->id, 'name' => 'Hub '.$user->id,
            'slug' => 'hub-'.$user->id, 'status' => 'approved']);
    }

    private function parcel(LogisticsProvider $hub, string $method = 'wallet'): array
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'is_suspended' => false]);
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active', 'is_suspended' => false]);
        $seller = Seller::create(['user_id' => $sellerUser->id, 'name' => 'Store '.$sellerUser->id,
            'slug' => 'store-'.$sellerUser->id, 'status' => 'approved']);
        $order = Order::create(['buyer_id' => $buyer->id, 'reference' => 'ORD-'.Str::uuid(),
            'total_minor' => 10000, 'payment_method' => $method, 'payment_status' => 'pending', 'shipping_address' => []]);
        $parcel = SellerOrder::create(['order_id' => $order->id, 'seller_id' => $seller->id,
            'logistics_provider_id' => $hub->id, 'subtotal_minor' => 10000, 'commission_minor' => 1000, 'status' => 'pending']);
        $shipment = Shipment::create(['seller_order_id' => $parcel->id, 'logistics_provider_id' => $hub->id,
            'origin_hub_id' => $hub->id, 'destination_hub_id' => $hub->id,
            'tracking_code' => 'CTZ-'.Str::uuid(), 'status' => 'unassigned', 'cod_amount_minor' => $method === 'cod' ? 10000 : 0]);
        $payment = Payment::create(['order_id' => $order->id, 'method' => $method === 'cod' ? 'cod' : 'gcash',
            'amount_minor' => 10000, 'status' => 'pending', 'provider_ref' => 'GCASH-'.$order->id,
            'receipt_path' => 'payment_receipts/receipt-'.$order->id.'.png']);
        return compact('buyer', 'sellerUser', 'seller', 'order', 'parcel', 'shipment', 'payment');
    }

    private function refundCase(array $record)
    {
        $record['order']->update(['payment_status' => 'paid']);
        $report = AdminDispute::create(['order_id' => $record['order']->id, 'seller_order_id' => $record['parcel']->id,
            'reporter_id' => $record['buyer']->id, 'type' => 'problem_report', 'subject' => 'Damaged item',
            'description' => 'Item arrived damaged.', 'requested_resolution' => 'refund', 'status' => 'open']);
        $case = app(ReturnWorkflow::class)->open($report);
        $case->update(['status' => 'refund_pending', 'amount_minor' => 10000]);
        return $case;
    }

    private function refundData(): array
    {
        return ['method' => 'gcash', 'reference' => 'REFUND-'.Str::uuid(), 'paid_at' => now()->toDateString(),
            'paid_confirmation' => '1', 'proof' => UploadedFile::fake()->create('proof.png', 10, 'image/png'),
            'notes' => 'Completed transfer checked against receipt.'];
    }

    private function product(array $record): \App\Models\ProductVariant
    {
        $category = \App\Models\Category::firstOrCreate(['slug' => 'general'], ['name' => 'General']);
        $product = \App\Models\Product::create(['seller_id' => $record['seller']->id, 'category_id' => $category->id,
            'name' => 'Test product', 'slug' => 'product-'.Str::uuid(), 'is_active' => true, 'approval_status' => 'approved']);
        return \App\Models\ProductVariant::create(['product_id' => $product->id, 'name' => 'Standard',
            'sku' => 'SKU-'.Str::uuid(), 'price_minor' => 10000, 'stock' => 10, 'is_active' => true]);
    }

    public function test_web_gcash_checkout_stores_one_pending_payment_with_its_uploaded_evidence(): void
    {
        Storage::fake('public');
        $record = $this->parcel($this->hub());
        $record['buyer']->update(['id_status' => 'verified']);
        $variant = $this->product($record);
        $this->mock(BuyerCart::class, function ($mock) use ($variant) {
            $mock->shouldReceive('all')->andReturn(['item' => ['variant_id' => $variant->id, 'quantity' => 1]]);
            $mock->shouldReceive('remove')->once()->with(['item']);
        });
        $this->actingAs($record['buyer'])->post(route('checkout.process'), [
            'payment_method' => 'GCash', 'delivery_address' => 'Test address', 'selected_items' => ['item'],
            'gcash_reference' => 'WEB-REFERENCE', 'receipt' => UploadedFile::fake()->create('receipt.png', 10, 'image/png'),
        ])->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']))->assertSessionHasNoErrors();
        $order = Order::latest('id')->firstOrFail();
        $this->assertSame('pending', $order->payment_status);
        $this->assertSame(1, $order->payments()->count());
        $payment = $order->payments()->first();
        $this->assertSame('gcash', $payment->method);
        $this->assertSame('WEB-REFERENCE', $payment->provider_ref);
        $this->assertNull($payment->verified_at);
        Storage::disk('public')->assertExists($payment->receipt_path);
    }

    public function test_mobile_gcash_checkout_retry_reuses_payment_and_does_not_leave_extra_receipts(): void
    {
        Storage::fake('public');
        $record = $this->parcel($this->hub());
        $record['buyer']->update(['id_status' => 'verified']);
        $variant = $this->product($record);
        $address = \App\Models\Address::create(['user_id' => $record['buyer']->id, 'label' => 'Home',
            'recipient' => 'Buyer', 'phone' => '09171234567', 'line1' => '123 Test Road',
            'barangay' => 'Test', 'city' => 'Pasig', 'province' => 'Metro Manila', 'region' => 'NCR', 'postal_code' => '1600']);
        \Laravel\Sanctum\Sanctum::actingAs($record['buyer']);
        $data = ['checkout_key' => 'same-tap', 'payment_method' => 'GCash', 'address_id' => $address->id,
            'items' => [['variant_id' => $variant->id, 'quantity' => 1]], 'gcash_reference' => 'MOBILE-REFERENCE'];
        $response = $this->postJson('/api/orders', $data + ['receipt' => UploadedFile::fake()->create('receipt.png', 10, 'image/png')]);
        $response->assertCreated()->assertJsonPath('order.payment_status', 'pending');
        $order = Order::findOrFail($response->json('order.id'));
        $this->assertSame(1, $order->payments()->count());
        $this->assertSame('MOBILE-REFERENCE', $order->payments()->first()->provider_ref);
        $this->postJson('/api/orders', $data + ['receipt' => UploadedFile::fake()->create('retry.png', 10, 'image/png')])
            ->assertOk()->assertJsonPath('order.id', $order->id);
        $this->assertCount(1, Storage::disk('public')->allFiles('payment_receipts'));
        $this->assertSame(9, $variant->fresh()->stock);
    }

    public function test_direct_gcash_cannot_mix_stores_and_rolls_back_stock_deductions(): void
    {
        $hub = $this->hub();
        $first = $this->parcel($hub);
        $second = $this->parcel($hub);
        $one = $this->product($first);
        $two = $this->product($second);
        $orderCount = Order::count();
        try {
            app(OrderPlacement::class)->place($first['buyer'], [
                ['variant_id' => $one->id, 'quantity' => 1], ['variant_id' => $two->id, 'quantity' => 1],
            ], 'GCash', 'Test address');
            $this->fail('Direct GCash must be paid to one store per checkout.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('one store at a time', $e->getMessage());
        }
        $this->assertSame($orderCount, Order::count());
        $this->assertSame(10, $one->fresh()->stock);
        $this->assertSame(10, $two->fresh()->stock);
    }

    public function test_hub_ledger_excludes_other_hubs_and_cod_and_supports_reference_search(): void
    {
        $hub = $this->hub();
        $own = $this->parcel($hub);
        $other = $this->parcel($this->hub());
        $cod = $this->parcel($hub, 'cod');
        $this->actingAs($hub->user)->get(route('logistics.online_payments'))
            ->assertOk()->assertSee($own['order']->reference)->assertSee($own['payment']->provider_ref)
            ->assertDontSee($other['order']->reference)->assertDontSee($cod['order']->reference)
            ->assertSee('not gateway-confirmed payments');
        $this->get(route('logistics.online_payments', ['search' => $own['payment']->provider_ref, 'status' => 'pending']))
            ->assertOk()->assertViewHas('orders', fn ($orders) => $orders->total() === 1);
        $this->get(route('logistics.online_payments', ['to' => now()->toDateString()]))->assertOk();
    }

    public function test_destination_hub_can_follow_payment_record_without_receiving_online_funds(): void
    {
        $record = $this->parcel($this->hub());
        $destination = $this->hub();
        $record['shipment']->update(['destination_hub_id' => $destination->id]);
        $this->actingAs($destination->user)->get(route('logistics.online_payments'))
            ->assertOk()->assertSee($record['order']->reference);
    }

    public function test_receipt_download_is_scoped_to_the_hub_and_the_order(): void
    {
        Storage::fake('public');
        $hub = $this->hub();
        $own = $this->parcel($hub);
        $other = $this->parcel($this->hub());
        Storage::disk('public')->put($own['payment']->receipt_path, 'payment evidence');
        $this->actingAs($hub->user)->get(route('logistics.online_payments.receipt', [$own['parcel']->id, $own['payment']->id]))->assertOk();
        $this->get(route('logistics.online_payments.receipt', [$other['parcel']->id, $other['payment']->id]))->assertNotFound();
        $this->get(route('logistics.online_payments.receipt', [$own['parcel']->id, $other['payment']->id]))->assertNotFound();
        $this->actingAs($own['buyer'])->get(route('logistics.online_payments'))->assertForbidden();
    }

    public function test_verification_records_actor_timestamp_and_receipt_without_allowing_overwrite(): void
    {
        $hub = $this->hub();
        $record = $this->parcel($hub);
        $this->actingAs($record['sellerUser'])->post(route('seller.orders.payment-verify', $record['parcel']->id),
            ['decision' => 'approved', 'notes' => 'Transfer received in seller account.'])->assertRedirect()->assertSessionHasNoErrors();
        $record['payment']->refresh();
        $this->assertSame('paid', $record['order']->fresh()->payment_status);
        $this->assertSame($record['sellerUser']->id, $record['payment']->verified_by);
        $this->assertNotNull($record['payment']->verified_at);
        $this->post(route('seller.orders.payment-verify', $record['parcel']->id), ['decision' => 'rejected'])->assertStatus(409);
        $this->assertSame('paid', $record['order']->fresh()->payment_status);
        $this->actingAs($hub->user)->get(route('logistics.online_payments'))
            ->assertOk()->assertSee($record['sellerUser']->name)->assertSee('Transfer received in seller account.');
    }

    public function test_screenshot_or_reference_alone_does_not_allow_payment_approval(): void
    {
        $record = $this->parcel($this->hub());
        $record['payment']->update(['provider_ref' => null]);
        $this->actingAs($record['sellerUser'])->post(route('seller.orders.payment-verify', $record['parcel']->id), ['decision' => 'approved'])->assertStatus(422);
        $record['payment']->update(['provider_ref' => 'EXAMPLE', 'receipt_path' => null]);
        $this->post(route('seller.orders.payment-verify', $record['parcel']->id), ['decision' => 'approved'])->assertStatus(422);
        $this->assertSame('pending', $record['order']->fresh()->payment_status);
    }

    public function test_cod_and_legacy_multi_store_orders_cannot_be_marked_paid_by_one_seller(): void
    {
        $hub = $this->hub();
        $cod = $this->parcel($hub, 'cod');
        $this->actingAs($cod['sellerUser'])->post(route('seller.orders.payment-verify', $cod['parcel']->id), ['decision' => 'approved'])->assertStatus(422);
        $record = $this->parcel($hub);
        $second = $this->parcel($hub);
        $second['parcel']->update(['order_id' => $record['order']->id]);
        $this->actingAs($record['sellerUser'])->post(route('seller.orders.payment-verify', $record['parcel']->id), ['decision' => 'approved'])->assertStatus(422);
    }

    public function test_logistics_can_track_online_refund_but_cannot_disburse_or_mark_it_paid(): void
    {
        Storage::fake('local');
        $hub = $this->hub();
        $record = $this->parcel($hub);
        $case = $this->refundCase($record);
        $this->actingAs($hub->user)->get(route('logistics.online_payments'))
            ->assertOk()->assertSee('Case #'.$case->id)->assertSee('No completed refund has been recorded');
        $this->get(route('returns.index', ['case' => $case->id]))->assertOk()->assertDontSee('Disburse COD refund');
        $this->post(route('returns.action', [$case->id, 'refund']), $this->refundData())->assertForbidden();
        $this->assertDatabaseCount('refund_transactions', 0);
        $this->assertSame('refund_pending', $case->fresh()->status);
        $this->assertCount(0, Storage::disk('local')->allFiles());
        $this->get(route('logistics.cod_remittances'))->assertOk()->assertViewHas('pendingRefundCases', fn ($cases) => $cases->isEmpty());
    }

    public function test_admin_completed_online_refund_is_audited_and_visible_to_the_handling_hub(): void
    {
        Storage::fake('local');
        $hub = $this->hub();
        $record = $this->parcel($hub);
        $case = $this->refundCase($record);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'is_suspended' => false]);
        $data = $this->refundData();
        $this->actingAs($admin)->post(route('returns.action', [$case->id, 'refund']), $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('refunded', $record['order']->fresh()->payment_status);
        $this->assertSame($admin->id, $case->fresh()->refund->recorded_by);
        $this->assertDatabaseHas('return_events', ['return_case_id' => $case->id, 'user_id' => $admin->id, 'status' => 'refunded']);
        $this->post(route('returns.action', [$case->id, 'refund']), $data)->assertSessionHasErrors('reference');
        $this->assertDatabaseCount('refund_transactions', 1);
        $this->actingAs($hub->user)->get(route('logistics.online_payments'))
            ->assertOk()->assertSee($data['reference'])->assertSee($admin->name)->assertSee('Completed refund:');
        $this->get(route('returns.proof', $case->id))->assertOk();
    }

    public function test_cod_refund_requires_unremitted_cash_and_prevents_later_seller_remittance(): void
    {
        Storage::fake('local');
        $hub = $this->hub();
        $record = $this->parcel($hub, 'cod');
        $record['parcel']->update(['status' => 'delivered', 'delivered_at' => now()->subDays(8)]);
        $record['shipment']->update(['status' => 'delivered', 'cod_collected' => true, 'cod_remitted_at' => now()]);
        $case = $this->refundCase($record);
        $this->actingAs($hub->user)->post(route('returns.action', [$case->id, 'refund']), $this->refundData())->assertForbidden();
        $record['shipment']->update(['cod_remitted_at' => null]);
        $this->post(route('returns.action', [$case->id, 'refund']), $this->refundData())->assertRedirect()->assertSessionHasNoErrors();
        $this->assertFalse($record['shipment']->fresh()->isCodEligibleForRemittance());
    }

    public function test_demo_payment_cannot_confirm_real_payments_outside_tests(): void
    {
        $record = $this->parcel($this->hub());
        $this->app->instance('env', 'production');
        try {
            app(BuyerOrders::class)->pay($record['order']->reference, $record['buyer']->id);
            $this->fail('Demo payment must not be available in production.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        } finally {
            $this->app->instance('env', 'testing');
        }
        $this->assertSame('pending', $record['order']->fresh()->payment_status);
    }
}
