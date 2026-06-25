<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Monitoring') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Daily Time Records') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Select a student and month to view their attendance records.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-page-card compact>
                <div id="dtr-list-mount">
                    @include('dtr.partials.student-list')
                </div>
            </x-page-card>
        </div>
    </div>
</x-app-layout>
