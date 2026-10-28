<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\ProductInquiryMessage;
use App\Models\Seller;
use App\Models\SellerMessage;
use App\Models\SellerOrder;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    private function threads(Request $request)
    {
        $query = SellerOrder::with(['order.buyer', 'seller', 'items', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_id', '!=', $request->user()->id)->whereNull('read_at')]);

        return $request->user()->isSeller() ? $query->where('seller_id', $request->user()->seller?->id ?? 0)
            : $query->whereHas('order', fn ($q) => $q->where('buyer_id', $request->user()->id));
    }

    /** Pre-purchase product conversations for the current user. */
    private function inquiries(Request $request)
    {
        $userId = $request->user()->id;

        return ProductInquiry::visibleTo($request->user())
            ->with(['buyer', 'seller', 'product', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_id', '!=', $userId)->whereNull('read_at')]);
    }

    public function index(Request $request)
    {
        if (!$request->user()->isSeller()) {
            $request->merge(['tab' => 'messages']);
            $request->query->set('tab', 'messages');

            return app(\App\Http\Controllers\Buyer\DashboardController::class)->index($request);
        }

        return view('seller.chat', $this->inboxData($request));
    }

    public function inboxData(Request $request): array
    {
        $request->validate(['contact' => 'nullable|integer', 'inquiry' => 'nullable|integer', 'search' => 'nullable|string|max:150', 'unread' => 'nullable|boolean']);
        $userId = $request->user()->id;
        $query = $this->threads($request);
        if ($request->boolean('unread')) {
            $query->whereHas('messages', fn ($q) => $q->where('sender_id', '!=', $userId)->whereNull('read_at'));
        }
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q
                ->whereHas('order', fn ($o) => $o->where('reference', 'like', '%'.$request->search.'%')->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', '%'.$request->search.'%')))
                ->orWhereHas('seller', fn ($s) => $s->where('name', 'like', '%'.$request->search.'%')));
        }
        $contacts = $query->orderByDesc(SellerMessage::select('created_at')->whereColumn('seller_order_id', 'seller_orders.id')->latest('id')->limit(1))->latest('seller_orders.id')->paginate(15)->withQueryString();

        $inquiryQuery = $this->inquiries($request);
        if ($request->user()->isSeller()) {
            // Sellers only see product questions once the buyer has actually sent something.
            $inquiryQuery->whereHas('messages');
        }
        if ($request->boolean('unread')) {
            $inquiryQuery->whereHas('messages', fn ($q) => $q->where('sender_id', '!=', $userId)->whereNull('read_at'));
        }
        if ($request->filled('search')) {
            $inquiryQuery->where(fn ($q) => $q
                ->whereHas('product', fn ($p) => $p->where('name', 'like', '%'.$request->search.'%'))
                ->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', '%'.$request->search.'%'))
                ->orWhereHas('seller', fn ($s) => $s->where('name', 'like', '%'.$request->search.'%')));
        }
        $inquiries = $inquiryQuery
            ->orderByDesc(ProductInquiryMessage::select('created_at')->whereColumn('product_inquiry_id', 'product_inquiries.id')->latest('id')->limit(1))
            ->latest('product_inquiries.id')
            ->paginate(10, ['*'], 'inquiries_page')->withQueryString();

        $current = null;
        $messages = null;
        $currentInquiry = null;
        $inquiryMessages = null;

        if ($request->filled('inquiry')) {
            $currentInquiry = $this->inquiries($request)->findOrFail($request->inquiry);
            $inquiryMessages = $currentInquiry->messages()->with('sender')->latest('id')->paginate(40, ['*'], 'messages_page')->withQueryString();
            $currentInquiry->messages()->whereIn('id', $inquiryMessages->getCollection()->modelKeys())->where('sender_id', '!=', $userId)->whereNull('read_at')->update(['read_at' => now()]);
            $inquiries->each(function ($inquiry) use ($currentInquiry) {
                if ($inquiry->id === $currentInquiry->id) {
                    $inquiry->unread_count = 0;
                }
            });
        } else {
            $current = $request->filled('contact') ? $this->threads($request)->findOrFail($request->contact) : $contacts->first();
        }

        if ($current) {
            $messages = $current->messages()->with('sender')->latest('id')->paginate(40, ['*'], 'messages_page')->withQueryString();
            $current->messages()->whereIn('id', $messages->getCollection()->modelKeys())->where('sender_id', '!=', $userId)->whereNull('read_at')->update(['read_at' => now()]);
            $contacts->each(function ($contact) use ($current) {
                if ($contact->id === $current->id) {
                    $contact->unread_count = 0;
                }
            });
        }

        return [
            'shop' => $request->user()->seller, 'contacts' => $contacts, 'current' => $current, 'messages' => $messages,
            'inquiries' => $inquiries, 'currentInquiry' => $currentInquiry, 'inquiryMessages' => $inquiryMessages,
            'isSeller' => $request->user()->isSeller(),
        ];
    }

    public function send(Request $request, int $contactId)
    {
        $thread = $this->threads($request)->findOrFail($contactId);
        $request->merge(['message' => trim((string) $request->input('message'))]);
        $data = $request->validate(['message' => 'required|string|max:5000']);
        $userId = $request->user()->id;

        $otherUserId = $thread->order?->buyer_id === $userId ? $thread->seller?->user_id : $thread->order?->buyer_id;
        $isDelivered = $otherUserId && \Illuminate\Support\Facades\Cache::has('user_online:'.$otherUserId);

        $message = $thread->messages()->create([
            'sender_id' => $userId,
            'body' => $data['message'],
            'delivered_at' => $isDelivered ? now() : null,
        ]);

        \Illuminate\Support\Facades\Cache::forget("chat_typing:order:{$thread->id}:{$userId}");

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->inquiryMessageJson($message, $userId)], 201);
        }

        return redirect()->route($request->user()->isSeller() ? 'seller.chat' : 'buyer.messages', ['contact' => $thread->id]);
    }

    /**
     * Open (or reuse) the buyer's conversation with a product's seller, even before buying.
     */
    public function startInquiry(Request $request, int $productId)
    {
        $buyer = $request->user();
        abort_unless($buyer->role === 'buyer' && $buyer->status === 'active', 403);

        $product = Product::where('is_active', true)
            ->whereHas('seller', fn ($q) => $q->where('status', 'approved')
                ->whereHas('user', fn ($user) => $user->where('status', 'active')))
            ->findOrFail($productId);
        abort_if($product->seller->user_id === $buyer->id, 403);

        $inquiry = ProductInquiry::firstOrCreate(
            ['buyer_id' => $buyer->id, 'product_id' => $product->id],
            ['seller_id' => $product->seller_id],
        );

        return $this->openInquiry($request, $inquiry);
    }

    /**
     * Open (or reuse) the buyer's general conversation with a store, not tied to one product.
     */
    public function startStoreInquiry(Request $request, string $slug)
    {
        $buyer = $request->user();
        abort_unless($buyer->role === 'buyer' && $buyer->status === 'active', 403);

        $seller = Seller::publiclyVisible()->where('slug', $slug)->firstOrFail();
        abort_if($seller->user_id === $buyer->id, 403);

        $inquiry = ProductInquiry::firstOrCreate(['buyer_id' => $buyer->id, 'seller_id' => $seller->id, 'product_id' => null]);

        return $this->openInquiry($request, $inquiry);
    }

    private function openInquiry(Request $request, ProductInquiry $inquiry)
    {
        $buyer = $request->user();

        if ($request->expectsJson()) {
            // Chat popup (product or store page): return the latest messages instead of leaving the page.
            $messages = $inquiry->messages()->latest('id')->limit(40)->get()->reverse()->values();
            $inquiry->messages()->whereIn('id', $messages->modelKeys())
                ->where('sender_id', '!=', $buyer->id)
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'delivered_at' => now()]);
            $inquiry->messages()->whereIn('id', $messages->modelKeys())
                ->where('sender_id', '!=', $buyer->id)
                ->whereNull('delivered_at')
                ->update(['delivered_at' => now()]);

            \Illuminate\Support\Facades\Cache::put('user_online:'.$buyer->id, now()->timestamp, 120);
            $otherUserId = $inquiry->seller?->user_id;

            return response()->json([
                'type' => 'inquiry',
                'thread_id' => $inquiry->id,
                'send_url' => route('buyer.inquiries.send', $inquiry->id),
                'inbox_url' => route('buyer.messages', ['inquiry' => $inquiry->id]),
                'messages' => $messages->map(fn ($message) => $this->inquiryMessageJson($message, $buyer->id)),
                'typing' => $this->checkOtherUserTyping('inquiry', $inquiry->id, $otherUserId),
            ]);
        }

        return redirect()->route('buyer.messages', ['inquiry' => $inquiry->id]);
    }

    /**
     * Recent conversations for the header chat popup: store chats, product questions and order chats.
     */
    public function popupConversations(Request $request)
    {
        $userId = $request->user()->id;
        \Illuminate\Support\Facades\Cache::put('user_online:'.$userId, now()->timestamp, 120);

        // Mark incoming messages as delivered to the viewer
        ProductInquiryMessage::whereIn('product_inquiry_id', $this->inquiries($request)->select('product_inquiries.id'))
            ->where('sender_id', '!=', $userId)
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        SellerMessage::whereIn('seller_order_id', $this->threads($request)->select('seller_orders.id'))
            ->where('sender_id', '!=', $userId)
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        $preview = fn ($message) => $message ? \Illuminate\Support\Str::limit(($message->sender_id === $userId ? 'You: ' : '').$message->body, 70) : null;
        $isSeller = $request->user()->isSeller();
        $route = $isSeller ? 'seller.popup.' : 'buyer.popup.';

        $inquiries = $this->inquiries($request)->whereHas('messages')
            ->orderByDesc(ProductInquiryMessage::select('created_at')->whereColumn('product_inquiry_id', 'product_inquiries.id')->latest('id')->limit(1))->limit(20)->get()
            ->map(fn ($inquiry) => [
                'key' => 'inquiry-'.$inquiry->id,
                'type' => 'inquiry',
                'thread_id' => $inquiry->id,
                'name' => $isSeller ? ($inquiry->buyer?->name ?? 'Buyer') : ($inquiry->seller?->name ?? 'Seller'),
                'avatar' => $isSeller ? null : $inquiry->seller?->logoUrl(),
                'subtitle' => $inquiry->product_id === null ? 'Store chat' : 'About: '.($inquiry->product?->name ?? 'Product no longer available'),
                'preview' => $preview($inquiry->latestMessage),
                'unread' => (int) $inquiry->unread_count,
                'sort' => $inquiry->latestMessage?->created_at?->getTimestamp() ?? 0,
                'time' => $inquiry->latestMessage?->created_at?->diffForHumans(null, true, true),
                'thread_url' => route($route.'inquiry', $inquiry->id),
            ]);
        $orders = $this->threads($request)->whereHas('messages')
            ->orderByDesc(SellerMessage::select('created_at')->whereColumn('seller_order_id', 'seller_orders.id')->latest('id')->limit(1))->limit(20)->get()
            ->map(fn ($thread) => [
                'key' => 'order-'.$thread->id,
                'type' => 'order',
                'thread_id' => $thread->id,
                'name' => $isSeller ? ($thread->order->buyer?->name ?? 'Buyer') : ($thread->seller?->name ?? 'Seller'),
                'avatar' => $isSeller ? null : $thread->seller?->logoUrl(),
                'subtitle' => 'Order '.$thread->order->reference,
                'preview' => $preview($thread->latestMessage),
                'unread' => (int) $thread->unread_count,
                'sort' => $thread->latestMessage?->created_at?->getTimestamp() ?? 0,
                'time' => $thread->latestMessage?->created_at?->diffForHumans(null, true, true),
                'thread_url' => route($route.'order', $thread->id),
            ]);

        return response()->json([
            'conversations' => $inquiries->concat($orders)->sortByDesc('sort')->take(20)
                ->map(fn ($item) => \Illuminate\Support\Arr::except($item, 'sort'))->values(),
            'unread_total' => $this->unreadTotal($request),
        ]);
    }

    /** One product question or store chat, for the header chat popup. */
    public function popupInquiry(Request $request, int $inquiryId)
    {
        $inquiry = $this->inquiries($request)->findOrFail($inquiryId);
        $isSeller = $request->user()->isSeller();
        $otherUserId = $inquiry->buyer_id === $request->user()->id ? $inquiry->seller?->user_id : $inquiry->buyer_id;

        return $this->popupThread($request, $inquiry->messages(), [
            'type' => 'inquiry',
            'thread_id' => $inquiry->id,
            'title' => $isSeller ? ($inquiry->buyer?->name ?? 'Buyer') : ($inquiry->seller?->name ?? 'Seller'),
            'avatar' => $isSeller ? null : $inquiry->seller?->logoUrl(),
            'subtitle' => $inquiry->product_id === null ? 'Store chat' : 'About: '.($inquiry->product?->name ?? 'Product no longer available'),
            'send_url' => route($isSeller ? 'seller.chat.inquiry.send' : 'buyer.inquiries.send', $inquiry->id),
            'inbox_url' => route($isSeller ? 'seller.chat' : 'buyer.messages', ['inquiry' => $inquiry->id]),
        ], 'inquiry', $inquiry->id, $otherUserId);
    }

    /** One order conversation, for the header chat popup. */
    public function popupOrder(Request $request, int $contactId)
    {
        $thread = $this->threads($request)->findOrFail($contactId);
        $isSeller = $request->user()->isSeller();
        $otherUserId = $thread->order?->buyer_id === $request->user()->id ? $thread->seller?->user_id : $thread->order?->buyer_id;

        return $this->popupThread($request, $thread->messages(), [
            'type' => 'order',
            'thread_id' => $thread->id,
            'title' => $isSeller ? ($thread->order->buyer?->name ?? 'Buyer') : ($thread->seller?->name ?? 'Seller'),
            'avatar' => $isSeller ? null : $thread->seller?->logoUrl(),
            'subtitle' => 'Order '.$thread->order->reference,
            'send_url' => route($isSeller ? 'seller.chat.send' : 'buyer.messages.send', $thread->id),
            'inbox_url' => route($isSeller ? 'seller.chat' : 'buyer.messages', ['contact' => $thread->id]),
        ], 'order', $thread->id, $otherUserId);
    }

    private function popupThread(Request $request, $messageQuery, array $details, string $type = 'inquiry', int $threadId = 0, ?int $otherUserId = null)
    {
        $userId = $request->user()->id;
        $messages = (clone $messageQuery)->latest('id')->limit(40)->get()->reverse()->values();

        (clone $messageQuery)->whereIn('id', $messages->modelKeys())
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'delivered_at' => now()]);

        (clone $messageQuery)->whereIn('id', $messages->modelKeys())
            ->where('sender_id', '!=', $userId)
            ->whereNull('delivered_at')
            ->update(['delivered_at' => now()]);

        \Illuminate\Support\Facades\Cache::put('user_online:'.$userId, now()->timestamp, 120);

        return response()->json($details + [
            'messages' => $messages->map(fn ($message) => $this->inquiryMessageJson($message, $userId)),
            'unread_total' => $this->unreadTotal($request),
            'typing' => $this->checkOtherUserTyping($type, $threadId, $otherUserId),
        ]);
    }

    /** Unread count for the popup badge: buyers use their nav count, sellers count across their threads. */
    private function unreadTotal(Request $request): ?int
    {
        if (!$request->user()->isSeller()) {
            return app(\App\Services\BuyerNavigationCounts::class)->forUser($request->user())['messages'] ?? null;
        }

        return (int) $this->inquiries($request)->get()->sum('unread_count') + (int) $this->threads($request)->get()->sum('unread_count');
    }

    /** Check if the other user in the thread is currently typing */
    private function checkOtherUserTyping(string $type, int $id, ?int $otherUserId): array
    {
        if (!$otherUserId) {
            return ['is_typing' => false, 'name' => null];
        }

        $data = \Illuminate\Support\Facades\Cache::get("chat_typing:{$type}:{$id}:{$otherUserId}");
        if ($data && (now()->timestamp - ($data['at'] ?? 0)) <= 4) {
            return ['is_typing' => true, 'name' => $data['name'] ?? 'The other party'];
        }

        return ['is_typing' => false, 'name' => null];
    }

    /** Endpoint to record user typing activity */
    public function recordTyping(Request $request)
    {
        $request->validate([
            'type' => 'required|in:order,inquiry',
            'id' => 'required|integer',
            'typing' => 'required|boolean',
        ]);

        $userId = $request->user()->id;
        $type = $request->input('type');
        $id = (int) $request->input('id');
        $isTyping = $request->boolean('typing');
        $key = "chat_typing:{$type}:{$id}:{$userId}";

        if ($isTyping) {
            $name = $request->user()->isSeller()
                ? ($request->user()->seller?->name ?? $request->user()->name)
                : $request->user()->name;

            \Illuminate\Support\Facades\Cache::put($key, [
                'name' => $name,
                'at' => now()->timestamp,
            ], 6);
        } else {
            \Illuminate\Support\Facades\Cache::forget($key);
        }

        return response()->json(['ok' => true]);
    }

    /** @param ProductInquiryMessage|SellerMessage $message */
    private function inquiryMessageJson($message, int $viewerId): array
    {
        $status = 'sent';
        if ($message->read_at) {
            $status = 'seen';
        } elseif ($message->delivered_at) {
            $status = 'delivered';
        }

        return [
            'id' => $message->id,
            'body' => $message->body,
            'mine' => $message->sender_id === $viewerId,
            'status' => $status,
            'read_at' => $message->read_at?->toISOString(),
            'delivered_at' => $message->delivered_at?->toISOString(),
            'time' => $message->created_at->setTimezone('Asia/Manila')->format('M j, g:i A'),
        ];
    }

    public function sendInquiry(Request $request, int $inquiryId)
    {
        $inquiry = ProductInquiry::visibleTo($request->user())->findOrFail($inquiryId);
        $request->merge(['message' => trim((string) $request->input('message'))]);
        $data = $request->validate(['message' => 'required|string|max:5000']);
        $userId = $request->user()->id;

        $otherUserId = $inquiry->buyer_id === $userId ? $inquiry->seller?->user_id : $inquiry->buyer_id;
        $isDelivered = $otherUserId && \Illuminate\Support\Facades\Cache::has('user_online:'.$otherUserId);

        $message = $inquiry->messages()->create([
            'sender_id' => $userId,
            'body' => $data['message'],
            'delivered_at' => $isDelivered ? now() : null,
        ]);
        $inquiry->touch();

        \Illuminate\Support\Facades\Cache::forget("chat_typing:inquiry:{$inquiry->id}:{$userId}");

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->inquiryMessageJson($message, $userId)], 201);
        }

        return redirect()->route($request->user()->isSeller() ? 'seller.chat' : 'buyer.messages', ['inquiry' => $inquiry->id]);
    }
}
