<?php

namespace Tests\Feature;

use App\Models\{Order, ProductInquiry, Seller, SellerMessage, SellerOrder, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderChatPopupTest extends TestCase
{
    use RefreshDatabase;

    private function seller(): Seller
    {
        $owner = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        return Seller::create(['user_id' => $owner->id, 'name' => 'Shoe Studio', 'slug' => 'shoe-studio', 'status' => 'approved']);
    }

    public function test_popup_is_only_rendered_for_active_buyers(): void
    {
        $this->get(route('home'))->assertOk()->assertDontSee('id="cw"', false);
        $this->actingAs($this->seller()->user)->get(route('home'))->assertOk()->assertDontSee('id="cw"', false);

        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $this->actingAs($buyer)->get(route('home'))->assertOk()
            ->assertSee('id="cw"', false)->assertSee('js/chat-widget.js', false)
            ->assertSee('id="cw-minimize"', false)->assertSeeInOrder(['js/chat-dock.js', 'js/chat-widget.js'], false)
            ->assertSee('data-buyer-nav="messages"', false)->assertSee(route('buyer.dashboard', ['tab' => 'messages']), false);
    }

    public function test_popup_lists_and_opens_store_and_order_conversations(): void
    {
        $seller = $this->seller();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);
        $other = User::factory()->create(['role' => 'buyer', 'status' => 'active']);

        $storeChat = ProductInquiry::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        $storeChat->messages()->create(['sender_id' => $seller->user_id, 'body' => 'Hi! How can we help?']);
        $order = Order::forceCreate(['buyer_id' => $buyer->id, 'reference' => 'ORD-123', 'total_minor' => 1000, 'shipping_address' => json_encode([])]);
        $thread = SellerOrder::forceCreate(['order_id' => $order->id, 'seller_id' => $seller->id, 'status' => 'pending', 'subtotal_minor' => 1000]);
        SellerMessage::create(['seller_order_id' => $thread->id, 'sender_id' => $seller->user_id, 'body' => 'Your order is packed.']);
        $othersChat = ProductInquiry::create(['buyer_id' => $other->id, 'seller_id' => $seller->id]);
        $othersChat->messages()->create(['sender_id' => $other->id, 'body' => 'Private question']);

        $this->getJson(route('buyer.popup.conversations'))->assertUnauthorized();

        $list = $this->actingAs($buyer)->getJson(route('buyer.popup.conversations'))->assertOk()->json('conversations');
        $this->assertCount(2, $list); // empty inquiries and other buyers' chats are left out
        $this->assertEqualsCanonicalizing(['Store chat', 'Order ORD-123'], array_column($list, 'subtitle'));
        $this->assertSame([1, 1], array_column($list, 'unread'));
        $this->assertSame([null, null], array_column($list, 'avatar')); // no logo uploaded: the bubble falls back to the initial

        $store = collect($list)->firstWhere('subtitle', 'Store chat');
        $this->getJson($store['thread_url'])->assertOk()
            ->assertJsonPath('title', 'Shoe Studio')->assertJsonPath('avatar', null)->assertJsonPath('messages.0.body', 'Hi! How can we help?')
            ->assertJsonPath('send_url', route('buyer.inquiries.send', $storeChat->id))->assertJsonPath('unread_total', 1);
        $this->assertNotNull($storeChat->messages()->first()->fresh()->read_at);

        $orderItem = collect($list)->firstWhere('subtitle', 'Order ORD-123');
        $data = $this->getJson($orderItem['thread_url'])->assertOk()->assertJsonPath('messages.0.body', 'Your order is packed.')->assertJsonPath('unread_total', 0)->json();
        $this->postJson($data['send_url'], ['message' => 'Thank you!'])->assertCreated()->assertJsonPath('message.body', 'Thank you!')->assertJsonPath('message.mine', true);

        $this->getJson(route('buyer.popup.inquiry', $othersChat->id))->assertNotFound();
        $this->actingAs($seller->user)->getJson(route('buyer.popup.conversations'))->assertForbidden();
    }

    public function test_message_status_and_typing_indicator(): void
    {
        $seller = $this->seller();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active']);

        $inquiry = ProductInquiry::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        $msg = $inquiry->messages()->create([
            'sender_id' => $buyer->id,
            'body' => 'Hello seller',
        ]);

        // When created, message has no delivered_at or read_at -> sent
        $buyerData = $this->actingAs($buyer)->getJson(route('buyer.popup.inquiry', $inquiry->id))
            ->assertOk()
            ->json();
        $this->assertSame('sent', $buyerData['messages'][0]['status']);
        $this->assertFalse($buyerData['typing']['is_typing']);

        // Seller records typing
        $this->actingAs($seller->user)->postJson(route('chat.typing'), [
            'type' => 'inquiry',
            'id' => $inquiry->id,
            'typing' => true,
        ])->assertOk();

        // Buyer checks thread again -> typing is active
        $buyerData = $this->actingAs($buyer)->getJson(route('buyer.popup.inquiry', $inquiry->id))
            ->assertOk()
            ->json();
        $this->assertTrue($buyerData['typing']['is_typing']);
        $this->assertSame('Shoe Studio', $buyerData['typing']['name']);

        // Seller opens thread (which marks the message as read & delivered)
        $this->actingAs($seller->user)->getJson(route('seller.chat', ['inquiry' => $inquiry->id]))->assertOk();

        // Buyer checks thread again -> message status is now seen
        $buyerData = $this->actingAs($buyer)->getJson(route('buyer.popup.inquiry', $inquiry->id))
            ->assertOk()
            ->json();
        $this->assertSame('seen', $buyerData['messages'][0]['status']);

        // Seller stops typing
        $this->actingAs($seller->user)->postJson(route('chat.typing'), [
            'type' => 'inquiry',
            'id' => $inquiry->id,
            'typing' => false,
        ])->assertOk();

        $buyerData = $this->actingAs($buyer)->getJson(route('buyer.popup.inquiry', $inquiry->id))
            ->assertOk()
            ->json();
        $this->assertFalse($buyerData['typing']['is_typing']);
    }
}
