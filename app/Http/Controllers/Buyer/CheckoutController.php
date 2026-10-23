<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\OrderPlacement;
use App\Services\VoucherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    /**
     * Show Checkout Page
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // If not logged in, redirect to login
        if (!$user) {
            return redirect()->route('login')->with('info', 'Please log in to proceed to checkout.');
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
        $vouchers = app(VoucherService::class);

        $deliveryAddress = \App\Models\Address::where('user_id', $user->id)->where('is_default', true)->first();
        $checkoutAddress = $deliveryAddress?->formatted_address ?: $user->address;
        // Same rule as OrderPlacement: shipping is priced for the saved default address.
        $destination = $deliveryAddress && $deliveryAddress->formatted_address === $checkoutAddress ? $deliveryAddress : null;

        // Group items by store: each store ships its own parcel, priced by route (Shopee/Lazada style).
        $storeItems = [];
        $hasUnavailableItems = false;
        foreach ($checkoutItems as $item) {
            // Items that can't be matched to a store (key 0) ship as one marketplace parcel; placing the order re-checks them.
            $storeItems[$vouchers->sellerIdFor($item) ?? 0][] = $item;
            $hasUnavailableItems = $hasUnavailableItems || !empty($item['unavailable']);
        }
        $storeSubtotals = collect($storeItems)->except(0)
            ->map(fn ($items) => array_sum(array_map(fn ($item) => (int) round(($item['price'] ?? 0) * 100) * (int) ($item['quantity'] ?? 1), $items)))->all();
        $shippingQuotes = \App\Services\ShippingCalculator::forStores(array_keys($storeSubtotals), $destination);
        if (isset($storeItems[0])) {
            $origin = config('shipping.default_origin');
            $shippingQuotes[0] = \App\Services\ShippingCalculator::quote($origin, $destination?->province) + ['from' => $origin];
        }
        $storeShipping = array_map(fn ($quote) => $quote['fee_minor'], $shippingQuotes);
        $shopNames = \App\Models\Seller::whereIn('id', array_keys($storeSubtotals))->pluck('name', 'id');

        // Shop vouchers per store: one discount voucher and one free-shipping voucher, best ones pre-selected.
        $shopOptions = $vouchers->shopOptions($user, $storeSubtotals, $storeShipping);
        $pick = fn (string $kind, string $query) => $shopOptions
            ->map(fn ($options) => VoucherService::ofKind($options, $kind))
            ->filter(fn ($options) => $options->isNotEmpty())
            ->map(fn ($options, $sellerId) => ['options' => $options, 'selected' => $this->selectedOption($options, ((array) $request->query($query, []))[$sellerId] ?? null)]);
        $shopDiscountGroups = $pick('items', 'shop_voucher');
        $shopShippingGroups = $pick('shipping', 'shop_shipping_voucher');
        $shopDiscountMinor = (int) $shopDiscountGroups->sum(fn ($group) => $group['selected']['discount_minor'] ?? 0);
        $shopShippingDiscountMinor = (int) $shopShippingGroups->sum(fn ($group) => $group['selected']['discount_minor'] ?? 0);

        // cartzy vouchers apply to what is left after shop vouchers; pre-select the best one of each kind.
        $shippingMinor = array_sum($storeShipping);
        $voucherOptions = $vouchers->options($user, (int) round($subtotal * 100) - $shopDiscountMinor, $shippingMinor - $shopShippingDiscountMinor);
        $itemVoucherOptions = $voucherOptions->reject(fn ($o) => $o['voucher']->isShipping())->values();
        $shippingVoucherOptions = $voucherOptions->filter(fn ($o) => $o['voucher']->isShipping())->values();
        $selectedItemVoucher = $this->selectedOption($itemVoucherOptions, $request->query('voucher'));
        $selectedShippingVoucher = $this->selectedOption($shippingVoucherOptions, $request->query('shipping_voucher'));

        $shippingFee = $shippingMinor / 100;
        $voucherDiscount = ($selectedItemVoucher['discount_minor'] ?? 0) / 100;
        $discount = ($selectedShippingVoucher['discount_minor'] ?? 0) / 100;
        $shopVoucherDiscount = $shopDiscountMinor / 100;
        $shopShippingDiscount = $shopShippingDiscountMinor / 100;
        $total = $subtotal + $shippingFee - $shopShippingDiscount - $discount - $voucherDiscount - $shopVoucherDiscount;
        $totalQuantity = array_sum(array_column($checkoutItems, 'quantity'));
        $originalSubtotal = array_sum(array_map(fn ($item) => max($item['original_price'] ?? $item['price'], $item['price']) * $item['quantity'], $checkoutItems));
        $productDiscount = round($originalSubtotal - $subtotal, 2);
        $totalSavings = $productDiscount + $discount + $voucherDiscount + $shopVoucherDiscount + $shopShippingDiscount;

        return view('buyer.checkout', compact('user', 'checkoutItems', 'selectedKeys', 'subtotal', 'shippingFee', 'discount', 'total', 'deliveryAddress', 'checkoutAddress', 'destination',
            'totalQuantity', 'originalSubtotal', 'productDiscount', 'totalSavings', 'hasUnavailableItems', 'itemVoucherOptions', 'shippingVoucherOptions', 'selectedItemVoucher',
            'selectedShippingVoucher', 'voucherDiscount', 'storeItems', 'shippingQuotes', 'shopNames', 'shopDiscountGroups', 'shopShippingGroups', 'shopVoucherDiscount', 'shopShippingDiscount'));
    }

    /** The voucher the buyer asked for (if usable), "none" for no voucher, otherwise the best one. */
    private function selectedOption($options, $requested): ?array
    {
        if ($requested === 'none') {
            return null;
        }
        $chosen = $requested ? $options->first(fn ($o) => $o['eligible'] && (string) $o['voucher']->id === (string) $requested) : null;

        return $chosen ?? $options->first(fn ($o) => $o['eligible']);
    }

    /**
     * Add a voucher code to the buyer's wallet, then return to the same checkout.
     */
    public function applyCode(Request $request)
    {
        $data = $request->validate([
            'voucher_code' => ['required', 'string', 'max:40'],
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['required', 'string', 'distinct'],
        ]);
        $voucher = app(VoucherService::class)->claimCode(Auth::user(), $data['voucher_code']);
        $choice = match (true) {
            $voucher->isShopVoucher() && $voucher->isShipping() => ['shop_shipping_voucher' => [$voucher->seller_id => $voucher->id]],
            $voucher->isShopVoucher() => ['shop_voucher' => [$voucher->seller_id => $voucher->id]],
            $voucher->isShipping() => ['shipping_voucher' => $voucher->id],
            default => ['voucher' => $voucher->id],
        };

        return redirect()->route('checkout', ['selection' => 1, 'selected_items' => $data['selected_items']] + $choice)
            ->with('voucher_success', 'Voucher "'.$voucher->name.'" added.');
    }

    /**
     * Process Order Placement
     */
    public function process(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login')->with('info', 'Please log in to complete your order.');
        }

        $request->validate([
            'payment_method'   => ['required', 'string', 'in:COD'],
            'delivery_address' => ['required', 'string'],
            'selected_items' => ['required', 'array', 'min:1'],
            'selected_items.*' => ['required', 'string', 'distinct'],
            'voucher_id' => ['nullable', 'integer'],
            'shipping_voucher_id' => ['nullable', 'integer'],
            'shop_voucher' => ['nullable', 'array'],
            'shop_voucher.*' => ['nullable', 'integer'],
            'shop_shipping_voucher' => ['nullable', 'array'],
            'shop_shipping_voucher.*' => ['nullable', 'integer'],
        ]);
        $ids = fn (string $field) => collect((array) $request->input($field, []))->map(fn ($id) => $id ? (int) $id : null)->all();
        $shopVoucherIds = ['shops' => $ids('shop_voucher'), 'shop_shipping' => $ids('shop_shipping_voucher')];

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
            $order = \Illuminate\Support\Facades\DB::transaction(function () use ($user, $checkoutItems, $request, $selectedCart, $shopVoucherIds) {
                $order = app(OrderPlacement::class)->place(
                    $user,
                    $checkoutItems,
                    $request->input('payment_method'),
                    $request->input('delivery_address'),
                    ['items' => $request->integer('voucher_id') ?: null, 'shipping' => $request->integer('shipping_voucher_id') ?: null] + $shopVoucherIds
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
