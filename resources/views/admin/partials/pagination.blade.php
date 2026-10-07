@if($paginator->hasPages())
<nav class="pagination" aria-label="Pagination">
    <span>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
    <div class="actions">
        @if($paginator->previousPageUrl())<a class="button subtle" href="{{ $paginator->previousPageUrl() }}">Previous</a>@endif
        <span>Page {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
        @if($paginator->nextPageUrl())<a class="button subtle" href="{{ $paginator->nextPageUrl() }}">Next</a>@endif
    </div>
</nav>
@endif
