<x-app-layout>

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Documentation') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Required Documents') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Configure the list of documents students must submit.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <x-page-header
        :actionHref="$canManage ? route('required-documents.create') : null"
        actionLabel="{{ __('Add Required Document') }}"
    />

    <x-page-card compact>
        <x-search-bar
            :action="route('required-documents.index')"
            :placeholder="__('Search by name, description, or phase...')"
            :value="request('search')"
            hxTarget="#required-documents-ajax-mount"
        />
        <div id="required-documents-ajax-mount">
            @include('required-documents.partials.ajax-list')
        </div>
    </x-page-card>
</x-app-layout>
