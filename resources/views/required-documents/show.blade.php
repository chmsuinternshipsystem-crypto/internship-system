<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Required Documents'), 'url' => route('required-documents.index')],
    ['label' => $requiredDocument->name],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">Documentation</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Required Document Details</h2>
                <p class="text-sm text-gray-500">View document requirement information.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $requiredDocument->name }}</h3>
                    </div>

                    <div class="space-y-2">
                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Phase') }}</span>
                            <span class="text-sm text-gray-900">
                                {{ Str::headline($requiredDocument->phase ?? 'all') }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Display Order') }}</span>
                            <span class="text-sm text-gray-900">
                                {{ $requiredDocument->order_index ?? '-' }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Description') }}</span>
                            <span class="text-sm text-gray-900 break-all">
                                {{ $requiredDocument->description ?? '-' }}
                            </span>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end space-x-2">
                        <a href="{{ route('required-documents.edit', $requiredDocument) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
                            {{ __('Edit') }}
                        </a>
                        <a href="{{ route('required-documents.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                            {{ __('Back to list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

