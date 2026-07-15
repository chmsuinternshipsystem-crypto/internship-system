@props(['route' => '', 'type' => ''])

<template x-teleport="body">
    <div x-data="archiveModal('{{ $route }}', '{{ $type }}')"
         x-show="open"
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden"
         @keydown.escape.window="open = false"
         @open-archive-modal.window="onOpen($event.detail)">
        <div x-show="open"
             x-transition:enter="ease-in-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in-out duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"
             @click="open = false">
        </div>
        <div x-show="open"
             x-transition:enter="transform transition ease-in-out duration-300"
             x-transition:enter-start="translate-y-full"
             x-transition:enter-end="translate-y-0"
             x-transition:leave="transform transition ease-in-out duration-200"
             x-transition:leave-start="translate-y-0"
             x-transition:leave-end="translate-y-full"
             class="absolute bottom-0 left-0 right-0 sm:inset-y-0 sm:right-0 sm:left-auto sm:w-full sm:max-w-md bg-white shadow-2xl"
             @click.away="open = false">
            <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Archive') }}</p>
                    <h3 class="text-base font-semibold text-gray-900 mt-0.5" x-text="recordName">{{ __('Archive record') }}</h3>
                </div>
                <button type="button" @click="open = false"
                    class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            <form :action="archiveUrl" method="POST" class="p-5 space-y-4">
                @csrf
                <div>
                    <x-input-label for="archive_reason" :value="__('Reason for archiving')" />
                    <p class="text-xs text-gray-500 mt-1 mb-2">{{ __('Archived records can be restored later from the Archive section.') }}</p>
                    <select name="reason" id="archive_reason"
                        class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option value="">{{ __('Select a reason...') }}</option>
                        <option value="graduated">{{ __('Graduated / Completed') }}</option>
                        <option value="transferred">{{ __('Transferred / Withdrew') }}</option>
                        <option value="inactive">{{ __('Inactive / No longer participating') }}</option>
                        <option value="data_cleanup">{{ __('Data cleanup / Duplicate record') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="open = false"
                        class="rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </button>
                    <button type="submit"
                        class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 shadow-sm">
                        <i class="bi bi-archive me-1"></i>
                        {{ __('Archive') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>


