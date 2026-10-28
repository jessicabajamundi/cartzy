<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\LogisticsMessage;
use App\Models\LogisticsProvider;
use App\Models\PickupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Seller ↔ logistics hub chat. Messages live in logistics_messages with the seller as the
 * hub's contact: sender_type "contact" is the seller, "hub" is the logistics hub.
 */
class LogisticsChatController extends Controller
{
    public function index(Request $request)
    {
        $hubs = $this->hubs($request);
        $current = $hubs->firstWhere('id', (int) $request->query('hub')) ?? $hubs->first();
        $messages = $current ? $this->thread($request, $current['provider']) : [];
        $shop = $request->user()->seller;

        return view('seller.logistics-chat', compact('hubs', 'current', 'messages', 'shop'));
    }

    public function conversations(Request $request)
    {
        return response()->json([
            'conversations' => $this->hubs($request)->map(fn ($hub) => collect($hub)->except('provider'))->values(),
            'unread_total' => $this->unreadTotal($request),
        ]);
    }

    public function show(Request $request, LogisticsProvider $provider)
    {
        abort_unless($provider->isApproved(), 404);
        $messages = $this->thread($request, $provider);

        return response()->json([
            'title' => $provider->name,
            'subtitle' => $this->handlesPickups($request, $provider) ? 'Handles your pickups' : 'Logistics hub',
            'send_url' => route('seller.logistics.send', $provider),
            'inbox_url' => route('seller.logistics', ['hub' => $provider->id]),
            'messages' => $messages,
            'unread_total' => $this->unreadTotal($request),
        ]);
    }

    public function send(Request $request, LogisticsProvider $provider)
    {
        abort_unless($provider->isApproved(), 404);
        $data = $request->validate(['message' => ['required', 'string', 'max:5000']]);

        $message = LogisticsMessage::create([
            'logistics_provider_id' => $provider->id,
            'contact_user_id' => $request->user()->id,
            'sender_type' => 'contact',
            'message' => trim($data['message']),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => $this->present($message)]);
        }

        return redirect()->route('seller.logistics', ['hub' => $provider->id]);
    }

    /** Approved hubs, the ones handling this seller's pickups or already chatting first. */
    private function hubs(Request $request)
    {
        $userId = $request->user()->id;
        $pickupHubIds = $this->pickupHubIds($request);

        return LogisticsProvider::where('status', 'approved')
            ->withCount(['messages as unread' => fn ($q) => $q->where('contact_user_id', $userId)->where('sender_type', 'hub')->whereNull('read_at')])
            ->get()
            ->map(function ($provider) use ($userId, $pickupHubIds) {
                $latest = LogisticsMessage::where('logistics_provider_id', $provider->id)
                    ->where('contact_user_id', $userId)->latest('id')->first();
                $handles = $pickupHubIds->contains($provider->id);

                return [
                    'id' => $provider->id,
                    'provider' => $provider,
                    'name' => $provider->name,
                    'subtitle' => $handles ? 'Handles your pickups' : 'Logistics hub',
                    'preview' => $latest ? Str::limit(($latest->sender_type === 'contact' ? 'You: ' : '').$latest->message, 70) : 'Ask about pickups, schedules, or parcels.',
                    'unread' => (int) $provider->unread,
                    'time' => $latest?->created_at?->diffForHumans(null, true, true),
                    'thread_url' => route('seller.logistics.thread', $provider),
                    'sort' => [$latest ? 0 : 1, $handles ? 0 : 1, -($latest?->id ?? 0), $provider->name],
                ];
            })
            ->sortBy('sort')
            ->map(fn ($hub) => collect($hub)->except('sort')->all())
            ->values();
    }

    /** Messages with one hub, oldest first; opening the thread marks the hub's replies read. */
    private function thread(Request $request, LogisticsProvider $provider): array
    {
        $query = LogisticsMessage::where('logistics_provider_id', $provider->id)
            ->where('contact_user_id', $request->user()->id);

        (clone $query)->where('sender_type', 'hub')->whereNull('read_at')->update(['read_at' => now()]);

        return $query->orderBy('id')->get()->map(fn ($message) => $this->present($message))->all();
    }

    private function present(LogisticsMessage $message): array
    {
        $mine = $message->sender_type === 'contact';

        return [
            'id' => $message->id,
            'mine' => $mine,
            'body' => $message->message,
            'time' => $message->created_at->format('M j, g:i A'),
            'status' => $mine && $message->read_at ? 'seen' : 'sent',
        ];
    }

    private function unreadTotal(Request $request): int
    {
        return LogisticsMessage::where('contact_user_id', $request->user()->id)
            ->where('sender_type', 'hub')->whereNull('read_at')
            ->whereHas('logisticsProvider', fn ($q) => $q->where('status', 'approved'))
            ->count();
    }

    private function pickupHubIds(Request $request)
    {
        $sellerId = $request->user()->seller?->id;

        return $sellerId
            ? PickupRequest::where('seller_id', $sellerId)->whereNotNull('logistics_provider_id')->distinct()->pluck('logistics_provider_id')
            : collect();
    }

    private function handlesPickups(Request $request, LogisticsProvider $provider): bool
    {
        return $this->pickupHubIds($request)->contains($provider->id);
    }
}
