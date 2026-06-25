<x-app-layout>
    @php
        $dashRole = auth()->user()?->role;
        $navOk = fn (string $item) => $dashRole && \App\Support\InternshipRoles::staffSidebarShows($dashRole, $item);
    @endphp
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('System Intelligence') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Overview') }}
                </h2>
                <p class="text-sm text-gray-500">
                    @if ($navOk('students'))
                        {{ __('Monitoring BSIS student deployments and document compliance this internship cycle.') }}
                    @else
                        {{ __('Partner company and document workflow access for this cycle.') }}
                    @endif
                </p>
            </div>
        </div>
    </x-slot>

    <div class="layout-section-y">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (count($kpiCards) > 0)
            @php
                $cardCount = count($kpiCards);
                $gridClass = 'sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-' . min($cardCount, 6);
            @endphp
            <div class="grid gap-4 {{ $gridClass }}">
                @foreach ($kpiCards as $card)
                    <x-stat-card
                        :label="$card['label']"
                        :value="$card['value']"
                        :sub="$card['sub'] ?? null"
                        :color="$card['color']"
                        :icon="$card['icon']"
                        :link="$card['link'] ?? null"
                    />
                @endforeach
            </div>
            @endif

            @if ($atRiskCount > 0)
            <div class="rounded-lg border border-red-200 bg-red-50 px-5 py-4 flex items-start gap-3">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-red-900">
                        {{ __(':count student(s) flagged as at-risk', ['count' => $atRiskCount]) }}
                    </p>
                    <p class="text-xs text-red-700 mt-0.5">
                        {{ __('These students have unresolved risk flags (absences, late journals, missing documents, or expired deployments).') }}
                    </p>
                    <div class="mt-2">
                        <a href="{{ route('compliance.index', ['risk' => 1]) }}"
                           class="inline-flex items-center gap-1 text-xs font-semibold text-red-800 hover:text-red-900 underline">
                            {{ __('Review at-risk students') }} &rarr;
                        </a>
                    </div>
                </div>
            </div>
            @endif

            @if ($sectionCompliance->isNotEmpty())
            <div class="bg-white shadow-sm rounded-lg p-5">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-bar-chart-fill text-emerald-600"></i>
                    {{ __('Requirements per Section') }}
                </h3>
                <p class="mt-1 text-xs text-gray-500">{{ __('Document submission status breakdown by section.') }}</p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                    @foreach ($sectionCompliance as $section)
                        @php
                            $pct = $section->total > 0 ? round(($section->compliant_count / $section->total) * 100) : 0;
                            $barColor = $pct >= 80 ? 'bg-emerald-500' : ($pct >= 50 ? 'bg-amber-500' : 'bg-red-500');
                        @endphp
                        <div class="rounded-lg border border-gray-200 px-4 py-3">
                            <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('Section') }} {{ $section->section }}</p>
                            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $section->compliant_count }}/{{ $section->total }}</p>
                            <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $barColor }} rounded-full" style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">{{ $pct }}% {{ __('complete') }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if ($evalSummary)
            <div class="bg-white shadow-sm rounded-lg p-5">
                <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-star-fill text-violet-500"></i>
                    {{ __('Evaluations Overview') }}
                </h3>
                <p class="mt-1 text-xs text-gray-500">{{ __('Student performance summary across evaluation types.') }}</p>
                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-lg border border-gray-200 px-4 py-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('Total Evaluations') }}</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">{{ $evalSummary->total }}</p>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-4 py-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('Industry Avg') }}</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ $evalSummary->industryAvg ? number_format($evalSummary->industryAvg, 1) : '—' }}
                            <span class="text-sm font-normal text-gray-500">/ 100</span>
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-4 py-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('School Avg') }}</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ $evalSummary->schoolAvg ? number_format($evalSummary->schoolAvg, 1) : '—' }}
                            <span class="text-sm font-normal text-gray-500">/ 100</span>
                        </p>
                    </div>
                    <div class="rounded-lg border border-gray-200 px-4 py-3">
                        <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('Student Feedback') }}</p>
                        <p class="mt-1 text-2xl font-bold text-gray-900">
                            {{ $evalSummary->studentFeedbackAvg ? number_format($evalSummary->studentFeedbackAvg, 1) : '—' }}
                            <span class="text-sm font-normal text-gray-500">/ 100</span>
                        </p>
                    </div>
                </div>
            </div>
            @endif

            <!-- Quick access & compliance teaser -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div class="{{ $navOk('compliance') ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-white shadow-sm rounded-lg p-5">
                    <h3 class="text-sm font-semibold text-gray-800">
                        {{ __('Quick access') }}
                    </h3>
                    <p class="mt-1 text-xs text-gray-500">
                        {{ __('Jump directly to the most used modules.') }}
                    </p>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        @if ($navOk('students'))
                            <a href="{{ route('students.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-emerald-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Students') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Registry of BSIS interns') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('companies'))
                            <a href="{{ route('companies.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-sky-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Companies') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Partner organizations') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('deployments'))
                            <a href="{{ route('deployments.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-indigo-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Deployments') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Assignments & schedules') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('required-documents'))
                            <a href="{{ route('required-documents.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-amber-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Required Documents') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Document checklist setup') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('compliance'))
                            <a href="{{ route('compliance.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-emerald-600"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Requirements Overview') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('View Complete / In Progress / Needs Attention') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('evaluations'))
                            <a href="{{ route('evaluations.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-violet-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Evaluations') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Student performance scores') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('reports'))
                            <a href="{{ route('reports.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-cyan-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Reports') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Export PDF summaries') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('announcements'))
                            <a href="{{ route('announcements.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-rose-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Announcements') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Share updates with students') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('workflow-queue'))
                            <a href="{{ route('student-documents.queue') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-slate-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Document Queue') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Documents waiting on your role') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('messages'))
                            <a href="{{ route('messages.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-teal-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Messages') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Staff and student threads') }}</p>
                                </div>
                            </a>
                        @endif
                        @if ($navOk('attendance'))
                            <a href="{{ route('attendance.index') }}" class="flex items-start gap-2 rounded-lg border border-gray-200 px-3 py-3 hover:bg-gray-50">
                                <span class="mt-0.5 h-2 w-2 rounded-full bg-orange-500"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ __('Attendance') }}</p>
                                    <p class="text-xs text-gray-500">{{ __('Check-ins and review flags') }}</p>
                                </div>
                            </a>
                        @endif
                    </div>
                </div>

                @if ($navOk('compliance'))
                <div class="bg-white shadow-sm rounded-lg p-5 flex flex-col justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-800">
                            {{ __('Requirements at a glance') }}
                        </h3>
                        <p class="mt-1 text-xs text-gray-500">
                            {{ __('Quick snapshot of document submission status.') }}
                        </p>

                        <dl class="mt-4 space-y-1 text-xs">
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600">{{ __('Complete') }}</dt>
                                <dd class="font-semibold text-emerald-700">{{ $complianceSummary['compliant'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600">{{ __('In Progress') }}</dt>
                                <dd class="font-semibold text-blue-700">{{ $complianceSummary['partial'] }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt class="text-gray-600">{{ __('Needs Attention') }}</dt>
                                <dd class="font-semibold text-red-700">{{ $complianceSummary['nonCompliant'] }}</dd>
                            </div>
                        </dl>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('compliance.index') }}"
                           class="inline-flex w-full justify-center items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                            {{ __('Open Requirements Overview') }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
