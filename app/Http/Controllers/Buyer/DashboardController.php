<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Review;
use App\Models\SellerOrder;
use App\Models\Wishlist;
use App\Services\BuyerOrders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;

class DashboardController extends Controller
{
    /**
     * Buyer Dashboard — orders, cart, wishlist, tracking, notifications,
     * reviews, profile, addresses and settings, all backed by the database.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isLogistics()) {
            if (in_array($request->query('tab'), ['settings', 'profile', 'addresses'], true)) {
                return redirect()->route('logistics.account');
            }
            if (!$request->has('tab')) {
                return redirect()->route('logistics.dashboard');
            }
        }

        // Cart saved to the buyer's account.
        $cart = array_values(app(\App\Services\BuyerCart::class)->all());
        $cartSubtotal = collect($cart)->sum(fn ($i) => $i['price'] * $i['quantity']);
        $cartCount = count($cart);

        // Orders (database): one card per seller order
        $sellerOrders = app(BuyerOrders::class)->sellerOrders($user->id);

        $orders = $sellerOrders->map(fn ($so) => $this->orderCard($so))->all();
        $statusCounts = collect($orders)->countBy('status')->all();

        $wishlist = $this->wishlist($user->id);
        $reviews = $this->reviews($user->id, $sellerOrders);

        [$archivedDbNotifications, $dbNotifications] = \App\Services\NotificationService::getUserNotifications($user->id)
            ->partition(fn ($n) => $n->archived_at !== null);
        $notificationRow = fn ($n) => [
            'id'      => $n->id,
            'title'   => $n->title,
            'message' => $n->message,
            'time'    => $n->created_at->diffForHumans(),
            'type'    => $n->type,
            'unread'  => !$n->is_read,
            'link'    => $n->link,
        ];
        $notifications = $dbNotifications->map($notificationRow)->values()->all();
        $archivedNotifications = $archivedDbNotifications->map($notificationRow)->values()->all();

        $stats = [
            'total_orders' => count($orders),
            'total_spent'  => collect($orders)->where('status', 'completed')->sum('total'),
            'cart_items'   => $cartCount,
            'wishlist'     => count($wishlist),
        ];

        return view('buyer.dashboard', [
            'inbox' => $request->query('tab') === 'messages'
                ? app(\App\Http\Controllers\Seller\MessageController::class)->inboxData($request) : null,
            'user'          => $user,
            'orders'        => $orders,
            'statusCounts'  => $statusCounts,
            'cart'          => $cart,
            'cartSubtotal'  => $cartSubtotal,
            'cartCount'     => $cartCount,
            'wishlist'      => $wishlist,
            'reviews'       => $reviews,
            'notifications' => $notifications,
            'archivedNotifications' => $archivedNotifications,
            'notificationBox' => $request->query('box') === 'archived' ? 'archived' : 'inbox',
            'unread'        => collect($notifications)->where('unread', true)->count(),
            'stats'         => $stats,
            'addresses'     => $this->addresses($user),
            'savedAddresses' => \App\Models\Address::where('user_id', $user->id)->orderByDesc('is_default')->orderBy('id')->get(),
            'tracking'      => $this->tracking($sellerOrders, $request->query('track')),
            // Tracking lives inside the Orders tab; it opens from a card's "Track order" button or an old Track Order link.
            'showTracking'  => $request->filled('track') || $request->query('tab') === 'track',
            'vouchers'      => app(\App\Services\VoucherService::class)->options($user, (int) round($cartSubtotal * 100)),
            // Shop code vouchers the buyer added; public shop vouchers apply automatically, so they are shown on store pages instead.
            'walletShopVouchers' => \App\Models\Voucher::live()->whereNotNull('seller_id')->with('seller')
                ->whereIn('id', \Illuminate\Support\Facades\DB::table('voucher_user')->where('user_id', $user->id)->select('voucher_id'))
                ->where(fn ($q) => $q->whereNull('total_limit')->orWhereColumn('used_count', '<', 'total_limit'))
                ->orderBy('ends_at')->get(),
            'tab'           => in_array($request->query('tab'), ['profile', 'addresses']) ? 'settings' : $request->query('tab', 'orders'),
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

        if (!$request->expectsJson()) {
            return redirect()->route('home')->withFragment('daily-discover-section')
                ->with('wishlist_status', $wished ? 'Saved to your wishlist.' : 'Removed from your wishlist.');
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
        $order = app(BuyerOrders::class)->pay($reference, Auth::id());

        return redirect()->route('buyer.dashboard', ['tab' => 'orders'])->with('success', 'Payment recorded for ' . $order->reference . '.');
    }

    public function claimVoucher(Request $request)
    {
        $data = $request->validate(['voucher_code' => ['required', 'string', 'max:40']]);
        $voucher = app(\App\Services\VoucherService::class)->claimCode(Auth::user(), $data['voucher_code']);

        return redirect()->route('buyer.dashboard', ['tab' => 'vouchers'])
            ->with('success', 'Voucher "'.$voucher->name.'" added. It will be applied for you at checkout.');
    }

    public function cancelOrder(Request $request, int $id)
    {
        $message = app(BuyerOrders::class)->requestCancellation($id, Auth::id(), $request->all());

        return redirect()->route('buyer.dashboard', ['tab' => 'orders'])->with('success', $message);
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

    /**
     * Move a notification out of the inbox into the archive.
     */
    public function archiveNotification(int $id)
    {
        \App\Models\AppNotification::where('user_id', Auth::id())->findOrFail($id)->update(['is_read' => true, 'archived_at' => now()]);

        return redirect()->route('buyer.dashboard', ['tab' => 'notifications'])->with('success', 'Notification archived.');
    }

    /**
     * Move an archived notification back to the inbox.
     */
    public function restoreNotification(int $id)
    {
        \App\Models\AppNotification::where('user_id', Auth::id())->findOrFail($id)->update(['archived_at' => null]);

        return redirect()->route('buyer.dashboard', ['tab' => 'notifications', 'box' => 'archived'])->with('success', 'Notification moved back to your inbox.');
    }

    /* ----------------------------------------------------------------------
     | Data builders
     * -------------------------------------------------------------------- */

    private function dashboardStatus(SellerOrder $so): string
    {
        return app(BuyerOrders::class)->status($so);
    }

    private function imageUrl(?string $path): string
    {
        return app(BuyerOrders::class)->imageUrl($path);
    }

    private function orderCard(SellerOrder $so): array
    {
        return app(BuyerOrders::class)->card($so);
    }

    private function wishlist(int $userId): array
    {
        return Wishlist::where('user_id', $userId)->latest()->get()->map(fn ($w) => [
            'ref'       => $w->ref,
            'name'      => $w->name,
            'price'     => $w->price_minor / 100,
            'variation' => $w->variation ?: 'Standard',
            'image'     => $this->imageUrl($w->image),
            'in_stock'  => $this->wishlistItemInStock($w),
        ])->all();
    }

    private function wishlistItemInStock(Wishlist $item): bool
    {
        try {
            $variant = app(\App\Services\ProductPricing::class)->variant([
                'id' => $item->ref, 'variation' => $item->variation ?: 'Standard',
            ]);
            return $variant && $variant->stock > 0;
        } catch (\RuntimeException $e) {
            return false;
        }
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

        return app(\App\Services\OrderTracking::class)->build($so);
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
