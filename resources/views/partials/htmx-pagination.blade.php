@php
    /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Contracts\Pagination\Paginator $paged */
    $target = $hxTarget ?? null;
    $pushUrl = $hxPushUrl ?? false;
    $pushAttr = $pushUrl && $target ? 'hx-push-url="true"' : '';
@endphp

@if ($paged->hasPages())
    @php
        $current = $paged->currentPage();
        $last = $paged->lastPage();
        $start = max(1, $current - 1);
        $end = min($last, $current + 1);
        if ($current <= 2) {
            $end = min($last, 3);
        }
        if ($current >= $last - 1) {
            $start = max(1, $last - 2);
        }
    @endphp

    <div class="pagination-shell mt-4">
        <div class="pagination-summary">
            {{ __('Showing :from to :to of :total', [
                'from' => $paged->firstItem() ?? 0,
                'to' => $paged->lastItem() ?? 0,
                'total' => method_exists($paged, 'total') ? $paged->total() : $paged->count(),
            ]) }}
        </div>
        <div class="pagination-nav">
            @if ($paged->onFirstPage())
                <span class="pagination-btn pagination-btn-disabled">{{ __('Previous') }}</span>
            @else
                <a href="{{ $paged->previousPageUrl() }}"
                   @if ($target) hx-get="{{ $paged->previousPageUrl() }}" hx-target="{{ $target }}" hx-swap="innerHTML" @if ($pushUrl) hx-push-url="true" @endif @endif
                   class="pagination-btn">
                    {{ __('Previous') }}
                </a>
            @endif

            @if ($start > 1)
                <a href="{{ $paged->url(1) }}"
                   @if ($target) hx-get="{{ $paged->url(1) }}" hx-target="{{ $target }}" hx-swap="innerHTML" @if ($pushUrl) hx-push-url="true" @endif @endif
                   class="pagination-btn">
                    1
                </a>
                @if ($start > 2)
                    <span class="pagination-ellipsis">...</span>
                @endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                @if ($page === $current)
                    <span class="pagination-btn pagination-btn-active">{{ $page }}</span>
                @else
                    <a href="{{ $paged->url($page) }}"
                       @if ($target) hx-get="{{ $paged->url($page) }}" hx-target="{{ $target }}" hx-swap="innerHTML" @if ($pushUrl) hx-push-url="true" @endif @endif
                       class="pagination-btn">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            @if ($end < $last)
                @if ($end < $last - 1)
                    <span class="pagination-ellipsis">...</span>
                @endif
                <a href="{{ $paged->url($last) }}"
                   @if ($target) hx-get="{{ $paged->url($last) }}" hx-target="{{ $target }}" hx-swap="innerHTML" @if ($pushUrl) hx-push-url="true" @endif @endif
                   class="pagination-btn">
                    {{ $last }}
                </a>
            @endif

            @if ($paged->hasMorePages())
                <a href="{{ $paged->nextPageUrl() }}"
                   @if ($target) hx-get="{{ $paged->nextPageUrl() }}" hx-target="{{ $target }}" hx-swap="innerHTML" @if ($pushUrl) hx-push-url="true" @endif @endif
                   class="pagination-btn">
                    {{ __('Next') }}
                </a>
            @else
                <span class="pagination-btn pagination-btn-disabled">{{ __('Next') }}</span>
            @endif
        </div>
    </div>
@endif

