<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\StoreController as WebStoreController;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Official store page for the mobile app: the same products, ratings and totals
 * as the website store page, all read from the database.
 */
class StoreController extends Controller
{
    public function __construct(private WebStoreController $store, private CatalogController $catalog) {}

    /** The app may know a store by its slug, its id, or only its name. */
    private function seller(string $identifier): Seller
    {
        $sellers = Seller::publiclyVisible()->with(['user', 'pickupAddress']);
        $seller = (clone $sellers)->where('slug', $identifier)->first()
            ?? (ctype_digit($identifier) ? (clone $sellers)->find((int) $identifier) : null)
            ?? (clone $sellers)->get()->first(fn (Seller $s) => Str::slug($s->name) === $identifier);
        abort_unless($seller, 404, 'Store not found.');

        return $seller;
    }

    public function show(Request $request, string $identifier)
    {
        $seller = $this->seller($identifier);
        $text = fn (string $key) => is_string($value = $request->query($key)) ? trim($value) : '';
        $sort = array_key_exists($text('sort'), WebStoreController::SORTS) ? $text('sort') : 'latest';
        $ratingFilter = (int) $text('rating');

        $products = $this->store->productQuery($seller, mb_substr($text('q'), 0, 100), $text('category'), $sort)
            ->with(['seller', 'category'])
            ->orderByDesc('id')->limit(50)->get();

        $reviews = $this->store->reviewQuery($seller)->with(['user', 'product' => fn ($q) => $q->withTrashed()])
            ->when($ratingFilter >= 1 && $ratingFilter <= 5, fn ($q) => $q->where('rating', $ratingFilter))
            ->latest()->orderByDesc('id')->limit(20)->get();

        $viewer = $request->user('sanctum');
        $location = $seller->pickupAddress ? $seller->pickupAddress->city.', '.$seller->pickupAddress->province : null;

        return response()->json([
            'seller' => [
                'id' => $seller->id,
                'slug' => $seller->slug,
                'name' => $seller->name,
                'logo_url' => $seller->logoUrl(),
                'banner_url' => $seller->bannerUrl(),
                'description' => $seller->description,
                'location' => $location,
                'joined' => $seller->created_at?->format('F Y'),
                'joined_ago' => $seller->created_at?->diffForHumans(),
            ],
            'stats' => $this->store->stats($seller),
            'categories' => $this->store->categories($seller)
                ->map(fn ($category) => ['name' => $category->name, 'slug' => $category->slug])->values(),
            'vouchers' => $seller->publicVouchers()
                ->map(fn ($voucher) => ['code' => $voucher->code, 'headline' => $voucher->headline(), 'terms' => $voucher->terms()])->values(),
            'products' => $products->map(fn ($product) => $this->catalog->productJson($product))->values(),
            'reviews' => $reviews->map(fn ($review) => [
                'id' => $review->id,
                'user_name' => $review->user?->name ?? 'Buyer',
                'rating' => (int) $review->rating,
                'comment' => $review->comment,
                'product_name' => $review->product?->name,
                'created_at' => $review->created_at?->format('M j, Y'),
            ])->values(),
            'is_following' => $viewer ? $seller->followers()->whereKey($viewer->id)->exists() : false,
        ]);
    }

    /** Follow or unfollow a store. Same rule as the website: active buyers, not their own store. */
    public function toggleFollow(Request $request, string $identifier)
    {
        $seller = $this->seller($identifier);
        $user = $request->user();
        abort_unless($user->status === 'active' && $seller->user_id !== $user->id, 403);

        $following = $seller->followers()->whereKey($user->id)->exists();
        $following ? $seller->followers()->detach($user->id) : $seller->followers()->attach($user->id);

        return response()->json([
            'is_following' => !$following,
            'followers' => $seller->followers()->count(),
        ]);
    }
}
