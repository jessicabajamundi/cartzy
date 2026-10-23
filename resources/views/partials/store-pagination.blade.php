@if($paginator->hasPages())
<nav class="st-pagination" aria-label="{{ $label }}">
    <span>{{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} of {{ $paginator->total() }}</span>
    <div>
        @if($paginator->onFirstPage())<span aria-disabled="true">&lsaquo; Previous</span>@else<a href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo; Previous</a>@endif
        <span aria-current="page">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        @if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}" rel="next">Next &rsaquo;</a>@else<span aria-disabled="true">Next &rsaquo;</span>@endif
    </div>
</nav>
@endif
