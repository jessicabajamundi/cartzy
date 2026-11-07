<?php

namespace Tests\Feature;

use App\Models\{Category, Product, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiFlashSaleTest extends TestCase
{
    use RefreshDatabase;

    private function product(string $name, array $sale = [], int $stock = 5): Product
    {
        $owner = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $seller = Seller::create(['user_id' => $owner->id, 'name' => $name.' Shop', 'slug' => Str::slug($name).'-shop', 'status' => 'approved']);
        $category = Category::firstOrCreate(['slug' => 'general'], ['name' => 'General', 'is_active' => true]);
        $product = Product::create($sale + ['seller_id' => $seller->id, 'category_id' => $category->id, 'name' => $name, 'slug' => Str::slug($name), 'is_active' => true]);
        $product->variants()->create(['name' => 'Default', 'sku' => strtoupper(Str::random(8)), 'price_minor' => 100000, 'stock' => $stock, 'is_active' => true]);

        return $product;
    }

    private function sale(array $dates = []): array
    {
        return $dates + ['discount_type' => 'percentage', 'discount_value' => 10];
    }

    public function test_only_running_in_stock_sales_are_flash_deals(): void
    {
        $this->product('Running', $this->sale(['discount_starts_at' => now()->subDay(), 'discount_ends_at' => now()->addDay()]));
        $this->product('No end date', $this->sale());
        $this->product('Regular price');
        $this->product('Already ended', $this->sale(['discount_starts_at' => now()->subDays(3), 'discount_ends_at' => now()->subMinute()]));
        $this->product('Not started', $this->sale(['discount_starts_at' => now()->addHour(), 'discount_ends_at' => now()->addDay()]));
        $this->product('Sold out', $this->sale(['discount_ends_at' => now()->addDay()]), 0);

        $names = collect($this->getJson('/api/products?on_sale=1')->assertOk()->json('products'))->pluck('name')->sort()->values()->all();

        $this->assertSame(['No end date', 'Running'], $names);
        $this->assertCount(6, $this->getJson('/api/products')->json('products'));
    }

    public function test_sale_end_time_is_the_real_moment_the_sale_ends(): void
    {
        $this->travelTo(now()->startOfSecond());
        $end = now()->addHours(5)->startOfMinute();
        $this->product('Running', $this->sale(['discount_ends_at' => $end]));

        $sent = $this->getJson('/api/products?on_sale=1')->json('products.0.sale_ends_at');

        // Includes the timezone, so a phone anywhere counts down to the same moment.
        $this->assertTrue($end->equalTo(\Carbon\Carbon::parse($sent)));
        $this->assertStringContainsString('+00:00', $sent);
    }

    public function test_products_not_on_a_running_sale_have_no_end_time(): void
    {
        $this->product('No end date', $this->sale());
        $this->product('Regular price');
        $this->product('Already ended', $this->sale(['discount_ends_at' => now()->subMinute()]));

        foreach ($this->getJson('/api/products')->json('products') as $product) {
            $this->assertNull($product['sale_ends_at'], $product['name']);
        }
    }
}
