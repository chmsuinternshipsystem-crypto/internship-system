@props([
    'action',
    'message',
    'title' => null,
    /** Optional stable id for a11y (e.g. per-row model id) */
    'dialogId' => null,
    /** Override wrapper div class (default: block w-full) */
    'wrapperClass' => 'block w-full',
])
@php
    $did = $dialogId ?? 'confirm-delete-'.substr(md5((string) $action), 0, 12);
    $heading = $title ?? __('Confirm deletion');
@endphp
<div x-data="{ confirmOpen: false }" class="{{ $wrapperClass }}">
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
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4"
            @keydown.escape.window="confirmOpen = false"
        >
            <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="confirmOpen = false" aria-hidden="true"></div>
            <div
                x-show="confirmOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                class="relative z-10 w-full max-w-md rounded-2xl border border-gray-100 bg-white p-6 shadow-2xl"
                role="alertdialog"
                aria-modal="true"
                aria-labelledby="{{ $did }}-title"
                @click.stop
            >
                <div class="text-center">
                    <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-red-50 text-red-600 mb-4 ring-1 ring-red-100">
                        <i class="bi bi-exclamation-triangle text-2xl"></i>
                    </span>
                    <h3 id="{{ $did }}-title" class="text-lg font-semibold text-gray-900">{{ $heading }}</h3>
                    <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
                </div>
                <div class="mt-6 flex justify-center gap-3">
                    <button type="button" @click="confirmOpen = false"
                        class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        {{ __('Cancel') }}
                    </button>
                    <form method="POST" action="{{ $action }}" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="px-5 py-2.5 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition-colors shadow-sm btn-lift">
                            <i class="bi bi-trash me-1.5"></i>
                            {{ __('Delete') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
