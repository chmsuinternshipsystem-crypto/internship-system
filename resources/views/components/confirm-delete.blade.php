@props([
    'action',
    'message',
    'title' => null,
    /** Optional stable id for a11y (e.g. per-row model id) */
    'dialogId' => null,
])
@php
    $did = $dialogId ?? 'confirm-delete-'.substr(md5((string) $action), 0, 12);
    $heading = $title ?? __('Confirm deletion');
@endphp
<div x-data="{ confirmOpen: false }" class="block w-full">
    <button
        type="button"
        {{ $attributes->merge(['class' => 'action-menu-item action-danger']) }}
        @click="confirmOpen = true"
    >
        {{ $slot }}
    </button>
    <template x-teleport="body">
        <div
            x-show="confirmOpen"
            x-cloak
            x-transition
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            @keydown.escape.window="confirmOpen = false"
        >
            <div class="absolute inset-0 bg-gray-900/50" @click="confirmOpen = false" aria-hidden="true"></div>
            <div
                class="relative z-10 w-full max-w-md rounded-xl border border-gray-200 bg-white p-5 shadow-xl"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="{{ $did }}-title"
                @click.stop
            >
                <div class="flex items-center justify-between">
                    <h3 id="{{ $did }}-title" class="text-base font-semibold text-gray-900">{{ $heading }}</h3>
                    <button type="button" @click="confirmOpen = false" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <i class="bi bi-x-lg text-sm"></i>
                    </button>
                </div>
                <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
                <div class="mt-5 flex flex-wrap justify-end gap-2">
                    <x-button variant="secondary" size="sm" type="button" @click="confirmOpen = false">
                        {{ __('Cancel') }}
                    </x-button>
                    <form method="POST" action="{{ $action }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <x-button variant="danger" size="sm" type="submit">
                            {{ __('Delete') }}
                        </x-button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
