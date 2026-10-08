<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
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

    public function index(Request $request)
    {
        $request->validate(['contact' => 'nullable|integer', 'search' => 'nullable|string|max:150']);
        $query = $this->threads($request);
        if ($request->filled('search')) {
            $query->where(fn ($q) => $q
                ->whereHas('order', fn ($o) => $o->where('reference', 'like', '%'.$request->search.'%')->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', '%'.$request->search.'%')))
                ->orWhereHas('seller', fn ($s) => $s->where('name', 'like', '%'.$request->search.'%')));
        }
        $contacts = $query->orderByDesc(SellerMessage::select('created_at')->whereColumn('seller_order_id', 'seller_orders.id')->latest('id')->limit(1))->latest('seller_orders.id')->paginate(15)->withQueryString();
        $current = $request->filled('contact') ? $this->threads($request)->findOrFail($request->contact) : $contacts->first();
        $messages = null;
        if ($current) {
            $messages = $current->messages()->with('sender')->latest('id')->paginate(40, ['*'], 'messages_page')->withQueryString();
            $current->messages()->whereIn('id', $messages->getCollection()->modelKeys())->where('sender_id', '!=', $request->user()->id)->whereNull('read_at')->update(['read_at' => now()]);
            $contacts->each(function ($contact) use ($current) {
                if ($contact->id === $current->id) {
                    $contact->unread_count = 0;
                }
            });
        }

        return view('seller.chat', ['shop' => $request->user()->seller, 'contacts' => $contacts, 'current' => $current, 'messages' => $messages, 'isSeller' => $request->user()->isSeller()]);
    }

    public function send(Request $request, int $contactId)
    {
        $thread = $this->threads($request)->findOrFail($contactId);
        $request->merge(['message' => trim((string) $request->input('message'))]);
        $data = $request->validate(['message' => 'required|string|max:5000']);
        $thread->messages()->create(['sender_id' => $request->user()->id, 'body' => $data['message']]);

        return redirect()->route($request->user()->isSeller() ? 'seller.chat' : 'buyer.messages', ['contact' => $thread->id]);
    }
}
