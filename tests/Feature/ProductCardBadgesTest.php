<?php

namespace Tests\Feature;

use App\Models\{Product, Voucher};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Once;
use Tests\TestCase;

class ProductCardBadgesTest extends TestCase
{
    use RefreshDatabase;

    private function freeShipping(array $attributes = []): Voucher
    {
        Once::flush();

        return Voucher::create($attributes + [
            'name' => 'Free ship', 'discount_type' => 'free_shipping', 'amount_minor' => null,
            'min_spend_minor' => 50000, 'audience' => 'public', 'per_user_limit' => 0,
        ]);
    }

    public function test_no_free_shipping_badge_without_a_fully_free_public_voucher(): void
    {
        // The seeded "Shipping Saver" is only ₱50 off shipping, so it must not count.
        $this->assertTrue(Voucher::where('name', 'Shipping Saver')->exists());
        $this->assertFalse(Product::qualifiesForFreeShipping(999999));

        $this->freeShipping(['amount_minor' => 3000]);
        $this->assertFalse(Product::qualifiesForFreeShipping(999999));

        $this->freeShipping(['audience' => 'code', 'code' => 'SHIPFREE']);
        $this->assertFalse(Product::qualifiesForFreeShipping(999999));

        $this->freeShipping(['is_active' => false]);
        $this->assertFalse(Product::qualifiesForFreeShipping(999999));

        $this->freeShipping(['ends_at' => now()->subDay()]);
        $this->assertFalse(Product::qualifiesForFreeShipping(999999));

        $this->freeShipping(['total_limit' => 5, 'used_count' => 5]);
        $this->assertFalse(Product::qualifiesForFreeShipping(999999));
    }

    public function test_free_shipping_badge_respects_min_spend(): void
    {
        $this->freeShipping();

        $this->assertTrue(Product::qualifiesForFreeShipping(50000));
        $this->assertFalse(Product::qualifiesForFreeShipping(49999));
        $this->assertFalse(Product::qualifiesForFreeShipping(null));
    }

    public function test_rating_is_zero_until_reviewed(): void
    {
        $product = new Product;
        $this->assertSame('0', $product->ratingLabel());

        $product->setAttribute('reviews_avg_rating', '4.3333');
        $this->assertSame('4.3', $product->ratingLabel());
    }
}
