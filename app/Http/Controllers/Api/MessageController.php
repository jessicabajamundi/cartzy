<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductInquiry;
use App\Models\ProductInquiryMessage;
use App\Models\Seller;
use App\Models\SellerMessage;
use App\Models\SellerOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MessageController extends Controller
{
    private function resolveProductImage(?Product $product): ?string
    {
        if (!$product) return null;
        $path = $product->coverImage?->path ?? $product->images?->first()?->path;
        if (!$path) return null;
        return preg_match('~^(https?://|/)~', $path) ? $path : Storage::disk('public')->url($path);
    }

    /**
     * List all conversation threads for the current buyer.
     * Merges product inquiries and order chats.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Product Inquiries
        $inquiries = ProductInquiry::where('buyer_id', $userId)
            ->with(['seller', 'product.coverImage', 'latestMessage.sender'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_id', '!=', $userId)->whereNull('read_at')])
            ->get()
            ->map(function ($inq) use ($userId) {
                $lastMsg = $inq->latestMessage;
                $timestamp = $lastMsg?->created_at ?? $inq->created_at;
                return [
                    'id' => $inq->id,
                    'type' => 'inquiry',
                    'key' => 'inquiry-' . $inq->id,
                    'store' => $inq->seller?->name ?? 'Cartzy Seller',
                    'seller_id' => $inq->seller_id,
                    'product_id' => $inq->product_id,
                    'product_name' => $inq->product?->name,
                    'product_image' => $this->resolveProductImage($inq->product),
                    'order_reference' => null,
                    'order_id' => null,
                    'avatar' => $inq->seller?->logoUrl(),
                    'last_message' => $lastMsg?->body ?? 'Conversation started',
                    'last_message_at' => $timestamp?->toIso8601String(),
                    'time' => $timestamp?->diffForHumans(null, true, true) ?? '',
                    'unread_count' => (int) $inq->unread_count,
                    'sort_ts' => $timestamp?->timestamp ?? 0,
                ];
            });

        // 2. Order Conversations
        $orderThreads = SellerOrder::whereHas('order', fn ($q) => $q->where('buyer_id', $userId))
            ->with(['seller', 'order', 'latestMessage.sender'])
            ->withCount(['messages as unread_count' => fn ($q) => $q->where('sender_id', '!=', $userId)->whereNull('read_at')])
            ->get()
            ->map(function ($so) use ($userId) {
                $lastMsg = $so->latestMessage;
                $timestamp = $lastMsg?->created_at ?? $so->created_at;
                return [
                    'id' => $so->id,
                    'type' => 'order',
                    'key' => 'order-' . $so->id,
                    'store' => $so->seller?->name ?? 'Cartzy Store',
                    'seller_id' => $so->seller_id,
                    'product_id' => null,
                    'product_name' => null,
                    'product_image' => null,
                    'order_reference' => $so->order?->reference ?? 'Order #' . $so->id,
                    'order_id' => $so->order_id,
                    'avatar' => $so->seller?->logoUrl(),
                    'last_message' => $lastMsg?->body ?? 'Order conversation started',
                    'last_message_at' => $timestamp?->toIso8601String(),
                    'time' => $timestamp?->diffForHumans(null, true, true) ?? '',
                    'unread_count' => (int) $so->unread_count,
                    'sort_ts' => $timestamp?->timestamp ?? 0,
                ];
            });

        $conversations = $inquiries->concat($orderThreads)
            ->sortByDesc('sort_ts')
            ->values();

        $unreadTotal = $conversations->sum('unread_count');

        return response()->json([
            'conversations' => $conversations,
            'unread_total' => $unreadTotal,
        ]);
    }

    /**
     * Get total unread count for badges.
     */
    public function unreadCount(Request $request)
    {
        $userId = $request->user()->id;

        $inquiryUnread = ProductInquiryMessage::whereIn(
            'product_inquiry_id',
            ProductInquiry::where('buyer_id', $userId)->select('id')
        )
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();

        $orderUnread = SellerMessage::whereIn(
            'seller_order_id',
            SellerOrder::whereHas('order', fn ($q) => $q->where('buyer_id', $userId))->select('id')
        )
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'unread_total' => $inquiryUnread + $orderUnread,
        ]);
    }

    /**
     * Get messages for one thread (inquiry or order).
     * Automatically marks messages from the other user as read.
     */
    public function thread(Request $request)
    {
        $request->validate([
            'type' => 'required|in:inquiry,order',
            'id' => 'required|integer',
        ]);

        $userId = $request->user()->id;
        $type = $request->query('type');
        $id = (int) $request->query('id');

        if ($type === 'inquiry') {
            $inquiry = ProductInquiry::where('buyer_id', $userId)
                ->with(['seller', 'product.coverImage'])
                ->findOrFail($id);

            // Mark unread as read
            $inquiry->messages()
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'delivered_at' => now()]);

            $rawMessages = $inquiry->messages()
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();

            $threadDetails = [
                'id' => $inquiry->id,
                'type' => 'inquiry',
                'store' => $inquiry->seller?->name ?? 'Cartzy Seller',
                'avatar' => $inquiry->seller?->logoUrl(),
                'product_id' => $inquiry->product_id,
                'product_name' => $inquiry->product?->name,
                'product_image' => $this->resolveProductImage($inquiry->product),
                'order_reference' => null,
            ];
        } else {
            $sellerOrder = SellerOrder::whereHas('order', fn ($q) => $q->where('buyer_id', $userId))
                ->with(['seller', 'order'])
                ->findOrFail($id);

            // Mark unread as read
            $sellerOrder->messages()
                ->where('sender_id', '!=', $userId)
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'delivered_at' => now()]);

            $rawMessages = $sellerOrder->messages()
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();

            $threadDetails = [
                'id' => $sellerOrder->id,
                'type' => 'order',
                'store' => $sellerOrder->seller?->name ?? 'Cartzy Store',
                'avatar' => $sellerOrder->seller?->logoUrl(),
                'product_id' => null,
                'product_name' => null,
                'product_image' => null,
                'order_reference' => $sellerOrder->order?->reference ?? 'Order #' . $sellerOrder->id,
            ];
        }

        $formattedMessages = $rawMessages->map(function ($msg) use ($userId) {
            $isMe = $msg->sender_id === $userId;
            return [
                'id' => $msg->id,
                'body' => $msg->body,
                'sender_id' => $msg->sender_id,
                'sender_name' => $msg->sender?->name ?? ($isMe ? 'You' : 'Seller'),
                'is_me' => $isMe,
                'created_at' => $msg->created_at?->toIso8601String(),
                'time' => $msg->created_at?->format('h:i A') ?? '',
                'date' => $msg->created_at?->format('M d, Y') ?? '',
                'delivered_at' => $msg->delivered_at?->toIso8601String(),
                'read_at' => $msg->read_at?->toIso8601String(),
                'status' => $msg->read_at ? 'read' : ($msg->delivered_at ? 'delivered' : 'sent'),
            ];
        });

        return response()->json([
            'thread' => $threadDetails,
            'messages' => $formattedMessages,
        ]);
    }

    /**
     * Send a message to an existing thread.
     */
    public function send(Request $request)
    {
        $request->validate([
            'type' => 'required|in:inquiry,order',
            'id' => 'required|integer',
            'message' => 'required|string|max:5000',
        ]);

        $userId = $request->user()->id;
        $type = $request->input('type');
        $id = (int) $request->input('id');
        $body = trim($request->input('message'));

        if ($type === 'inquiry') {
            $inquiry = ProductInquiry::where('buyer_id', $userId)->findOrFail($id);
            $msg = $inquiry->messages()->create([
                'sender_id' => $userId,
                'body' => $body,
                'delivered_at' => now(),
            ]);
            $inquiry->touch();
        } else {
            $sellerOrder = SellerOrder::whereHas('order', fn ($q) => $q->where('buyer_id', $userId))->findOrFail($id);
            $msg = $sellerOrder->messages()->create([
                'sender_id' => $userId,
                'body' => $body,
                'delivered_at' => now(),
            ]);
            $sellerOrder->touch();
        }

        return response()->json([
            'message' => [
                'id' => $msg->id,
                'body' => $msg->body,
                'sender_id' => $msg->sender_id,
                'sender_name' => 'You',
                'is_me' => true,
                'created_at' => $msg->created_at?->toIso8601String(),
                'time' => $msg->created_at?->format('h:i A') ?? '',
                'date' => $msg->created_at?->format('M d, Y') ?? '',
                'status' => 'delivered',
            ],
        ], 201);
    }

    /**
     * Start or fetch an inquiry from a product details page or store.
     */
    public function inquire(Request $request)
    {
        $request->validate([
            'product_id' => 'nullable|integer',
            'seller_id' => 'nullable|integer',
            'message' => 'nullable|string|max:5000',
        ]);

        $userId = $request->user()->id;

        if ($request->filled('product_id')) {
            $product = Product::findOrFail($request->product_id);
            $inquiry = ProductInquiry::firstOrCreate(
                ['buyer_id' => $userId, 'product_id' => $product->id],
                ['seller_id' => $product->seller_id]
            );
        } elseif ($request->filled('seller_id')) {
            $seller = Seller::findOrFail($request->seller_id);
            $inquiry = ProductInquiry::firstOrCreate(
                ['buyer_id' => $userId, 'seller_id' => $seller->id, 'product_id' => null]
            );
        } else {
            return response()->json(['message' => 'product_id or seller_id is required'], 422);
        }

        if ($request->filled('message')) {
            $body = trim($request->input('message'));
            if ($body !== '') {
                $inquiry->messages()->create([
                    'sender_id' => $userId,
                    'body' => $body,
                    'delivered_at' => now(),
                ]);
                $inquiry->touch();
            }
        }

        $request->merge(['type' => 'inquiry', 'id' => $inquiry->id]);
        return $this->thread($request);
    }
}
