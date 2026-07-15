<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Certificates') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Student Certificates') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Upload and verify student completion certificates.') }}</p>
            </div>
            @if (in_array(auth()->user()->role ?? '', \App\Support\InternshipRoles::programAdministratorRoles(), true))
                <a href="{{ route('certificates.create') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest btn-primary">
                    <i class="bi bi-plus-lg me-2"></i> {{ __('Upload Certificate') }}
                </a>
            @endif
        </div>
    </x-slot>

    <x-page-card compact>
        <x-search-bar
            :action="route('certificates.index')"
            :value="$search"
            placeholder="{{ __('Student number, name...') }}"
            :showClear="$search !== '' || $type !== '' || $status !== ''"
            sticky
            hxTarget="#certificates-ajax-mount"
        >
            <span class="filter-label">{{ __('Type') }}</span>
            <select name="type" class="filter-select">
                <option value="">{{ __('All') }}</option>
                <option value="completion" @selected(($type ?? '') === 'completion')>{{ __('Completion') }}</option>
                <option value="merit" @selected(($type ?? '') === 'merit')>{{ __('Merit') }}</option>
                <option value="attendance" @selected(($type ?? '') === 'attendance')>{{ __('Attendance') }}</option>
                <option value="special" @selected(($type ?? '') === 'special')>{{ __('Special') }}</option>
                <option value="other" @selected(($type ?? '') === 'other')>{{ __('Other') }}</option>
            </select>

            <span class="filter-label">{{ __('Status') }}</span>
            <select name="status" class="filter-select">
                <option value="">{{ __('All') }}</option>
                <option value="pending" @selected($status === 'pending')>{{ __('Pending') }}</option>
                <option value="verified" @selected($status === 'verified')>{{ __('Verified') }}</option>
                <option value="rejected" @selected($status === 'rejected')>{{ __('Rejected') }}</option>
            </select>
        </x-search-bar>

        <div id="certificates-ajax-mount">
            @include('certificates.partials.ajax-list')
        </div>
    </x-page-card>
</x-app-layout>