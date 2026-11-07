<?php

namespace App\Services;

use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;

/**
 * Ranks public stores and products by items delivered in the last 30 days. Sellers see each other's items
 * sold and ratings, which are already public on store pages, but never each other's earnings.
 */
class StoreRankings
{
    public const DAYS = 30;

    private const TOP = 10;

    private const MY_PRODUCTS = 5;

    /**
     * @return array{top: array<int, array>, mine: ?array, total: int, products: array<int, array>} "mine" is set only when
     *     the store is outside the top list; "products" are this store's best sellers with their rank across all shops.
     */
    public function forSeller(?Seller $shop): array
    {
        $sold = $this->delivered()
            ->groupBy('seller_orders.seller_id')
            ->selectRaw('seller_orders.seller_id as store_id, SUM(order_items.quantity) as items_sold')
            ->pluck('items_sold', 'store_id');

        $stores = Seller::publiclyVisible()->whereIn('id', $sold->keys())->get();
        $ratings = Review::query()
            ->join('products', 'products.id', '=', 'reviews.product_id')
            ->whereIn('products.seller_id', $stores->pluck('id'))
            ->groupBy('products.seller_id')
            ->selectRaw('products.seller_id as store_id, AVG(reviews.rating) as average_rating')
            ->pluck('average_rating', 'store_id');

        // Ties go to the better-rated store, then alphabetically, so the order never shuffles between visits.
        $ranked = $stores
            ->map(fn (Seller $store) => $this->row($store, (int) $sold[$store->id], $ratings[$store->id] ?? null, $shop))
            ->sort(fn (array $a, array $b) => [$b['sold'], $b['rating'] ?? 0, $a['name']] <=> [$a['sold'], $a['rating'] ?? 0, $b['name']])
            ->values()
            ->map(fn (array $row, int $index) => ['rank' => $index + 1] + $row);

        $mine = $ranked->firstWhere('is_mine', true);
        if (!$mine && $shop) {
            $rating = Review::whereHas('product', fn ($query) => $query->where('seller_id', $shop->id))->avg('rating');
            $mine = ['rank' => null] + $this->row($shop, 0, $rating, $shop);
        }

        return [
            'top' => $ranked->take(self::TOP)->all(),
            'mine' => $mine && ($mine['rank'] === null || $mine['rank'] > self::TOP) ? $mine : null,
            'total' => $ranked->count(),
            'products' => $this->myBestSellers($shop),
        ];
    }

    /** A rank counts every public product, so "#3 on Cartzy" means the third best seller of all shops. */
    private function myBestSellers(?Seller $shop): array
    {
        if (!$shop) {
            return [];
        }

        $sold = $this->delivered()
            ->groupBy('order_items.product_id')
            ->selectRaw('order_items.product_id as ranked_product_id, SUM(order_items.quantity) as items_sold')
            ->pluck('items_sold', 'ranked_product_id');

        $products = Product::approved()->where('is_active', true)->whereIn('id', $sold->keys())
            ->whereHas('seller', fn ($seller) => $seller->publiclyVisible())
            ->get(['id', 'seller_id', 'name'])
            ->sort(fn (Product $a, Product $b) => [(int) $sold[$b->id], $a->id] <=> [(int) $sold[$a->id], $b->id])
            ->values();

        return $products
            ->map(fn (Product $product, int $index) => [
                'rank' => $index + 1,
                'seller_id' => $product->seller_id,
                'name' => $product->name,
                'url' => route('products.show', $product->id),
                'sold' => (int) $sold[$product->id],
            ])
            ->where('seller_id', $shop->id)
            ->take(self::MY_PRODUCTS)
            ->values()
            ->all();
    }

    private function delivered()
    {
        return OrderItem::query()
            ->join('seller_orders', 'seller_orders.id', '=', 'order_items.seller_order_id')
            ->whereIn('seller_orders.status', ['delivered', 'completed'])
            ->where('seller_orders.delivered_at', '>=', now()->subDays(self::DAYS - 1)->startOfDay());
    }

    private function row(Seller $store, int $sold, mixed $rating, ?Seller $shop): array
    {
        return [
            'name' => $store->name,
            'url' => route('store.show', $store->slug),
            'logo' => $store->logoUrl(),
            'sold' => $sold,
            'rating' => $rating === null ? null : round((float) $rating, 1),
            'is_mine' => $shop && $store->id === $shop->id,
        ];
    }
}
