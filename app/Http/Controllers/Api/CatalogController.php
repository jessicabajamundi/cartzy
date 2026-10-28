<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Public catalog for the mobile app. Shows exactly what the website storefront shows.
 */
class CatalogController extends Controller
{
    /** Same visibility rule as the website product page. */
    private function available(): Builder
    {
        return Product::where('is_active', true)
            ->where('approval_status', Product::APPROVAL_APPROVED)
            ->whereHas('seller', fn ($q) => $q->where('status', 'approved')
                ->whereHas('user', fn ($user) => $user->where('status', 'active')));
    }

    private function imageUrl(?string $path): ?string
    {
        if (!$path) return null;

        return preg_match('~^(https?://|/)~', $path) ? $path : \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }

    private function productJson(Product $product, bool $full = false): array
    {
        $variants = $product->variants;
        // sale_price_minor reads $variant->product; hand it the loaded parent
        // instead of re-querying it for every variant (N+1 against a remote DB).
        $variants->each->setRelation('product', $product);
        $cheapest = $variants->sortBy(fn ($variant) => $variant->sale_price_minor)->first();
        $json = [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category ? ['id' => $product->category->id, 'name' => $product->category->name, 'slug' => $product->category->slug] : null,
            'seller' => ['id' => $product->seller->id, 'name' => $product->seller->name],
            'price' => $cheapest ? $cheapest->sale_price_minor / 100 : null,
            'regular_price' => $cheapest ? $cheapest->price_minor / 100 : null,
            'on_sale' => (bool) $product->sale_is_active,
            'badge' => $product->badge ?: null,
            'image' => $this->imageUrl($product->coverImage?->path) ?? $variants->map->image_url->filter()->first(),
            'rating' => $product->reviews_avg_rating !== null ? round((float) $product->reviews_avg_rating, 1) : null,
            'reviews_count' => (int) ($product->reviews_count ?? 0),
            'sold' => (int) ($product->sold_quantity ?? 0),
            'in_stock' => $variants->sum('stock') > 0,
        ];
        if ($full) {
            $reviewQuery = $product->reviews();
            $starCounts = (clone $reviewQuery)->selectRaw('rating, count(*) as total')->groupBy('rating')->pluck('total', 'rating');
            $reviewsList = (clone $reviewQuery)->with('user')->latest()->take(20)->get()->map(fn ($r) => [
                'id' => $r->id,
                'user_name' => $r->user?->name ?? 'Buyer',
                'rating' => (int) $r->rating,
                'comment' => $r->comment,
                'seller_reply' => $r->seller_reply,
                'created_at' => $r->created_at?->format('M j, Y') ?? '',
            ])->values();

            $freeShippingMin = null;
            if (class_exists(\App\Services\VoucherService::class)) {
                $spends = \App\Services\VoucherService::sellerFreeShippingMinSpends();
                $freeShippingMin = isset($spends[$product->seller_id]) ? ($spends[$product->seller_id] / 100) : null;
            }

            $json['gender'] = $product->gender;
            $json['seller'] = [
                'id' => $product->seller->id,
                'name' => $product->seller->name,
                'slug' => $product->seller->slug,
                'logo_url' => method_exists($product->seller, 'logoUrl') ? $product->seller->logoUrl() : null,
                'free_shipping_min' => $freeShippingMin,
            ];

            $json += [
                'description' => $product->description,
                'images' => $product->images->sortBy('position')->map(fn ($image) => $this->imageUrl($image->path))->values(),
                'variants' => $variants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'name' => $variant->name,
                    'color' => $variant->options['color_name'] ?? null,
                    'size' => $variant->options['size'] ?? null,
                    'price' => $variant->sale_price_minor / 100,
                    'regular_price' => $variant->price_minor / 100,
                    'stock' => $variant->stock,
                    'sku' => $variant->sku,
                    'image' => $variant->image_url,
                ])->values(),
                'star_counts' => [
                    '5' => (int) ($starCounts[5] ?? 0),
                    '4' => (int) ($starCounts[4] ?? 0),
                    '3' => (int) ($starCounts[3] ?? 0),
                    '2' => (int) ($starCounts[2] ?? 0),
                    '1' => (int) ($starCounts[1] ?? 0),
                ],
                'reviews' => $reviewsList,
            ];
        }

        return $json;
    }

    public function categories()
    {
        return response()->json(['categories' => Category::where('is_active', true)->whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->where('is_active', true)->orderBy('position')])
            ->orderBy('position')->orderBy('name')->get()
            ->map(fn ($category) => [
                'id' => $category->id, 'name' => $category->name, 'slug' => $category->slug,
                'children' => $category->children->map(fn ($child) => ['id' => $child->id, 'name' => $child->name, 'slug' => $child->slug])->values(),
            ])]);
    }

    public function products(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:100', 'category' => 'nullable|string|max:100',
            'sort' => 'nullable|in:newest,price_asc,price_desc', 'per_page' => 'nullable|integer|min:1|max:50',
        ]);
        $activeVariants = fn ($q) => $q->where('is_active', true);
        $query = $this->available()
            ->whereHas('variants', $activeVariants)
            ->with(['seller', 'category', 'coverImage', 'variants' => $activeVariants])
            ->withAvg('reviews', 'rating')->withCount('reviews')
            ->withSum(['orderItems as sold_quantity' => fn ($items) => $items->whereHas('sellerOrder', fn ($order) => $order->whereIn('status', ['delivered', 'completed']))], 'quantity');

        if ($request->filled('q')) {
            $term = '%'.$request->q.'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('description', 'like', $term));
        }
        if ($request->filled('category')) {
            // The app sends category names (e.g. "Shoes"); slugs and ids also work.
            $category = Category::where('slug', $request->category)->orWhere('name', $request->category)->orWhere('id', $request->category)->first();
            $ids = $category ? [$category->id, ...$category->children()->pluck('id')] : [0];
            $query->where(fn ($q) => $q->whereIn('category_id', $ids)->orWhereHas('categories', fn ($c) => $c->whereIn('categories.id', $ids)));
        }
        $minPrice = '(select min(price_minor) from product_variants where product_variants.product_id = products.id and product_variants.is_active = 1)';
        match ($request->input('sort', 'newest')) {
            'price_asc' => $query->orderByRaw("$minPrice asc"),
            'price_desc' => $query->orderByRaw("$minPrice desc"),
            default => $query->latest()->orderByDesc('id'),
        };

        $page = $query->paginate((int) $request->input('per_page', 20))->withQueryString();

        return response()->json([
            'products' => $page->getCollection()->map(fn ($product) => $this->productJson($product))->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'total' => $page->total()],
        ]);
    }

    public function heroSlides()
    {
        $slides = \App\Models\HeroSlide::where('is_active', true)
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(fn ($slide) => [
                'id' => $slide->id,
                'key' => $slide->key,
                'label' => $slide->label,
                'title' => $slide->title,
                'accent' => $slide->accent,
                'description' => $slide->description,
                'cta' => $slide->cta,
                'href' => $slide->href,
                'alt' => $slide->alt,
                'image_url' => asset($slide->image_path),
                'image_path' => $slide->image_path,
                'position' => $slide->position,
            ]);

        return response()->json(['slides' => $slides]);
    }

    public function product(int $id)
    {
        $product = $this->available()
            ->with(['seller', 'category', 'coverImage', 'images', 'variants' => fn ($q) => $q->where('is_active', true)])
            ->withAvg('reviews', 'rating')->withCount('reviews')
            ->findOrFail($id);

        return response()->json(['product' => $this->productJson($product, true)]);
    }
}
