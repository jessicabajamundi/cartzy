<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BuyerCart;
use App\Services\ProductPricing;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    public function __construct(private BuyerCart $buyerCart) {}

    /**
     * Get the buyer's saved cart from the server.
     */
    public function index(Request $request)
    {
        $cart = $this->buyerCart->all();
        return response()->json($this->formatCartResponse($cart));
    }

    /**
     * Add an item to the buyer's saved cart.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id'         => 'required',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'product_id' => 'nullable|integer',
            'name'       => 'required|string',
            'price'      => 'required|numeric|min:0',
            'image'      => 'nullable|string',
            'variation'  => 'nullable|string',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        $id = (string) $validated['id'];
        $name = $validated['name'];
        $price = (float) $validated['price'];
        $image = $validated['image'] ?? '';
        $variation = $validated['variation'] ?? 'Standard';
        $quantity = (int) ($validated['quantity'] ?? 1);

        try {
            $variant = app(ProductPricing::class)->variant($validated);
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['variant_id' => $e->getMessage()]);
        }

        if ($variant) {
            $name = $variant->product->name;
            $price = $variant->sale_price_minor / 100;
            $variation = $variant->name;
            $image = $variant->resolved_image_url;
        }

        $cart = $this->buyerCart->all();
        $cartKey = $variant ? 'variant_'.$variant->id : $id . '_' . md5($variation);

        if ($variant && $quantity + (int) ($cart[$cartKey]['quantity'] ?? 0) > $variant->stock) {
            throw ValidationException::withMessages([
                'quantity' => 'Only '.$variant->stock.' item(s) are available for this option.',
            ]);
        }

        $this->buyerCart->add($cartKey, [
            'id'         => $id,
            'cart_key'   => $cartKey,
            'variant_id' => $variant?->id,
            'name'       => $name,
            'price'      => $price,
            'image'      => $image,
            'variation'  => $variation,
            'quantity'   => $quantity,
        ]);

        $updatedCart = $this->buyerCart->all();
        return response()->json(array_merge([
            'success' => true,
            'message' => 'Added to cart!',
        ], $this->formatCartResponse($updatedCart)));
    }

    /**
     * Update quantity of a cart item.
     */
    public function update(Request $request, string $cartKey)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:1000000',
        ]);

        $cart = $this->buyerCart->all();
        $stock = $cart[$cartKey]['stock'] ?? null;
        if ($stock !== null && (int) $validated['quantity'] > $stock) {
            throw ValidationException::withMessages([
                'quantity' => $stock > 0
                    ? 'Only '.$stock.' item(s) are available for this option.'
                    : 'This option is out of stock.',
            ]);
        }

        $this->buyerCart->quantity($cartKey, (int) $validated['quantity']);
        $updatedCart = $this->buyerCart->all();

        return response()->json(array_merge([
            'success' => true,
        ], $this->formatCartResponse($updatedCart)));
    }

    /**
     * Toggle selection of an item.
     */
    public function toggleSelect(Request $request, string $cartKey)
    {
        $validated = $request->validate([
            'selected' => 'required|boolean',
        ]);

        $this->buyerCart->toggleSelected($cartKey, (bool) $validated['selected']);
        $updatedCart = $this->buyerCart->all();

        return response()->json(array_merge([
            'success' => true,
        ], $this->formatCartResponse($updatedCart)));
    }

    /**
     * Update full selection of items.
     */
    public function selection(Request $request)
    {
        $validated = $request->validate([
            'selected_items' => 'required|array',
            'selected_items.*' => 'string',
        ]);

        $this->buyerCart->select($validated['selected_items']);
        $updatedCart = $this->buyerCart->all();

        return response()->json(array_merge([
            'success' => true,
        ], $this->formatCartResponse($updatedCart)));
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(string $cartKey)
    {
        $this->buyerCart->remove([$cartKey]);
        $updatedCart = $this->buyerCart->all();

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Item removed from cart.',
        ], $this->formatCartResponse($updatedCart)));
    }

    /**
     * Clear all items in the cart.
     */
    public function clear()
    {
        $this->buyerCart->clear();

        return response()->json([
            'success' => true,
            'items' => [],
            'count' => 0,
            'total_units' => 0,
            'selected_total' => 0.0,
            'message' => 'Cart cleared.',
        ]);
    }

    /**
     * Sync / merge local mobile cart items into database cart.
     */
    public function sync(Request $request)
    {
        $localItems = $request->input('items', []);
        if (is_array($localItems) && count($localItems) > 0) {
            foreach ($localItems as $local) {
                if (empty($local['id']) && empty($local['productId']) && empty($local['variant_id'])) {
                    continue;
                }
                $varId = $local['variantId'] ?? ($local['variant_id'] ?? null);
                $pId = $local['productId'] ?? ($local['id'] ?? null);
                $variation = $local['variation'] ?? 'Standard';
                $cartKey = $varId ? 'variant_'.$varId : $pId . '_' . md5($variation);

                try {
                    $this->buyerCart->add($cartKey, [
                        'id'         => (string) $pId,
                        'cart_key'   => $cartKey,
                        'variant_id' => $varId ? (int) $varId : null,
                        'name'       => $local['name'] ?? 'Product',
                        'price'      => (float) ($local['price'] ?? 0),
                        'image'      => $local['image'] ?? '',
                        'variation'  => $variation,
                        'quantity'   => max(1, (int) ($local['quantity'] ?? 1)),
                    ]);
                } catch (\Throwable $e) {
                    // Skip invalid legacy items
                }
            }
        }

        $cart = $this->buyerCart->all();
        return response()->json($this->formatCartResponse($cart));
    }

    /**
     * Format cart response into a consistent list for mobile JSON.
     */
    private function formatCartResponse(array $cart): array
    {
        $items = [];
        $selectedTotal = 0.0;
        $totalUnits = 0;

        foreach ($cart as $key => $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $price = (float) ($item['price'] ?? 0);
            $selected = (bool) ($item['selected'] ?? true);
            $isOutOfStock = isset($item['stock']) && $item['stock'] <= 0;

            if ($selected && !$isOutOfStock) {
                $selectedTotal += ($price * $qty);
            }
            $totalUnits += $qty;

            $items[] = [
                'id'             => (string) ($item['cart_key'] ?? $key),
                'cart_key'       => (string) ($item['cart_key'] ?? $key),
                'product_id'     => isset($item['product_id']) ? (int) $item['product_id'] : (isset($item['id']) ? (int) $item['id'] : null),
                'productId'      => isset($item['product_id']) ? (int) $item['product_id'] : (isset($item['id']) ? (int) $item['id'] : null),
                'variant_id'     => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                'variantId'      => isset($item['variant_id']) ? (int) $item['variant_id'] : null,
                'name'           => (string) ($item['name'] ?? 'Product'),
                'price'          => $price,
                'original_price' => isset($item['original_price']) ? (string) $item['original_price'] : null,
                'originalPrice'  => isset($item['original_price']) ? (string) $item['original_price'] : null,
                'image'          => (string) ($item['image'] ?? ''),
                'category'       => (string) ($item['category'] ?? ''),
                'variation'      => (string) ($item['variation'] ?? 'Standard'),
                'quantity'       => $qty,
                'selected'       => $selected,
                'stock'          => isset($item['stock']) ? (int) $item['stock'] : null,
                'store'          => (string) ($item['store'] ?? 'Cartzy Store'),
            ];
        }

        return [
            'items'          => $items,
            'count'          => count($items),
            'total_units'    => $totalUnits,
            'selected_total' => $selectedTotal,
        ];
    }
}
