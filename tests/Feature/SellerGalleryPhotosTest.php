<?php

namespace Tests\Feature;

use App\Models\{Category, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerGalleryPhotosTest extends TestCase
{
    use RefreshDatabase;

    private function submitPhotos(array $sizes)
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        Seller::create(['user_id' => $user->id, 'name' => 'Photo Store', 'slug' => 'photo-store', 'status' => 'approved']);
        $category = Category::firstOrCreate(['slug' => 'shoes'], ['name' => 'Shoes', 'is_active' => true]);

        return $this->actingAs($user)->post(route('seller.products.add'), [
            'name' => 'Gallery product', 'category_id' => $category->id,
            'price' => 100, 'stock' => 1,
            'images' => collect($sizes)->map(fn ($size, $i) => UploadedFile::fake()->create("photo-$i.jpg", $size, 'image/jpeg'))->all(),
        ]);
    }

    public function test_more_than_ten_gallery_photos_are_all_saved(): void
    {
        $this->submitPhotos(array_fill(0, 12, 100))->assertSessionHasNoErrors();
        $product = \App\Models\Product::where('name', 'Gallery product')->firstOrFail();
        $this->assertCount(12, $product->images);
        $this->assertSame(range(0, 11), $product->images->pluck('position')->sort()->values()->all());
        $this->assertCount(12, Storage::disk('public')->files('products'));
    }

    public function test_gallery_accepts_exactly_ten_mb(): void
    {
        $this->submitPhotos([5120, 5120])->assertSessionHasNoErrors();
        $this->assertCount(2, Storage::disk('public')->files('products'));
    }

    public function test_gallery_rejects_combined_size_above_ten_mb(): void
    {
        $this->submitPhotos([5120, 5121])->assertSessionHasErrors('images');
        $this->assertDatabaseMissing('products', ['name' => 'Gallery product']);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
