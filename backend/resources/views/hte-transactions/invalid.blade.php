<x-layouts.hte>
    <div class="max-w-lg mx-auto text-center">
        <div class="rounded-xl border border-red-200 bg-white px-6 py-8 shadow-sm">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 text-red-500 mb-4">
                <i class="bi bi-exclamation-triangle text-3xl"></i>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide text-red-600">{{ __('HTE Portal') }}</p>
            <h2 class="mt-1 text-xl font-semibold text-gray-900">{{ __('Invalid or Expired Link') }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ __('This transaction link is no longer available. Please request a new link from the internship coordinator.') }}</p>
        </div>
        <p class="mt-4 text-xs text-gray-400">{{ __('Carlos Hilado Memorial State University \u2014 Talisay \u00b7 BSIS Internship Program') }}</p>
    </div>
</x-layouts.hte>
