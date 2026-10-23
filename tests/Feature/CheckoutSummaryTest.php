<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BuyerCart;
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutSummaryTest extends TestCase
{
    use RefreshDatabase;

    private function items(): array
    {
        return [
            'watch' => ['name' => 'Watch', 'image' => '/watch.jpg', 'variation' => 'Black', 'price' => 450, 'original_price' => 600, 'quantity' => 2, 'selected' => true],
            'bag' => ['name' => 'Bag', 'image' => '/bag.jpg', 'variation' => 'Blue', 'price' => 200, 'quantity' => 1, 'selected' => true],
            'unselected' => ['name' => 'Not selected', 'image' => '/other.jpg', 'price' => 500, 'quantity' => 1, 'selected' => false],
        ];
    }

    public function test_summary_separates_product_savings_from_shipping_and_counts_units(): void
    {
        $this->mock(BuyerCart::class)->shouldReceive('all')->andReturn($this->items());
        $this->actingAs(User::factory()->create(['id_status' => 'verified', 'address' => 'Test delivery address']))
            ->get(route('checkout'))->assertOk()
            ->assertViewHas('totalQuantity', 3)
            ->assertViewHas('originalSubtotal', 1400)
            ->assertViewHas('productDiscount', 300)
            ->assertViewHas('subtotal', 1100)
            ->assertViewHas('discount', 50)
            ->assertViewHas('totalSavings', 350)
            ->assertViewHas('total', 1110)
            ->assertSee('Best vouchers applied for you')
            ->assertSee('Product discounts')
            ->assertDontSee('Not selected');
    }

    public function test_shipping_threshold_uses_discounted_subtotal(): void
    {
        $items = $this->items();
        unset($items['bag'], $items['unselected']);
        $this->mock(BuyerCart::class)->shouldReceive('all')->andReturn($items);
        $this->actingAs(User::factory()->create(['id_status' => 'verified', 'address' => null]))
            ->get(route('checkout'))->assertOk()
            ->assertViewHas('originalSubtotal', 1200)
            ->assertViewHas('subtotal', 900)
            ->assertViewHas('discount', 0)
            ->assertViewHas('total', 960)
            ->assertSee('Add your delivery address to continue.')
            ->assertSee('class="checkout-submit" disabled', false);
    }

    public function test_checkout_rejects_unavailable_online_payment_methods(): void
    {
        $this->mock(OrderPlacement::class)->shouldNotReceive('place');
        $this->actingAs(User::factory()->create(['id_status' => 'verified']))
            ->post(route('checkout.process'), ['payment_method' => 'GCash', 'delivery_address' => 'Test address', 'selected_items' => ['watch']])
            ->assertSessionHasErrors('payment_method');
    }
}
