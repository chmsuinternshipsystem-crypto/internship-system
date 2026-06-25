<x-app-layout>
    @php
        $canManage = in_array(auth()->user()->role ?? null, \App\Support\InternshipRoles::operationalManagerRoles(), true);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Partners') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Companies') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Maintain internship partner companies, structured contact details, and partnership status.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <x-page-header
        :actionHref="$canManage ? route('companies.create') : null"
        actionLabel="{{ __('Add Company') }}">
        @if($canManage)
            <a href="{{ route('companies.import') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-wider bg-white hover:bg-gray-50 shadow-sm transition-colors">
                <i class="bi bi-upload"></i>
                {{ __('Import') }}
            </a>
        @endif
    </x-page-header>

    @if ($canManage)
        <div class="mb-3 flex justify-end">
            <a href="{{ route('company-industries.index') }}" class="inline-flex items-center gap-1.5 text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors">
                <i class="bi bi-tags"></i> {{ __('Manage Industries') }}
            </a>
        </div>
    @endif

    <x-page-card compact>
        <x-search-bar
            :action="route('companies.index')"
            placeholder="{{ __('Company, contact person, email, phone, address, or status...') }}"
            :value="$search ?? ''"
            hxTarget="#companies-ajax-mount"
            sticky
        >
            <label class="inline-flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer select-none">
                <input type="checkbox" name="geofenced" value="1" @checked(request('geofenced')) class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600">
                <span>{{ __('Geofenced only') }}</span>
            </label>
            @if (($industries ?? false) && $industries->isNotEmpty())
                <select name="industry" class="filter-select rounded-lg border-2 border-gray-200 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">{{ __('All industries') }}</option>
                    @foreach ($industries as $ind)
                        <option value="{{ $ind->id }}" @selected(($industryFilter ?? '') == $ind->id)>{{ $ind->name }}</option>
                    @endforeach
                </select>
            @endif
        </x-search-bar>
        <div id="companies-ajax-mount">
            @include('companies.partials.ajax-list')
        </div>
    </x-page-card>
</x-app-layout>
