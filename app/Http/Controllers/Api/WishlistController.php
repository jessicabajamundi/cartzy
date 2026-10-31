<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WishlistController extends Controller
{
    /**
     * Get all wishlist items for the authenticated buyer.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $items = Wishlist::where('user_id', $userId)
            ->latest()
            ->get()
            ->map(function (Wishlist $w) {
                $productId = is_numeric($w->ref) ? (int) $w->ref : null;
                $product = $productId ? Product::find($productId) : null;

                $inStock = true;
                if ($product) {
                    $inStock = $product->stock > 0 && $product->status === 'published';
                }

                return [
                    'id'          => $w->id,
                    'ref'         => (string) $w->ref,
                    'product_id'  => $productId,
                    'name'        => $w->name,
                    'price'       => round($w->price_minor / 100, 2),
                    'image'       => $this->normalizeImageUrl($w->image),
                    'variation'   => $w->variation ?: 'Standard',
                    'in_stock'    => $inStock,
                    'rating'      => $product ? round((float) $product->rating, 1) : 5.0,
                    'sold_count'  => $product ? (int) $product->sold_count : 0,
                    'seller_name' => $product?->seller?->store_name ?? 'Cartzy Seller',
                    'created_at'  => $w->created_at ? $w->created_at->toIso8601String() : null,
                ];
            });

        return response()->json([
            'success' => true,
            'count'   => $items->count(),
            'refs'    => $items->pluck('ref')->values()->all(),
            'items'   => $items->values()->all(),
        ]);
    }

    /**
     * Get array of all wishlisted product references for fast heart toggle checking.
     */
    public function refs(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $refs = Wishlist::where('user_id', $userId)->pluck('ref')->map(fn ($r) => (string) $r)->all();

        return response()->json([
            'success' => true,
            'count'   => count($refs),
            'refs'    => $refs,
        ]);
    }

    /**
     * Toggle a product in the wishlist (add if missing, remove if present).
     */
    public function toggle(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ref'       => ['required', 'string', 'max:100'],
            'name'      => ['required', 'string', 'max:255'],
            'price'     => ['required', 'numeric', 'min:0'],
            'image'     => ['nullable', 'string', 'max:500'],
            'variation' => ['nullable', 'string', 'max:255'],
        ]);

        $userId = $request->user()->id;
        $ref = (string) $data['ref'];

        $existing = Wishlist::where('user_id', $userId)->where('ref', $ref)->first();

        if ($existing) {
            $existing->delete();
            $wished = false;
        } else {
            Wishlist::create([
                'user_id'     => $userId,
                'ref'         => $ref,
                'name'        => $data['name'],
                'price_minor' => (int) round($data['price'] * 100),
                'image'       => $data['image'] ?? null,
                'variation'   => $data['variation'] ?? 'Standard',
            ]);
            $wished = true;
        }

        $allRefs = Wishlist::where('user_id', $userId)->pluck('ref')->map(fn ($r) => (string) $r)->all();

        return response()->json([
            'success' => true,
            'wished'  => $wished,
            'count'   => count($allRefs),
            'refs'    => $allRefs,
        ]);
    }

    /**
     * Remove an item from the wishlist by reference.
     */
    public function destroy(Request $request, string $ref): JsonResponse
    {
        $userId = $request->user()->id;

        Wishlist::where('user_id', $userId)->where('ref', $ref)->delete();

        $allRefs = Wishlist::where('user_id', $userId)->pluck('ref')->map(fn ($r) => (string) $r)->all();

        return response()->json([
            'success' => true,
            'count'   => count($allRefs),
            'refs'    => $allRefs,
        ]);
    }

    /**
     * Format/normalize image URL.
     */
    private function normalizeImageUrl(?string $path): string
    {
        if (!$path) return '';
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return Storage::disk('public')->url($path);
    }
}
