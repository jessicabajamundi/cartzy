<?php

namespace Tests\Feature;

use App\Models\{AppNotification, Order, Seller, SellerMessage, SellerOrder, User};
use App\Services\OrderAcceptanceMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderAcceptanceMessagingTest extends TestCase
{
    use RefreshDatabase;

    private function parcel(?Order $parent = null): SellerOrder
    {
        $buyer = $parent?->buyer ?? User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $owner = User::factory()->create(['role' => 'seller', 'status' => 'active', 'name' => 'Private Owner Name']);
        $seller = Seller::create(['user_id' => $owner->id, 'name' => 'Cartzy Shop '.$owner->id,
            'slug' => 'shop-'.$owner->id, 'status' => 'approved']);
        $parent ??= Order::create(['buyer_id' => $buyer->id, 'reference' => 'ORD-ACCEPT-'.$buyer->id,
            'total_minor' => 10000, 'payment_method' => 'cod', 'payment_status' => 'pending', 'shipping_address' => []]);
        return SellerOrder::create(['order_id' => $parent->id, 'seller_id' => $seller->id,
            'subtotal_minor' => 10000, 'commission_minor' => 1000, 'shipping_fee_minor' => 0, 'status' => 'pending']);
    }

    private function accept(SellerOrder $parcel): void
    {
        $this->actingAs($parcel->seller->user)
            ->post(route('seller.orders.decision', $parcel->id), ['decision' => 'accepted'])
            ->assertRedirect()->assertSessionHasNoErrors();
    }

    public function test_pending_order_is_visible_and_only_acceptance_sends_shop_message_and_notification(): void
    {
        $parcel = $this->parcel();
        $this->actingAs($parcel->seller->user)->get(route('seller.orders'))->assertOk()->assertSee($parcel->order->reference);
        $this->assertDatabaseCount('seller_messages', 0);
        $this->assertDatabaseCount('app_notifications', 0);
        $this->accept($parcel);

        $this->assertSame('accepted', $parcel->fresh()->status);
        $message = $parcel->messages()->sole();
        $this->assertSame($parcel->seller->user_id, $message->sender_id);
        $this->assertSame('order_accepted', $message->event_key);
        $this->assertStringStartsWith("Hi, Dear Customer!\n\n", $message->body);
        $this->assertStringContainsString('Thank you for choosing '.$parcel->seller->name.'!', $message->body);
        $this->assertStringContainsString('Order number: '.$parcel->order->reference, $message->body);
        $this->assertStringContainsString('Order date: '.$parcel->order->created_at->timezone('Asia/Manila')->format('M d, Y'), $message->body);
        $this->assertStringEndsWith("Best regards,\n".$parcel->seller->name, $message->body);
        $this->assertStringNotContainsString('Private Owner Name', $message->body);
        $this->assertNull($message->read_at);
        $notification = AppNotification::sole();
        $this->assertSame($parcel->order->buyer_id, $notification->user_id);
        $this->assertStringContainsString($parcel->order->reference, $notification->message);
        $this->assertStringContainsString('Order date:', $notification->message);
        $this->assertSame(route('buyer.messages', ['contact' => $parcel->id]), $notification->link);

        $this->actingAs($parcel->order->buyer)->get(route('buyer.dashboard', ['tab' => 'notifications']))
            ->assertOk()->assertSee('Open order conversation')->assertSee($notification->link, false);
        $this->get($notification->link)->assertOk()->assertSee('Thank you for choosing '.$parcel->seller->name.'!');
    }

    public function test_retries_and_repeated_dispatch_cannot_duplicate_automatic_records(): void
    {
        $parcel = $this->parcel();
        $this->accept($parcel);
        $this->post(route('seller.orders.decision', $parcel->id), ['decision' => 'accepted'])->assertUnprocessable();
        app(OrderAcceptanceMessage::class)->send($parcel->fresh());
        app(OrderAcceptanceMessage::class)->send($parcel->fresh());
        $this->assertDatabaseCount('seller_messages', 1);
        $this->assertDatabaseCount('app_notifications', 1);
    }

    public function test_database_rejects_duplicate_system_events_but_allows_personal_replies(): void
    {
        $parcel = $this->parcel();
        $this->accept($parcel);
        try {
            \Illuminate\Support\Facades\DB::transaction(fn () => $parcel->messages()->create([
                'sender_id' => $parcel->seller->user_id, 'body' => 'Duplicate acceptance', 'event_key' => 'order_accepted',
            ]));
            $this->fail('Expected the unique order event constraint to reject the duplicate.');
        } catch (\Illuminate\Database\QueryException $e) {
            $this->assertStringContainsString('unique', strtolower($e->getMessage()));
        }
        foreach (['First personal update', 'Second personal update'] as $body) {
            $parcel->messages()->create(['sender_id' => $parcel->seller->user_id, 'body' => $body]);
        }
        $this->assertSame(3, $parcel->messages()->count());
        $this->assertSame(1, $parcel->messages()->where('event_key', 'order_accepted')->count());
    }

    public function test_seller_can_continue_personalized_chat_and_other_sellers_cannot_access_it(): void
    {
        $parcel = $this->parcel();
        $this->accept($parcel);
        $this->post(route('seller.chat.send', $parcel->id), ['message' => 'We will pack your blue item today.'])->assertRedirect();
        $this->assertDatabaseHas('seller_messages', ['seller_order_id' => $parcel->id,
            'body' => 'We will pack your blue item today.', 'event_key' => null]);
        $other = $this->parcel();
        $this->actingAs($other->seller->user)->post(route('seller.chat.send', $parcel->id), ['message' => 'Unauthorized'])->assertNotFound();
        Sanctum::actingAs($parcel->order->buyer);
        $this->getJson('/api/messages/thread?type=order&id='.$parcel->id)->assertOk()
            ->assertJsonCount(2, 'messages')->assertJsonPath('messages.1.body', 'We will pack your blue item today.');
        Sanctum::actingAs($other->order->buyer);
        $this->getJson('/api/messages/thread?type=order&id='.$parcel->id)->assertNotFound();
    }

    public function test_rejected_blocked_and_foreign_orders_do_not_send_acceptance_messages(): void
    {
        $parcel = $this->parcel();
        $other = $this->parcel();
        $this->actingAs($other->seller->user)->post(route('seller.orders.decision', $parcel->id), ['decision' => 'accepted'])->assertNotFound();
        $parcel->update(['cancellation_status' => 'pending']);
        $this->actingAs($parcel->seller->user)->post(route('seller.orders.decision', $parcel->id), ['decision' => 'accepted'])->assertUnprocessable();
        $parcel->update(['cancellation_status' => null]);
        $this->post(route('seller.orders.decision', $parcel->id), ['decision' => 'rejected'])->assertRedirect();
        $this->assertDatabaseCount('seller_messages', 0);
        $this->assertSame(0, AppNotification::where('type', 'order_accepted')->count());
    }

    public function test_notification_failure_rolls_back_acceptance_and_message(): void
    {
        $parcel = $this->parcel();
        Event::listen('eloquent.creating: '.AppNotification::class, function () {
            throw new \RuntimeException('Notification write failed');
        });
        $this->withoutExceptionHandling();
        try {
            $this->actingAs($parcel->seller->user)->post(route('seller.orders.decision', $parcel->id), ['decision' => 'accepted']);
            $this->fail('Expected notification failure.');
        } catch (\RuntimeException $e) {
            $this->assertSame('Notification write failed', $e->getMessage());
        }
        $this->assertSame('pending', $parcel->fresh()->status);
        $this->assertDatabaseCount('seller_messages', 0);
        $this->assertDatabaseCount('app_notifications', 0);
    }

    public function test_each_shop_in_one_checkout_has_its_own_message_and_mobile_link(): void
    {
        $first = $this->parcel();
        $second = $this->parcel($first->order);
        $this->accept($first);
        $this->assertSame('pending', $second->fresh()->status);
        $this->assertSame(0, $second->messages()->count());
        $this->accept($second);
        Sanctum::actingAs($first->order->buyer);
        $notifications = $this->getJson('/api/notifications')->assertOk()->assertJsonCount(2, 'notifications')->json('notifications');
        $this->assertEqualsCanonicalizing([$first->id, $second->id], array_column($notifications, 'conversation_order_id'));
        foreach ($notifications as $notification) {
            $this->assertSame('orders', $notification['tag']);
            $this->assertSame($first->order->reference, $notification['order_reference']);
        }
        $this->getJson('/api/messages')->assertOk()->assertJsonPath('unread_total', 2);
    }
}
