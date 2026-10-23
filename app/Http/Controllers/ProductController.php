<?php

namespace App\Http\Controllers;

use App\Models\{Product, ProductComment, ProductCommentReaction, Review};
use Illuminate\Http\Request;

class ProductController extends Controller
{
    private function availableProduct(string|int $id): Product
    {
        return Product::where('is_active', true)
            ->whereHas('seller', fn ($q) => $q->where('status', 'approved')
                ->whereHas('user', fn ($user) => $user->where('status', 'active')))
            ->with(['seller', 'category', 'images', 'coverImage', 'variants' => fn ($q) => $q->where('is_active', true)])
            ->where(function ($q) use ($id) {
                if (is_numeric($id)) {
                    $q->where('id', (int) $id)->orWhere('slug', (string) $id);
                } else {
                    $q->where('slug', (string) $id);
                }
            })
            ->firstOrFail();
    }

    public function show(string|int $id)
    {
        $product = $this->availableProduct($id);
        $variants = $product->variants->map(function ($variant) use ($product) {
            $variant->setRelation('product', $product);
            // Option names are "Color / Size"; a bare slash belongs to the color itself (e.g. "Black/Hyper Pink").
            $parts = array_map('trim', explode(' / ', $variant->name, 2));
            return [
                'id' => $variant->id, 'name' => $variant->name,
                'color' => $variant->options['color_name'] ?? (count($parts) > 1 ? $parts[0] : $variant->name),
                'size' => $variant->options['size'] ?? ($parts[1] ?? ''),
                'price' => $variant->sale_price_minor, 'regular' => $variant->price_minor,
                'stock' => $variant->stock, 'sku' => $variant->sku,
                'image' => $variant->image_url,
                'images' => $variant->gallery_urls,
            ];
        });
        $imageUrl = fn ($path) => preg_match('~^(https?://|/)~', $path) ? $path : asset('storage/'.$path);
        $photos = $product->images->pluck('path')->map($imageUrl)
            ->merge($variants->pluck('images')->flatten()->filter())->unique()->values();
        $assignedPhotos = $variants->pluck('images')->flatten()->filter()->unique();
        $generalPhotos = $product->images->pluck('path')->map($imageUrl)->diff($assignedPhotos)->values();
        $colorPhotos = $variants->groupBy('color')->map(fn ($group) => $group->pluck('images')->flatten()->filter()->unique()->values());
        $variants = $variants->map(function ($variant) use ($colorPhotos, $generalPhotos) {
            $gallery = $colorPhotos->get($variant['color']);
            $variant['images'] = ($gallery->isNotEmpty() ? $gallery : $generalPhotos)->all();
            $variant['image'] = $variant['images'][0] ?? null;
            return $variant;
        });
        $selected = $variants->firstWhere('stock', '>', 0) ?? $variants->first();
        $comments = ProductComment::where('product_id', $id)->whereNull('parent_id')->with(['user', 'replies.user'])
            ->latest()->orderByDesc('id')->paginate(10, ['*'], 'comments_page')->withQueryString()->fragment('comments');
        $reactions = $this->reactionSummary(
            $comments->getCollection()->flatMap(fn ($comment) => [$comment->id, ...$comment->replies->modelKeys()])->all(),
            request()->user()?->id,
        );
        $canComment = $this->canComment(request()->user(), $product);
        $reviewQuery = Review::where('product_id', $id);
        $rating = (clone $reviewQuery)->avg('rating');
        $starCounts = (clone $reviewQuery)->selectRaw('rating, count(*) as total')->groupBy('rating')->pluck('total', 'rating');
        $reviews = $reviewQuery->with('user')->latest()->paginate(5, ['*'], 'reviews_page')->withQueryString()->fragment('reviews');
        $soldCount = (int) $product->orderItems()->whereHas('sellerOrder', fn ($order) => $order->whereIn('status', ['delivered', 'completed']))->sum('quantity');

        $shopVouchers = $product->seller->publicVouchers();
        // Shipping estimate from this store to the buyer's default address (Shopee/Lazada show it before checkout).
        $buyerAddress = request()->user() ? \App\Models\Address::where('user_id', request()->user()->id)->where('is_default', true)->first() : null;
        $shippingEstimate = $buyerAddress?->province
            ? \App\Services\ShippingCalculator::quote(\App\Services\ShippingCalculator::originFor($product->seller->loadMissing('pickupAddress')), $buyerAddress->province) + ['city' => $buyerAddress->city, 'province' => $buyerAddress->province]
            : null;
        $shippingFromMinor = \App\Services\ShippingCalculator::lowestFeeMinor();
        $storeFreeShippingMin = \App\Services\VoucherService::sellerFreeShippingMinSpends()[$product->seller_id] ?? null;

        return view('product', compact('shopVouchers', 'shippingEstimate', 'shippingFromMinor', 'storeFreeShippingMin', 'product', 'variants', 'photos', 'selected', 'comments', 'reviews', 'rating', 'starCounts', 'soldCount', 'reactions', 'canComment'));
    }

