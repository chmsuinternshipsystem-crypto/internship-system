@php
    $wfSteps = $studentDocument->workflowTemplate?->steps ?? collect();
    $wfTotal = $wfSteps->count();
    $wfOrder = (int) ($studentDocument->current_step_order ?? 0);
    $workflowStatus = (string) ($studentDocument->workflow_status ?? '');
    $wfNextFromTemplate = $wfSteps->sortBy('step_order')
        ->first(fn ($s) => (int) $s->step_order > $wfOrder)?->role;
    $wfNextResolved = filled($studentDocument->next_step_role)
        ? (string) $studentDocument->next_step_role
        : ($wfNextFromTemplate !== null && $wfNextFromTemplate !== '' ? (string) $wfNextFromTemplate : '');

    $filePath = $studentDocument->file_path;
    $hasFile = $filePath && Storage::disk('public')->exists($filePath);
    $ext = $hasFile ? strtolower(pathinfo($filePath, PATHINFO_EXTENSION)) : '';
    $isPdf = $hasFile && $ext === 'pdf';
    $isDocx = $hasFile && $ext === 'docx';
    $fileUrl = $hasFile ? Storage::disk('public')->url($filePath) : null;
    $downloadUrl = $hasFile ? route('student-documents.download', ['student' => $student, 'studentDocument' => $studentDocument]) : null;

    $recentActions = $studentDocument->actions()->with('actor')->latest('acted_at')->limit(50)->get();
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
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-600">{{ __('Document Details') }}</p>
                <h2 class="text-lg font-bold text-gray-900 truncate mt-0.5">
                    {{ $studentDocument->requiredDocument?->name ?? __('Document') }}
                    <span class="text-gray-400 font-normal mx-1.5">·</span>
                    <span class="font-semibold text-gray-600">{{ $student->name }}</span>
                    <span class="text-gray-300 font-normal mx-1.5">·</span>
                    <span class="text-sm font-normal text-gray-500">{{ $student->student_number }}</span>
                </h2>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if ($hasFile)
                <a href="{{ $downloadUrl }}" target="_blank"
                   class="inline-flex items-center rounded-lg border border-gray-200 px-3.5 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                    <i class="bi bi-download me-1.5"></i>{{ __('Download') }}
                </a>
            @endif
            <button type="button"
                    onclick="window.dispatchEvent(new Event('close-panel'))"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700"
                    aria-label="{{ __('Close panel') }}">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </header>

    @if ($wfTotal > 0)
    <div class="border-b border-gray-200 px-6 py-3 shrink-0">
        <div class="flex items-center gap-3 text-sm">
            <span class="text-gray-500 whitespace-nowrap text-xs">
                <span class="font-semibold text-gray-700">{{ __('Step :current of :total', ['current' => $wfOrder, 'total' => $wfTotal]) }}</span>
                <span class="text-gray-300 mx-1.5">·</span>
                <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">
                    {{ str($workflowStatus)->replace('_', ' ')->title() }}
                </span>
            </span>
            @if (filled($wfNextResolved))
                <span class="text-emerald-700 font-semibold text-[11px] whitespace-nowrap">
                    <i class="bi bi-arrow-right me-0.5"></i>{{ __('Next: :role', ['role' => str($wfNextResolved)->replace('_', ' ')->title()]) }}
                </span>
            @endif
        </div>
    </div>
    @endif

    <div class="flex-1 flex overflow-hidden">
        <div class="flex-1 overflow-hidden bg-gray-50/30">
            @if ($hasFile && $isPdf)
                <div class="h-full flex flex-col" x-data="pdfPreview('{{ $downloadUrl }}', '{{ $fileUrl }}')">
                    <template x-if="loading">
                        <div class="flex items-center justify-center flex-1 p-6">
                            <div class="text-center">
                                <div class="w-10 h-10 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                                <p class="text-sm text-gray-500">{{ __('Loading document...') }}</p>
                            </div>
                        </div>
                    </template>
                    <template x-if="error">
                        <div class="flex items-center justify-center flex-1 p-6">
                            <div class="text-center max-w-md">
                                <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center">
                                    <i class="bi bi-file-earmark-text text-4xl text-amber-500"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('Could not preview this file') }}</h3>
                                <p class="text-sm text-gray-500 mb-6" x-text="error"></p>
                                <a :href="downloadUrl" target="_blank"
                                   class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
                                    <i class="bi bi-download me-2"></i>{{ __('Download File') }}
                                </a>
                            </div>
                        </div>
                    </template>
                    <iframe x-ref="pdfFrame" class="w-full h-full border-0 flex-1" title="{{ __('Document preview') }}"></iframe>
                </div>
            @elseif ($hasFile && $isDocx)
                <div class="h-full flex flex-col" x-data="docxPreview('{{ $downloadUrl }}', '{{ $fileUrl }}')">
                    <div class="flex-1 relative">
                        <div x-show="loading"
                             class="absolute inset-0 z-10 flex items-center justify-center bg-white p-6">
                            <div class="text-center">
                                <div class="w-10 h-10 border-2 border-emerald-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                                <p class="text-sm text-gray-500">{{ __('Loading document...') }}</p>
                            </div>
                        </div>
                        <div x-show="error"
                             class="absolute inset-0 z-10 flex items-center justify-center bg-white p-6">
                            <div class="text-center max-w-md">
                                <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center">
                                    <i class="bi bi-file-earmark-text text-4xl text-amber-500"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('Could not preview this file') }}</h3>
                                <p class="text-sm text-gray-500 mb-6" x-text="error"></p>
                                <a :href="downloadUrl" target="_blank"
                                   class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
                                    <i class="bi bi-download me-2"></i>{{ __('Download File') }}
                                </a>
                            </div>
                        </div>
                        <div id="docx-container"
                             class="absolute inset-0"
                             style="overflow-y: auto !important;"></div>
                    </div>
                </div>
            @elseif ($hasFile)
                <div class="flex items-center justify-center h-full">
                    <div class="text-center max-w-md">
                        <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center">
                            <i class="bi bi-file-earmark-text text-4xl text-amber-500"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('Cannot preview this file') }}</h3>
                        <p class="text-sm text-gray-500 mb-1">{{ strtoupper(pathinfo($filePath, PATHINFO_EXTENSION)) }} files cannot be displayed inline.</p>
                        <p class="text-sm text-gray-400 mb-6">{{ __('Download the file to view its contents.') }}</p>
                        <a href="{{ $downloadUrl }}" target="_blank"
                           class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-3 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
                            <i class="bi bi-download me-2"></i>{{ __('Download File') }}
                        </a>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-center h-full">
                    <div class="text-center">
                        <div class="w-20 h-20 mx-auto mb-5 rounded-2xl bg-gray-100 border border-gray-200 flex items-center justify-center">
                            <i class="bi bi-file-earmark-x text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ __('No file uploaded') }}</h3>
                        <p class="text-sm text-gray-500">{{ __('This document has no file attached.') }}</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="w-80 lg:w-96 border-l border-gray-200 flex flex-col bg-white">
            <div class="flex-1 overflow-y-auto p-5">
                <div class="flex items-center justify-between mb-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Activity') }}</p>
                    @if ($recentActions->isNotEmpty())
                        <span class="text-[11px] text-gray-400">{{ count($recentActions) }} {{ __('events') }}</span>
                    @endif
                </div>

                @if ($recentActions->isNotEmpty())
                    <div class="relative">
                        <div class="absolute left-[15px] top-0 bottom-0 w-px bg-gray-100"></div>
                        <div class="space-y-0">
                            @foreach ($recentActions as $action)
                                <div class="flex items-start gap-3 relative pb-5 last:pb-0">
                                    <div class="flex items-center justify-center w-[30px] h-[30px] rounded-full shrink-0 z-10 ring-2 ring-white
                                        @if ($action->action === 'review') bg-blue-100 text-blue-600
                                        @elseif($action->action === 'approve' || $action->action === 'sign') bg-emerald-100 text-emerald-600
                                        @elseif($action->action === 'return_for_revision') bg-amber-100 text-amber-600
                                        @elseif($action->action === 'initialize' || $action->action === 'upload') bg-sky-100 text-sky-600
                                        @else bg-gray-100 text-gray-500 @endif">
                                        @if ($action->action === 'review')
                                            <i class="bi bi-eye" style="font-size: 12px;"></i>
                                        @elseif($action->action === 'approve' || $action->action === 'sign')
                                            <i class="bi bi-check-lg" style="font-size: 12px;"></i>
                                        @elseif($action->action === 'return_for_revision')
                                            <i class="bi bi-arrow-return-left" style="font-size: 12px;"></i>
                                        @elseif($action->action === 'initialize' || $action->action === 'upload')
                                            <i class="bi bi-upload" style="font-size: 12px;"></i>
                                        @else
                                            <i class="bi bi-circle-fill" style="font-size: 6px;"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0 pt-0.5">
                                        <div class="flex items-baseline justify-between gap-2">
                                            <div>
                                                <p class="text-sm font-semibold text-gray-800">
                                                    {{ str($action->action)->replace('_', ' ')->title() }}
                                                </p>
                                                @if ($action->actor)
                                                    <p class="text-xs text-gray-500">
                                                        <span class="font-medium text-gray-700">{{ $action->actor->name }}</span>
                                                        @if ($action->actor->role)
                                                            <span class="text-gray-400">· {{ str($action->actor->role)->replace('_', ' ')->title() }}</span>
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-gray-400 shrink-0 whitespace-nowrap">{{ $action->acted_at?->diffForHumans() ?? '' }}</span>
                                        </div>
                                        @if ($action->note)
                                            <div class="mt-2 bg-gray-50 border border-gray-100 rounded-lg px-3 py-2">
                                                <p class="text-xs text-gray-600 italic">"{{ $action->note }}"</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-12">
                        <div class="w-12 h-12 mx-auto mb-3 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center">
                            <i class="bi bi-clock-history text-2xl text-gray-400"></i>
                        </div>
                        <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ __('No activity recorded') }}</h3>
                        <p class="text-xs text-gray-500">{{ __('No actions recorded for this document yet.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($hasFile && ($isPdf || $isDocx))
<script src="https://cdn.jsdelivr.net/npm/jszip@3/dist/jszip.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.7/dist/docx-preview.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
<script>
var fallbackFetch = async function (urls) {
    for (var i = 0; i < urls.length; i++) {
        try {
            var resp = await fetch(urls[i]);
            if (!resp.ok) throw new Error();
            return await resp.arrayBuffer();
        } catch (e) {}
    }
    throw new Error('Unable to fetch the document from any source.');
};

window.pdfPreview = function (downloadUrl, fileUrl) {
    return {
        loading: true,
        error: '',
        downloadUrl: downloadUrl,
        async init() {
            try {
                var buffer = await fallbackFetch([downloadUrl, fileUrl]);
                var blob = new Blob([buffer], { type: 'application/pdf' });
                var blobUrl = URL.createObjectURL(blob);
                this.$refs.pdfFrame.src = blobUrl;
            } catch (e) {
                this.error = e.message || 'Could not preview this document.';
            } finally {
                this.loading = false;
            }
        }
    };
};

window.docxPreview = function (downloadUrl, fileUrl) {
    return {
        loading: true,
        error: '',
        downloadUrl: downloadUrl,
        async init() {
            var check = setInterval(function () {
                if (typeof docx !== 'undefined' && typeof mammoth !== 'undefined' && typeof JSZip !== 'undefined') {
                    clearInterval(check);
                    this.loadDocx();
                }
            }.bind(this), 50);
            setTimeout(function () {
                clearInterval(check);
                if (this.loading) {
                    this.error = 'Failed to load the document viewer.';
                    this.loading = false;
                }
            }.bind(this), 15000);
        },
        async loadDocx() {
            var urls = [downloadUrl, fileUrl];
            var plans = [
                { urlIdx: 0, renderer: 'docx' },
                { urlIdx: 1, renderer: 'docx' },
                { urlIdx: 0, renderer: 'mammoth' },
                { urlIdx: 1, renderer: 'mammoth' },
            ];
            for (var i = 0; i < plans.length; i++) {
                var plan = plans[i];
                try {
                    var resp = await fetch(urls[plan.urlIdx]);
                    if (!resp.ok) throw new Error();
                    var buffer = await resp.arrayBuffer();
                    var container = document.getElementById('docx-container');
                    if (!container) throw new Error('Preview container not found.');
                    if (plan.renderer === 'docx') {
                        await docx.renderAsync(buffer, container, null, {
                            className: 'docx-viewer',
                            inWrapper: true,
                            ignoreWidth: false,
                            ignoreHeight: true,
                            renderHeaders: true,
                            renderFooters: true,
                            renderFootnotes: true,
                            renderEndnotes: true,
                        });
                        container.style.overflowY = 'auto';
                    } else {
                        var result = await mammoth.convertToHtml({ arrayBuffer: buffer });
                        container.innerHTML = '<div class="p-6 prose prose-sm max-w-none">' + result.value + '</div>';
                    }
                    this.loading = false;
                    return;
                } catch (e) {}
            }
            this.error = 'Could not preview this document.';
            this.loading = false;
        }
    };
};
</script>
@endif
