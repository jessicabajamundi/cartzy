@if($paginator->hasPages())
<nav class="pagination" aria-label="Pagination"><span>{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span><div class="inline">@if($paginator->onFirstPage())<span class="muted">Previous</span>@else<a href="{{ $paginator->previousPageUrl() }}">← Previous</a>@endif @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}">Next →</a>@else<span class="muted">Next</span>@endif</div></nav>
@endif
