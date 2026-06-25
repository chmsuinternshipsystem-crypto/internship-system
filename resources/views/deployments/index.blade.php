<x-app-layout>
    @php
        $canManage = in_array(auth()->user()->role ?? null, \App\Support\InternshipRoles::operationalManagerRoles(), true);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Deployment management') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Deployments') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Assign students to partner companies and track internship schedules.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <x-page-card compact>
        <div class="flex flex-col sm:flex-row gap-3 mb-3">
            <div class="flex-1">
                <x-search-bar
                    :action="route('deployments.index')"
                    placeholder="{{ __('Student, company, dates (e.g. Apr 2026), status, remarks...') }}"
                    :value="$search ?? ''"
                    hxTarget="#deployments-ajax-mount"
                    :debounceMs="900"
                    sticky
                >
                    <select name="status" class="filter-select min-w-[8rem]">
                        <option value="">{{ __('All Statuses') }}</option>
                        <option value="active" @selected(($statusFilter ?? '') === 'active')>{{ __('Active') }}</option>
                        <option value="completed" @selected(($statusFilter ?? '') === 'completed')>{{ __('Completed') }}</option>
                        <option value="pending" @selected(($statusFilter ?? '') === 'pending')>{{ __('Pending') }}</option>
                    </select>
                    @if ((auth()->user()?->role ?? '') === 'instructor')
                    <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 whitespace-nowrap">
                        <input type="hidden" name="my_students" value="0">
                        <input type="checkbox" name="my_students" value="1" @checked($myStudents ?? true) class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600"
                               onchange="this.closest('form').requestSubmit()">
                        {{ __('My Students') }}
                    </label>
                    @endif
                </x-search-bar>
            </div>
            @if (($industries ?? false) && $industries->isNotEmpty())
                <div class="sm:w-48 shrink-0">
                    <select
                        name="industry"
                        class="block w-full rounded-lg border-2 border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
                        hx-get="{{ route('deployments.index') }}"
                        hx-target="#deployments-ajax-mount"
                        hx-trigger="change"
                        hx-include="[name='search'],[name='status']"
                    >
                        <option value="">{{ __('All industries') }}</option>
                        @foreach ($industries as $ind)
                            <option value="{{ $ind->id }}" @selected(($industryFilter ?? '') == $ind->id)>{{ $ind->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
        <div id="deployments-ajax-mount">
            @include('deployments.partials.ajax-list')
        </div>
    </x-page-card>
</x-app-layout>
