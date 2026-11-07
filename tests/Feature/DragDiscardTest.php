<?php

namespace Tests\Feature;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DragDiscardTest extends TestCase
{
    use RefreshDatabase;

    private function shop(): Seller
    {
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);

        return Seller::create(['user_id' => $user->id, 'name' => 'Drag Shop', 'slug' => 'drag-shop', 'status' => 'approved']);
    }

    public function test_drag_script_loads_before_the_buyer_chat_dock(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'buyer', 'status' => 'active']))
            ->get(route('home'))->assertOk()
            ->assertSeeInOrder(['js/drag-discard.js', 'js/chat-dock.js', 'js/chat-widget.js'], false);
    }

    public function test_drag_script_loads_before_the_seller_chat_bubbles(): void
    {
        $this->actingAs($this->shop()->user)
            ->get('/seller/orders')->assertOk()
            ->assertSeeInOrder(['id="sm-bubble-wrap"', 'id="lc-bubble-wrap"', 'js/drag-discard.js', 'js/seller-chat-popups.js'], false);
    }

    public function test_store_chat_bubble_is_on_the_page_before_the_drag_script_runs(): void
    {
        $this->get(route('store.show', $this->shop()->slug))->assertOk()
            ->assertSeeInOrder(['chat-bubble-form', 'js/drag-discard.js'], false);
    }
}
