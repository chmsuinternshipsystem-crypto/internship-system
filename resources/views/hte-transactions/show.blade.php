<x-layouts.hte>
    @php
        $hasEvaluateErrors = $errors->evaluate->any();
        $categories = \App\Models\EvaluationCriterion::getActiveCriteria();
        $totalCriteria = collect($categories)->sum(fn($cat) => count($cat['items']));
    @endphp

    <div class="space-y-5">
        {{-- Error Alerts --}}
        @if ($hasEvaluateErrors)
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <p class="font-semibold">{{ __('Please complete all required ratings below.') }}</p>
                @if ($errors->evaluate->has('form'))
                    <p class="mt-1">{{ $errors->evaluate->first('form') }}</p>
                @endif
            </div>
        @endif

        {{-- Header: Student + Company + Expiry --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ __('On-The-Job Training Evaluation') }}</p>
                    <h2 class="mt-0.5 text-lg font-semibold text-gray-900">{{ __('Student Performance Assessment') }}</h2>
                    <div class="mt-1.5 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-600">
                        <span><span class="font-semibold text-gray-900">{{ $link->student?->name ?? '—' }}</span></span>
                        @if ($link->company)
                            <span class="text-gray-300">|</span>
                            <span>{{ $link->company->name }}</span>
                        @endif
                        <span class="text-gray-300">|</span>
                        <span class="text-xs text-amber-700">{{ __('Expires') }}: {{ $link->expires_at?->format('M d, Y') }}</span>
                    </div>
                </div>
                <span class="shrink-0 rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-amber-700">{{ __('One-time secure link') }}</span>
            </div>
        </div>

        {{-- Main Evaluation Form --}}
        <form method="POST" action="{{ route('hte.transaction.evaluate', $link->token) }}"
              x-data="evaluationForm()"
              class="rounded-xl border border-gray-200 bg-white p-4 md:p-5 shadow-sm">
            @csrf

            {{-- Rating Scale — inline compact --}}
            <div class="mb-5 flex flex-wrap gap-2 text-[11px] text-gray-600">
                <span class="font-semibold text-gray-700 me-1">{{ __('Scale') }}:</span>
                <span><strong>1</strong> {{ __('Did not meet') }}</span>
                <span><strong>2</strong> {{ __('Minimum') }}</span>
                <span><strong>3</strong> {{ __('Normal') }}</span>
                <span><strong>4</strong> {{ __('Fully met') }}</span>
                <span><strong>5</strong> {{ __('Exceeded') }}</span>
            </div>

            {{-- Criteria Categories --}}
            @foreach ($categories as $catKey => $category)
                <div class="mb-5 last:mb-0">
                    <h3 class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-800">{{ __($category['label']) }}</h3>
                    <div class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="py-1.5 px-3 text-left font-semibold text-gray-600 w-full">{{ __('Criteria') }}</th>
                                    <th class="py-1.5 px-1 text-center font-semibold text-gray-600 w-8">1</th>
                                    <th class="py-1.5 px-1 text-center font-semibold text-gray-600 w-8">2</th>
                                    <th class="py-1.5 px-1 text-center font-semibold text-gray-600 w-8">3</th>
                                    <th class="py-1.5 px-1 text-center font-semibold text-gray-600 w-8">4</th>
                                    <th class="py-1.5 px-1 text-center font-semibold text-gray-600 w-8">5</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($category['items'] as $itemKey => $itemLabel)
                                    @php $fieldName = "criteria_scores[{$catKey}][{$itemKey}]"; @endphp
                                    <tr class="border-b border-gray-100 hover:bg-gray-50/50">
                                        <td class="py-2 px-3 text-gray-700 leading-snug">{{ __($itemLabel) }}</td>
                                        @for ($val = 1; $val <= 5; $val++)
                                            <td class="py-2 px-1 text-center">
                                                <input type="radio"
                                                       name="{{ $fieldName }}"
                                                       value="{{ $val }}"
                                                       x-model="scores['{{ $catKey }}']['{{ $itemKey }}']"
                                                       @change="recompute()"
                                                       class="h-3.5 w-3.5 border-gray-300 text-emerald-600 focus:ring-emerald-600"
                                                       @error("criteria_scores.{$catKey}.{$itemKey}") aria-invalid="true" @enderror
                                                       aria-label="{{ $val }} - {{ __($itemLabel) }}"
                                                       required>
                                            </td>
                                        @endfor
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @foreach ($category['items'] as $itemKey => $itemLabel)
                        @error("criteria_scores.{$catKey}.{$itemKey}")
                            <p class="mt-1 text-[11px] text-red-600">{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>
            @endforeach

            {{-- Overall Rating --}}
            <div class="my-5 rounded-lg border border-emerald-200 bg-emerald-50/70 px-4 py-3">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold uppercase tracking-wide text-emerald-800">{{ __('Overall Rating') }}</p>
                    <p class="text-xl font-bold text-emerald-700">
                        <span x-text="overallDisplay"></span>
                        <span class="text-sm font-normal text-emerald-600" x-show="overallValue > 0">
                            · <span x-text="overallPercent"></span>%
                        </span>
                    </p>
                </div>
            </div>

            {{-- Comments --}}
            <div class="mb-4">
                <x-input-label for="comments" :value="__('Comments / Suggestions (optional)')" />
                <textarea id="comments" name="comments" rows="3" maxlength="1000" placeholder="{{ __('Share your observations about the student\'s performance...') }}"
                          class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm">{{ old('comments') }}</textarea>
                <p class="mt-1 text-xs text-gray-400">{{ __('Maximum 1000 characters.') }}</p>
                @if ($errors->evaluate->has('comments'))
                    <p class="mt-1 text-sm text-red-600">{{ $errors->evaluate->first('comments') }}</p>
                @endif
            </div>

            {{-- Supervisor Info --}}
            <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50/70 px-3 py-2.5">
                <div class="flex flex-wrap gap-x-6 gap-y-1 text-xs text-gray-600">
                    <span><span class="font-semibold text-gray-700">{{ __('Supervisor') }}:</span> {{ $link->supervisor_name ?: __('Not provided') }}</span>
                    <span><span class="font-semibold text-gray-700">{{ __('Email') }}:</span> {{ $link->supervisor_email ?: __('Not provided') }}</span>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex items-center justify-between gap-3 border-t border-gray-100 pt-4">
                <div>
                    <button type="submit"
                            class="inline-flex items-center rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            :disabled="!allFilled">
                        <i class="bi bi-check2-circle me-1.5"></i>{{ __('Submit Evaluation') }}
                    </button>
                </div>
                <p class="text-xs text-gray-400" x-show="!allFilled">
                    <span x-text="totalCount - Object.values(scores).reduce((a, cat) => a + Object.values(cat).filter(v => v !== null && v !== '').length, 0)"></span>
                    {{ __('criteria remaining') }}
                </p>
            </div>
        </form>

        {{-- Help --}}
        <div class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-xs text-gray-500 shadow-sm">
            <p><span class="font-semibold text-gray-700">{{ __('Need help?') }}</span>
            {{ __('This secure link can be used once. After submission it is consumed. Request a fresh link from your internship coordinator for corrections.') }}</p>
        </div>
    </div>

    @push('scripts')
    <script>
        function evaluationForm() {
            return {
                scores: @json(collect($categories)->mapWithKeys(fn($cat, $key) => [$key => collect($cat['items'])->keys()->flip()->map(fn() => null)])),
                categoryTotals: {},
                overallValue: 0,
                overallPercent: 0,
                allFilled: false,
                totalCount: {{ $totalCriteria }},

                init() { this.recompute(); },

                recompute() {
                    const allValues = [];
                    const cats = @json(collect($categories)->mapWithKeys(fn($cat, $key) => [$key => collect($cat['items'])->keys()]));
                    let filled = 0;

                    for (const [catKey, items] of Object.entries(cats)) {
                        const values = [];
                        for (const itemKey of items) {
                            const v = this.scores[catKey]?.[itemKey];
                            if (v !== null && v !== undefined && v !== '') {
                                values.push(parseInt(v));
                                allValues.push(parseInt(v));
                                filled++;
                            }
                        }
                        this.categoryTotals[catKey] = values.length > 0
                            ? (values.reduce((a, b) => a + b, 0) / values.length).toFixed(2)
                            : null;
                    }

                    this.allFilled = filled === this.totalCount;

                    this.overallValue = allValues.length > 0
                        ? Math.round((allValues.reduce((a, b) => a + b, 0) / allValues.length) * 100) / 100
                        : 0;

                    this.overallPercent = this.overallValue > 0
                        ? Math.round(this.overallValue * 20)
                        : 0;
                },

                get overallDisplay() {
                    return this.overallValue > 0 ? this.overallValue.toFixed(2) : '\u2014';
                },
            };
        }
    </script>
    @endpush
</x-layouts.hte>
