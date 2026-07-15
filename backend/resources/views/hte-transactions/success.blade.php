<x-layouts.hte>
    <div class="max-w-lg mx-auto text-center">
        <div class="rounded-xl border border-emerald-200 bg-white px-6 py-8 shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mb-4">
                <i class="bi bi-check2-circle text-3xl"></i>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ __('HTE Portal') }}</p>
            <h2 class="mt-1 text-xl font-semibold text-gray-900">{{ $title }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ $message }}</p>
            <p class="mt-3 text-xs text-gray-400">{{ __('This secure link is now completed and cannot be reused.') }}</p>
            <p class="mt-1 text-xs text-gray-400">{{ __('The student and instructor will be notified automatically.') }}</p>
        </div>
        <p class="mt-4 text-xs text-gray-400">{{ __('Carlos Hilado Memorial State University \u2014 Talisay \u00b7 BSIS Internship Program') }}</p>
    </div>
</x-layouts.hte>
