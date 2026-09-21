<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    /**
     * Show Checkout Page with ID Verification Gate
     */
    public function index()
    {
        $user = Auth::user();

        // If not logged in, redirect to login
        if (!$user) {
            return redirect()->route('login')->with('info', 'Please log in to proceed to checkout.');
        }

        // =========================================================================
        // ID VERIFICATION CHECKOUT GATE
        // User MUST have verified their ID before being permitted to check out
        // =========================================================================
        if (!$user->isIdVerified()) {
            $msg = match ($user->id_status) {
                'pending' => '⏳ Your submitted ID is currently under review by our compliance team. Checkout will be unlocked as soon as it is approved!',
                'rejected' => '✕ Your ID verification was disapproved: ' . ($user->id_rejection_reason ?: 'Invalid or unclear ID.') . ' Please upload a valid ID in your profile before checking out.',
                default => '⚠️ ID Verification Required: In compliance with buyer security policies, you must verify your valid government ID before checking out.',
            };

            return redirect()->route('account.index', ['tab' => 'profile'])
                ->with('id_verification_required', true)
                ->with('warning', $msg);
        }

        // Load real cart from session
        $cartItems = array_values(session('cart', []));

        // If cart is empty, redirect to homepage
        if (empty($cartItems)) {
            return redirect()->route('cart.index')->with('info', 'Your cart is empty. Add some products before checking out!');
        }

        $subtotal = array_sum(array_map(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 1), $cartItems));
        $shippingFee = 60.00;
        $discount = $subtotal >= 1000 ? 50.00 : 0.00;
        $total = $subtotal + $shippingFee - $discount;

        return view('buyer.checkout', compact('user', 'cartItems', 'subtotal', 'shippingFee', 'discount', 'total'));
    }

    /**
     * Process Order Placement
     */
    public function process(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->isIdVerified()) {
            return redirect()->route('account.index', ['tab' => 'profile'])
                ->with('warning', 'Valid ID verification is required before completing your order.');
        }

        $request->validate([
            'payment_method'   => ['required', 'string'],
            'delivery_address' => ['required', 'string'],
        ]);

        return redirect()->route('account.purchases')
            ->with('success', '🎉 Order placed successfully! Your verified buyer order is being prepared for dispatch.');
    }
}
