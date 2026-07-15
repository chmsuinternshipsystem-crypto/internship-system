<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
                    ['label' => __('Evaluations'), 'url' => route('evaluations.index')],
                    ['label' => __('Criteria')],
                ]" />
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Performance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Evaluation Criteria') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Manage the evaluation criteria shown in HTE evaluation forms, grouped by category.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @foreach ($categories as $key => $cat)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">{{ $cat['label'] }}</h3>

                        <form method="POST" action="{{ route('evaluations.criteria.store') }}" class="flex flex-wrap items-end gap-2 mb-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                            @csrf
                            <input type="hidden" name="category_key" value="{{ $key }}">
                            <div class="flex-1 min-w-[140px]">
                                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Key (internal)') }}</label>
                                <input type="text" name="item_key" required maxlength="100"
                                       placeholder="e.g. punctuality"
                                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <div class="flex-[2] min-w-[200px]">
                                <label class="block text-xs font-medium text-gray-600 mb-1">{{ __('Label (visible to evaluator)') }}</label>
                                <input type="text" name="item_label" required maxlength="255"
                                       placeholder="e.g. Punctuality and attendance"
                                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </div>
                            <button type="submit"
                                    class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition-colors">
                                <i class="bi bi-plus-lg"></i> {{ __('Add') }}
                            </button>
                        </form>

                        @php $items = $criteria->get($key, collect()); @endphp
                        @if ($items->isNotEmpty())
                            <ul class="divide-y divide-gray-100 border border-gray-200 rounded-lg text-sm">
                                @foreach ($items as $item)
                                    <li class="flex items-center justify-between gap-3 px-3 py-2.5 hover:bg-gray-50">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $item->item_label }}</p>
                                            <p class="text-xs text-gray-500 font-mono">{{ $item->item_key }}</p>
                                        </div>
                                        <form method="POST" action="{{ route('evaluations.criteria.destroy', $item) }}"
                                              onsubmit="return confirm('{{ __('Remove this criterion?') }}')">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-400 hover:text-red-600 transition-colors p-1">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-sm text-gray-500 py-3 text-center border border-dashed border-gray-200 rounded-lg">
                                {{ __('No criteria in this category yet.') }}
                            </p>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="flex justify-start">
                <a href="{{ route('evaluations.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    {{ __('Back to Evaluations') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
