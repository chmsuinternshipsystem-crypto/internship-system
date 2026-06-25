<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Documents') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Document Queue') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Documents currently assigned to your role for review or action.') }}</p>
            </div>
        </div>
    </x-slot>

    <div
        class="py-8 space-y-4"
        x-data="{ reviewOpen: false, loadingReview: false }"
        x-on:close-review-panel.window="reviewOpen = false; htmx.ajax('GET', '{{ route('student-documents.queue', request()->query()) }}', {target: '#queue-table-wrapper', swap: 'innerHTML'})"
        x-on:reload-queue.window="htmx.ajax('GET', '{{ route('student-documents.queue', request()->query()) }}', {target: '#queue-table-wrapper', swap: 'innerHTML'})"
        x-on:open-review-panel.window="reviewOpen = true; loadingReview = true"
        x-on:review-panel-loaded.window="loadingReview = false"
        x-effect="document.body.style.overflow = reviewOpen ? 'hidden' : ''"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-card compact>
                <x-search-bar
                    :action="route('student-documents.queue')"
                    :value="$studentQ ?? ''"
                    placeholder="{{ __('e.g. 2024… or last name') }}"
                    :showClear="($studentQ ?? '') !== '' || $statusFilter !== '' || $requiredDocumentFilter > 0"
                    hxTarget="#queue-table-wrapper"
                >
                    <span class="filter-label">{{ __('Queue status') }}</span>
                    <select name="status" class="filter-select min-w-[10rem]">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($statusOptions as $st)
                            <option value="{{ $st }}" @selected($statusFilter === $st)>{{ str($st)->replace('_', ' ')->title() }}</option>
                        @endforeach
                    </select>
                    <span class="filter-label">{{ __('Document type') }}</span>
                    <select name="required_document_id" class="filter-select min-w-[12rem]">
                        <option value="0">{{ __('All') }}</option>
                        @foreach ($requiredDocuments as $rd)
                            <option value="{{ $rd->id }}" @selected((int) $requiredDocumentFilter === (int) $rd->id)>{{ $rd->name }}</option>
                        @endforeach
                    </select>
                    <span class="filter-label">{{ __('Section') }}</span>
                    <select name="section" class="filter-select">
                        <option value="">{{ __('All') }}</option>
                        @foreach ($sectionOptions as $sec)
                            <option value="{{ $sec }}" @selected(($sectionFilter ?? '') === $sec)>{{ __('Section') }} {{ $sec }}</option>
                        @endforeach
                    </select>
                    @if ((auth()->user()?->role ?? '') === 'instructor')
                    <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 whitespace-nowrap">
                        <input type="checkbox" name="my_students" value="1" @checked($myStudents ?? false) class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600">
                        {{ __('My Students') }}
                    </label>
                    @endif
                </x-search-bar>

                <div id="queue-table-wrapper">
                    @include('student-documents.partials.queue-table')
                </div>
            </x-page-card>
        </div>

        <div
            x-show="reviewOpen"
            x-cloak
            class="fixed right-0 top-0 z-40 flex flex-col bg-white border-l border-gray-200 shadow-[-4px_0_12px_rgba(0,0,0,0.06)] h-screen !mt-0"
            style="width: calc(100vw - 16rem);"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            @keydown.escape.window="reviewOpen = false"
        >
            <div x-show="loadingReview" class="flex-1 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <div class="w-8 h-8 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-sm">{{ __('Opening document...') }}</p>
                </div>
            </div>
            <div x-show="!loadingReview" class="flex-1 flex flex-col">
                <div id="review-panel-content" class="flex-1 flex flex-col">
                    {{-- Review panel content loaded here via HTMX --}}
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                // Must match the browser URL: the controller may clear server-side focus when the row
                // is not in the filtered queue, but the query string still carries focus_sd for deep links.
                var params = new URLSearchParams(window.location.search);
                var sd = parseInt(params.get('focus_sd') || '0', 10) || {{ (int) request()->query('focus_sd', 0) }};
                if (!sd) return;
                function scrollRowIntoMain(row, main) {
                    if (!main) {
                        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }
                    var rowRect = row.getBoundingClientRect();
                    var mainRect = main.getBoundingClientRect();
                    var padding = 48;
                    var targetTop = main.scrollTop + (rowRect.top - mainRect.top) - (mainRect.height / 2) + (rowRect.height / 2);
                    main.scrollTo({ top: Math.max(0, targetTop - padding), behavior: 'smooth' });
                }
                function go() {
                    var row = document.getElementById('queue-row-' + sd);
                    if (!row) return;
                    var main = row.closest('main');
                    scrollRowIntoMain(row, main);
                    row.classList.add('ring-2', 'ring-emerald-500', 'ring-offset-1', 'bg-emerald-50/50');
                    setTimeout(function () {
                        row.classList.remove('ring-2', 'ring-emerald-500', 'ring-offset-1', 'bg-emerald-50/50');
                    }, 5500);
                }
                function run() {
                    go();
                    if (!document.getElementById('queue-row-' + sd)) {
                        setTimeout(go, 150);
                        setTimeout(go, 400);
                        setTimeout(go, 900);
                    }
                }
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', run);
                } else {
                    run();
                }
            })();
        </script>
    @endpush
</x-app-layout>
