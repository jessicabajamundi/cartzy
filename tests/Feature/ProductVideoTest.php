<?php

namespace Tests\Feature;

use App\Models\{Category, Product, Seller, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductVideoTest extends TestCase
{
    use RefreshDatabase;

    private Seller $shop;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $user = User::factory()->create(['role' => 'seller', 'status' => 'active']);
        $this->shop = Seller::create(['user_id' => $user->id, 'name' => 'Video shop', 'slug' => 'video-shop', 'status' => 'approved']);
        $this->category = Category::firstOrCreate(['slug' => 'video'], ['name' => 'Video', 'is_active' => true]);
        $this->actingAs($user);
    }

    private function video(int $kilobytes = 1024, string $name = 'promo.mp4', string $type = 'video/mp4'): UploadedFile
    {
        return UploadedFile::fake()->create($name, $kilobytes, $type);
    }

    private function photo(int $kilobytes): UploadedFile
    {
        return UploadedFile::fake()->create('photo.jpg', $kilobytes, 'image/jpeg');
    }

    private function add(array $files)
    {
        return $this->post(route('seller.products.add'), ['name' => 'Promo product', 'category_id' => $this->category->id, 'price' => 100, 'stock' => 1] + $files);
    }

    /** @return array{0: Product, 1: array} */
    private function existing(?string $videoPath = null): array
    {
        $product = $this->shop->products()->create(['name' => 'Saved', 'slug' => 'saved', 'category_id' => $this->category->id, 'is_active' => true, 'approval_status' => Product::APPROVAL_APPROVED, 'video_path' => $videoPath]);

        return [$product, ['name' => 'Saved', 'category_id' => $this->category->id, 'low_stock_threshold' => 5, 'is_active' => 1]];
    }

    public function test_a_new_product_saves_its_video_with_photos_at_exactly_thirty_mb(): void
    {
        $this->add(['video' => $this->video(20 * 1024), 'images' => [$this->photo(10 * 1024)]])->assertSessionHasNoErrors();

        $product = Product::where('name', 'Promo product')->firstOrFail();
        Storage::disk('public')->assertExists($product->video_path);
        $this->assertStringStartsWith('products/videos/', $product->video_path);
        $this->assertCount(1, $product->images);
        $this->assertSame(Product::APPROVAL_PENDING, $product->approval_status);
    }

    public function test_photos_and_video_together_cannot_pass_thirty_mb(): void
    {
        $this->add(['video' => $this->video(20 * 1024), 'images' => [$this->photo(10 * 1024 + 1)]])->assertSessionHasErrors('images');

        $this->assertDatabaseMissing('products', ['name' => 'Promo product']);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_only_mp4_and_webm_videos_up_to_thirty_mb_are_accepted(): void
    {
        $this->add(['video' => $this->video(100, 'promo.avi', 'video/x-msvideo')])->assertSessionHasErrors('video');
        $this->add(['video' => $this->video(100, 'fake.pdf', 'application/pdf')])->assertSessionHasErrors('video');
        $this->add(['video' => $this->video(30 * 1024 + 1)])->assertSessionHasErrors('video');
        $this->assertDatabaseMissing('products', ['name' => 'Promo product']);

        $this->add(['video' => $this->video(30 * 1024, 'promo.webm', 'video/webm')])->assertSessionHasNoErrors();
    }

    public function test_editing_adds_replaces_and_removes_the_video(): void
    {
        [$product, $data] = $this->existing();

        $this->post(route('seller.products.details', $product), $data + ['video' => $this->video()])->assertSessionHasNoErrors();
        $first = $product->fresh()->video_path;
        Storage::disk('public')->assertExists($first);

        $this->post(route('seller.products.details', $product), $data + ['video' => $this->video()])->assertSessionHasNoErrors();
        $second = $product->fresh()->video_path;
        $this->assertNotSame($first, $second);
        Storage::disk('public')->assertMissing($first);

        $this->post(route('seller.products.details', $product), $data)->assertSessionHasNoErrors();
        $this->assertSame($second, $product->fresh()->video_path);

        $this->post(route('seller.products.details', $product), $data + ['remove_video' => 1])->assertSessionHasNoErrors();
        $this->assertNull($product->fresh()->video_path);
        Storage::disk('public')->assertMissing($second);
    }

    public function test_a_saved_video_counts_toward_the_total_unless_it_is_being_replaced(): void
    {
        Storage::disk('public')->put('products/videos/old.mp4', str_repeat('x', 25 * 1024 * 1024));
        [$product, $data] = $this->existing('products/videos/old.mp4');

        $this->post(route('seller.products.details', $product), $data + ['new_photos' => [$this->photo(5 * 1024 + 1)]])->assertSessionHasErrors('photos');
        $this->assertCount(0, $product->fresh()->images);
        Storage::disk('public')->assertExists('products/videos/old.mp4');

        $this->post(route('seller.products.details', $product), $data + ['remove_video' => 1, 'new_photos' => [$this->photo(5 * 1024 + 1)]])->assertSessionHasNoErrors();
        $this->assertCount(1, $product->fresh()->images);
    }

    public function test_buyers_and_admins_can_watch_the_video_and_sellers_see_the_field(): void
    {
        [$product] = $this->existing('products/videos/promo.mp4');
        $url = Storage::disk('public')->url('products/videos/promo.mp4');

        $this->get(route('seller.products.edit', $product))->assertOk()->assertSee('data-product-video', false)->assertSee($url, false);
        $this->get(route('seller.products', ['create' => 1]))->assertOk()->assertSee('name="video"', false)->assertSee('/ 30 MB');

        $this->get(route('products.show', $product->id))->assertOk()->assertSee('class="pd-video"', false)->assertSee($url, false);

        $this->actingAs(User::factory()->create(['role' => 'admin', 'status' => 'active']))
            ->get(route('admin.products'))->assertOk()->assertSee('details-video', false)->assertSee(str_replace('/', '\/', $url), false);
    }

    public function test_a_product_without_a_video_shows_no_player(): void
    {
        [$product] = $this->existing();

        $this->get(route('products.show', $product->id))->assertOk()->assertDontSee('class="pd-video"', false);
    }
}
