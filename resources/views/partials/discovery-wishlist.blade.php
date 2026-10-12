@php($saved = in_array($product['ref'], $wishlistRefs ?? [], true))
<form method="POST" action="{{ route('wishlist.toggle', [], false) }}" class="discovery-wishlist-form">
    @csrf
    @foreach(['ref', 'name', 'price', 'image', 'variation'] as $field)
        <input type="hidden" name="{{ $field }}" value="{{ $product[$field] ?? '' }}">
    @endforeach
<button type="submit" class="discovery-wishlist"
        data-wishlist-product="{{ json_encode($product) }}"
        aria-pressed="{{ $saved ? 'true' : 'false' }}"
        aria-label="{{ $saved ? 'Remove from wishlist: ' : 'Add to wishlist: ' }}{{ $product['name'] }}"
        title="{{ $saved ? 'Remove from wishlist' : 'Save to wishlist' }}">
    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8Z"/>
    </svg>
</button>
</form>
