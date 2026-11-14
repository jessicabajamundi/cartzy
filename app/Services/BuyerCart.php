<?php

namespace App\Services;

use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class BuyerCart
{
    private function cart(): ?Cart
    {
        $user = auth()->user();
        if (!$user?->isBuyer()) return null;

        return DB::transaction(function () use ($user) {
            $cart = Cart::firstOrCreate(['user_id' => $user->id]);
            $cart = Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            // Import the current buyer's old session cart once, without replacing saved items.
            if (request()->hasSession()) {
                if (!session()->has('database_cart_owner') && session()->has('cart')) {
                    foreach (session('cart', []) as $key => $item) {
                        $cart->items()->firstOrCreate(['cart_key' => $key], [
                            'snapshot' => $item, 'quantity' => $item['quantity'], 'selected' => true,
                        ]);
                    }
                }
                session()->put('database_cart_owner', $user->id);
            }
            return $cart;
        });
    }

    public function all(): array
    {
        $cart = $this->cart();
        if (!$cart) return [];
        $items = [];
        foreach ($cart->items()->with(['variant.product.coverImage', 'variant.product.seller'])->get() as $row) {
            $item = $row->snapshot;
            if (!$item && $row->variant) {
                $variant = $row->variant;
                $item = [
                    'id' => $variant->product_id,
                    'product_id' => $variant->product_id,
                    'name' => $variant->product?->name ?? 'Deleted product',
                    'price' => $variant->price,
                    'variation' => $variant->name,
                    'image' => $variant->resolved_image_url,
                    'store' => $variant->product?->seller?->name ?? 'Cartzy Store',
                ];
            }
            if (!$item) continue;
            if ($row->product_variant_id) $item['variant_id'] = $row->product_variant_id;
            try {
                $pricedVariant = app(ProductPricing::class)->variant($item);
                if ($pricedVariant) {
                    $item['variant_id'] = $pricedVariant->id;
                    $item['product_id'] = $pricedVariant->product_id;
                    $item['price'] = $pricedVariant->sale_price_minor / 100;
                    $item['original_price'] = $pricedVariant->price;
                    $item['stock'] = (int) $pricedVariant->stock;
                    if ($pricedVariant->product?->seller) {
                        $item['store'] = $pricedVariant->product->seller->name;
                    }
                }
            } catch (\RuntimeException $e) {
                // Keep unavailable items removable; order placement rejects them.
                $item['unavailable'] = true;
            }
            $key = $row->cart_key ?? 'variant_'.$row->product_variant_id;
            $items[$key] = array_merge($item, [
                'cart_key' => $key,
                'quantity' => $row->quantity,
                'selected' => (bool) $row->selected,
            ]);
        }
        if (request()->hasSession()) {
            session()->put('cart', $items);
        }
        return $items;
    }

    public function add(string $key, array $item): void
    {
        $cart = $this->cart();
        abort_unless($cart, 401);
        DB::transaction(function () use ($cart, $key, $item) {
            Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $row = $cart->items()->firstOrNew(['cart_key' => $key]);
            $row->quantity = ($row->exists ? $row->quantity : 0) + $item['quantity'];
            $row->snapshot = $item;
            if (!$row->exists) $row->selected = true;
            $row->save();
        });
    }

    public function quantity(string $key, int $quantity): void
    {
        $this->rowsForKeys([$key])->update(['quantity' => $quantity]);
    }

    private function rowsForKeys(array $keys)
    {
        $cart = $this->cart();
        abort_unless($cart, 401);
        return $cart->items()->where(function ($query) use ($keys) {
            $query->whereIn('cart_key', $keys);
            foreach ($keys as $key) {
                if (preg_match('/^variant_(\d+)$/', $key, $match)) {
                    $query->orWhere(fn ($legacy) => $legacy->whereNull('cart_key')->where('product_variant_id', $match[1]));
                }
            }
        });
    }

    public function remove(array $keys): void
    {
        $this->rowsForKeys($keys)->delete();
        $this->all();
    }

    public function clear(): void
    {
        $cart = $this->cart();
        abort_unless($cart, 401);
        $cart->items()->delete();
        if (request()->hasSession()) {
            session()->put('cart', []);
        }
    }

    public function toggleSelected(string $key, bool $selected): void
    {
        $this->rowsForKeys([$key])->update(['selected' => $selected]);
    }

    public function select(array $keys): void
    {
        $cart = $this->cart();
        abort_unless($cart, 401);
        DB::transaction(function () use ($cart, $keys) {
            Cart::whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $cart->items()->update(['selected' => false]);
            $this->rowsForKeys($keys)->update(['selected' => true]);
        });
    }
}
