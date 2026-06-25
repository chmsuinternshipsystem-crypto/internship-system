<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Transmittal') }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Create Forwarding Batch') }}</h2>
        </div>
    </x-slot>

    <x-page-card compact>
        <form method="POST" action="{{ route('document-forwarding.store') }}" class="space-y-4" x-data="{ selectAll: false }">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Release schedule') }}</label>
                <input data-flatpickr="datetime" name="release_at" value="{{ old('release_at') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                <p class="mt-1 text-xs text-gray-500">{{ __('Leave empty to release immediately.') }}</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('Batch notes (optional)') }}</label>
                <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">{{ old('notes') }}</textarea>
            </div>

            <div class="overflow-x-auto rounded-md border border-gray-200">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left">
                                <label class="inline-flex items-center gap-1">
                                    <input type="checkbox" @change="selectAll = !selectAll; document.querySelectorAll('.doc-check').forEach(el => el.checked = selectAll)">
                                    <span>{{ __('Pick') }}</span>
                                </label>
                            </th>
                            <th class="px-3 py-2 text-left">{{ __('Student') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Document') }}</th>
                            <th class="px-3 py-2 text-left">{{ __('Submitted') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($documents as $doc)
                            <tr>
                                <td class="px-3 py-2 align-top"><input class="doc-check" type="checkbox" name="student_document_ids[]" value="{{ $doc->id }}"></td>
                                <td class="px-3 py-2 align-top">{{ $doc->student?->name ?? '—' }}</td>
                                <td class="px-3 py-2 align-top">{{ $doc->requiredDocument?->name ?? '—' }}</td>
                                <td class="px-3 py-2 align-top">{{ $doc->submitted_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @error('student_document_ids')
                <p class="text-sm text-red-600">{{ $message }}</p>
            @enderror

            <div class="flex justify-end gap-2">
                <a href="{{ route('document-forwarding.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
                <button type="submit" class="rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">{{ __('Save batch') }}</button>
            </div>
        </form>
    </x-page-card>
</x-app-layout>
