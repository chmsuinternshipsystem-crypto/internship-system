@if ($errors->any())
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false }, 6000)" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2"
         class="mb-4 rounded-xl border border-red-200 bg-white px-4 py-3.5 text-sm text-red-700 shadow-lg" role="alert">
        <div class="flex items-start gap-3">
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-red-50 text-red-600">
                <i class="bi bi-exclamation-triangle"></i>
            </span>
            <div class="flex-1 min-w-0">
                <p class="font-semibold text-red-900">{{ __('Please review the highlighted fields.') }}</p>
                <ul class="mt-1.5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li class="flex items-start gap-1.5 text-red-600">
                            <span class="mt-0.5 shrink-0">•</span>
                            <span>{{ $error }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <button @click="show = false" type="button" class="shrink-0 rounded-lg p-1 text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors" aria-label="{{ __('Dismiss') }}">
                <i class="bi bi-x-lg text-sm"></i>
            </button>
        </div>
    </div>
@endif
