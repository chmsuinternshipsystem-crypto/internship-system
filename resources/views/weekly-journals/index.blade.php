<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Monitoring') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Weekly Journals') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Review and approve student weekly journal submissions.') }}</p>
            </div>
        </div>
    </x-slot>

    <x-page-card compact>
        <x-search-bar
            :action="route('weekly-journals.index')"
            :value="$search"
            placeholder="{{ __('Student number, name...') }}"
            :showClear="$search !== '' || $status !== '' || $section !== ''"
            sticky
            hxTarget="#weekly-journals-ajax-mount"
        >
            <span class="filter-label">{{ __('Section') }}</span>
            <select name="section" class="filter-select">
                <option value="">{{ __('All') }}</option>
                @foreach (['A', 'B', 'C', 'D'] as $sec)
                    <option value="{{ $sec }}" @selected(($section ?? '') === $sec)>{{ __('Section') }} {{ $sec }}</option>
                @endforeach
            </select>

            <span class="filter-label">{{ __('Status') }}</span>
            <select name="status" class="filter-select">
                <option value="">{{ __('All') }}</option>
                <option value="draft" @selected(($status ?? '') === 'draft')>{{ __('Draft') }}</option>
                <option value="submitted" @selected(($status ?? '') === 'submitted')>{{ __('Submitted') }}</option>
                <option value="reviewed" @selected(($status ?? '') === 'reviewed')>{{ __('Reviewed') }}</option>
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

        <div id="weekly-journals-ajax-mount">
            @include('weekly-journals.partials.ajax-list')
        </div>
    </x-page-card>
</x-app-layout>