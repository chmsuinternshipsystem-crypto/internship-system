<x-app-layout>
    @php
        $panelMode = request()->boolean('panel');
        $viewerIsChairperson = strtolower((string) (auth()->user()?->role ?? '')) === \App\Support\InternshipRoles::CHAIRPERSON;
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Requirements') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Student Documents') }}</h2>
                <p class="text-sm text-gray-500">{{ $student->student_number }} &mdash; {{ $student->name }}</p>
            </div>
        </div>
    </x-slot>

    <div
        class="py-8 space-y-4"
        x-data="{ panelOpen: false, loadingPanel: false }"
        x-on:open-panel.window="panelOpen = true; loadingPanel = true"
        x-on:panel-loaded.window="loadingPanel = false"
        x-on:close-panel.window="panelOpen = false; htmx.ajax('GET', window.location.href, {target: '#docs-table-wrapper', swap: 'innerHTML'})"
        x-on:close-review-panel.window="panelOpen = false; htmx.ajax('GET', window.location.href, {target: '#docs-table-wrapper', swap: 'innerHTML'})"
        x-effect="document.body.style.overflow = panelOpen ? 'hidden' : ''"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (! $panelMode)
                <a href="{{ $backUrl }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-emerald-700 transition-colors mb-4 block">
                    <i class="bi bi-arrow-left"></i> {{ $backLabel }}
                </a>
            @endif
            <x-page-card compact>
                @if ($documentsPaginator->total() === 0)
                    <p class="text-sm text-gray-600">{{ __('No required documents are defined for this student.') }}</p>
                @else
                    <div class="mb-4 rounded-lg bg-gray-50/80 border border-gray-100 px-4 py-2.5 text-[11px] text-gray-600 flex flex-wrap items-center gap-x-5 gap-y-1">
                        <span class="font-semibold text-gray-800 uppercase tracking-wider">{{ __('Legend') }}:</span>
                        <span class="inline-flex items-center gap-1.5"><i class="bi bi-file-earmark-text text-gray-300 text-sm"></i>{{ __('Not submitted') }}</span>
                        <span class="text-gray-300">|</span>
                        <span class="inline-flex items-center gap-1.5"><i class="bi bi-upload text-sky-600 text-sm"></i>{{ __('Uploaded') }}</span>
                        <span class="text-gray-300">|</span>
                        <span class="inline-flex items-center gap-1.5"><i class="bi bi-hourglass-split text-amber-500 text-sm"></i>{{ __('In review') }}</span>
                        <span class="text-gray-300">|</span>
                        <span class="inline-flex items-center gap-1.5"><i class="bi bi-check-circle-fill text-emerald-600 text-sm"></i>{{ __('Completed') }}</span>
                    </div>
                    <div id="docs-table-wrapper">
                        @include('student-documents.partials.checklist-table')
                    </div>
                @endif
            </x-page-card>
        </div>

        <div
            x-show="panelOpen"
            x-cloak
            class="fixed right-0 top-0 z-40 flex flex-col bg-white border-l border-gray-200 shadow-[-4px_0_12px_rgba(0,0,0,0.06)] h-screen !mt-0"
            style="width: calc(100vw - 16rem);"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            @keydown.escape.window="panelOpen = false"
        >
            <div x-show="loadingPanel" class="flex-1 flex items-center justify-center">
                <div class="flex flex-col items-center gap-2 text-gray-400">
                    <div class="w-8 h-8 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin"></div>
                    <p class="text-sm">{{ __('Loading...') }}</p>
                </div>
            </div>
            <div x-show="!loadingPanel" class="flex-1 flex flex-col">
                <div id="review-panel-content" class="flex-1 flex flex-col"></div>
            </div>
        </div>
    </div>
</x-app-layout>
