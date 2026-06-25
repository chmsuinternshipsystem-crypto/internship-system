@php
    $docName = $requiredDocument?->name ?? $updatedDoc?->requiredDocument?->name ?? __('Document');
    $existingDoc = $studentDoc ?? $updatedDoc ?? null;
    $hasFile = $existingDoc && $existingDoc->file_path && Storage::disk('public')->exists($existingDoc->file_path);
    $filePath = $existingDoc?->file_path;
    $ext = $hasFile ? strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) : '';
    $isPdf = $hasFile && $ext === 'pdf';
    $isDocx = $hasFile && $ext === 'docx';
    $downloadUrl = $hasFile ? route('student-documents.download', ['student' => $student, 'studentDocument' => $existingDoc]) : null;
    $uploadSuccess = $hasFile && ($updatedDoc ?? false);
@endphp

<div class="flex flex-col h-full bg-white">
    <header class="flex items-center justify-between border-b border-gray-200 px-6 py-4 shrink-0">
        <div class="flex items-center gap-4 min-w-0">
            <button type="button"
                    onclick="window.dispatchEvent(new Event('close-panel'))"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700"
                    aria-label="{{ __('Close') }}">
                <i class="bi bi-arrow-left text-lg"></i>
            </button>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">{{ __('Upload Document') }}</p>
                <h2 class="text-lg font-bold text-gray-900 truncate mt-0.5">
                    {{ $docName }}
                    <span class="text-gray-400 font-normal mx-1.5">·</span>
                    <span class="font-semibold text-gray-600">{{ $student->name }}</span>
                </h2>
            </div>
        </div>
        <button type="button"
                onclick="window.dispatchEvent(new Event('close-panel'))"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700"
                aria-label="{{ __('Close panel') }}">
            <i class="bi bi-x-lg"></i>
        </button>
    </header>

    <div class="flex-1 overflow-y-auto p-8">
        @if ($uploadSuccess)
            <div class="flex flex-col items-center justify-center h-full text-center">
                <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center">
                    <i class="bi bi-check-circle-fill text-4xl text-emerald-500"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">{{ __('File uploaded successfully') }}</h3>
                <p class="text-sm text-gray-500 mb-8">{{ __('The document has been saved and is ready for review.') }}</p>

                @if ($hasFile)
                    <div class="w-full max-w-md bg-gray-50 rounded-xl border border-gray-200 p-4 mb-8">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                <i class="bi bi-file-earmark-check text-lg text-emerald-600"></i>
                            </div>
                            <div class="min-w-0 flex-1 text-left">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ basename($filePath) }}</p>
                                <p class="text-xs text-gray-500">{{ __('Uploaded just now') }}</p>
                            </div>
                            @if ($downloadUrl)
                                <a href="{{ $downloadUrl }}" target="_blank"
                                   class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 shrink-0">
                                    <i class="bi bi-download me-1"></i>{{ __('Download') }}
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                <button type="button"
                        onclick="window.dispatchEvent(new Event('close-panel'))"
                        class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
                    {{ __('Done') }}
                </button>
            </div>
        @else
            <div class="max-w-lg mx-auto">
                <div class="text-center mb-8">
                    <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                        <i class="bi bi-upload text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">{{ __('Upload :doc', ['doc' => $docName]) }}</h3>
                    <p class="text-sm text-gray-500">{{ __('Select a file to upload for this requirement.') }}</p>
                </div>

                <form method="POST"
                      action="{{ route('student-documents.update', $student) }}"
                      enctype="multipart/form-data"
                      hx-post="{{ route('student-documents.update', $student) }}"
                      hx-target="#docs-panel-content"
                      hx-swap="innerHTML"
                      hx-encoding="multipart/form-data"
                      hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                      class="space-y-6">
                    @csrf
                    <input type="hidden" name="return" value="{{ request()->query('return', '') }}">

                    <div class="rounded-xl border-2 border-dashed border-gray-300 p-8 text-center hover:border-emerald-400 transition-colors">
                        <label class="block cursor-pointer">
                            <input type="file"
                                   name="documents[{{ $requiredDocument->id }}][file]"
                                   accept=".pdf,.doc,.docx"
                                   required
                                   onchange="this.closest('form').requestSubmit()"
                                   class="block w-full text-sm text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-700" />
                        </label>
                        <p class="mt-3 text-xs text-gray-400">{{ __('PDF, DOC, DOCX only. Max 2 MB.') }}</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <button type="button"
                                onclick="window.dispatchEvent(new Event('close-panel'))"
                                class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
                            <i class="bi bi-upload me-1.5"></i>{{ __('Upload File') }}
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>
</div>
