<?php

namespace Tests\Feature;

use App\Models\{Category, Product, ProductInquiry, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerInquiryInboxTest extends TestCase
{
    use RefreshDatabase;

    private function seller(string $name = 'Shoe Studio'): Seller
    {
        $owner = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        return Seller::create(['user_id' => $owner->id, 'name' => $name, 'slug' => \Illuminate\Support\Str::slug($name), 'status' => 'approved']);
    }

    public function test_seller_sees_and_answers_store_chats_and_product_questions(): void
    {
        $seller = $this->seller();
        $category = Category::create(['name' => 'Shoes', 'slug' => 'shoes', 'is_active' => true]);
        $product = Product::create(['seller_id' => $seller->id, 'category_id' => $category->id, 'name' => 'Everyday sneaker', 'slug' => 'everyday-sneaker', 'is_active' => true]);
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'name' => 'Ana Buyer']);

        $storeChat = ProductInquiry::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        $storeChat->messages()->create(['sender_id' => $buyer->id, 'body' => 'Do you ship to Cebu?']);
        $question = ProductInquiry::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id, 'product_id' => $product->id]);
        $question->messages()->create(['sender_id' => $buyer->id, 'body' => 'Is it true to size?']);
        ProductInquiry::create(['buyer_id' => User::factory()->create(['role' => 'buyer'])->id, 'seller_id' => $seller->id]); // opened but never used
        $otherChat = ProductInquiry::create(['buyer_id' => $buyer->id, 'seller_id' => $this->seller('Other Shop')->id]);
        $otherChat->messages()->create(['sender_id' => $buyer->id, 'body' => 'Message for another store']);

        $this->actingAs($seller->user)->get(route('seller.chat'))->assertOk()
            ->assertSee('Product &amp; store questions', false)->assertSee('Ana Buyer')
            ->assertSee('Store chat')->assertSee('About: Everyday sneaker')->assertSee('Do you ship to Cebu?')
            ->assertDontSee('Message for another store')
            ->assertViewHas('inquiries', fn ($inquiries) => $inquiries->total() === 2);

        $this->get(route('seller.chat', ['inquiry' => $storeChat->id]))->assertOk()
            ->assertSee('Store chat · not linked to an order')->assertSee(route('store.show', $seller->slug), false)
            ->assertSee(route('seller.chat.inquiry.send', $storeChat->id), false);
        $this->assertNotNull($storeChat->messages()->first()->fresh()->read_at);

        $this->get(route('seller.chat', ['inquiry' => $question->id]))->assertOk()
            ->assertSee('Question about Everyday sneaker')->assertSee('Is it true to size?');

        $this->post(route('seller.chat.inquiry.send', $storeChat->id), ['message' => 'Yes, 3-5 days.'])
            ->assertRedirect(route('seller.chat', ['inquiry' => $storeChat->id]));
        $this->assertDatabaseHas('product_inquiry_messages', ['product_inquiry_id' => $storeChat->id, 'sender_id' => $seller->user_id, 'body' => 'Yes, 3-5 days.']);

        // The buyer sees the reply in the header popup.
        $this->actingAs($buyer)->getJson(route('buyer.popup.inquiry', $storeChat->id))->assertOk()->assertJsonPath('messages.1.body', 'Yes, 3-5 days.');

        // Other sellers cannot open or answer it.
        $this->actingAs($otherChat->seller->user)->get(route('seller.chat', ['inquiry' => $storeChat->id]))->assertNotFound();
        $this->post(route('seller.chat.inquiry.send', $storeChat->id), ['message' => 'Hijack'])->assertNotFound();
    }

    public function test_search_and_unread_filters_include_questions(): void
    {
        $seller = $this->seller();
        $buyer = User::factory()->create(['role' => 'buyer', 'status' => 'active', 'name' => 'Ana Buyer']);
        $chat = ProductInquiry::create(['buyer_id' => $buyer->id, 'seller_id' => $seller->id]);
        $chat->messages()->create(['sender_id' => $buyer->id, 'body' => 'Hello there']);

        $this->actingAs($seller->user)->get(route('seller.chat', ['search' => 'Ana']))->assertOk()->assertViewHas('inquiries', fn ($i) => $i->total() === 1);
        $this->get(route('seller.chat', ['search' => 'Nobody']))->assertOk()->assertViewHas('inquiries', fn ($i) => $i->total() === 0)->assertSee('No conversations found');
        $this->get(route('seller.chat', ['unread' => 1]))->assertOk()->assertViewHas('inquiries', fn ($i) => $i->total() === 1);
        $chat->messages()->update(['read_at' => now()]);
        $this->get(route('seller.chat', ['unread' => 1]))->assertOk()->assertViewHas('inquiries', fn ($i) => $i->total() === 0);
    }
}
