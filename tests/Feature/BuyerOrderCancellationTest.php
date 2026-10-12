<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, Seller, SellerOrder, User};
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerOrderCancellationTest extends TestCase
{
    use RefreshDatabase;

    private function place(): SellerOrder
    {
        $buyer = User::factory()->create();
        $sellerUser = User::factory()->create(['role' => 'seller']);
        Seller::create(['user_id' => $sellerUser->id, 'name' => 'Test Shop', 'slug' => 'test-shop', 'status' => 'approved']);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);
        $order = app(OrderPlacement::class)->place($buyer, [
            ['id' => 1, 'name' => 'Coffee mug', 'price' => 280, 'quantity' => 1],
        ], 'GCash', 'Test address');
        $this->actingAs($buyer);

        return $order->sellerOrders()->firstOrFail();
    }

    public function test_buyer_can_cancel_and_cannot_pay_cancelled_order(): void
    {
        $so = $this->place();
        $this->get('/dashboard?tab=orders')->assertOk()->assertSee('Cancel order');
        $this->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind'])->assertRedirect(route('buyer.dashboard', ['tab' => 'orders']))->assertSessionHasNoErrors();
        $this->assertSame('pending', $so->fresh()->status);
        $this->assertSame('pending', $so->fresh()->cancellation_status);
        $this->assertDatabaseHas('payments', ['order_id' => $so->order_id, 'status' => 'pending']);
        $this->get('/dashboard?tab=orders')->assertOk()->assertSee('awaiting seller approval');
        $this->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('app_notifications', 2);
        $this->approve($so);
        $this->assertSame('cancelled', $so->fresh()->status);
        $this->assertDatabaseHas('payments', ['order_id' => $so->order_id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('delivery_events', ['shipment_id' => $so->shipment->id, 'status' => 'cancelled']);
        $this->get('/dashboard?tab=orders')->assertOk()->assertDontSee('Cancel order')->assertDontSee('Pay now');
        $this->get('/dashboard?tab=track')->assertViewHas('tracking', fn ($tracking) => $tracking['eta'] === 'Cancelled');
        $this->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind'])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('delivery_events', 2);
        $this->post(route('orders.pay', $so->order->reference))->assertSessionHasErrors('order');
        $this->assertSame('pending', $so->order->fresh()->payment_status);
    }

    public function test_only_owner_can_cancel(): void
    {
        $so = $this->place();
        $this->actingAs(User::factory()->create())->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind'])->assertNotFound();
        $this->assertSame('pending', $so->fresh()->status);
    }

    public function test_reason_is_required_and_other_requires_details(): void
    {
        $so = $this->place();
        $this->post(route('orders.cancel', $so->id))->assertSessionHasErrors('reason');
        $this->post(route('orders.cancel', $so->id), ['reason' => 'Invalid reason'])->assertSessionHasErrors('reason');
        $this->post(route('orders.cancel', $so->id), ['reason' => 'Other reason', 'reason_details' => '   '])->assertSessionHasErrors('reason_details');
        $this->assertSame('pending', $so->fresh()->status);
        $this->assertDatabaseCount('delivery_events', 1);
        $so->update(['note' => 'Existing seller note']);
        $this->post(route('orders.cancel', $so->id), ['reason' => 'Other reason', 'reason_details' => 'I will be away on delivery day.'])->assertSessionHasNoErrors();
        $this->assertSame('Existing seller note', $so->fresh()->note);
        $this->flushSession();
        $this->actingAs($so->seller->user)->get('/seller/orders?status=cancellation_requested')->assertOk()->assertSee('I will be away on delivery day.')->assertSee('Approve cancellation');
        $this->approve($so);
        $this->assertSame("Existing seller note\nCancellation reason: Other reason: I will be away on delivery day.", $so->fresh()->note);
        $this->assertDatabaseHas('delivery_events', [
            'shipment_id' => $so->shipment->id, 'status' => 'cancelled',
            'note' => 'Cancellation approved by seller. Cancellation reason: Other reason: I will be away on delivery day.',
        ]);
        $this->flushSession();
        $this->flushSession();
        $this->actingAs($so->seller->user)->get('/seller/orders')->assertOk()->assertSee('I will be away on delivery day.');
    }

    public function test_collected_and_completed_orders_cannot_be_cancelled(): void
    {
        $so = $this->place();
        $so->shipment->update(['status' => 'picked_up']);
        $this->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind'])->assertSessionHasErrors('order');
        $this->get('/dashboard?tab=orders')->assertDontSee('Cancel order');
        $so->shipment->update(['status' => 'unassigned']);
        foreach (['shipped', 'delivered', 'completed'] as $status) {
            $so->update(['status' => $status]);
            $this->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind'])->assertSessionHasErrors('order');
            $this->assertSame($status, $so->fresh()->status);
        }
    }

    public function test_paid_order_cancellation_preserves_payment_and_shows_refund_guidance(): void
    {
        $so = $this->place();
        $so->order->update(['payment_status' => 'paid']);
        $so->order->payments()->update(['status' => 'paid']);
        $so->update(['status' => 'ready_to_ship']);
        $this->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind'])->assertSessionHasNoErrors();
        $this->assertSame('ready_to_ship', $so->fresh()->status);
        $this->approve($so);
        $this->assertSame('cancelled', $so->fresh()->status);
        $this->assertSame('paid', $so->order->fresh()->payment_status);
        $this->assertDatabaseHas('payments', ['order_id' => $so->order_id, 'status' => 'paid']);
    }

    private function approve(SellerOrder $so): void
    {
        $this->flushSession();
        $this->actingAs($so->seller->user)->post(route('seller.orders.cancellation', $so->id), ['decision' => 'approved'])->assertRedirect()->assertSessionHasNoErrors();
        $this->flushSession();
        $this->actingAs($so->order->buyer);
    }

    public function test_seller_can_decline_and_only_own_seller_can_review(): void
    {
        $so = $this->place();
        $this->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind']);
        $this->post(route('seller.orders.cancellation', $so->id), ['decision' => 'approved'])->assertForbidden();
        $other = User::factory()->create(['role' => 'seller']);
        $this->actingAs($other)->post(route('seller.orders.cancellation', $so->id), ['decision' => 'approved'])->assertNotFound();
        $this->flushSession();
        $this->actingAs($so->seller->user)->post(route('seller.orders.cancellation', $so->id), ['decision' => 'rejected'])->assertRedirect();
        $this->assertSame('pending', $so->fresh()->status);
        $this->assertSame('rejected', $so->fresh()->cancellation_status);
        $this->assertDatabaseHas('payments', ['order_id' => $so->order_id, 'status' => 'pending']);
        $this->post(route('seller.orders.cancellation', $so->id), ['decision' => 'approved'])->assertUnprocessable();
    }

    public function test_approval_rechecks_shipment_and_pending_request_blocks_packing(): void
    {
        $so = $this->place();
        $so->order->update(['payment_status' => 'paid']);
        $this->post(route('orders.cancel', $so->id), ['reason' => 'I changed my mind']);
        $this->flushSession();
        $this->actingAs($so->seller->user)->post(route('seller.orders.pack', $so->id))->assertUnprocessable();
        $so->shipment->update(['status' => 'picked_up']);
        $this->post(route('seller.orders.cancellation', $so->id), ['decision' => 'approved'])->assertUnprocessable();
        $this->assertSame('pending', $so->fresh()->status);
    }
}
