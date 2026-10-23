<?php

namespace Tests\Feature;

use App\Models\{Category, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerOptionPhotosTest extends TestCase
{
    use RefreshDatabase;

    private function form(Seller $shop, array $photos, array $overrides = []): array
    {
        $category = Category::firstOrCreate(['slug' => 'shoes'], ['name' => 'Shoes', 'is_active' => true]);

        return $overrides + [
            'name' => 'Runner shoes', 'category_id' => $category->id, 'primary_variant' => 1,
            'primary_color' => 'Black', 'primary_size' => '', 'sku' => 'RUN-1', 'price' => '999', 'stock' => 3,
            'has_variants' => 1,
            'variants' => [
                ['name' => 'White / 40', 'price' => '999', 'stock' => 2, 'photo_group' => 'gwhite'],
                ['name' => 'White / 41', 'price' => '999', 'stock' => 4, 'photo_group' => 'gwhite'],
            ],
            'option_photos' => ['gwhite' => $photos],
        ];
    }

    public function test_one_color_can_have_many_photos_shared_by_all_its_sizes(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $shop = Seller::create(['user_id' => $user->id, 'name' => 'Run Store', 'slug' => 'run-store', 'status' => 'approved']);
        $photos = collect(range(1, 6))->map(fn ($i) => UploadedFile::fake()->create("white-$i.jpg", 9 * 1024, 'image/jpeg'))->all();

        $this->actingAs($user)->post(route('seller.products.add'), $this->form($shop, $photos))->assertSessionHasNoErrors();

        $product = $shop->products()->where('name', 'Runner shoes')->firstOrFail();
        $white40 = $product->variants->firstWhere('name', 'White / 40');
        $white41 = $product->variants->firstWhere('name', 'White / 41');
        $this->assertCount(6, $white40->gallery, 'All six photos are kept for the color.');
        $this->assertSame($white40->gallery, $white41->gallery, 'Every size of the color shares the same photos.');
        $this->assertSame($white40->gallery[0], $white40->image_path, 'The first photo is the main one.');
        $this->assertCount(6, Storage::disk('public')->files('products/variants'), 'Each photo is stored once, not once per size.');

        // Buyers see every photo of the option on the product page.
        $page = $this->get(route('products.show', $product->id))->assertOk();
        foreach ($white40->gallery_urls as $url) {
            $page->assertSee($url, false);
        }
    }

    public function test_each_photo_must_be_an_image_up_to_10_mb(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $shop = Seller::create(['user_id' => $user->id, 'name' => 'Run Store', 'slug' => 'run-store', 'status' => 'approved']);
        $this->actingAs($user);

        $this->post(route('seller.products.add'), $this->form($shop, [UploadedFile::fake()->create('ok.jpg', 100, 'image/jpeg'), UploadedFile::fake()->create('huge.jpg', 11 * 1024, 'image/jpeg')]))
            ->assertSessionHasErrors('option_photos.gwhite.1');
        $this->post(route('seller.products.add'), $this->form($shop, [UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf')], ['sku' => 'RUN-2']))
            ->assertSessionHasErrors('option_photos.gwhite.0');
        $this->assertSame(0, $shop->products()->count());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
