<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Report') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Attendance Export') }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.attendance-export', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-pdf me-1"></i>{{ __('PDF') }}</a>
                <a href="{{ route('reports.attendance-export', array_merge(request()->query(), ['export' => 'csv'])) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-filetype-csv me-1"></i>{{ __('CSV') }}</a>
                <a onclick="window.print()" class="btn btn-sm btn-outline-secondary cursor-pointer"><i class="bi bi-printer me-1"></i>{{ __('Print') }}</a>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="layout-section-y">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-page-card compact>
                <x-search-bar
                    :action="route('reports.attendance-export')"
                    :value="request('search')"
                    placeholder="{{ __('Name or student number...') }}"
                    :showClear="request()->hasAny(['date_from', 'date_to', 'section', 'search'])"
                    hxTarget="#attendance-export-mount"
                >
                    <span class="filter-label">{{ __('Date From') }}</span>
                    <input data-flatpickr name="date_from" value="{{ request('date_from') }}" class="search-input-date" />
                    <span class="filter-label">{{ __('Date To') }}</span>
                    <input data-flatpickr name="date_to" value="{{ request('date_to') }}" class="search-input-date" />
                    <span class="filter-label">{{ __('Section') }}</span>
                    <select name="section" class="filter-select">
                        <option value="">{{ __('All sections') }}</option>
                        @foreach ($sections as $sec)
                            <option value="{{ $sec }}" @selected(request('section') === $sec)>{{ $sec }}</option>
                        @endforeach
                    </select>
                </x-search-bar>
            </x-page-card>

            <x-page-card compact>
                <div id="attendance-export-mount">
                    @include('reports.partials.attendance-export-table')
                </div>
            </x-page-card>
        </div>
    </div>
</x-app-layout>
