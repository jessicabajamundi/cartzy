<?php

namespace Tests\Feature;

use App\Models\{Address, AdminDispute, Category, LogisticsProvider, Order, Product, ProductVariant, RefundTransaction, Seller, User, WalletTransaction};
use App\Services\{BuyerWallet, CheckoutQuote, OrderPlacement, ReturnWorkflow};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BuyerWalletTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;
    private Address $address;
    private ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->address = Address::create(['user_id' => $this->buyer->id, 'recipient' => 'Buyer', 'phone' => '09171234567',
            'label' => 'Home', 'line1' => '12 Test St', 'barangay' => 'San Antonio', 'city' => 'Pasig', 'province' => 'Metro Manila', 'region' => 'NCR', 'postal_code' => '1600', 'is_default' => true]);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Hub', 'slug' => 'hub', 'status' => 'approved']);
        $this->variant = $this->product('one');
        Sanctum::actingAs($this->buyer);
        \Illuminate\Support\Facades\Http::fake();
    }

    private function product(string $slug): ProductVariant
    {
        $seller = Seller::create(['user_id' => User::factory()->create(['role' => 'seller', 'status' => 'active'])->id,
            'name' => $slug, 'slug' => $slug, 'status' => 'approved']);
        $category = Category::firstOrCreate(['slug' => 'test'], ['name' => 'Test', 'position' => 0, 'is_active' => true]);
        $product = Product::create(['package_length_cm' => 20, 'package_width_cm' => 15, 'package_height_cm' => 10, 'seller_id' => $seller->id, 'category_id' => $category->id, 'name' => $slug, 'slug' => $slug,
            'is_active' => true, 'approval_status' => Product::APPROVAL_APPROVED]);
        return ProductVariant::create(['weight_grams' => 500, 'product_id' => $product->id, 'sku' => $slug, 'name' => 'Standard',
            'price_minor' => 10000, 'stock' => 10, 'is_active' => true]);
    }

    private function lines(): array { return [['variant_id' => $this->variant->id, 'quantity' => 1]]; }
    private function fund(int $amount = 100000): void
    {
        app(BuyerWallet::class)->record($this->buyer, $amount, 'verified-refund', 'refund', 'Verified test refund');
    }
    private function payload(array $extra = []): array
    {
        return $extra + ['checkout_key' => 'wallet-tap', 'payment_method' => 'COD', 'wallet_amount_minor' => 5000,
            'address_id' => $this->address->id, 'items' => $this->lines()];
    }
    private function refundCase(Order $order)
    {
        $parcel = $order->sellerOrders()->firstOrFail();
        $report = AdminDispute::create(['order_id' => $order->id, 'seller_order_id' => $parcel->id,
            'reporter_id' => $this->buyer->id, 'type' => 'problem_report', 'subject' => 'Refund request',
            'description' => 'Damaged item', 'requested_resolution' => 'refund', 'status' => 'open']);
        $case = app(ReturnWorkflow::class)->open($report);
        $case->update(['status' => 'refund_pending', 'amount_minor' => $order->total_minor]);
        return $case;
    }

    public function test_full_wallet_payment_and_durable_retry_do_not_double_charge(): void
    {
        $this->fund();
        $quote = $this->postJson('/api/checkout/quote', ['address_id' => $this->address->id, 'items' => $this->lines()])->assertOk()->json();
        $payload = $this->payload(['payment_method' => 'CartzyWallet', 'wallet_amount_minor' => $quote['total_minor']]);
        $this->postJson('/api/orders', $payload)->assertCreated()->assertJsonPath('order.payment_status', 'paid')
            ->assertJsonPath('order.remaining_minor', 0);
        Cache::flush();
        $this->postJson('/api/orders', $payload)->assertSuccessful();
        $this->assertSame(1, Order::count());
        $this->assertSame(1, WalletTransaction::where('type', 'purchase')->count());
        $this->assertSame(100000 - $quote['total_minor'], app(BuyerWallet::class)->balance($this->buyer->id));
        $this->assertSame(9, $this->variant->fresh()->stock);
        $this->assertSame(0, Order::first()->sellerOrders->first()->shipment->cod_amount_minor);
    }

    public function test_wallet_and_cod_collect_only_the_remainder_and_restore_on_cancellation_once(): void
    {
        $this->fund(5000);
        $this->postJson('/api/orders', $this->payload())->assertCreated();
        $order = Order::firstOrFail();
        $parcel = $order->sellerOrders->first();
        $this->assertSame($order->total_minor - 5000, $parcel->shipment->cod_amount_minor);
        $this->assertSame(0, app(BuyerWallet::class)->balance($this->buyer->id));
        $parcel->update(['status' => 'cancelled']);
        app(BuyerWallet::class)->restoreCancelled($parcel);
        $this->assertSame(5000, app(BuyerWallet::class)->balance($this->buyer->id));
        $this->assertSame(1, WalletTransaction::where('type', 'cancellation_refund')->count());
    }

    public function test_insufficient_balance_and_forged_wallet_only_payment_leave_stock_and_money_unchanged(): void
    {
        $this->postJson('/api/orders', $this->payload())->assertStatus(422);
        $this->fund(5000);
        $this->postJson('/api/orders', $this->payload(['payment_method' => 'CartzyWallet']))->assertStatus(422);
        $this->assertSame(0, Order::count());
        $this->assertSame(10, $this->variant->fresh()->stock);
        $this->assertSame(5000, app(BuyerWallet::class)->balance($this->buyer->id));
    }

    public function test_wallet_credit_can_buy_from_multiple_other_stores(): void
    {
        $this->fund();
        $other = $this->product('two');
        $lines = [...$this->lines(), ['variant_id' => $other->id, 'quantity' => 1]];
        $total = app(CheckoutQuote::class)->build($this->buyer, $lines, $this->address)['total_minor'];
        $this->postJson('/api/orders', $this->payload(['items' => $lines, 'payment_method' => 'CartzyWallet', 'wallet_amount_minor' => $total]))->assertCreated();
        $order = Order::firstOrFail();
        $this->assertSame(2, $order->sellerOrders->count());
        $this->assertSame($total, $order->sellerOrders->sum('wallet_paid_minor'));
        foreach ($order->sellerOrders as $parcel) $this->assertSame($parcel->buyerDueMinor(), $parcel->wallet_paid_minor);
    }

    public function test_pending_refund_cannot_be_spent_and_completed_credit_is_atomic_and_unique(): void
    {
        $order = app(OrderPlacement::class)->place($this->buyer, $this->lines(), 'GCash', $this->address->formatted_address);
        $order->update(['payment_status' => 'paid']);
        $case = $this->refundCase($order);
        $this->getJson('/api/wallet')->assertOk()->assertJsonPath('balance_minor', 0)->assertJsonPath('pending_refunds_minor', $order->total_minor);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->actingAs($admin, 'web')->postJson(route('returns.action', [$case->id, 'refund']), ['method' => 'cartzy_wallet', 'notes' => 'Verified refundable payment'])
            ->assertOk()->assertJsonPath('status', 'refunded');
        $this->assertSame($order->total_minor, app(BuyerWallet::class)->balance($this->buyer->id));
        $this->assertSame('cartzy_wallet', RefundTransaction::first()->method);
        $this->actingAs($admin, 'web')->postJson(route('returns.action', [$case->id, 'refund']), ['method' => 'cartzy_wallet', 'notes' => 'Retried refund'])->assertStatus(422);
        $this->assertSame(1, WalletTransaction::count());
    }

    public function test_wallet_balance_and_history_are_private(): void
    {
        $this->fund();
        $other = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        Sanctum::actingAs($other);
        $this->getJson('/api/wallet')->assertOk()->assertJsonPath('balance_minor', 0)->assertJsonCount(0, 'transactions');
        Sanctum::actingAs($this->variant->product->seller->user);
        $this->getJson('/api/wallet')->assertForbidden();
    }

    public function test_wallet_funded_purchase_cannot_be_refunded_as_cash(): void
    {
        $this->fund();
        $total = app(CheckoutQuote::class)->build($this->buyer, $this->lines(), $this->address)['total_minor'];
        $order = app(OrderPlacement::class)->place($this->buyer, $this->lines(), 'CartzyWallet', $this->address->formatted_address,
            null, $this->address, null, null, $total, 'cashout-test');
        $case = $this->refundCase($order);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        app(ReturnWorkflow::class)->act($admin, $case->id, 'refund', ['method' => 'cash', 'notes' => 'Cash-out attempt']);
    }

    public function test_web_profile_wallet_appears_after_personal_details(): void
    {
        $this->fund();
        $response = $this->actingAs($this->buyer, 'web')->get('/dashboard?tab=settings&section=wallet')->assertOk();
        $html = $response->getContent();
        $this->assertLessThan(strpos($html, 'id="acct-wallet"'), strpos($html, 'id="acct-personal"'));
        $this->assertLessThan(strpos($html, 'id="acct-addresses"'), strpos($html, 'id="acct-wallet"'));
        $response->assertSee('No cash-out')->assertSee('1,000.00');
    }

    public function test_web_checkout_spends_wallet_without_requesting_a_gcash_receipt(): void
    {
        $this->fund();
        $this->buyer->update(['id_status' => 'verified']);
        $total = app(CheckoutQuote::class)->build($this->buyer, $this->lines(), $this->address)['total_minor'];
        $this->mock(\App\Services\BuyerCart::class, function ($mock) {
            $mock->shouldReceive('all')->andReturn(['item' => $this->lines()[0]]);
            $mock->shouldReceive('remove')->once()->with(['item']);
        });
        $this->actingAs($this->buyer, 'web')->post(route('checkout.process'), [
            'payment_method' => 'GCash', 'wallet_only' => 1, 'wallet_amount_minor' => $total,
            'checkout_key' => 'web-wallet', 'delivery_address' => $this->address->formatted_address,
            'selected_items' => ['item'],
        ])->assertSessionHasNoErrors()->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']));
        $this->assertSame('paid', Order::first()->payment_status);
        $this->assertSame(100000 - $total, app(BuyerWallet::class)->balance($this->buyer->id));
    }

    public function test_split_gcash_payment_keeps_its_receipt_and_verifies_only_the_remaining_amount(): void
    {
        $this->fund(5000);
        $order = app(OrderPlacement::class)->place($this->buyer, $this->lines(), 'GCash', $this->address->formatted_address,
            null, $this->address, 'GCASH-REF', 'receipt.png', 5000, 'split-gcash');
        $parcel = $order->sellerOrders->first();
        $payment = app(\App\Services\PaymentVerification::class)->verify($parcel, $parcel->seller->user, ['decision' => 'approved']);
        $this->assertSame('gcash', $payment->method);
        $this->assertSame($order->total_minor - 5000, $payment->amount_minor);
        $this->assertSame('GCASH-REF', $payment->provider_ref);
        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('GCash', app(\App\Services\BuyerOrders::class)->card($parcel->fresh())['payment']);
    }

    public function test_wallet_refund_of_wallet_purchase_cannot_be_credited_again_by_cancellation(): void
    {
        $this->fund();
        $total = app(CheckoutQuote::class)->build($this->buyer, $this->lines(), $this->address)['total_minor'];
        $order = app(OrderPlacement::class)->place($this->buyer, $this->lines(), 'CartzyWallet', $this->address->formatted_address,
            null, $this->address, null, null, $total, 'wallet-return');
        $case = $this->refundCase($order);
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        app(ReturnWorkflow::class)->act($admin, $case->id, 'refund', ['method' => 'cartzy_wallet', 'notes' => 'Confirmed refund']);
        $order->sellerOrders()->first()->update(['status' => 'cancelled']);
        $this->assertSame(100000, app(BuyerWallet::class)->balance($this->buyer->id));
        $this->assertSame(0, WalletTransaction::where('type', 'cancellation_refund')->count());
    }
}
