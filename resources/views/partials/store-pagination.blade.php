@if($paginator->hasPages())
<nav class="st-pagination" aria-label="{{ $label }}">
    <span class="st-pagination-summary">{{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
    <div class="st-pagination-controls">
        @if($paginator->onFirstPage())
            <span class="st-pagination-arrow disabled" aria-disabled="true" aria-label="Previous page" title="Previous page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
            </span>
        @else
            <a class="st-pagination-arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page" title="Previous page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
            </a>
        @endif

        <span class="st-pagination-current-page" aria-current="page">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        @if($paginator->hasMorePages())
            <a class="st-pagination-arrow" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page" title="Next page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            </a>
        @else
            <span class="st-pagination-arrow disabled" aria-disabled="true" aria-label="Next page" title="Next page">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            </span>
        @endif
    </div>
    <span class="st-pagination-spacer" aria-hidden="true"></span>
</nav>
@endif
