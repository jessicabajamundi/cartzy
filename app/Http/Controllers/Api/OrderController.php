<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Buyer\ProblemReportController;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\AdminDispute;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\SellerOrder;
use App\Models\Order;
use App\Services\BuyerOrders;
use App\Services\OrderPlacement;
use App\Services\OrderTracking;
use App\Services\ProductPricing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

/**
 * Buyer "My Orders" for the mobile app: the same cards, rules and actions as the website dashboard.
 */
class OrderController extends Controller
{
    public function __construct(private BuyerOrders $orders) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $sellerOrders = $this->orders->sellerOrders($user->id);
        $reviews = Review::where('user_id', $user->id)->get()->keyBy('order_item_id');

        $cards = $sellerOrders->map(function ($so) use ($reviews) {
            $card = $this->orders->card($so);
            $report = $card['report'];
            $card['report'] = $report ? [
                'id' => $report->id,
                'status' => $report->status,
                'subject' => $report->subject,
                'admin_notes' => $report->admin_notes,
            ] : null;
            $card['items'] = array_map(fn ($item) => $item + [
                'rating' => $reviews->get($item['id'])?->rating,
                'comment' => $reviews->get($item['id'])?->comment,
            ], $card['items']);

            return $card;
        })->values();

        $defaultAddress = Address::where('user_id', $user->id)->where('is_default', true)->first();

