<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Archive') }}</p>
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">{{ __('Archived Records') }}</h2>
            <p class="text-sm text-gray-500">{{ __('View and restore archived students, companies, and deployments.') }}</p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filters --}}
            <div class="flex flex-wrap items-center gap-3">
                <form id="archive-filters" method="GET" action="{{ route('archive.index') }}"
                      hx-get="{{ route('archive.index') }}" hx-target="#archive-content" hx-swap="innerHTML" hx-push-url="true"
                      hx-trigger="change from:select">
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="relative">
                            <select name="type"
                                class="appearance-none rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm font-medium text-gray-700 focus:border-emerald-300 focus:ring-emerald-200/50 focus:outline-none">
                                <option value="all" {{ $type === 'all' ? 'selected' : '' }}>{{ __('All Types') }}</option>
                                <option value="students" {{ $type === 'students' ? 'selected' : '' }}>{{ __('Students') }}</option>
                                <option value="companies" {{ $type === 'companies' ? 'selected' : '' }}>{{ __('Companies') }}</option>
                                <option value="deployments" {{ $type === 'deployments' ? 'selected' : '' }}>{{ __('Deployments') }}</option>
                            </select>
                            <i class="bi bi-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>
                        <div class="relative">
                            <select name="school_year"
                                class="appearance-none rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm font-medium text-gray-700 focus:border-emerald-300 focus:ring-emerald-200/50 focus:outline-none">
                                <option value="">{{ __('All School Years') }}</option>
                                @foreach ($schoolYears as $sy)
                                    <option value="{{ $sy }}" {{ $schoolYear === $sy ? 'selected' : '' }}>{{ $sy }}</option>
                                @endforeach
                            </select>
                            <i class="bi bi-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                        </div>
                        <span class="text-xs text-gray-500">
                            {{ $students->total() + $companies->total() + $deployments->total() }} {{ __('archived records') }}
                        </span>
                    </div>
                </form>
            </div>
            <div id="archive-content">
                @include('archive.partials.list')
            </div>
        </div>
    </div>
</x-app-layout>
@push('scripts')
<script>
    document.addEventListener('htmx:afterSwap', function (e) {
        if (e.detail.target && e.detail.target.id === 'archive-content') {
            if (window.Alpine) Alpine.initTree(e.detail.target);
        }
    });
</script>
@endpush
