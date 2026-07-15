<x-app-layout>
    @php
        $visibleDocIds = $requiredDocuments->pluck('id');
        $totalDocs = $visibleDocIds->count();
        $existingVisible = $existing->intersectByKeys($visibleDocIds->flip());
        $verifiedCount = $existingVisible->filter(fn ($row) => in_array($row->status, ['Submitted', 'Verified'], true) || $row->workflow_status === 'completed')->count();
        $pendingCount = $existingVisible->filter(fn ($row) => $row->status === 'Pending')->count();
        $missingCount = max(0, $totalDocs - $verifiedCount - $pendingCount);
        $completionPct = $totalDocs > 0 ? (int) round(($verifiedCount / $totalDocs) * 100) : 0;

        $deadlineView = (string) request()->query('deadline_view', 'all');
        if (! in_array($deadlineView, ['all', 'action_needed', 'due_today', 'late_missing'], true)) {
            $deadlineView = 'all';
        }
        $nowTs = now();
        $visibleRequiredDocuments = $requiredDocuments->filter(function ($doc) use ($existing, $deadlineView, $nowTs) {
            if ($deadlineView === 'all') {
                return true;
            }

            $current = $existing->get($doc->id);
            $submittedAt = $current?->submitted_at ?? ($current?->file_path ? $current?->updated_at : null);
            $deadlineAt = $doc->submission_deadline_at;
            $isMissing = ! $submittedAt;
            $isLateSubmitted = $submittedAt && $deadlineAt && $submittedAt->gt($deadlineAt);
            $isDueToday = $isMissing && $deadlineAt && $deadlineAt->isSameDay($nowTs);
            $isMissingPastDeadline = $isMissing && $deadlineAt && $deadlineAt->lt($nowTs);
            $isActionNeeded = ($isMissing && !$deadlineAt) || $isDueToday || $isMissingPastDeadline || $isLateSubmitted;

            return match ($deadlineView) {
                'action_needed' => $isActionNeeded,
                'due_today' => $isDueToday,
                'late_missing' => ($isMissing && !$deadlineAt) || $isMissingPastDeadline || $isLateSubmitted,
                default => true,
            };
        });
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-1">
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Student Portal') }}</p>
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">{{ __('My Documents') }}</h2>
            <p class="text-sm text-gray-500">{{ __('Upload and track all internship requirements in one place.') }}</p>
        </div>
    </x-slot>

    {{-- Limited portal notice --}}
    @if (! empty($studentPortalLimited) && $studentPortalLimited)
        <div class="mb-5 rounded-xl border-l-4 border-l-amber-500 border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-900">
            <div class="flex items-start gap-3">
                <i class="bi bi-shield-exclamation mt-0.5 text-amber-600 text-xl"></i>
                <div>
                    <p class="font-semibold text-amber-950">{{ __('⚠ Limited Portal Access') }}</p>
                    <p class="mt-1 text-amber-800">{{ __('Upload and track requirements here. Full portal access — including attendance, journals, DTR, messages, and certificates — unlocks after all items below are completed and your deployment is active.') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Getting Started (new students only) --}}
    @if (! empty($showGettingStarted) && $showGettingStarted)
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50/50 px-5 py-4">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <i class="bi bi-rocket-takeoff text-lg"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Welcome to the Internship Portal!') }}</h3>
                    <p class="text-xs text-gray-600 mt-1">{{ __('Here\'s what to do next:') }}</p>
                    <ol class="mt-3 space-y-2 text-sm">
                        <li class="flex items-center gap-2 text-gray-700">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">1</span>
                            <span>{{ __('Upload your required documents in the Documents section') }}</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-700">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">2</span>
                            <span>{{ __('Start clocking in using the attendance feature') }}</span>
                        </li>
                        <li class="flex items-center gap-2 text-gray-700">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-xs font-bold text-emerald-700">3</span>
                            <span>{{ __('Submit your weekly journals regularly') }}</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
    @endif

    {{-- Progress overview --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row items-center gap-6">
            {{-- Progress ring --}}
            <div class="relative shrink-0">
                <x-progress-ring :percentage="$completionPct" size="100" strokeWidth="8" />
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-xl font-bold text-gray-900">{{ $completionPct }}%</span>
                    <span class="text-[10px] font-medium text-gray-500 -mt-0.5">{{ __('Complete') }}</span>
                </div>
            </div>

            {{-- Stat pills --}}
            <div class="flex flex-1 flex-wrap gap-3">
                <div class="flex flex-1 flex-col items-center gap-1 rounded-xl border border-gray-100 bg-gray-50/50 px-4 py-3 min-w-[100px]">
                    <span class="text-2xl font-bold text-gray-900">{{ $totalDocs }}</span>
                    <span class="text-[11px] font-medium text-gray-500 flex items-center gap-1">
                        <i class="bi bi-file-earmark-text"></i>
                        {{ __('Documents') }}
                    </span>
                </div>
                <div class="flex flex-1 flex-col items-center gap-1 rounded-xl border border-emerald-100 bg-emerald-50/50 px-4 py-3 min-w-[100px]">
                    <span class="text-2xl font-bold text-emerald-700">{{ $verifiedCount }}</span>
                    <span class="text-[11px] font-medium text-emerald-600 flex items-center gap-1">
                        <i class="bi bi-check-circle"></i>
                        {{ __('Verified') }}
                    </span>
                </div>
                <div class="flex flex-1 flex-col items-center gap-1 rounded-xl border border-amber-100 bg-amber-50/50 px-4 py-3 min-w-[100px]">
                    <span class="text-2xl font-bold text-amber-700">{{ $pendingCount }}</span>
                    <span class="text-[11px] font-medium text-amber-600 flex items-center gap-1">
                        <i class="bi bi-clock"></i>
                        {{ __('Pending') }}
                    </span>
                </div>
                <div class="flex flex-1 flex-col items-center gap-1 rounded-xl border border-rose-100 bg-rose-50/50 px-4 py-3 min-w-[100px]">
                    <span class="text-2xl font-bold text-rose-700">{{ $missingCount }}</span>
                    <span class="text-[11px] font-medium text-rose-600 flex items-center gap-1">
                        <i class="bi bi-exclamation-circle"></i>
                        {{ __('Missing') }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div x-data="{
        search: '',
        phaseFilter: '{{ request()->query('phase', 'all') }}',
        viewTab: '{{ request()->query('view', 'all') }}',
        switchTab(tab) {
            this.viewTab = tab;
            var params = new URLSearchParams();
            if (tab !== 'all') params.set('view', tab);
            if (this.phaseFilter !== 'all') params.set('phase', this.phaseFilter);
            var q = params.toString();
            var url = '{{ route('student.documents') }}' + (q ? '?' + q : '');
            if (window.htmx) htmx.ajax('GET', url, {target: '#documents-list', select: '#documents-list', swap: 'outerHTML', pushUrl: true});
        },
        switchPhase(phase) {
            this.phaseFilter = phase;
            var params = new URLSearchParams();
            if (this.viewTab !== 'all') params.set('view', this.viewTab);
            if (phase !== 'all') params.set('phase', phase);
            var q = params.toString();
            var url = '{{ route('student.documents') }}' + (q ? '?' + q : '');
            if (window.htmx) htmx.ajax('GET', url, {target: '#documents-list', select: '#documents-list', swap: 'outerHTML', pushUrl: true});
        }
    }"
         x-init='$nextTick(() => {
             let focus = new URLSearchParams(window.location.search).get("focus");
             if (focus) {
                 let el = document.querySelector("[data-doc-id=\"" + focus + "\"]");
                 if (el) {
                     el.scrollIntoView({ behavior: "smooth", block: "center" });
                     el.style.boxShadow = "0 0 0 1px rgba(5, 150, 105, 0.3), 0 0 12px rgba(5, 150, 105, 0.35)";
                     el.style.transition = "box-shadow 0.8s ease-out";
                     setTimeout(() => { el.style.boxShadow = ""; el.style.transition = ""; }, 5000);
                 }
             }
         })'>
    @php
        $viewMode = (string) request()->query('view', 'all');
        if (! in_array($viewMode, ['all', 'missing', 'completed'], true)) $viewMode = 'all';

        $phaseFilter = (string) request()->query('phase', 'all');
        if (! in_array($phaseFilter, ['all', 'pre', 'monitoring', 'post'], true)) $phaseFilter = 'all';

        $filteredDocs = $requiredDocuments->filter(function ($doc) use ($existing, $viewMode, $phaseFilter) {
            if ($phaseFilter !== 'all' && ($doc->phase ?? 'all') !== $phaseFilter) return false;
            $current = $existing->get($doc->id);
            $isCompleted = $current && (in_array($current->status, ['Submitted', 'Verified'], true) || $current->workflow_status === 'completed');
            $isMissing = !$current || (!in_array($current->status, ['Submitted', 'Verified'], true) && $current->workflow_status !== 'completed');
            return match ($viewMode) {
                'missing' => $isMissing,
                'completed' => $isCompleted,
                default => true,
            };
        });
    @endphp

    {{-- Tabs: All | Missing | Completed --}}
    <div class="mb-4 flex gap-1 border-b border-gray-200">
        <template x-for="[tabKey, tabLabel, tabColor] in [['all', '{{ __('All Documents') }}', ''], ['missing', '{{ __('Missing') }}', 'text-rose-600'], ['completed', '{{ __('Completed') }}', 'text-emerald-600']]">
            <button @click="switchTab(tabKey)" type="button"
                :class="viewTab === tabKey ? 'border-emerald-600 ' + tabColor : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold border-b-2 transition -mb-[1px]">
                <span x-show="tabKey === 'missing' && {{ $missingCount }} > 0"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-rose-100 text-[11px] font-bold text-rose-700">{{ $missingCount }}</span>
                <span x-show="tabKey === 'completed'"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-[11px] font-bold text-emerald-700">{{ $verifiedCount }}</span>
                <span x-text="tabLabel"></span>
            </button>
        </template>
    </div>

    {{-- Phase filter dropdown --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="relative">
            <select x-model="phaseFilter" @change="switchPhase(phaseFilter)"
                class="appearance-none rounded-lg border border-gray-200 bg-white py-2 pl-3 pr-8 text-sm font-medium text-gray-700 focus:border-emerald-300 focus:ring-emerald-200/50 focus:outline-none">
                <option value="all">{{ __('All Phases') }}</option>
                <option value="pre">{{ __('Pre-Deployment') }}</option>
                <option value="monitoring">{{ __('Monitoring') }}</option>
                <option value="post">{{ __('Post-Deployment') }}</option>
            </select>
            <i class="bi bi-chevron-down absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
        </div>
        <span class="text-xs text-gray-500">{{ $filteredDocs->count() }} {{ __('of') }} {{ $requiredDocuments->count() }} {{ __('documents') }}</span>
    </div>

    <div id="documents-list" class="space-y-3">
        @if ($filteredDocs->isNotEmpty())
            {{-- Table header --}}
            <div class="hidden sm:grid sm:grid-cols-12 gap-3 px-4 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider border-b border-gray-100">
                <div class="col-span-5">{{ __('Document') }}</div>
                <div class="col-span-2">{{ __('Phase') }}</div>
                <div class="col-span-3">{{ __('Status') }}</div>
                <div class="col-span-2 text-right">{{ __('Action') }}</div>
            </div>

            {{-- Document rows --}}
            @foreach ($filteredDocs as $doc)
                @php
                    $current = $existing->get($doc->id);
                @endphp
                <div x-show='!search || ($el.dataset.name || "").toLowerCase().includes(search.toLowerCase())'
                     data-name="{{ $doc->name }}" data-doc-id="{{ $doc->id }}">
                    @include('student.partials.document-card', [
                        'doc' => $doc,
                        'current' => $current,
                        'uploadError' => null,
                        'compact' => true,
                    ])
                </div>
            @endforeach
        @else
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 px-6 py-14 text-center">
                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                    <i class="bi bi-inbox text-2xl text-gray-400"></i>
                </div>
                <h3 class="mt-4 text-base font-semibold text-gray-700">
                    @if ($viewMode === 'missing')
                        {{ __('All documents completed!') }}
                    @elseif ($viewMode === 'completed')
                        {{ __('No completed documents yet') }}
                    @else
                        {{ __('No documents found') }}
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-500 max-w-sm">
                    @if ($viewMode === 'missing')
                        {{ __('You have submitted all required documents. Great job!') }}
                    @elseif ($viewMode === 'completed')
                        {{ __('Documents will appear here once they are verified.') }}
                    @else
                        {{ __('No documents match the current filter.') }}
                    @endif
                </p>
                @if ($viewMode !== 'all' || $phaseFilter !== 'all')
                    <a href="{{ route('student.documents') }}"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                        <i class="bi bi-files"></i>
                        {{ __('Show All Documents') }}
                    </a>
                @endif
            </div>
        @endif
    </div>

    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/jszip@3/dist/jszip.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/docx-preview@0.3.7/dist/docx-preview.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/mammoth@1.6.0/mammoth.browser.min.js"></script>
        <script>
            window.addEventListener('pageshow', function (event) {
                if (event.persisted) {
                    window.location.reload();
                }
            });

            function refreshDocuments() {
                var list = document.getElementById('documents-list');
                if (!list || !window.htmx) return;
                if (document.querySelector('.htmx-request')) return;
                htmx.ajax('GET', window.location.href, {
                    target: '#documents-list',
                    select: '#documents-list',
                    swap: 'outerHTML'
                });
            }

            window.addEventListener('refresh-documents', refreshDocuments);

            window.pdfPreview = function (downloadUrl) {
                return {
                    loading: true,
                    error: '',
                    downloadUrl: downloadUrl,
                    init() {
                        this.$refs.pdfFrame.src = downloadUrl;
                        this.loading = false;
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
                        var plans = [
                            { url: downloadUrl, renderer: 'docx' },
                            { url: fileUrl, renderer: 'docx' },
                            { url: downloadUrl, renderer: 'mammoth' },
                            { url: fileUrl, renderer: 'mammoth' },
                        ];
                        for (var i = 0; i < plans.length; i++) {
                            var plan = plans[i];
                            try {
                                var resp = await fetch(plan.url);
                                if (!resp.ok) throw new Error();
                                var buffer = await resp.arrayBuffer();
                                var container = this.$refs.docxContainer;
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
    @endpush
</x-app-layout>
