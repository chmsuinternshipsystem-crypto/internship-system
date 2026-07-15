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

    <div x-data="{ search: '', activeFilter: '{{ $deadlineView }}' }"
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
    {{-- Search + Filter --}}
    <div class="mb-5 space-y-3">
        {{-- Search --}}
        <div class="relative">
            <i class="bi bi-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
            <input type="text" x-model="search" placeholder="{{ __('Search documents...') }}"
                class="block w-full rounded-xl border border-gray-200 bg-white py-2.5 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 ring-1 ring-transparent transition focus:border-emerald-300 focus:ring-emerald-200/50 focus:outline-none">
            <button x-show="search.length > 0" @click="search = ''" type="button"
                class="absolute right-3 top-1/2 -translate-y-1/2 flex h-5 w-5 items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                <i class="bi bi-x text-sm"></i>
            </button>
        </div>

        {{-- Filter pills --}}
        <div class="flex flex-wrap items-center gap-1.5">
            @foreach ([
                'all' => [__('All'), 'bi-files'],
                'action_needed' => [__('Needs Action'), 'bi-exclamation-triangle'],
                'due_today' => [__('Due Today'), 'bi-alarm'],
                'late_missing' => [__('Overdue / Missing'), 'bi-x-circle'],
            ] as $viewKey => [$viewLabel, $viewIcon])
                <a href="{{ route('student.documents', ['deadline_view' => $viewKey]) }}"
                    class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-xs font-semibold transition-all duration-150
                    {{ $deadlineView === $viewKey
                        ? 'bg-emerald-600 text-white shadow-sm'
                        : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50 hover:border-gray-300' }}">
                    <i class="bi {{ $viewIcon }}"></i>
                    {{ $viewLabel }}
                </a>
            @endforeach
            @if ($visibleRequiredDocuments->count() < $requiredDocuments->count())
                <span class="ml-1 text-xs text-gray-400">
                    {{ __(':count of :total shown', ['count' => $visibleRequiredDocuments->count(), 'total' => $requiredDocuments->count()]) }}
                </span>
            @endif
        </div>
    </div>

    {{-- Phase sections with cards --}}
    @php
        $phases = [
            'pre' => ['label' => __('Pre-Requirements'), 'icon' => 'bi-file-earmark-check', 'color' => 'text-emerald-600', 'bg' => 'bg-emerald-50', 'ring' => 'ring-emerald-200'],
            'monitoring' => ['label' => __('Monitoring Requirements'), 'icon' => 'bi-eye', 'color' => 'text-blue-600', 'bg' => 'bg-blue-50', 'ring' => 'ring-blue-200'],
            'post' => ['label' => __('Post-Requirements'), 'icon' => 'bi-check2-all', 'color' => 'text-purple-600', 'bg' => 'bg-purple-50', 'ring' => 'ring-purple-200'],
            'all' => ['label' => __('General Requirements'), 'icon' => 'bi-folder', 'color' => 'text-gray-600', 'bg' => 'bg-gray-50', 'ring' => 'ring-gray-200'],
        ];
        $grouped = $visibleRequiredDocuments->groupBy(fn ($doc) => $doc->phase ?? 'all');
    @endphp

    <div class="space-y-6">
        @foreach ($phases as $phaseKey => $phaseInfo)
            @php $phaseDocs = $grouped->get($phaseKey, collect()); @endphp
            @if ($phaseDocs->isNotEmpty())
                <section>
                    {{-- Phase header --}}
                    <div class="-mx-1 mb-3 flex items-center gap-2.5 rounded-lg bg-white/90 px-3 py-2.5 backdrop-blur-sm border-b border-gray-100">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $phaseInfo['bg'] }} ring-1 {{ $phaseInfo['ring'] }}">
                            <i class="bi {{ $phaseInfo['icon'] }} text-sm {{ $phaseInfo['color'] }}"></i>
                        </span>
                        <h3 class="text-sm font-semibold text-gray-800">{{ $phaseInfo['label'] }}</h3>
                        <span class="inline-flex items-center justify-center rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-semibold text-gray-600">{{ $phaseDocs->count() }}</span>
                    </div>

                    {{-- Card grid --}}
                    <div class="grid gap-3">
                        @foreach ($phaseDocs as $doc)
                            <div x-show='!search || ($el.dataset.name || "").toLowerCase().includes(search.toLowerCase())'
                                 data-name="{{ $doc->name }}" data-doc-id="{{ $doc->id }}">
                                @include('student.partials.document-card', [
                                    'doc' => $doc,
                                    'current' => $existing->get($doc->id),
                                    'uploadError' => null,
                                ])
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>

    {{-- Empty state for filters --}}
    @if ($visibleRequiredDocuments->isEmpty())
        <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50/50 px-6 py-14 text-center">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                <i class="bi bi-inbox text-2xl text-gray-400"></i>
            </div>
            <h3 class="mt-4 text-base font-semibold text-gray-700">{{ __('No documents found') }}</h3>
            <p class="mt-1 text-sm text-gray-500 max-w-sm">{{ __('No documents match the current filter. Try switching to "All" to see all requirements.') }}</p>
            @if ($deadlineView !== 'all')
                <a href="{{ route('student.documents', ['deadline_view' => 'all']) }}"
                    class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    <i class="bi bi-files"></i>
                    {{ __('Show All Documents') }}
                </a>
            @endif
        </div>
    @endif

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

            window.fallbackFetch = async function (urls) {
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
