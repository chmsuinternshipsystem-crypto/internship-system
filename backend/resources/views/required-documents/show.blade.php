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

                        <div>
                            <span class="block text-xs font-semibold text-gray-500 mb-2">{{ __('Sample Template') }}</span>
                            @php $template = $requiredDocument->template; @endphp
                            @if ($template)
                                <div class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2">
                                    <i class="bi bi-file-earmark-text text-emerald-600"></i>
                                    <span class="text-sm text-gray-700 flex-1 truncate">{{ $template->original_name }}</span>
                                    <a href="{{ route('required-documents.template.download', $requiredDocument) }}" class="inline-flex items-center gap-1 rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                        <i class="bi bi-download"></i> {{ __('Download') }}
                                    </a>
                                    <form method="POST" action="{{ route('required-documents.template.destroy', $requiredDocument) }}" class="inline" onsubmit="return confirm('{{ __('Remove template?') }}')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="inline-flex items-center rounded-lg border border-rose-200 px-2.5 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50"><i class="bi bi-trash"></i></button>
                                    </form>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">{{ __('No template uploaded yet.') }}</p>
                            @endif
                            <form method="POST" action="{{ route('required-documents.template.upload', $requiredDocument) }}" enctype="multipart/form-data" class="mt-2 flex items-center gap-2">
                                @csrf
                                <input type="file" name="template_file" accept=".pdf,.doc,.docx" required class="text-sm file:mr-2 file:rounded-md file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100">
                                <button type="submit" class="inline-flex items-center rounded-lg bg-emerald-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-emerald-700">{{ __('Upload') }}</button>
                            </form>
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

