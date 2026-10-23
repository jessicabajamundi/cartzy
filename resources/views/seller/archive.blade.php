@extends('layouts.seller')
@section('page_title', 'Archive')
@section('content')
<div class="page-heading">
    <div>
        <div class="eyebrow">STORE ARCHIVE</div>
        <h1>Archive</h1>
        <p class="subtitle">Review, restore, or delete items removed from your active catalog.</p>
    </div>
    <div>
        <a class="button secondary" href="{{ route('seller.products') }}">
            &larr; Back to active products
        </a>
    </div>
</div>

<div class="notice">
    Archived products are hidden from your store and cannot be bought by buyers. Their variations, photos, and stock counts remain safely preserved.
</div>

<form class="filters" method="GET" action="{{ route('seller.archive') }}">
    <label>Search archived products
        <input name="search" value="{{ request('search') }}" placeholder="Product name or SKU">
    </label>
    <button class="button" type="submit">Apply filters</button>
    <a class="button secondary" href="{{ route('seller.archive') }}">Reset</a>
</form>

<section class="panel">
    @forelse($products as $product)
        @php($totalUnits = $product->variants->sum('stock'))
        <div class="panel-header">
            <div class="product-row">
                @if($product->coverImage)
                    <img class="product-image" src="{{ str_starts_with($product->coverImage->path, 'http') || str_starts_with($product->coverImage->path, '/') ? $product->coverImage->path : asset('storage/'.$product->coverImage->path) }}" alt="">
                @else
                    <span class="product-image">@include('seller.partials.icon',['name'=>'box'])</span>
                @endif
                <div>
                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <h3>{{ $product->name }}</h3>
                        @if($product->badge)
                            <span class="badge badge-product-highlight">
                                <span>{{ $product->badge }}</span>
                            </span>
                        @endif
                    </div>
                    <small>
                        {{ $product->category?->name }} · Target: <strong>{{ ucfirst($product->gender ?? 'unisex') }}</strong> · {{ $product->variants->count() }} variant(s) · {{ $totalUnits }} unit(s) total
                    </small>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0; flex-wrap: wrap;">
                <span class="badge archived" style="background: var(--brand-light); color: var(--brand-muted); border-color: var(--brand-border);">
                    Archived
                </span>
            </div>
        </div>

        <div class="editor product-list-editor" style="padding: 14px 20px; background: #faf9fb; border-top: 1px solid var(--line); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
            <span class="muted" style="font-size: 12px;">Archived product · Restore to make it visible to buyers</span>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                {{-- Both forms ask for confirmation in the modal below before submitting. --}}
                <form action="{{ route('seller.products.archive', $product) }}" method="POST"
                      data-archive-confirm="restore" data-product-name="{{ $product->name }}" data-product-meta="{{ $product->variants->count() }} variant(s) · {{ $totalUnits }} unit(s) in stock">
                    @csrf
                    <button type="submit" class="button small">
                        Restore product &uarr;
                    </button>
                </form>
                <form action="{{ route('seller.archive.delete', $product) }}" method="POST"
                      data-archive-confirm="delete" data-product-name="{{ $product->name }}" data-product-meta="{{ $product->variants->count() }} variant(s) · {{ $totalUnits }} unit(s) in stock">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button secondary small archive-delete-button" aria-label="Delete {{ $product->name }}">Delete product</button>
                </form>
            </div>
        </div>
    @empty
        <div class="empty">
            <div class="empty-icon">@include('seller.partials.icon',['name'=>'archive'])</div>
            <h3>No archived products</h3>
            <p>You don't have any archived products. When you archive items from your catalog, they will safely appear here.</p>
            <a class="button" href="{{ route('seller.products') }}">Go to active products</a>
        </div>
    @endforelse

    @include('seller.partials.pagination', ['paginator' => $products])
</section>

{{-- Confirm modal for Restore and Delete; filled in by the script below from the clicked product. --}}
<dialog class="archive-confirm" id="archive-confirm" aria-labelledby="archive-confirm-title" aria-describedby="archive-confirm-text">
    <div class="archive-confirm-panel">
        <span class="archive-confirm-icon" aria-hidden="true">
            <svg data-icon="restore" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/></svg>
            <svg data-icon="delete" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6M10 11v6M14 11v6"/></svg>
        </span>
        <h2 id="archive-confirm-title"></h2>
        <p id="archive-confirm-text"></p>
        <div class="archive-confirm-product">
            <strong data-confirm-name></strong>
            <small data-confirm-meta></small>
        </div>
        <ul class="archive-confirm-points" data-confirm-points></ul>
        <div class="archive-confirm-actions">
            <button type="button" class="button secondary" data-confirm-cancel>Cancel</button>
            <button type="button" class="button" data-confirm-ok></button>
        </div>
    </div>
</dialog>
@endsection

@push('scripts')
<script>
(() => {
    const dialog = document.getElementById('archive-confirm');
    if (!dialog) return;
    const copy = {
        restore: {
            title: 'Restore this product?',
            text: 'It will go back to your active catalog and buyers can find and order it again.',
            points: ['Its variations, photos and stock are restored as they were.', 'You can archive it again anytime.'],
            ok: 'Restore product',
        },
        delete: {
            title: 'Delete this product permanently?',
            text: 'It will be removed from your archive and can\'t be restored. This can\'t be undone.',
            points: ['Past orders that include it are kept for your records.', 'Want to keep it hidden instead? Leave it in the archive.'],
            ok: 'Delete permanently',
        },
    };
    let pending = null;
    const ok = dialog.querySelector('[data-confirm-ok]');

    document.querySelectorAll('form[data-archive-confirm]').forEach(form => form.addEventListener('submit', event => {
        if (form.dataset.confirmed === 'true') return;
        event.preventDefault();
        const kind = form.dataset.archiveConfirm;
        const text = copy[kind];
        pending = form;
        dialog.dataset.kind = kind;
        document.getElementById('archive-confirm-title').textContent = text.title;
        document.getElementById('archive-confirm-text').textContent = text.text;
        dialog.querySelector('[data-confirm-name]').textContent = form.dataset.productName;
        dialog.querySelector('[data-confirm-meta]').textContent = form.dataset.productMeta;
        const list = dialog.querySelector('[data-confirm-points]');
        list.replaceChildren(...text.points.map(point => Object.assign(document.createElement('li'), { textContent: point })));
        ok.textContent = text.ok;
        dialog.showModal();
        // Deleting is permanent, so start on Cancel; restoring is safe, so start on the action.
        (kind === 'delete' ? dialog.querySelector('[data-confirm-cancel]') : ok).focus();
    }));

    ok.addEventListener('click', () => {
        if (!pending) return;
        ok.disabled = true;
        ok.textContent = dialog.dataset.kind === 'delete' ? 'Deleting…' : 'Restoring…';
        pending.dataset.confirmed = 'true';
        pending.submit();
    });
    dialog.querySelector('[data-confirm-cancel]').addEventListener('click', () => dialog.close());
    dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
    dialog.addEventListener('close', () => {
        if (pending?.dataset.confirmed !== 'true') pending?.querySelector('button')?.focus();
    });
})();
</script>
@endpush
