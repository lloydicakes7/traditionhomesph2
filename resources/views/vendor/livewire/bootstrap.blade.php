<div>
    @php
        $paginatorName = $paginator->getPageName();
        $paginatorCount = isset($this->numberOfPaginatorsRendered[$paginatorName]) ? $this->numberOfPaginatorsRendered[$paginatorName]++ : ($this->numberOfPaginatorsRendered[$paginatorName] = 1);

        // Build a deduplicated sliding window of page numbers.
        // This avoids a bug in Laravel 7's UrlWindow where slider/last (or first/slider)
        // overlap near the edges, causing duplicate page numbers like "199 200 199 200".
        $lastPage = $paginator->lastPage();
        $currentPage = $paginator->currentPage();
        $onEachSide = 2; // pages to show on each side of the current page

        $windowPages = [];

        // Always show page 1
        $windowPages[] = 1;

        // Calculate the sliding window range (excluding page 1 and lastPage)
        $start = max(2, $currentPage - $onEachSide);
        $end   = min($lastPage - 1, $currentPage + $onEachSide);

        // Ellipsis before the window?
        if ($start > 2) {
            $windowPages[] = '...';
        }

        // Window pages
        for ($i = $start; $i <= $end; $i++) {
            $windowPages[] = $i;
        }

        // Ellipsis after the window?
        if ($end < $lastPage - 1) {
            $windowPages[] = '...';
        }

        // Always show last page (if more than 1 page)
        if ($lastPage > 1) {
            $windowPages[] = $lastPage;
        }
    @endphp

    <div class="thha-pagination-wrapper d-flex flex-wrap align-items-center justify-content-between" style="gap:0.5rem;">
        @if ($paginator->hasPages())
            {{-- Info summary — hidden on very small screens --}}
            <div class="text-muted small thha-pagination-info">
                @if ($paginator->total() > 0)
                    <span>
                        Showing <strong>{{ $paginator->firstItem() }}</strong>
                    </span>
                    <span>
                        to <strong>{{ $paginator->lastItem() }}</strong>
                    </span>
                    <span>
                        of <strong>{{ number_format($paginator->total()) }}</strong>
                    </span>
                @else
                    <span>No results</span>
                @endif
            </div>
        @endif

        @if ($paginator->hasPages())
            <nav class="thha-pagination-nav">
                <ul class="pagination pagination-sm mb-0 thha-pagination-list">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link thha-page-link" aria-hidden="true"><i class="fas fa-fw fa-chevron-left"></i></span>
                        </li>
                    @else
                        <li class="page-item">
                            <button type="button" dusk="previousPage{{ $paginatorName == 'page' ? '' : '.' . $paginatorName }}" class="page-link thha-page-link" wire:click="previousPage('{{ $paginatorName }}')" wire:loading.attr="disabled" rel="prev" aria-label="@lang('pagination.previous')">
                                <i class="fas fa-fw fa-chevron-left"></i>
                            </button>
                        </li>
                    @endif

                    {{-- Pagination Elements (deduplicated sliding window) --}}
                    @foreach ($windowPages as $page)
                        @if (is_string($page) && $page === '...')
                            <li class="page-item disabled thha-page-ellipsis" aria-disabled="true"><span class="page-link thha-page-link">…</span></li>
                        @else
                            @if ($page == $currentPage)
                                <li class="page-item active thha-page-num" wire:key="paginator-{{ $paginatorName }}-{{ $paginatorCount }}-page-{{ $page }}" aria-current="page"><span class="page-link thha-page-link">{{ $page }}</span></li>
                            @else
                                <li class="page-item thha-page-num" wire:key="paginator-{{ $paginatorName }}-{{ $paginatorCount }}-page-{{ $page }}"><button type="button" class="page-link thha-page-link" wire:click="gotoPage({{ $page }}, '{{ $paginatorName }}')">{{ $page }}</button></li>
                            @endif
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <button type="button" dusk="nextPage{{ $paginatorName == 'page' ? '' : '.' . $paginatorName }}" class="page-link thha-page-link" wire:click="nextPage('{{ $paginatorName }}')" wire:loading.attr="disabled" rel="next" aria-label="@lang('pagination.next')">
                                <i class="fas fa-fw fa-chevron-right"></i>
                            </button>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link thha-page-link" aria-hidden="true"><i class="fas fa-fw fa-chevron-right"></i></span>
                        </li>
                    @endif
                </ul>
            </nav>
        @endif
    </div>

    {{-- Responsive pagination CSS — compact on mobile, scrollable if needed --}}
    @once
        @push('css')
        <style>
        .thha-pagination-wrapper {
            width: 100%;
        }

        /* Info summary: hide on < 480px */
        @media (max-width: 479.98px) {
            .thha-pagination-info {
                display: none;
            }
        }

        /* Nav: allow horizontal scroll on small screens */
        .thha-pagination-nav {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            max-width: 100%;
        }

        .thha-pagination-list {
            flex-wrap: nowrap;
            white-space: nowrap;
        }

        /* Compact page links on small screens — rely on scroll, don't hide specific items */
        @media (max-width: 575.98px) {
            .thha-page-link {
                padding: 0.25rem 0.45rem !important;
                font-size: 0.75rem !important;
                min-width: 28px;
                text-align: center;
            }
        }

        @media (max-width: 399.98px) {
            .thha-page-link {
                padding: 0.2rem 0.35rem !important;
                font-size: 0.7rem !important;
                min-width: 24px;
            }
        }
        </style>
        @endpush
    @endonce
</div>
