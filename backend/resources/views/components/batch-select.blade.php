<div
    x-data="batchSelect()"
    x-init="$el.setAttribute('x-data', $el.getAttribute('x-data'))"
    class="relative"
>
    {{-- Select All checkbox --}}
    <div class="flex items-center gap-2 px-4 py-2 border-b border-gray-100 bg-gray-50/50" x-show="items.length > 0">
        <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-600">
            <input
                type="checkbox"
                @change="toggleAll($event.target.checked)"
                :checked="selected.length === items.length && items.length > 0"
                :indeterminate="selected.length > 0 && selected.length < items.length"
                class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600"
            >
            <span>{{ __('Select all') }}</span>
        </label>
        <span class="text-xs text-gray-400 ml-auto" x-show="selected.length > 0" x-cloak>
            {{ __(':count selected', ['count' => '']) }}

{{-- Action bar --}}
<div
    x-cloak
    x-show="selected.length > 0"
    class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-40 px-4 py-3 transition-transform"
>
    <div class="max-w-7xl mx-auto flex items-center justify-between">
        <span class="text-sm font-medium text-gray-700" x-text="selected.length + ' selected'"></span>
        <div class="flex items-center gap-2">
            {{ $slot }}
            <button @click="clear()" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
                {{ __('Cancel') }}
            </button>
        </div>
    </div>
</div>

@once
<script>
function batchSelect() {
    return {
        selected: [],
        items: [],
        register(id) {
            if (!this.items.includes(id)) {
                this.items.push(id);
            }
        },
        toggle(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) {
                this.selected.push(id);
            } else {
                this.selected.splice(idx, 1);
            }
        },
        toggleAll(checked) {
            this.selected = checked ? [...this.items] : [];
        },
        clear() {
            this.selected = [];
        },
        isSelected(id) {
            return this.selected.includes(id);
        },
    };
}
</script>
@endonce
