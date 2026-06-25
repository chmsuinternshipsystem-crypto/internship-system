<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Performance') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Evaluations') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Clean and fast evaluation tracking for each student.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="mb-4 flex flex-wrap items-center justify-end gap-2">
        @if ($canManage)
            <a href="{{ route('evaluations.hte-links.create') }}" class="inline-flex items-center rounded-md border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                {{ __('Send HTE Link') }}
            </a>
            <a href="{{ route('evaluations.create') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                {{ __('Add Evaluation') }}
            </a>
        @endif
    </div>

    <x-page-card compact>
        <p class="mb-3 text-xs text-gray-500">{{ __('Tip: use quick Type filter first, then open advanced filters only when needed.') }}</p>
        <x-search-bar
            :action="route('evaluations.index')"
            :value="$search"
            :placeholder="__('Student fields, type, score, evaluator, date...')"
            hxTarget="#evaluation-ajax-mount"
            :showClear="$hasActiveFilters"
            sticky
        >
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <span class="filter-label">{{ __('Type') }}</span>
                <select name="type" class="filter-select min-w-[9rem]">
                    <option value="">{{ __('All') }}</option>
                    <option value="industry" @selected($type === 'industry')>{{ __('Industry') }}</option>
                    <option value="school" @selected($type === 'school')>{{ __('School') }}</option>
                    <option value="student_feedback" @selected($type === 'student_feedback')>{{ __('Student Feedback') }}</option>
                </select>
            </div>
            <details class="w-full sm:w-auto" @open($hasActiveFilters && ($evaluator || $score || $evalYear || $evalMonth))>
                <summary class="cursor-pointer text-sm font-medium text-gray-700">{{ __('Advanced Filters') }}</summary>
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="filter-label">{{ __('Evaluator') }}</span>
                        <select name="evaluator" class="filter-select min-w-[10rem]">
                            <option value="">{{ __('All') }}</option>
                            @if ($hasNullEvaluator)
                                <option value="none" @selected($evaluator === 'none')>{{ __('HTE / External (no staff account)') }}</option>
                            @endif
                            @foreach ($evaluatorUsers as $u)
                                <option value="{{ $u->id }}" @selected((string) $evaluator === (string) $u->id)>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="filter-label">{{ __('Section') }}</span>
                        <select name="section" class="filter-select min-w-[6rem]">
                            <option value="">{{ __('All') }}</option>
                            @foreach (['A', 'B', 'C', 'D'] as $sec)
                                <option value="{{ $sec }}" @selected(($section ?? '') === $sec)>{{ __('Section') }} {{ $sec }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="filter-label">{{ __('Year') }}</span>
                        <select name="eval_year" class="filter-select min-w-[6rem]">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($evalYears as $y)
                                <option value="{{ $y }}" @selected((string) $evalYear === (string) $y)>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="filter-label">{{ __('Month') }}</span>
                        <select name="eval_month" class="filter-select min-w-[8rem]">
                            <option value="">{{ __('All') }}</option>
                            @foreach ($monthOptions as $mVal => $mLabel)
                                <option value="{{ $mVal }}" @selected((string) $evalMonth === (string) $mVal)>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </details>
        </x-search-bar>

        <div id="evaluation-ajax-mount">
            @include('evaluations.partials.ajax-list')
        </div>
    </x-page-card>
</x-app-layout>
