<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Communication') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Announcements') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Post official updates and reminders for internship requirements.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <x-page-header
        :actionHref="$canManage ? route('announcements.create') : null"
        actionLabel="{{ __('Add Announcement') }}"
    />

    <x-page-card compact>
        <x-search-bar
            :action="route('announcements.index')"
            :value="$search"
            :placeholder="__('Title, body, audience, author, date...')"
            hxTarget="#announcement-ajax-mount"
            :showClear="$hasActiveFilters"
            sticky
        >
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <label for="filter_audience" class="filter-label">{{ __('Visible to') }}</label>
                <select
                    id="filter_audience"
                    name="audience"
                    class="filter-select min-w-[10rem]"
                >
                    <option value="">{{ __('All') }}</option>
                    @foreach ($audienceOptions as $val => $label)
                        <option value="{{ $val }}" @selected($audience === $val)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <label for="filter_author" class="filter-label">{{ __('Author') }}</label>
                <select
                    id="filter_author"
                    name="author"
                    class="filter-select min-w-[10rem]"
                >
                    <option value="">{{ __('All') }}</option>
                    @foreach ($filterAuthors as $u)
                        <option value="{{ $u->id }}" @selected((string) $authorId === (string) $u->id)>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-search-bar>

        <div id="announcement-ajax-mount">
            @include('announcements.partials.ajax-list')
        </div>
    </x-page-card>
</x-app-layout>
