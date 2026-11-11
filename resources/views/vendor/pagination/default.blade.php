@if ($paginator->hasPages())
    <nav class="pagination-shared" role="navigation" aria-label="Pagination Navigation" style="display: flex; align-items: center; justify-content: space-between; gap: 14px; width: 100%; padding: 16px 0; font-size: 12px; color: #6f6382; flex-wrap: wrap;">
        <span class="pagination-shared-summary" style="flex: 1 1 0; min-width: 0; text-align: left;">
            {{ $paginator->firstItem() }}&ndash;{{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </span>

        <div class="pagination-shared-controls" style="flex: 0 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 12px;">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="pagination-shared-arrow disabled" aria-disabled="true" aria-label="Previous page" title="Previous page" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e1dde7; background: #faf9fc; color: #a39baa; opacity: 0.35; cursor: not-allowed;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 15px; height: 15px;" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </span>
            @else
                <a class="pagination-shared-arrow" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page" title="Previous page" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e1dde7; background: #fff; color: #292334; text-decoration: none; cursor: pointer; transition: all .15s ease;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 15px; height: 15px;" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </a>
            @endif

            {{-- Centered Page Number --}}
            <span class="pagination-shared-current-page" aria-current="page" style="font-weight: 600; color: #292334; font-size: 12px; white-space: nowrap; padding: 0 4px;">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a class="pagination-shared-arrow" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page" title="Next page" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e1dde7; background: #fff; color: #292334; text-decoration: none; cursor: pointer; transition: all .15s ease;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 15px; height: 15px;" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </a>
            @else
                <span class="pagination-shared-arrow disabled" aria-disabled="true" aria-label="Next page" title="Next page" style="display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border-radius: 8px; border: 1px solid #e1dde7; background: #faf9fc; color: #a39baa; opacity: 0.35; cursor: not-allowed;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 15px; height: 15px;" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
                </span>
            @endif
        </div>

        <span class="pagination-shared-spacer" aria-hidden="true" style="flex: 1 1 0; min-width: 0;"></span>
    </nav>
@endif