    /**
     * Who may take part in a product's comments: active buyers, and the product's own seller
     * (who can reply and react, but not start a new thread).
     */
    private function canComment(?\App\Models\User $user, Product $product): array
    {
        $active = $user && $user->status === 'active' && !$user->is_suspended;
        $isOwnSeller = $active && $user->isSeller() && $product->seller->user_id === $user->id;

        return ['post' => $active && $user->isBuyer(), 'reply' => $active && ($user->isBuyer() || $isOwnSeller), 'isSeller' => $isOwnSeller];
    }

    /** Reaction counts per comment, plus the viewer's own reaction. */
    private function reactionSummary(array $commentIds, ?int $viewerId): array
    {
        $summary = array_fill_keys($commentIds, ['counts' => [], 'total' => 0, 'mine' => null]);
        if (!$commentIds) return $summary;
        foreach (ProductCommentReaction::whereIn('product_comment_id', $commentIds)->selectRaw('product_comment_id, type, count(*) as total')->groupBy('product_comment_id', 'type')->get() as $row) {
            $summary[$row->product_comment_id]['counts'][$row->type] = (int) $row->total;
            $summary[$row->product_comment_id]['total'] += (int) $row->total;
        }
        if ($viewerId) {
            foreach (ProductCommentReaction::whereIn('product_comment_id', $commentIds)->where('user_id', $viewerId)->get() as $mine) {
                $summary[$mine->product_comment_id]['mine'] = $mine->type;
            }
        }
        foreach ($summary as &$item) arsort($item['counts']);

        return $summary;
    }

    public function comment(Request $request, int $id)
    {
        $product = $this->availableProduct($id);
        $data = $request->validate(['body' => 'required|string|max:2000', 'parent_id' => 'nullable|integer']);
        $permissions = $this->canComment($request->user(), $product);
        abort_unless(isset($data['parent_id']) ? $permissions['reply'] : $permissions['post'], 403);

        $parentId = null;
        if (isset($data['parent_id'])) {
            $parent = ProductComment::where('product_id', $product->id)->findOrFail($data['parent_id']);
            $parentId = $parent->parent_id ?? $parent->id; // replying to a reply stays in the same thread
        }
        $comment = ProductComment::create(['product_id' => $product->id, 'user_id' => $request->user()->id, 'parent_id' => $parentId, 'body' => $data['body']]);

        return redirect()->to(route('products.show', $id).'#comment-'.($parentId ?? $comment->id))
            ->with('success', $parentId ? 'Your reply has been posted.' : 'Your comment has been posted.');
    }

    /** Toggle the viewer's reaction on a comment. Picking the same reaction again removes it. */
    public function react(Request $request, int $commentId)
    {
        $comment = ProductComment::findOrFail($commentId);
        $product = $this->availableProduct($comment->product_id);
        abort_unless($this->canComment($request->user(), $product)['reply'], 403);
        $data = $request->validate(['type' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(ProductComment::REACTIONS))]]);

        $existing = ProductCommentReaction::where('product_comment_id', $comment->id)->where('user_id', $request->user()->id)->first();
        if ($existing?->type === $data['type']) {
            $existing->delete();
        } else {
            ProductCommentReaction::updateOrCreate(['product_comment_id' => $comment->id, 'user_id' => $request->user()->id], ['type' => $data['type']]);
        }

        $summary = $this->reactionSummary([$comment->id], $request->user()->id)[$comment->id];
        if ($request->expectsJson()) {
            return response()->json($summary + ['emoji' => ProductComment::REACTIONS]);
        }

        return redirect()->to(route('products.show', $product->id).'#comment-'.($comment->parent_id ?? $comment->id));
    }
}
