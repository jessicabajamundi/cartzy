<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="font-heading text-2xl font-bold text-gray-900">Messages</h2>
        <p class="mt-1 text-sm text-gray-500">Talk to your seller about your order.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <button type="button" data-go="orders" class="{{ $btnGhost }}">Back to My Orders</button>
        <a href="{{ route('buyer.messages', request()->only('contact', 'search')) }}" class="{{ $btnGhost }}">Refresh inbox</a>
    </div>
</div>
<div class="overflow-hidden rounded-3xl border border-[#E1DDE7] bg-white shadow-sm" data-buyer-inbox>
    <div class="grid grid-cols-1 xl:grid-cols-3">
        <aside class="border-b border-[#E1DDE7] xl:border-b-0 xl:border-r" aria-label="Conversations">
            <div class="border-b border-gray-100 p-4">
                <h3 class="text-lg font-bold text-gray-900">Conversations ({{ $contacts->total() }})</h3>
                <form method="GET" action="{{ route('buyer.messages') }}" class="mt-3 flex gap-2">
                    <input type="search" name="search" value="{{ request('search') }}" aria-label="Search store or order" placeholder="Search store or order..." class="{{ $inputCls }} min-w-0">
                    <button type="submit" class="{{ $btnGhost }}">Find</button>
                </form>
            </div>
            <div class="max-h-64 overflow-y-auto xl:max-h-[520px]">
                @forelse($contacts as $contact)
                    <a href="{{ route('buyer.messages', ['contact' => $contact->id, 'search' => request('search')]) }}"
                       class="flex gap-3 border-b border-gray-100 p-4 transition hover:bg-[#F6F4F8] {{ $current?->id === $contact->id ? 'bg-[#F1EFF5]' : '' }}"
                       @if($current?->id === $contact->id) aria-current="true" @endif>
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#F1EFF5] font-bold text-[#564B68]">{{ mb_substr($contact->seller?->name ?? 'S', 0, 1) }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-2">
                                <p class="truncate text-sm font-bold text-gray-900">{{ $contact->seller?->name }}</p>
                                @if($contact->unread_count)<span class="rounded-full bg-[#564B68] px-2 py-0.5 text-xs text-white">{{ $contact->unread_count }}</span>@endif
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ $contact->order->reference }}</p>
                            <p class="mt-2 truncate text-xs text-gray-500">{{ $contact->latestMessage?->body ?? 'Start a conversation about this order' }}</p>
                        </div>
                    </a>
                @empty
                    <p class="p-5 text-sm text-gray-500">{{ request('search') ? 'No conversations found. Try another store or order number.' : 'Your conversations will appear here after you place an order.' }}</p>
                    @if(request('search'))<a href="{{ route('buyer.messages') }}" class="m-4 {{ $btnGhost }}">Clear search</a>@endif
                @endforelse
            </div>
            @if($contacts->hasPages())<div class="p-3">{{ $contacts->links() }}</div>@endif
        </aside>
        <div class="min-w-0 xl:col-span-2">
            @if($current)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 bg-[#FAF9FB] p-5">
                    <div class="min-w-0">
                        <h3 class="text-xl font-bold text-gray-900">{{ $current->seller?->name }}</h3>
                        <p class="mt-1 text-xs text-gray-500">{{ $current->order->reference }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ Str::limit($current->items->first()?->product_name, 60) }}</p>
                    </div>
                    <span class="rounded-full bg-[#F1EFF5] px-3 py-1 text-xs font-bold text-[#564B68]">{{ ucfirst(str_replace('_', ' ', $current->status)) }}</span>
                </div>
                <div id="buyer-message-list" class="h-80 space-y-4 overflow-y-auto bg-[#FAF9FB] p-5 sm:h-96" aria-label="Message history">
                    @if($messages->hasPages()){{ $messages->links() }}@endif
                    @forelse($messages->getCollection()->reverse() as $message)
                        <div class="flex flex-col {{ $message->sender_id === auth()->id() ? 'items-end' : 'items-start' }}">
                            <p class="max-w-[90%] whitespace-pre-wrap break-words rounded-2xl px-4 py-3 text-sm {{ $message->sender_id === auth()->id() ? 'bg-[#564B68] text-white' : 'border border-[#E1DDE7] bg-white text-gray-800' }}">{{ $message->body }}</p>
                            <p class="mt-1 text-[11px] text-gray-500">{{ $message->sender_id === auth()->id() ? 'You' : $current->seller?->name }} · {{ $message->created_at->timezone('Asia/Manila')->format('M j, g:i A') }}</p>
                        </div>
                    @empty
                        <div class="flex h-full flex-col items-center justify-center text-center">
                            <span class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#F1EFF5] text-[#564B68]" aria-hidden="true"><svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path d="M21 11.5a8.4 8.4 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.4 8.4 0 01-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.4 8.4 0 013.8-.9h.5a8.5 8.5 0 018 8v.5z"/></svg></span>
                            <h3 class="text-xl font-bold text-gray-900">Start the conversation</h3>
                            <p class="mt-2 max-w-xs text-sm text-gray-500">Ask your seller about this order. Replies will appear here.</p>
                        </div>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('buyer.messages.send', $current) }}" class="border-t border-gray-100 p-4">
                    @csrf
                    <label for="buyer-message" class="mb-2 block text-sm font-bold text-gray-700">Your message</label>
                    <div class="flex items-end gap-2">
                        <textarea id="buyer-message" name="message" rows="2" maxlength="5000" required placeholder="Write your message..." class="{{ $inputCls }} min-w-0">{{ old('message') }}</textarea>
                        <button type="submit" class="{{ $btnPrimary }}">Send</button>
                    </div>
                </form>
            @else
                <div class="flex min-h-80 flex-col items-center justify-center p-8 text-center">
                    <h3 class="text-xl font-bold text-gray-900">Your conversations start here</h3>
                    <p class="mt-2 text-sm text-gray-500">Choose an order conversation to read messages and send a reply.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@push('scripts')
<script>
    requestAnimationFrame(() => {
        const list = document.getElementById('buyer-message-list');
        if (list && !new URLSearchParams(location.search).has('messages_page')) list.scrollTop = list.scrollHeight;
    });
</script>
@endpush
