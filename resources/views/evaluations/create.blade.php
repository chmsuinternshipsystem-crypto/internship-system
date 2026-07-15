<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Performance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Evaluation') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Record a new student performance evaluation.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <p class="text-sm font-semibold text-gray-900">{{ __('New Evaluation Entry') }}</p>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Fill in the required fields and submit once.') }}</p>
                </div>
                <div class="p-6 text-gray-900">
                    <form action="{{ route('evaluations.store') }}" method="POST">
                        @include('evaluations._form', ['evaluation' => new \App\Models\Evaluation(), 'submitLabel' => __('Create')])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

