<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OrderPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    /**
     * Show Checkout Page with ID Verification Gate
     */
    public function index(Request $request)
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

        // Load the buyer's saved cart.
        $cart = app(\App\Services\BuyerCart::class)->all();
        $selectedKeys = array_keys(array_filter($cart, fn ($item) => $item['selected'] ?? true));
        if ($request->has('selection')) {
            $validated = $request->validate([
                'selected_items' => ['required', 'array', 'min:1'],
                'selected_items.*' => ['required', 'string', 'distinct'],
            ]);
            $selectedKeys = $validated['selected_items'];
        }
        $selectedCart = array_intersect_key($cart, array_flip($selectedKeys));
        if (count($selectedCart) !== count($selectedKeys)) {
            return redirect()->route('cart.index')->with('info', 'Your cart changed. Please select your items again.');
        }
        $selectedKeys = array_keys($selectedCart);
        $checkoutItems = array_values($selectedCart);

        // If cart is empty, redirect to homepage
        if (empty($checkoutItems)) {
            return redirect()->route('cart.index')->with('info', 'Your cart is empty. Add some products before checking out!');
        }

        $subtotal = array_sum(array_map(fn($item) => ($item['price'] ?? 0) * ($item['quantity'] ?? 1), $checkoutItems));
        $shippingFee = 60.00;
        $discount = $subtotal >= 1000 ? 50.00 : 0.00;
        $total = $subtotal + $shippingFee - $discount;

        $deliveryAddress = \App\Models\Address::where('user_id', $user->id)->where('is_default', true)->first();
        $checkoutAddress = $deliveryAddress?->formatted_address ?: $user->address;

        return view('buyer.checkout', compact('user', 'checkoutItems', 'selectedKeys', 'subtotal', 'shippingFee', 'discount', 'total', 'deliveryAddress', 'checkoutAddress'));
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
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['required', 'string', 'distinct'],
        ]);

        $cart = app(\App\Services\BuyerCart::class)->all();
        $selectedKeys = $request->input('selected_items');
        $selectedCart = array_intersect_key($cart, array_flip($selectedKeys));
        if (count($selectedCart) !== count($selectedKeys)) {
            return redirect()->route('cart.index')->with('info', 'Your cart changed. Please select your items again.');
        }
        $checkoutItems = array_values($selectedCart);
        if (empty($checkoutItems)) {
            return redirect()->route('cart.index')->with('info', 'Your cart is empty. Add some products before checking out!');
        }

        try {
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $checkoutItems, $request, $selectedCart) {
                $order = app(OrderPlacement::class)->place(
                    $user,
                    $checkoutItems,
                    $request->input('payment_method'),
                    $request->input('delivery_address')
                );
                app(\App\Services\BuyerCart::class)->remove(array_keys($selectedCart));
                return $order;
            });
        } catch (\RuntimeException $e) {
            return redirect()->route('cart.index')->with('info', $e->getMessage());
        }

        try {
            \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
        } catch (\Throwable $e) {
            // SQL dump sync is best-effort; never block a placed order.
        }

        return redirect()->route('buyer.dashboard', ['tab' => 'orders'])
            ->with('success', 'Order ' . $order->reference . ' placed successfully! Your order is being prepared for dispatch.');
    }
}
