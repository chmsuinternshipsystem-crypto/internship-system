<x-app-layout>
    @php
        $canManage = in_array(auth()->user()->role ?? null, \App\Support\InternshipRoles::operationalManagerRoles(), true);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Requirements') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Requirements Overview') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Review each student\'s submitted documents and track requirement progress.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        <x-page-card compact>
            @if ($mandatoryDocsCount === 0)
                <p class="text-sm text-gray-600">
                    {{ __('No mandatory required documents are defined. Please configure them first in the Required Documents module.') }}
                </p>
            @else
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:justify-between sm:items-center">
                    <x-search-bar
                        :action="route('compliance.index')"
                        placeholder="{{ __('Student number, name, or section...') }}"
                        :value="$search"
                        hxTarget="#compliance-ajax-mount"
                        :showClear="($search !== '' && $search !== null) || (isset($filterStatus) && $filterStatus !== '' && $filterStatus !== null) || (isset($sectionFilter) && $sectionFilter !== '') || ($riskFilter ?? false)"
                        sticky
                    >
                        <span class="filter-label">{{ __('Section') }}</span>
                        <select name="section" class="filter-select">
                            <option value="">{{ __('All') }}</option>
                            @foreach (['A', 'B', 'C', 'D'] as $sec)
                                <option value="{{ $sec }}" @selected(($sectionFilter ?? '') === $sec)>{{ __('Section') }} {{ $sec }}</option>
                            @endforeach
                        </select>
                        <span class="filter-label">{{ __('Status') }}</span>
                        <select name="status" class="filter-select">
                            <option value="">{{ __('All') }}</option>
                            <option value="compliant" @selected($filterStatus === 'compliant')>{{ __('Complete') }}</option>
                            <option value="partially_compliant" @selected($filterStatus === 'partially_compliant')>{{ __('In Progress') }}</option>
                            <option value="non_compliant" @selected($filterStatus === 'non_compliant')>{{ __('Needs Submission') }}</option>
                        </select>
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-600 ml-2">
                            <input type="checkbox" name="risk" value="1" @checked($riskFilter ?? false) class="h-4 w-4 text-red-500 border-gray-300 rounded focus:ring-red-500">
                            {{ __('At Risk') }}
                        </label>
                        @if ((auth()->user()?->role ?? '') === 'instructor')
                        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-600 ml-2">
                            <input type="hidden" name="my_students" value="0">
                            <input type="checkbox" name="my_students" value="1" @checked($myStudents ?? true) class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600"
                                   onchange="this.closest('form').requestSubmit()">
                            {{ __('My Students') }}
                        </label>
                        @endif
                    </x-search-bar>
                </div>
                <div class="mb-4 rounded-lg bg-gray-50/80 border border-gray-100 px-4 py-3 text-[11px] text-gray-600 space-y-2">
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                        <span class="font-semibold text-gray-800 uppercase tracking-wider min-w-[44px]">{{ __('Status') }}:</span>
                        <span class="inline-flex items-center gap-1.5"><span class="status-badge badge-compliant px-1.5 py-0.5 text-[10px]">{{ __('Complete') }}</span><span class="text-gray-300">—</span>{{ __('All documents submitted') }}</span>
                        <span class="inline-flex items-center gap-1.5"><span class="status-badge badge-partial px-1.5 py-0.5 text-[10px]">{{ __('In Progress') }}</span><span class="text-gray-300">—</span>{{ __('Some documents still pending') }}</span>
                        <span class="inline-flex items-center gap-1.5"><span class="status-badge badge-non-compliant px-1.5 py-0.5 text-[10px]">{{ __('Needs Submission') }}</span><span class="text-gray-300">—</span>{{ __('No documents submitted yet') }}</span>
                    </div>
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1">
                        <span class="font-semibold text-gray-800 uppercase tracking-wider min-w-[44px]">{{ __('Risk') }}:</span>
                        <span class="inline-flex items-center gap-1.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-red-50 text-red-700 border border-red-100">CRITICAL</span><span class="text-gray-300">—</span>{{ __('No submissions + attendance concerns') }}</span>
                        <span class="inline-flex items-center gap-1.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-100">WARNING</span><span class="text-gray-300">—</span>{{ __('Partial submissions or anomalies') }}</span>
                        <span class="inline-flex items-center gap-1.5"><span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">LOW</span><span class="text-gray-300">—</span>{{ __('On track') }}</span>
                    </div>
                </div>
                <div id="compliance-ajax-mount">
                    @include('compliance.partials.ajax-list')
                </div>
            @endif
        </x-page-card>
    </div>
</x-app-layout>
