@props(['role' => ''])

<div x-data="fabMenu('{{ $role }}')"
     x-on:open-review-panel.window="open = false; visible = false"
     x-on:close-review-panel.window="visible = true"
     x-show="visible"
     class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-2">
    {{-- Menu items --}}
    <template x-for="(action, idx) in actions" :key="idx">
        <a :href="action.url"
           x-show="open"
           x-cloak
           x-transition:enter="ease-out duration-200"
           x-transition:enter-start="opacity-0 translate-y-4"
           x-transition:enter-end="opacity-100 translate-y-0"
           x-transition:leave="ease-in duration-150"
           x-transition:leave-start="opacity-100 translate-y-0"
           x-transition:leave-end="opacity-0 translate-y-4"
           :style="'transition-delay: ' + (idx * 30) + 'ms'"
           class="inline-flex items-center gap-2.5 rounded-full bg-white pl-4 pr-5 py-2.5 text-sm font-semibold text-gray-700 shadow-lg border border-gray-100 hover:bg-emerald-50 hover:text-emerald-700 hover:border-emerald-200 transition-all btn-lift whitespace-nowrap">
            <i :class="action.icon" class="text-sm"></i>
            <span x-text="action.label"></span>
        </a>
    </template>

    {{-- Main button --}}
    <button type="button"
            @click="open = !open"
            class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white shadow-lg hover:bg-emerald-700 hover:shadow-xl transition-all btn-lift"
            aria-label="{{ __('Quick actions') }}">
        <i class="bi bi-plus-lg text-xl" x-show="!open"></i>
        <i class="bi bi-x-lg text-xl" x-show="open" x-cloak></i>
    </button>

    {{-- Click-outside backdrop --}}
    <div x-show="open" x-cloak @click="open = false"
         class="fixed inset-0 -z-10"></div>
</div>
