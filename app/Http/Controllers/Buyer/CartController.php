<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Display the Shopping Cart page.
     */
    public function index()
    {
        $cart = app(\App\Services\BuyerCart::class)->all();
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }

        $shippingFee = count($cart) > 0 ? 60.00 : 0.00;
        $discount = (count($cart) > 0 && $subtotal >= 1000) ? 50.00 : 0.00;
        $total = max(0, $subtotal + $shippingFee - $discount);

        return view('buyer.cart', compact('cart', 'subtotal', 'shippingFee', 'discount', 'total'));
    }

    /**
     * Add an item to the buyer's saved cart.
     */
    public function add(Request $request)
    {
        if (!auth()->check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'requires_auth' => true,
                    'redirect' => route('register', ['from' => 'cart']),
                    'message' => 'Please create an account to add items to your cart.',
                ], 401);
            }
            return redirect()->route('register', ['from' => 'cart'])->with('info', 'Please create an account or sign up to add items to your cart.');
        }

        $validated = $request->validate([
            'id'        => 'required',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'name'      => 'required|string',
            'price'     => 'required|numeric|min:0',
            'image'     => 'nullable|string',
            'variation' => 'nullable|string',
            'quantity'  => 'nullable|integer|min:1',
        ]);

        $id = (string) $validated['id'];
        $name = $validated['name'];
        $price = (float) $validated['price'];
        $image = $validated['image'] ?? 'https://images.unsplash.com/photo-1546868871-7041f2a55e12?w=500&h=500&fit=crop&q=80';
        $variation = $validated['variation'] ?? 'Standard';
        $quantity = (int) ($validated['quantity'] ?? 1);

        try {
            $variant = app(\App\Services\ProductPricing::class)->variant($validated);
        } catch (\RuntimeException $e) {
            throw \Illuminate\Validation\ValidationException::withMessages(['variant_id' => $e->getMessage()]);
        }
        if ($variant) {
            $name = $variant->product->name;
            $price = $variant->sale_price_minor / 100;
            $variation = $variant->name;
            $image = $variant->resolved_image_url;
        }
        $cart = app(\App\Services\BuyerCart::class)->all();

        // Key by product ID + variation
        $cartKey = $variant ? 'variant_'.$variant->id : $id . '_' . md5($variation);

        if ($variant && $quantity + (int) ($cart[$cartKey]['quantity'] ?? 0) > $variant->stock) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => 'Only '.$variant->stock.' item(s) are available for this option, including any already in your cart.',
            ]);
        }

        app(\App\Services\BuyerCart::class)->add($cartKey, [
                'id'        => $id,
                'cart_key'  => $cartKey,
                'variant_id' => $variant?->id,
                'name'      => $name,
                'price'     => $price,
                'image'     => $image,
                'variation' => $variation,
                'quantity'  => $quantity,
            ]);
        $cart = app(\App\Services\BuyerCart::class)->all();

        $totalCount = count($cart);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'    => true,
                'message'    => '✓ "' . $name . '" added to your cart!',
                'cartCount'  => $totalCount,
                'cart'       => array_values($cart),
            ]);
        }

        return redirect()->back()->with('success', 'Added to cart successfully!');
    }

    /**
     * Update item quantity in the cart.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'cart_key' => 'required|string',
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        $cart = app(\App\Services\BuyerCart::class)->all();
        $key = $validated['cart_key'];

        if (isset($cart[$key])) {
            app(\App\Services\BuyerCart::class)->quantity($key, (int) $validated['quantity']);
            $cart = app(\App\Services\BuyerCart::class)->all();
        }

        $totalCount = count($cart);
        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += ($item['price'] * $item['quantity']);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'cartCount' => $totalCount,
                'subtotal'  => $subtotal,
                'cart'      => array_values($cart),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Cart updated successfully.');
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request)
    {
        $validated = $request->validate([
            'cart_key' => 'required|string',
        ]);

        $cart = app(\App\Services\BuyerCart::class)->all();
        $key = $validated['cart_key'];

        if (isset($cart[$key])) {
            app(\App\Services\BuyerCart::class)->remove([$key]);
            $cart = app(\App\Services\BuyerCart::class)->all();
        }

        $totalCount = count($cart);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Item removed from cart.',
                'cartCount' => $totalCount,
                'cart'      => array_values($cart),
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Item removed from cart.');
    }

    /**
     * Clear all items in cart.
     */
    public function clear(Request $request)
    {
        app(\App\Services\BuyerCart::class)->clear();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success'   => true,
                'message'   => 'Cart has been cleared.',
                'cartCount' => 0,
                'cart'      => [],
            ]);
        }

        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }

    public function selection(Request $request)
    {
        $data = $request->validate([
            'selected_items' => ['present', 'array'],
            'selected_items.*' => ['required', 'string', 'distinct'],
        ]);
        app(\App\Services\BuyerCart::class)->select($data['selected_items']);
        return response()->json(['success' => true]);
    }
}
