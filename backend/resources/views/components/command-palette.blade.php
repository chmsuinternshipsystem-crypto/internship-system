@props([])

<div x-data="commandPalette()"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-[60] overflow-hidden"
     @keydown.escape.window="close"
     @keydown.window="onKeydown($event)"
     @open-palette.window="onPaletteOpen()">
    <div x-show="open"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"
         @click="close">
    </div>
    <div class="absolute top-[15%] left-1/2 -translate-x-1/2 w-full max-w-lg px-4">
        <div x-show="open"
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden"
             @click.away="close">
            <div class="relative">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <input x-ref="searchInput"
                       x-model="query"
                       @input="search()"
                       type="text"
                       class="w-full border-0 bg-transparent py-4 pl-11 pr-4 text-base text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0"
                       placeholder="Search students, companies, pages..."
                       autocomplete="off">
                <button @click="close" class="absolute right-3 top-1/2 -translate-y-1/2 rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            <div x-show="loading" class="px-4 pb-3">
                <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-500 rounded-full animate-pulse" style="width: 40%"></div>
                </div>
            </div>
            <div x-show="results.length > 0" class="border-t border-gray-100 max-h-80 overflow-y-auto px-2 py-2 space-y-0.5">
                <template x-for="(item, idx) in results" :key="idx">
                    <a :href="item.url"
                       @click="close"
                       :class="selectedIndex === idx ? 'bg-emerald-50 text-emerald-900 ring-1 ring-emerald-200' : 'text-gray-700 hover:bg-gray-50'"
                       class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-50 text-gray-500 shrink-0" :class="{'bg-emerald-100 text-emerald-600': selectedIndex === idx}">
                            <i :class="item.icon" class="text-sm"></i>
                        </span>
                        <div class="flex-1 min-w-0">
                            <span x-text="item.label" class="truncate block"></span>
                            <span x-text="item.type" class="text-[11px] text-gray-400 uppercase tracking-wider"></span>
                        </div>
                        <kbd class="hidden sm:inline-flex items-center rounded-md border border-gray-200 px-1.5 py-0.5 text-[11px] font-medium text-gray-400" x-show="idx < 9" x-text="(idx + 1) % 10"></kbd>
                    </a>
                </template>
            </div>
            <div x-show="!loading && query.length > 0 && results.length === 0" class="border-t border-gray-100 px-4 py-6 text-center">
                <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-gray-50 mx-auto mb-3">
                    <i class="bi bi-search text-gray-400 text-lg"></i>
                </span>
                <p class="text-sm font-medium text-gray-700">{{ __('No results found') }}</p>
                <p class="text-xs text-gray-500 mt-0.5">{{ __('Try a different search term.') }}</p>
            </div>
            <div x-show="query.length === 0 && results.length === 0" class="border-t border-gray-100 px-4 py-3">
                <div class="flex flex-wrap gap-2">
                    <span class="text-xs text-gray-400">{{ __('Tip:') }}</span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-50 px-2.5 py-1 text-xs text-gray-600"><kbd class="font-semibold">/</kbd><span>{{ __('open search') }}</span></span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-50 px-2.5 py-1 text-xs text-gray-600"><kbd class="font-semibold">↑↓</kbd><span>{{ __('navigate') }}</span></span>
                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-50 px-2.5 py-1 text-xs text-gray-600"><kbd class="font-semibold">⏎</kbd><span>{{ __('open') }}</span></span>
                </div>
            </div>
        </div>
    </div>
</div>