        return response()->json([
            'orders' => $cards,
            'counts' => $cards->countBy('status'),
            'cancellation_reasons' => SellerOrder::CANCELLATION_REASONS,
            'report_categories' => collect(AdminDispute::CATEGORIES)
                ->map(fn ($c, $key) => ['key' => $key, 'label' => $c[0], 'needs_photos' => $c[1]])->values(),
            'report_resolutions' => collect(AdminDispute::RESOLUTIONS)
                ->map(fn ($label, $key) => ['key' => $key, 'label' => $label])->values(),
            'contact_phone' => $defaultAddress?->phone ?? $user->phone,
        ]);
    }

    /**
     * Place an order from the mobile cart: the same rules as the website checkout
     * (prices and stock come from the database, stock is deducted, vouchers applied).
     *
     * `checkout_key` is a one-time id the app makes per Place Order tap, so a retried
     * request returns the order already placed instead of creating a second one.
     */
    public function store(Request $request, OrderPlacement $placement, ProductPricing $pricing)
    {
        if ($request->has('items') && is_string($request->input('items'))) {
            $decoded = json_decode($request->input('items'), true);
            if (is_array($decoded)) {
                $request->merge(['items' => $decoded]);
            }
        }

        $data = $request->validate([
            'checkout_key' => ['required', 'string', 'max:64'],
            'payment_method' => ['required', 'string', 'in:COD,GCash,wallet,cod,gcash'],
            'gcash_reference' => ['nullable', 'string', 'max:50'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
            'address_id' => ['required', 'integer'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.variation' => ['nullable', 'string', 'max:255'],
            'items.*.name' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'], // real limit is the stock, checked when the order is placed
        ], [
            'address_id.required' => 'Add your delivery address before placing your order.',
            'items.required' => 'Select at least one item to check out.',
        ]);

        $user = $request->user();
        $cacheKey = "api-checkout:{$user->id}:{$data['checkout_key']}";

        $receiptPath = null;
        if ($request->hasFile('receipt') && $request->file('receipt')->isValid()) {
            $receiptPath = $request->file('receipt')->store('payment_receipts', 'public');
        }

        return Cache::lock($cacheKey.':lock', 30)->block(15, function () use ($data, $user, $cacheKey, $placement, $pricing, $receiptPath) {
            // Same tap sent twice (e.g. a retry after a slow network): answer with the first order.
            if ($orderId = Cache::get($cacheKey)) {
                $order = Order::find($orderId);
                if ($order) {
                    return response()->json($this->placedJson($order));
                }
            }

            $address = Address::where('user_id', $user->id)->find($data['address_id']);
            if (!$address) {
                return response()->json(['message' => 'Please choose one of your saved delivery addresses.'], 422);
            }

            // Only real products can be ordered from the app; price and stock always come from the database.
            $items = [];
            foreach ($data['items'] as $line) {
                $label = $line['name'] ?? 'An item in your cart';
                if (empty($line['product_id']) && empty($line['variant_id'])) {
                    return response()->json(['message' => "{$label} can't be ordered. Please remove it and add it to your cart again."], 422);
                }
                $item = [
                    'id' => $line['product_id'] ?? null,
                    'variant_id' => $line['variant_id'] ?? null,
                    'variation' => $line['variation'] ?? null,
                    'quantity' => (int) $line['quantity'],
                ];
                try {
                    $variant = $pricing->variant($item);
                } catch (RuntimeException $e) {
                    return response()->json(['message' => "{$label}: {$e->getMessage()}"], 422);
                }
                if (!$variant) {
                    return response()->json(['message' => "{$label} is no longer available. Please remove it from your cart."], 422);
                }
                $item['variant_id'] = $variant->id;
                $items[] = $item;
            }

            $chosenMethod = in_array(strtoupper(trim((string) ($data['payment_method'] ?? 'COD'))), ['COD', 'CASH ON DELIVERY'], true) ? 'COD' : 'GCash';

            try {
                $order = $placement->place($user, $items, $chosenMethod, $address->formatted_address, null, $address, $data['gcash_reference'] ?? null, $receiptPath);
            } catch (RuntimeException $e) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            Cache::put($cacheKey, $order->id, now()->addDay());

            try {
                \App\Console\Commands\ExportDatabaseSql::exportSqlFile();
            } catch (\Throwable $e) {
                // SQL dump sync is best-effort; never block a placed order.
            }

            return response()->json($this->placedJson($order), 201);
        });
    }

    private function placedJson(Order $order): array
    {
        $order->loadMissing('sellerOrders.seller');
        $isCod = $order->payment_method === 'cod';
        $totalFormatted = $order->total_minor / 100;
        $primarySeller = $order->sellerOrders->first()?->seller;

        $json = [
            'message' => "Order {$order->reference} placed successfully! Your order is being prepared for dispatch.",
            'order' => [
                'id' => $order->id,
                'reference' => $order->reference,
                'total' => $totalFormatted,
                'payment_method' => $isCod ? 'COD' : 'GCash',
                'payment_status' => $order->payment_status,
                'seller_order_ids' => $order->sellerOrders->pluck('id')->values(),
            ],
        ];

        if (!$isCod) {
            $latestPayment = $order->payments()->latest()->first();
            $json['order']['gcash'] = [
                'merchant_name' => $primarySeller?->gcash_name ?: ($primarySeller?->name ?: 'Cartzy Official Store'),
                'merchant_number' => $primarySeller?->gcash_number ?: '0917-555-2278',
                'qr_url' => $primarySeller?->gcash_qr_url,
                'amount' => $totalFormatted,
                'reference' => $order->reference,
                'provider_ref' => $latestPayment?->provider_ref,
                'receipt_url' => $latestPayment?->receipt_url,
                'payment_status' => $latestPayment?->status ?? 'pending',
                'verification_status' => 'pending_verification',
                'qr_data' => "00020101021226580014ph.com.gcash011891755522780210CARTZYPH520459995303608540" . sprintf("%02d", strlen((string)$totalFormatted)) . $totalFormatted . "5802PH5915Cartzy Commerce6006Manila62" . sprintf("%02d", strlen($order->reference) + 4) . "05" . sprintf("%02d", strlen($order->reference)) . $order->reference . "6304",
                'expiry_minutes' => 15,
            ];
        }

        return $json;
    }

    public function sellerPaymentInfo(Request $request)
    {
        $productIds = $request->input('product_ids', []);
        if (is_string($productIds)) {
            $productIds = array_filter(array_map('trim', explode(',', $productIds)));
        }

        $seller = null;
        if (!empty($productIds)) {
            $product = \App\Models\Product::whereIn('id', $productIds)->with('seller')->first();
            if ($product && $product->seller) {
                $seller = $product->seller;
            } else {
                try {
                    $seller = app(\App\Services\CatalogStores::class)->storeFor((string) reset($productIds));
                } catch (\Throwable $e) {}
            }
        }

        if (!$seller && $request->has('seller_id')) {
            $seller = \App\Models\Seller::find($request->input('seller_id'));
        }

        if (!$seller) {
            $seller = \App\Models\Seller::where('status', 'approved')->first();
        }

        return response()->json([
            'seller_id'     => $seller?->id,
            'store_name'    => $seller?->name ?? 'Cartzy Official Store',
            'gcash_name'    => $seller?->gcash_name ?: ($seller?->name ?? 'Cartzy Commerce'),
            'gcash_number'  => $seller?->gcash_number ?: '0917-555-2278',
            'gcash_qr_url'  => $seller?->gcash_qr_url,
            'has_custom_qr' => !empty($seller?->gcash_qr_url),
        ]);
    }

    public function tracking(Request $request, string $id)
    {
        $userId = $request->user()?->id;
        $so = null;

        if (is_numeric($id)) {
            $intId = (int) $id;
            $so = $this->orders->sellerOrders($userId)->firstWhere('id', $intId)
                ?? \App\Models\SellerOrder::where('id', $intId)->whereHas('order', fn ($q) => $q->where('buyer_id', $userId))->first()
                ?? \App\Models\SellerOrder::where('order_id', $intId)->whereHas('order', fn ($q) => $q->where('buyer_id', $userId))->first();
        }

        if (!$so) {
            $so = \App\Models\SellerOrder::whereHas('order', fn ($q) => $q->where('buyer_id', $userId))
                ->where(function ($q) use ($id) {
                    $q->whereHas('order', fn ($oq) => $oq->where('reference', $id))
                      ->orWhereHas('shipment', fn ($sq) => $sq->where('tracking_code', $id));
                })->first();
        }

        abort_unless($so, 404, 'Order not found.');

        return response()->json(['tracking' => app(OrderTracking::class)->build($so)]);
    }

    public function liveLocation(Request $request, string $id)
    {
        $userId = $request->user()?->id;
        $so = null;

        if (is_numeric($id)) {
            $intId = (int) $id;
            $so = \App\Models\SellerOrder::where('id', $intId)->first()
                ?? \App\Models\SellerOrder::where('order_id', $intId)->first();
        }

        if (!$so) {
            $so = \App\Models\SellerOrder::where(function ($q) use ($id) {
                $q->whereHas('order', fn ($oq) => $oq->where('reference', $id))
                  ->orWhereHas('shipment', fn ($sq) => $sq->where('tracking_code', $id));
            })->first();
        }

        abort_unless($so, 404, 'Order not found.');

        $trackingData = app(OrderTracking::class)->build($so);
        return response()->json([
            'order' => $trackingData['order'],
            'tracking_no' => $trackingData['tracking_no'],
            'map' => $trackingData['map'] ?? null,
            'current_status' => $trackingData['current_status'],
            'eta' => $trackingData['eta'],
        ]);
    }

    public function cancel(Request $request, int $id)
    {
        $message = $this->orders->requestCancellation($id, $request->user()->id, $request->all());

        return response()->json(['message' => $message]);
    }

    public function pay(Request $request, string $reference)
    {
        $order = $this->orders->pay($reference, $request->user()->id);

        return response()->json(['message' => 'Payment recorded for '.$order->reference.'.']);
    }

    public function report(Request $request, int $id)
    {
        $dispute = app(ProblemReportController::class)->createReport($request, $id);

        return response()->json([
            'message' => "Report sent (case #{$dispute->id}). Our team will review it and update you in Notifications.",
            'case_id' => $dispute->id,
        ]);
    }

    /** Save a review for an item from a received order. */
    public function review(Request $request)
    {
        $data = $request->validate([
            'order_item_id' => ['required', 'integer'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Please choose a star rating.',
        ]);

        $item = OrderItem::with('sellerOrder.order')->findOrFail($data['order_item_id']);
        abort_unless($item->sellerOrder->order->buyer_id === $request->user()->id, 403);

        if (!in_array($item->sellerOrder->status, ['delivered', 'completed'], true)) {
            return response()->json(['message' => 'You can only review items you have received.'], 422);
        }

        Review::updateOrCreate(
            ['user_id' => $request->user()->id, 'order_item_id' => $item->id],
            ['product_id' => $item->product_id, 'rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        return response()->json(['message' => 'Thanks! Your review has been saved.']);
    }
}
