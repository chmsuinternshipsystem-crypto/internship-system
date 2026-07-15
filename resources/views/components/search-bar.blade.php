@props([
    'action',
    'placeholder' => 'Search...',
    'value' => '',
    'hxTarget' => null,
    'showClear' => false,
    'debounceMs' => 150,
    'sticky' => false,
    'autoSubmit' => true,
    'advancedLabel' => null,
])

@php
    $formId = 'search-form-' . \Illuminate\Support\Str::slug($action . ($hxTarget ?? ''), '-') . '-' . substr(md5($action . ($hxTarget ?? '')), 0, 6);
@endphp

@if ($sticky)
<div class="sticky top-0 z-10 -mx-1 bg-white/95 pb-3 pt-1 backdrop-blur">
@endif
<form
    id="{{ $formId }}"
    method="GET"
    action="{{ $action }}"
    class="mb-4 flex flex-wrap items-center gap-2"
    @if ($hxTarget)
        hx-get="{{ $action }}"
        hx-target="{{ $hxTarget }}"
        hx-swap="innerHTML"
        hx-push-url="true"
        hx-trigger="submit, change from:select, change from:.filter-select, change from:input[name='my_students'], change from:input[name='risk'], keyup changed delay:{{ (int) $debounceMs }}ms from:input[name='search']"
    @elseif ($autoSubmit)
        data-search-auto="1"
        data-search-debounce="{{ (int) $debounceMs }}"
    @endif
>
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
            <i class="bi bi-search text-sm"></i>
        </span>
        <input type="search"
               name="search"
               value="{{ $value }}"
               placeholder="{{ $placeholder }}"
               autocomplete="off"
               class="search-input"
        />
    </div>

    {{ $slot }}

    @if (! $hxTarget)
        <button type="submit" class="search-btn">
            <i class="bi bi-search me-1"></i>{{ __('Search') }}
        </button>
    @else
        <button type="submit" class="search-btn search-btn-htmx" title="{{ __('Apply filters') }}">
            <i class="bi bi-funnel me-1"></i>{{ __('Apply') }}
        </button>
    @endif
    @if ($value || $showClear)
        <a href="{{ $action }}" class="search-clear">
            <i class="bi bi-x-circle me-1"></i>{{ __('Clear') }}
        </a>
    @endif
</form>

@if ($advancedLabel)
    <details class="mb-4 -mt-2">
        <summary class="cursor-pointer text-sm font-medium text-gray-700">{{ $advancedLabel }}</summary>
        <div class="mt-2 flex flex-wrap items-center gap-2">
            {{ $advanced }}
        </div>
    </details>
@endif

@if ($sticky)
</div>
@endif

@if (! $hxTarget && $autoSubmit)
    @once
        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    document.querySelectorAll('form[data-search-auto]').forEach(function (form) {
                        var ms = parseInt(form.getAttribute('data-search-debounce') || '400', 10);
                        var search = form.querySelector('input[name="search"]');
                        var t;
                        if (search) {
                            search.addEventListener('input', function () {
                                clearTimeout(t);
                                t = setTimeout(function () { form.requestSubmit(); }, ms);
                            });
                            search.addEventListener('search', function () {
                                form.requestSubmit();
                            });
                        }
                        form.querySelectorAll('select.filter-select, select[name]').forEach(function (sel) {
                            sel.addEventListener('change', function () {
                                form.requestSubmit();
                            });
                        });
                    });
                });
            </script>
        @endpush
    @endonce
@endif