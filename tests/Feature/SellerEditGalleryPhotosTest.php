<?php

namespace Tests\Feature;

use App\Models\{Category, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerEditGalleryPhotosTest extends TestCase
{
    use RefreshDatabase;

    private function gallery(): array
    {
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $shop = Seller::create(['user_id' => $user->id, 'name' => 'Gallery shop', 'slug' => 'gallery-shop', 'status' => 'approved']);
        $category = Category::firstOrCreate(['slug' => 'gallery'], ['name' => 'Gallery', 'is_active' => true]);
        $product = $shop->products()->create(['name' => 'Photos', 'slug' => 'photos', 'category_id' => $category->id, 'is_active' => true]);
        Storage::disk('public')->put('products/existing.jpg', str_repeat('x', 6 * 1024 * 1024));
        $image = $product->images()->create(['path' => 'products/existing.jpg', 'position' => 0]);
        $this->actingAs($user);

        return [$product, $image, ['name' => 'Photos', 'category_id' => $category->id, 'low_stock_threshold' => 5, 'is_active' => 1]];
    }

    public function test_existing_photos_count_toward_the_total_and_overflow_changes_nothing(): void
    {
        [$product, $image, $data] = $this->gallery();
        $this->post(route('seller.products.details', $product), $data + [
            'new_photos' => [UploadedFile::fake()->create('new.jpg', 4097, 'image/jpeg')],
        ])->assertSessionHasErrors('photos');
        $this->assertCount(1, $product->fresh()->images);
        Storage::disk('public')->assertExists($image->path);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_bulk_upload_accepts_more_than_five_photos_at_exactly_ten_mb(): void
    {
        [$product, $image, $data] = $this->gallery();
        $this->post(route('seller.products.details', $product), $data + [
            'new_photos' => collect(range(1, 8))->map(fn ($i) => UploadedFile::fake()->create("new-$i.jpg", 512, 'image/jpeg'))->all(),
        ])->assertSessionHasNoErrors();
        $this->assertCount(9, $product->fresh()->images);
    }

    public function test_replacement_excludes_the_old_photo_size(): void
    {
        [$product, $image, $data] = $this->gallery();
        $this->post(route('seller.products.details', $product), $data + [
            'photos' => [0 => UploadedFile::fake()->create('replacement.jpg', 10240, 'image/jpeg')],
        ])->assertSessionHasNoErrors();
        $this->assertCount(1, $product->fresh()->images);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_removed_photos_free_space_for_new_uploads(): void
    {
        [$product, $image, $data] = $this->gallery();
        $this->post(route('seller.products.details', $product), $data + [
            'remove_image_ids' => [$image->id],
            'photos' => [7 => UploadedFile::fake()->create('new.jpg', 10240, 'image/jpeg')],
        ])->assertSessionHasNoErrors();
        $this->assertCount(1, $product->fresh()->images);
        $this->assertSame(0, $product->fresh()->images->first()->position);
        Storage::disk('public')->assertMissing($image->path);
    }
}
