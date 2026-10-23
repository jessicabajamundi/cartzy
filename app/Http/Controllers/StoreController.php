<?php

namespace App\Http\Controllers;

use App\Models\{Category, Product, Review, Seller, Wishlist};
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public const SORTS = [
        'latest' => 'Newest',
        'popular' => 'Best selling',
        'rating' => 'Top rated',
        'price_asc' => 'Price: low to high',
        'price_desc' => 'Price: high to low',
    ];

    private function publicSeller(string $slug): Seller
    {
        return Seller::publiclyVisible()->with(['user', 'pickupAddress'])->where('slug', $slug)->firstOrFail();
    }

    /** Products a buyer can see and order: active, with at least one active variant. */
    private function catalog(Seller $seller)
    {
        return Product::where('seller_id', $seller->id)->where('is_active', true)
            ->whereHas('variants', fn ($q) => $q->where('is_active', true));
    }

    public function show(Request $request, string $slug)
    {
        $seller = $this->publicSeller($slug);
        $text = fn (string $key) => is_string($value = $request->query($key)) ? trim($value) : '';
        $tab = in_array($text('tab'), ['products', 'reviews', 'about'], true) ? $text('tab') : 'products';
        $sort = array_key_exists($text('sort'), self::SORTS) ? $text('sort') : 'latest';
        $search = mb_substr($text('q'), 0, 100);
        $categorySlug = $text('category');

        $soldScope = fn ($items) => $items->whereHas('sellerOrder', fn ($order) => $order->whereIn('status', ['delivered', 'completed']));
        $products = $this->catalog($seller)
            ->with(['coverImage', 'variants' => fn ($q) => $q->where('is_active', true)->orderBy('price_minor')])
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->withSum(['orderItems as sold_quantity' => $soldScope], 'quantity')
            ->withMin(['variants as min_price' => fn ($q) => $q->where('is_active', true)], 'price_minor')
            ->when($search !== '', fn ($q) => $q->where(fn ($match) => $match->where('name', 'like', '%'.addcslashes($search, '%_\\').'%')
                ->orWhere('description', 'like', '%'.addcslashes($search, '%_\\').'%')))
            ->when($categorySlug !== '', fn ($q) => $q->where(fn ($inCategory) => $inCategory
                ->whereHas('category', fn ($c) => $c->where('slug', $categorySlug))
                ->orWhereHas('categories', fn ($c) => $c->where('slug', $categorySlug))));
        match ($sort) {
            'popular' => $products->orderByDesc('sold_quantity'),
            'rating' => $products->orderByDesc('reviews_avg_rating')->orderByDesc('reviews_count'),
            'price_asc' => $products->orderBy('min_price'),
            'price_desc' => $products->orderByDesc('min_price'),
            default => $products->latest(),
        };
        $products = $products->orderByDesc('id')->paginate(20)->withQueryString()->fragment('store-products');

        $catalogIds = $this->catalog($seller)->pluck('id');
        $categories = Category::where('is_active', true)
            ->where(fn ($q) => $q->whereHas('allProducts', fn ($p) => $p->whereIn('products.id', $catalogIds))
                ->orWhereHas('products', fn ($p) => $p->whereIn('products.id', $catalogIds)))
            ->orderBy('position')->get();

        $reviewQuery = Review::whereIn('product_id', Product::withTrashed()->where('seller_id', $seller->id)->select('id'));
        $ratingBreakdown = (clone $reviewQuery)->selectRaw('rating, count(*) as total')->groupBy('rating')->pluck('total', 'rating');
        $reviewTotal = (int) $ratingBreakdown->sum();
        $stats = [
            'followers' => $seller->followers()->count(),
            'products' => $catalogIds->count(),
            'rating' => $reviewTotal ? (float) (clone $reviewQuery)->avg('rating') : null,
            'reviews' => $reviewTotal,
            'sold' => (int) \App\Models\OrderItem::whereIn('product_id', $catalogIds)
                ->whereHas('sellerOrder', fn ($order) => $order->where('seller_id', $seller->id)->whereIn('status', ['delivered', 'completed']))->sum('quantity'),
            'breakdown' => collect(range(5, 1))->mapWithKeys(fn ($star) => [$star => (int) ($ratingBreakdown[$star] ?? 0)]),
        ];

        $ratingFilter = (int) $text('rating');
        $reviews = $reviewQuery->with(['user', 'product'])
            ->when($ratingFilter >= 1 && $ratingFilter <= 5, fn ($q) => $q->where('rating', $ratingFilter))
            ->latest()->orderByDesc('id')->paginate(10, ['*'], 'reviews_page')->withQueryString()->fragment('store-reviews');

        $viewer = $request->user();
        $isFollowing = $viewer ? $seller->followers()->whereKey($viewer->id)->exists() : false;
        $canFollow = $viewer && $viewer->isBuyer() && $viewer->status === 'active' && $seller->user_id !== $viewer->id;
        $isOwner = $viewer && $seller->user_id === $viewer->id;
        $canChat = $canFollow; // same rule: active buyers who don't own the store
        $wishlistRefs = $viewer ? Wishlist::where('user_id', $viewer->id)->pluck('ref')->all() : [];

        $shopVouchers = $seller->publicVouchers();

        return view('store', compact('seller', 'products', 'categories', 'reviews', 'stats', 'tab', 'sort', 'search', 'categorySlug',
            'ratingFilter', 'isFollowing', 'canFollow', 'canChat', 'isOwner', 'wishlistRefs', 'shopVouchers'));
    }

    /** Follow or unfollow a store. Buyers only. */
    public function toggleFollow(Request $request, string $slug)
    {
        $seller = $this->publicSeller($slug);
        $user = $request->user();
        abort_unless($user->isBuyer() && $user->status === 'active' && $seller->user_id !== $user->id, 403);

        $following = $seller->followers()->whereKey($user->id)->exists();
        $following ? $seller->followers()->detach($user->id) : $seller->followers()->attach($user->id);
        $following = !$following;
        $followers = $seller->followers()->count();

        if ($request->expectsJson()) {
            return response()->json(['following' => $following, 'followers' => $followers]);
        }

        return back()->with('success', $following ? 'You are now following '.$seller->name.'.' : 'You unfollowed '.$seller->name.'.');
    }
}
