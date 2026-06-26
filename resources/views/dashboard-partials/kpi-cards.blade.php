@php
    $dashRole = auth()->user()?->role;
    $navOk = fn (string $item) => $dashRole && \App\Support\InternshipRoles::staffSidebarShows($dashRole, $item);
@endphp

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
