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
        $cart = Session::get('cart', []);
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
     * Add an item to the cart (Session-based).
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'id'        => 'required',
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

        $cart = Session::get('cart', []);

        // Key by product ID + variation
        $cartKey = $id . '_' . md5($variation);

        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'id'        => $id,
                'cart_key'  => $cartKey,
                'name'      => $name,
                'price'     => $price,
                'image'     => $image,
                'variation' => $variation,
                'quantity'  => $quantity,
            ];
        }

        Session::put('cart', $cart);

        $totalCount = array_sum(array_column($cart, 'quantity'));

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

        $cart = Session::get('cart', []);
        $key = $validated['cart_key'];

        if (isset($cart[$key])) {
            $cart[$key]['quantity'] = (int) $validated['quantity'];
            Session::put('cart', $cart);
        }

        $totalCount = array_sum(array_column($cart, 'quantity'));
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

        $cart = Session::get('cart', []);
        $key = $validated['cart_key'];

        if (isset($cart[$key])) {
            unset($cart[$key]);
            Session::put('cart', $cart);
        }

        $totalCount = array_sum(array_column($cart, 'quantity'));

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
        Session::forget('cart');

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
}
