@props(['id'])

<div class="relative inline-flex items-center" x-data="{ open: false }" @click.outside="open = false">
    <div x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-x-3"
         x-transition:enter-end="opacity-100 translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-3"
         class="action-menu-overlay absolute right-full mr-2 z-50 flex items-center gap-0.5 bg-white border border-gray-200 rounded-lg shadow-lg px-1.5 py-1 whitespace-nowrap"
         @click="open = false">
        {{ $slot }}
    </div>
    <button @click="open = !open" type="button"
            class="action-menu-trigger shrink-0" :aria-expanded="open">
        <i class="bi bi-three-dots-vertical"></i>
    </button>
</div>
