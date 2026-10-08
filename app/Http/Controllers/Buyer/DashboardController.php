<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\SellerOrder;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class DashboardController extends Controller
{
    /**
     * Buyer Dashboard — orders, cart, wishlist, tracking, notifications,
     * reviews, profile, addresses and settings, all backed by the database.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cart (session)
        $cart = array_values(Session::get('cart', []));
        $cartSubtotal = collect($cart)->sum(fn ($i) => $i['price'] * $i['quantity']);
        $cartCount = collect($cart)->sum('quantity');

        // Orders (database): one card per seller order
        $sellerOrders = SellerOrder::query()
            ->whereHas('order', fn ($q) => $q->where('buyer_id', $user->id))
            ->with(['order.sellerOrders', 'order.payments', 'seller', 'items.product.coverImage', 'shipment.deliveryEvents', 'shipment.rider', 'logisticsProvider'])
            ->latest()
            ->get();

        $orders = $sellerOrders->map(fn ($so) => $this->orderCard($so))->all();
        $statusCounts = collect($orders)->countBy('status')->all();

        $wishlist = $this->wishlist($user->id);
        $reviews = $this->reviews($user->id, $sellerOrders);

        $dbNotifications = \App\Services\NotificationService::getUserNotifications($user->id);
        $notifications = $dbNotifications->map(fn ($n) => [
            'id'      => $n->id,
            'title'   => $n->title,
            'message' => $n->message,
            'time'    => $n->created_at->diffForHumans(),
            'type'    => $n->type,
            'unread'  => !$n->is_read,
            'link'    => $n->link,
        ])->all();

        $stats = [
            'total_orders' => count($orders),
            'total_spent'  => collect($orders)->where('status', 'completed')->sum('total'),
            'cart_items'   => $cartCount,
            'wishlist'     => count($wishlist),
        ];

        return view('buyer.dashboard', [
            'user'          => $user,
            'orders'        => $orders,
            'statusCounts'  => $statusCounts,
            'cart'          => $cart,
            'cartSubtotal'  => $cartSubtotal,
            'cartCount'     => $cartCount,
            'wishlist'      => $wishlist,
            'reviews'       => $reviews,
            'notifications' => $notifications,
            'unread'        => collect($notifications)->where('unread', true)->count(),
            'stats'         => $stats,
            'addresses'     => $this->addresses($user),
            'tracking'      => $this->tracking($sellerOrders, $request->query('track')),
            'tab'           => $request->query('tab', 'overview'),
        ]);
    }

    /* ----------------------------------------------------------------------
     | Actions
     * -------------------------------------------------------------------- */

    /** Add/remove a storefront product from the buyer's wishlist (JSON). */
    public function toggleWishlist(Request $request)
    {
        $data = $request->validate([
            'ref'       => ['required', 'string', 'max:100'],
            'name'      => ['required', 'string', 'max:255'],
            'price'     => ['required', 'numeric', 'min:0'],
            'image'     => ['nullable', 'string', 'max:500'],
            'variation' => ['nullable', 'string', 'max:255'],
        ]);

        $existing = Wishlist::where('user_id', Auth::id())->where('ref', $data['ref'])->first();

        if ($existing) {
            $existing->delete();
            $wished = false;
        } else {
            Wishlist::create([
                'user_id'     => Auth::id(),
                'ref'         => $data['ref'],
                'name'        => $data['name'],
                'price_minor' => (int) round($data['price'] * 100),
                'image'       => $data['image'] ?? null,
                'variation'   => $data['variation'] ?? 'Standard',
            ]);
            $wished = true;
        }

        return response()->json([
            'success' => true,
            'wished'  => $wished,
            'count'   => Wishlist::where('user_id', Auth::id())->count(),
        ]);
    }

    /** Remove a wishlist entry (used after "Move to cart"). */
    public function removeWishlist(Request $request)
    {
        $request->validate(['ref' => ['required', 'string']]);
        Wishlist::where('user_id', Auth::id())->where('ref', $request->input('ref'))->delete();

        return response()->json(['success' => true]);
    }

    /** Save a review for an item from a completed order. */
    public function submitReview(Request $request)
    {
        $data = $request->validate([
            'order_item_id' => ['required', 'integer'],
            'rating'        => ['required', 'integer', 'between:1,5'],
            'comment'       => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Please choose a star rating.',
        ]);

        $item = OrderItem::with('sellerOrder.order')->findOrFail($data['order_item_id']);

        abort_unless($item->sellerOrder->order->buyer_id === Auth::id(), 403);

        if (!in_array($item->sellerOrder->status, ['delivered', 'completed'], true)) {
            return back()->withErrors(['rating' => 'You can only review items you have received.']);
        }

        Review::updateOrCreate(
            ['user_id' => Auth::id(), 'order_item_id' => $item->id],
            ['product_id' => $item->product_id, 'rating' => $data['rating'], 'comment' => $data['comment'] ?? null]
        );

        return redirect()->route('buyer.dashboard', ['tab' => 'reviews'])->with('success', 'Thanks! Your review has been saved.');
    }

    /**
     * Demo payment for wallet/card orders. There is no payment gateway wired in yet,
     * so this simply records the order as paid.
     */
    public function payOrder(string $reference)
    {
        $order = \App\Models\Order::where('reference', $reference)->where('buyer_id', Auth::id())->firstOrFail();

        if ($order->payment_status !== 'paid') {
            $order->update(['payment_status' => 'paid']);
            $order->payments()->update(['status' => 'paid']);
            \App\Services\NotificationService::notify(
                Auth::id(),
                'payment',
                'Payment Confirmed',
                "Payment for order {$order->reference} has been received.",
                route('buyer.dashboard', ['tab' => 'orders'])
            );
        }

        return redirect()->route('buyer.dashboard', ['tab' => 'orders'])->with('success', 'Payment recorded for ' . $order->reference . '.');
    }

    /**
     * Mark a single notification as read.
     */
    public function markNotificationRead(int $id)
    {
        \App\Services\NotificationService::markAsRead($id, Auth::id());
        $unread = \App\Models\AppNotification::where('user_id', Auth::id())->where('is_read', false)->count();

        return response()->json(['success' => true, 'unread' => $unread]);
    }

    /**
     * Mark all notifications for the user as read.
     */
    public function markAllNotificationsRead()
    {
        \App\Services\NotificationService::markAllAsRead(Auth::id());

        return response()->json(['success' => true, 'unread' => 0]);
    }

    /* ----------------------------------------------------------------------
     | Data builders
     * -------------------------------------------------------------------- */

    private function dashboardStatus(SellerOrder $so): string
    {
        return match (true) {
            $so->status === 'cancelled'                                   => 'cancelled',
            in_array($so->status, ['delivered', 'completed'], true)       => 'completed',
            $so->status === 'shipped'                                     => 'to_receive',
            in_array(optional($so->shipment)->status, ['picked_up', 'in_transit', 'out_for_delivery'], true) => 'to_receive',
            $so->order->payment_method === 'wallet' && $so->order->payment_status === 'pending' => 'to_pay',
            default                                                       => 'to_ship',
        };
    }

    private function imageUrl(?string $path): string
    {
        if (!$path) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=160&h=160&fit=crop&q=80';
        }

        return str_starts_with($path, 'http') ? $path : asset('storage/' . $path);
    }

    private function orderCard(SellerOrder $so): array
    {
        $order = $so->order;
        $singleSeller = $order->sellerOrders->count() === 1;

        return [
            'id'       => $order->reference,
            'seller_order_id' => $so->id,
            'store'    => $so->seller->name ?? 'Cartzy Seller',
            'status'   => $this->dashboardStatus($so),
            'date'     => $order->created_at->format('M d, Y'),
            'total'    => $singleSeller ? $order->total_minor / 100 : ($so->subtotal_minor + $so->shipping_fee_minor) / 100,
            'payment'  => $order->payments->first()->method ?? strtoupper($order->payment_method),
            'items'    => $so->items->map(fn ($i) => [
                'name'      => $i->product_name,
                'variation' => $i->variant_name,
                'qty'       => $i->quantity,
                'price'     => $i->unit_price_minor / 100,
                'image'     => $this->imageUrl(optional(optional($i->product)->coverImage)->path),
            ])->all(),
        ];
    }

    private function wishlist(int $userId): array
    {
        return Wishlist::where('user_id', $userId)->latest()->get()->map(fn ($w) => [
            'ref'       => $w->ref,
            'name'      => $w->name,
            'price'     => $w->price_minor / 100,
            'variation' => $w->variation ?: 'Standard',
            'image'     => $this->imageUrl($w->image),
        ])->all();
    }

    private function reviews(int $userId, $sellerOrders): array
    {
        $received = $sellerOrders->filter(fn ($so) => in_array($so->status, ['delivered', 'completed'], true));
        $existing = Review::where('user_id', $userId)->get()->keyBy('order_item_id');

        $rows = [];
        foreach ($received as $so) {
            foreach ($so->items as $item) {
                $review = $existing->get($item->id);
                $rows[] = [
                    'item_id' => $item->id,
                    'product' => $item->product_name,
                    'store'   => $so->seller->name ?? 'Cartzy Seller',
                    'order'   => $so->order->reference,
                    'image'   => $this->imageUrl(optional(optional($item->product)->coverImage)->path),
                    'rating'  => $review->rating ?? null,
                    'comment' => $review->comment ?? null,
                    'seller_reply' => $review->seller_reply ?? null,
                ];
            }
        }

        // Items still waiting for a review first
        usort($rows, fn ($a, $b) => ($a['rating'] === null ? 0 : 1) <=> ($b['rating'] === null ? 0 : 1));

        return $rows;
    }

    /** Derive notifications from real order/shipment state (updated within 24h = unread). */
    private function notifications(): array
    {
        $list = [];
        $sellerOrders = SellerOrder::query()
            ->whereHas('order', fn ($q) => $q->where('buyer_id', Auth::id()))
            ->with(['order', 'shipment'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        foreach ($sellerOrders as $so) {
            $ref = $so->order->reference;
            $status = $this->dashboardStatus($so);
            $stamp = max($so->updated_at, optional($so->shipment)->updated_at ?? $so->updated_at);

            [$title, $message, $type] = match ($status) {
                'to_pay'     => ['Payment pending', "Complete payment for {$ref} to get it processed.", 'payment'],
                'to_ship'    => ['Order is being prepared', "Your seller is preparing {$ref} for pickup.", 'order'],
                'to_receive' => ['Parcel on the way', "Order {$ref} is with our courier and will arrive soon.", 'delivery'],
                'completed'  => ['Order delivered', "Order {$ref} was delivered. Tell others what you think!", 'review'],
                'cancelled'  => ['Order cancelled', "Order {$ref} was cancelled.", 'order'],
            };

            $list[] = [
                'title'   => $title,
                'message' => $message,
                'time'    => $stamp->diffForHumans(),
                'type'    => $type,
                'unread'  => $stamp->greaterThan(now()->subDay()),
            ];
        }

        return $list;
    }

    /** Timeline for the requested order/tracking code, else the latest in-progress (or latest) order. */
    private function tracking($sellerOrders, ?string $query): ?array
    {
        if ($sellerOrders->isEmpty()) {
            return null;
        }

        $query = $query ? trim($query) : null;
        $so = null;

        if ($query) {
            $so = $sellerOrders->first(fn ($s) => strcasecmp($s->order->reference, $query) === 0
                || strcasecmp((string) optional($s->shipment)->tracking_code, $query) === 0);

            if (!$so) {
                return ['not_found' => true, 'query' => $query];
            }
        } else {
            $so = $sellerOrders->first(fn ($s) => in_array($this->dashboardStatus($s), ['to_receive', 'to_ship', 'to_pay'], true))
                ?? $sellerOrders->first();
        }

        $shipment = $so->shipment;
        $shipStatus = optional($shipment)->status;
        $events = $shipment ? $shipment->deliveryEvents->keyBy('status') : collect();
        $at = fn (string $status, $fallback = null) => optional(optional($events->get($status))->occurred_at ?? $fallback)->format('M d, h:i A') ?? 'Pending';

        $packed = in_array($so->status, ['packed', 'ready_to_ship', 'shipped', 'delivered', 'completed'], true);
        $moving = in_array($shipStatus, ['picked_up', 'in_transit', 'out_for_delivery', 'delivered'], true) || in_array($so->status, ['shipped', 'delivered', 'completed'], true);
        $ofd = in_array($shipStatus, ['out_for_delivery', 'delivered'], true) || in_array($so->status, ['delivered', 'completed'], true);
        $delivered = $shipStatus === 'delivered' || in_array($so->status, ['delivered', 'completed'], true);

        $steps = [
            ['label' => 'Order placed',               'detail' => 'Your order was received.',                         'time' => $so->order->created_at->format('M d, h:i A'), 'done' => true],
            ['label' => 'Packed by seller',           'detail' => 'The seller has packed your parcel.',               'time' => $packed ? $at('packed', $so->updated_at) : 'Pending', 'done' => $packed],
            ['label' => 'Picked up by courier',       'detail' => 'The courier has collected your parcel.',           'time' => $moving ? $at('picked_up', $shipment?->updated_at) : 'Pending', 'done' => $moving],
            ['label' => 'Out for delivery',           'detail' => 'Your rider is on the way to your address.',        'time' => $ofd ? $at('out_for_delivery', $shipment?->updated_at) : 'Pending', 'done' => $ofd],
            ['label' => 'Delivered',                  'detail' => 'Parcel handed over. Enjoy your purchase!',         'time' => $delivered ? ($so->delivered_at?->format('M d, h:i A') ?? $at('delivered')) : 'Pending', 'done' => $delivered],
        ];

        // Mark the latest completed step as "current" unless everything is done.
        for ($i = count($steps) - 1; $i >= 0; $i--) {
            if ($steps[$i]['done']) {
                if ($i < count($steps) - 1) {
                    $steps[$i]['current'] = true;
                }
                break;
            }
        }

        $riderName = optional(optional(optional($shipment)->rider)->user)->name;

        return [
            'order'       => $so->order->reference,
            'tracking_no' => $shipment->tracking_code ?? '—',
            'courier'     => trim(($so->logisticsProvider->name ?? 'Cartzy Express') . ($riderName ? " — {$riderName}" : '')),
            'eta'         => $delivered ? 'Delivered' : ($ofd ? 'Arriving today' : 'We\'ll notify you of each update'),
            'steps'       => $steps,
        ];
    }

    private function addresses($user): array
    {
        if (empty($user->address) && empty($user->street_address)) {
            return [];
        }

        $full = $user->address ?: implode(', ', array_filter([
            $user->street_address,
            $user->barangay ? 'Brgy. ' . $user->barangay : null,
            $user->city, $user->province, $user->region, $user->postal_code,
        ]));

        return [['label' => 'Home', 'name' => $user->name, 'phone' => $user->phone ?: 'No phone provided', 'full' => $full, 'default' => true]];
    }
}
