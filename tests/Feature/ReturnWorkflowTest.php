<?php

namespace Tests\Feature;

use App\Models\{AdminDispute, LogisticsProvider, Order, RefundTransaction, ReturnCase, Rider, Seller, SellerOrder, User};
use App\Services\ReturnWorkflow;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReturnWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function createOrderWithDispute(string $resolution = 'return_refund', int $amountMinor = 150000): array
    {
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'is_suspended' => false]);
        $sellerUser = User::factory()->create(['role' => 'seller', 'status' => 'active', 'is_suspended' => false]);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'name' => 'Test Store',
            'slug' => 'test-store',
            'status' => 'approved',
            'pickup_address' => [
                'street' => '123 Seller St',
                'barangay' => 'San Antonio',
                'city' => 'Pasig',
                'province' => 'Metro Manila',
                'postal_code' => '1600',
            ],
        ]);

        $order = Order::create([
            'buyer_id' => $buyer->id,
            'reference' => 'ORD-'.uniqid(),
            'total_minor' => $amountMinor,
            'payment_method' => 'wallet',
            'payment_status' => 'paid',
            'shipping_address' => [
                'name' => 'Buyer Name',
                'phone' => '09171234567',
                'address' => '456 Buyer Rd, Quezon City',
            ],
        ]);

        $sellerOrder = SellerOrder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'subtotal_minor' => $amountMinor,
            'commission_minor' => 1500,
            'status' => 'delivered',
        ]);

        $dispute = AdminDispute::create([
            'order_id' => $order->id,
            'seller_order_id' => $sellerOrder->id,
            'reporter_id' => $buyer->id,
            'type' => 'problem_report',
            'subject' => 'Damaged item',
            'description' => 'The item arrived damaged.',
            'contact_phone' => '09171234567',
            'requested_resolution' => $resolution,
            'items' => [['name' => 'Sample Product', 'qty' => 1, 'price_minor' => $amountMinor]],
            'status' => 'open',
        ]);

        $case = app(ReturnWorkflow::class)->open($dispute);

        return compact('buyer', 'sellerUser', 'seller', 'order', 'sellerOrder', 'dispute', 'case');
    }

    private function createLogisticsWithRider(): array
    {
        $hubUser = User::factory()->create(['role' => 'logistics', 'status' => 'active', 'is_suspended' => false]);
        $provider = LogisticsProvider::create([
            'user_id' => $hubUser->id,
            'name' => 'Metro Express Hub',
            'slug' => 'metro-express-hub',
            'status' => 'approved',
            'address' => [
                'street' => '789 Logistics Way',
                'city' => 'Taguig',
                'province' => 'Metro Manila',
            ],
        ]);

        $riderUser = User::factory()->create(['role' => 'courier', 'status' => 'active', 'is_suspended' => false]);
        $rider = Rider::create([
            'user_id' => $riderUser->id,
            'logistics_provider_id' => $provider->id,
            'status' => 'approved',
            'is_active' => true,
            'online_until' => now()->addHours(4),
        ]);

        return compact('hubUser', 'provider', 'riderUser', 'rider');
    }

    public function test_return_case_is_created_when_problem_report_requests_return_or_refund(): void
    {
        $data = $this->createOrderWithDispute('return_refund');
        $this->assertNotNull($data['case']);
        $this->assertSame('requested', $data['case']->status);
        $this->assertDatabaseHas('return_cases', ['admin_dispute_id' => $data['dispute']->id, 'status' => 'requested']);
    }

    public function test_seller_can_approve_return_request(): void
    {
        $data = $this->createOrderWithDispute('return_refund');
        $case = $data['case'];

        $response = $this->actingAs($data['sellerUser'])->post("/returns/{$case->id}/approve", [
            'amount' => '1500.00',
            'return_address' => '123 Seller Return Warehouse, Pasig City',
            'notes' => 'Approved, please return in original packaging.',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame('awaiting_return', $case->fresh()->status);
        $this->assertSame(150000, $case->fresh()->amount_minor);
        $this->assertSame('123 Seller Return Warehouse, Pasig City', $case->fresh()->return_address);
    }

    public function test_seller_can_reject_return_request_and_buyer_can_appeal(): void
    {
        $data = $this->createOrderWithDispute('return_refund');
        $case = $data['case'];

        // Seller rejects
        $this->actingAs($data['sellerUser'])->post("/returns/{$case->id}/reject", [
            'notes' => 'Product was used, not eligible.',
        ])->assertSessionHasNoErrors();

        $this->assertSame('rejected', $case->fresh()->status);
        $this->assertSame('resolved', $data['dispute']->fresh()->status);

        // Buyer appeals to admin
        $this->actingAs($data['buyer'])->post("/returns/{$case->id}/appeal", [
            'notes' => 'I never used the item, it arrived broken.',
        ])->assertSessionHasNoErrors();

        $this->assertSame('disputed', $case->fresh()->status);
        $this->assertSame('open', $data['dispute']->fresh()->status);
    }

    public function test_full_return_and_refund_lifecycle(): void
    {
        Storage::fake('local');
        $data = $this->createOrderWithDispute('return_refund');
        $case = $data['case'];
        $logistics = $this->createLogisticsWithRider();
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'is_suspended' => false]);

        // 1. Seller approves return
        $this->actingAs($data['sellerUser'])->post("/returns/{$case->id}/approve", [
            'amount' => '1500.00',
            'return_address' => 'Return Warehouse, Pasig',
            'notes' => 'Please return the parcel.',
        ])->assertSessionHasNoErrors();
        $this->assertSame('awaiting_return', $case->fresh()->status);

        // 2. Buyer schedules return
        $this->actingAs($data['buyer'])->post("/returns/{$case->id}/schedule", [
            'handover_method' => 'pickup',
            'logistics_provider_id' => $logistics['provider']->id,
            'scheduled_date' => now()->addDay()->format('Y-m-d'),
            'pickup_address' => '456 Buyer Rd, QC',
            'notes' => 'Available in the morning.',
        ])->assertSessionHasNoErrors();

        $case = $case->fresh();
        $this->assertSame('scheduled', $case->status);
        $this->assertStringStartsWith('RET-', $case->tracking_code);

        // 3. Logistics hub dispatches rider
        $this->actingAs($logistics['hubUser'])->post("/returns/{$case->id}/dispatch", [
            'rider_id' => $logistics['rider']->id,
            'notes' => 'Rider assigned for morning pickup.',
        ])->assertSessionHasNoErrors();
        $this->assertSame($logistics['rider']->id, $case->fresh()->rider_id);

        // 4. Logistics hub confirms collection
        $this->actingAs($logistics['hubUser'])->post("/returns/{$case->id}/collect", [
            'notes' => 'Parcel collected and in transit.',
        ])->assertSessionHasNoErrors();
        $this->assertSame('in_transit', $case->fresh()->status);

        // 5. Seller receives parcel
        $this->actingAs($data['sellerUser'])->post("/returns/{$case->id}/receive", [
            'notes' => 'Parcel arrived at our warehouse.',
        ])->assertSessionHasNoErrors();
        $this->assertSame('received', $case->fresh()->status);
        $this->assertNotNull($case->fresh()->received_at);

        // 6. Seller inspects parcel and accepts
        $this->actingAs($data['sellerUser'])->post("/returns/{$case->id}/inspect", [
            'result' => 'accepted',
            'notes' => 'Item verified damaged in transit.',
        ])->assertSessionHasNoErrors();
        $this->assertSame('refund_pending', $case->fresh()->status);

        // 7. Admin records refund payment
        $file = UploadedFile::fake()->create('receipt.png', 200, 'image/png');
        $this->actingAs($admin)->post("/returns/{$case->id}/refund", [
            'method' => 'gcash',
            'reference' => 'GCASH-REF-'.uniqid(),
            'paid_at' => now()->format('Y-m-d'),
            'paid_confirmation' => '1',
            'proof' => $file,
            'notes' => 'GCash payment sent to buyer.',
        ])->assertSessionHasNoErrors();

        $case = $case->fresh();
        $this->assertSame('refunded', $case->status);
        $this->assertSame('refunded', $data['order']->fresh()->payment_status);
        $this->assertSame('resolved', $data['dispute']->fresh()->status);
        $this->assertDatabaseHas('refund_transactions', [
            'return_case_id' => $case->id,
            'amount_minor' => 150000,
        ]);
    }

    public function test_visibility_scoping(): void
    {
        $data1 = $this->createOrderWithDispute('return_refund');
        $otherBuyer = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'is_suspended' => false]);

        // Other buyer cannot view case
        $response = $this->actingAs($otherBuyer)->getJson('/returns');
        $response->assertOk();
        $this->assertCount(0, $response->json('data'));

        // Case owner buyer can view case
        $response = $this->actingAs($data1['buyer'])->getJson('/returns');
        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($data1['case']->id, $response->json('data.0.id'));
    }

    public function test_admin_cannot_directly_resolve_dispute_if_return_case_exists(): void
    {
        $data = $this->createOrderWithDispute('return_refund');
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active', 'is_suspended' => false]);

        $this->actingAs($admin)->post("/admin/disputes/{$data['dispute']->id}/resolve", [
            'notes' => 'Try to close directly.',
        ])->assertStatus(422);
    }
}
