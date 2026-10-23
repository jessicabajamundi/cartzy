{{-- One product comment or reply. Expects: $comment, $product, $reactions, $canComment, $isReply --}}
@php
    $reaction = $reactions[$comment->id] ?? ['counts' => [], 'total' => 0, 'mine' => null];
    $byProductSeller = $comment->user_id === $product->seller->user_id;
    $authorName = $byProductSeller ? $product->seller->name : ($comment->user?->name ?? 'Buyer');
    $emoji = \App\Models\ProductComment::REACTIONS;
@endphp
<article class="pd-comment {{ $isReply ? 'is-reply' : '' }}" id="comment-{{ $comment->id }}">
    <span class="pd-avatar {{ $byProductSeller ? 'is-seller' : '' }}" aria-hidden="true">{{ mb_strtoupper(mb_substr($authorName, 0, 1)) }}</span>
    <div>
        <div class="pd-comment-head">
            <strong>{{ $authorName }}</strong>
            @if($byProductSeller)<span class="pd-seller-tag">Seller</span>@endif
            <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->setTimezone('Asia/Manila')->format('M j, Y · g:i A') }}</time>
        </div>
        <p>{{ $comment->body }}</p>
        <div class="pd-comment-actions" data-reactions data-react-url="{{ route('products.comments.react', $comment->id) }}">
            @if($canComment['reply'])
                <div class="pd-react">
                    <button type="button" class="pd-react-toggle {{ $reaction['mine'] ? 'is-active' : '' }}" data-react-toggle aria-haspopup="true" aria-expanded="false">
                        <span data-react-mine>{{ $reaction['mine'] ? $emoji[$reaction['mine']].' '.ucfirst($reaction['mine']) : 'React' }}</span>
                    </button>
                    <div class="pd-react-picker" role="group" aria-label="Choose a reaction" hidden>
                        @foreach($emoji as $type => $icon)
                            <form method="POST" action="{{ route('products.comments.react', $comment->id) }}">@csrf
                                <button type="submit" name="type" value="{{ $type }}" title="{{ ucfirst($type) }}" aria-label="{{ ucfirst($type) }}" aria-pressed="{{ $reaction['mine'] === $type ? 'true' : 'false' }}">{{ $icon }}</button>
                            </form>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="pd-reply-toggle" data-reply-to="{{ $comment->parent_id ?? $comment->id }}" data-reply-name="{{ $authorName }}">Reply</button>
            @endif
            <span class="pd-react-summary" data-react-summary @if(!$reaction['total']) hidden @endif>
                <span data-react-icons>@foreach(array_slice(array_keys($reaction['counts']), 0, 3) as $type){{ $emoji[$type] }}@endforeach</span>
                <span data-react-total>{{ $reaction['total'] }}</span>
            </span>
        </div>

        @unless($isReply)
            @if($comment->replies->isNotEmpty())
                <div class="pd-replies">
                    @foreach($comment->replies as $reply)
                        @include('partials.product-comment', ['comment' => $reply, 'isReply' => true])
                    @endforeach
                </div>
            @endif
            @if($canComment['reply'])
                <form class="pd-reply-form" method="POST" action="{{ route('products.comments.store', $product->id) }}" data-reply-form="{{ $comment->id }}" hidden>@csrf
                    <input type="hidden" name="parent_id" value="{{ $comment->id }}">
                    <label class="pd-chat-sr" for="reply-{{ $comment->id }}">Reply to {{ $authorName }}</label>
                    <textarea id="reply-{{ $comment->id }}" name="body" rows="1" maxlength="2000" required placeholder="Write a reply…"></textarea>
                    <div><button type="button" class="pd-reply-cancel" data-reply-cancel>Cancel</button><button class="pd-button" type="submit">Reply</button></div>
                </form>
            @endif
        @endunless
    </div>
</article>
