<?php

namespace Tests\Feature;

use App\Models\{LogisticsProvider, Seller, User};
use App\Services\OrderPlacement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogStoreTest extends TestCase
{
    use RefreshDatabase;

    private function setupMarketplace(): User
    {
        Seller::create(['user_id' => User::factory()->create()->id, 'name' => 'Registered Store', 'slug' => 'registered', 'status' => 'approved']);
        LogisticsProvider::create(['user_id' => User::factory()->create()->id, 'name' => 'Courier', 'slug' => 'courier', 'status' => 'approved']);

        return User::factory()->create();
    }

    private function mug(): array
    {
        return ['id' => 5, 'name' => 'Ceramic Coffee Mug Saucer Set', 'price' => 280, 'quantity' => 1];
    }

    public function test_catalog_uses_dedicated_store_and_reuses_it(): void
    {
        $buyer = $this->setupMarketplace();
        foreach ([1, 2] as $attempt) {
            $order = app(OrderPlacement::class)->place($buyer, [$this->mug()], 'COD', 'Test address');
            $so = $order->sellerOrders()->firstOrFail();
            $this->assertSame('Hearth & Home', $so->seller->name);
            $this->assertSame($so->seller_id, $so->items->first()->product->seller_id);
            $this->assertEquals(34000, $order->total_minor);
        }
        $this->assertDatabaseCount('sellers', 2);
        $this->assertDatabaseHas('sellers', ['name' => 'Registered Store', 'slug' => 'registered']);
    }

    public function test_multiple_stores_split_items_and_preserve_checkout_total_and_cod(): void
    {
        $buyer = $this->setupMarketplace();
        $order = app(OrderPlacement::class)->place($buyer, [$this->mug(),
            ['id' => 3, 'name' => 'Wireless Earphones', 'price' => 890, 'quantity' => 1],
        ], 'COD', 'Test address');
        $this->assertCount(2, $order->sellerOrders);
        $this->assertEquals(118000, $order->total_minor);
        $this->assertEquals($order->total_minor, $order->sellerOrders->sum(fn ($so) => $so->subtotal_minor + $so->shipping_fee_minor));
        $this->assertEquals($order->total_minor, $order->sellerOrders->sum(fn ($so) => $so->shipment->cod_amount_minor));
        $this->assertCount(1, $order->payments);
        foreach ($order->sellerOrders as $so) {
            $this->assertSame($so->seller_id, $so->items->first()->product->seller_id);
        }
    }

    public function test_sync_repairs_existing_unpaid_order_and_is_repeatable(): void
    {
        $buyer = $this->setupMarketplace();
        $so = app(OrderPlacement::class)->place($buyer, [$this->mug()], 'GCash', 'Test address')->sellerOrders()->firstOrFail();
        $registered = Seller::where('slug', 'registered')->firstOrFail();
        $so->update(['seller_id' => $registered->id]);
        $so->items->first()->product->update(['seller_id' => $registered->id]);
        $this->artisan('catalog:sync-stores')->assertSuccessful();
        $this->artisan('catalog:sync-stores')->assertSuccessful();
        $this->assertSame('Hearth & Home', $so->fresh()->seller->name);
        $this->assertSame($so->fresh()->seller_id, $so->items->first()->product->fresh()->seller_id);
        $this->assertDatabaseCount('sellers', 11);
        $this->assertDatabaseCount('seller_orders', 1);
    }

    public function test_cancelling_one_store_keeps_other_store_payable_for_its_amount(): void
    {
        $buyer = $this->setupMarketplace();
        $order = app(OrderPlacement::class)->place($buyer, [$this->mug(),
            ['id' => 3, 'name' => 'Wireless Earphones', 'price' => 890, 'quantity' => 1],
        ], 'GCash', 'Test address');
        $cancelled = $order->sellerOrders->first();
        $remaining = $order->sellerOrders->last();
        $this->actingAs($buyer)->post(route('orders.cancel', $cancelled->id), ['reason' => 'I changed my mind'])->assertSessionHasNoErrors();
        $this->post(route('orders.pay', $order->reference))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id, 'status' => 'paid',
            'amount_minor' => $remaining->subtotal_minor + $remaining->shipping_fee_minor,
        ]);
        $this->assertSame('cancelled', $cancelled->fresh()->status);
        $this->assertSame('pending', $remaining->fresh()->status);
    }
}
