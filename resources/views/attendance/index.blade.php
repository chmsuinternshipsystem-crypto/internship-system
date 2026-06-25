<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Attendance') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Campus Attendance Log') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Review student time in, time out, and total daily hours.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <x-page-card compact>
        <div class="sticky-list-toolbar mb-3">
            <x-search-bar
                :action="route('attendance.index')"
                :value="$studentQ ?? ''"
                placeholder="{{ __('Name or number') }}"
                hxTarget="#attendance-ajax-mount"
                :debounceMs="900"
                :showClear="($studentQ ?? '') !== '' || ($reviewScope ?? '') !== '' || ($status ?? '') !== '' || ($attendanceDate ?? '') !== ''"
                id="attendance-filter-form"
            >
                <span class="filter-label">{{ __('Queue') }}</span>
                <select name="review_scope" class="filter-select">
                    <option value="all" @selected(($reviewScope ?? '') === '' || ($reviewScope ?? '') === 'all')>{{ __('All records') }}</option>
                    <option value="needs_attention" @selected(($reviewScope ?? '') === 'needs_attention')>{{ __('Needs attention first') }}</option>
                    <option value="open_time_ins" @selected(($reviewScope ?? '') === 'open_time_ins')>{{ __('Open time-ins') }}</option>
                    <option value="history" @selected(($reviewScope ?? '') === 'history')>{{ __('History only') }}</option>
                </select>
                <span class="filter-label">{{ __('Location') }}</span>
                <select name="status" class="filter-select">
                    <option value="">{{ __('All') }}</option>
                    <option value="inside_pass" @selected(($status ?? '') === 'inside_pass')>{{ __('On campus') }}</option>
                    <option value="near_boundary_review" @selected(($status ?? '') === 'near_boundary_review')>{{ __('Near edge') }}</option>
                    <option value="outside_flagged" @selected(($status ?? '') === 'outside_flagged')>{{ __('Off campus') }}</option>
                    <option value="location_unavailable" @selected(($status ?? '') === 'location_unavailable')>{{ __('Location unknown') }}</option>
                </select>
                <span class="filter-label">{{ __('Date') }}</span>
                <input data-flatpickr name="date" value="{{ $attendanceDate ?? '' }}" class="search-input-date" />
                @if ((auth()->user()?->role ?? '') === 'instructor')
                <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 whitespace-nowrap">
                    <input type="checkbox" name="my_students" value="1" @checked($myStudents ?? false) class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600">
                    {{ __('My Students') }}
                </label>
                @endif
            </x-search-bar>
            <div class="flex justify-end mt-2">
                <a href="{{ route('attendance.export', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-white border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-colors shadow-sm">
                    <i class="bi bi-download"></i>
                    {{ __('Export CSV') }}
                </a>
            </div>
        </div>

        <div id="attendance-ajax-mount"
             hx-trigger="refresh-attendance from:body"
             hx-get="{{ route('attendance.index', request()->query()) }}"
             hx-target="#attendance-ajax-mount"
             hx-swap="innerHTML">
            @include('attendance.partials.ajax-list')
        </div>
    </x-page-card>

    @push('scripts')
        <script>
            (function () {
                const mount = document.getElementById('attendance-ajax-mount');
                if (!mount) return;

                let latest = '';
                const pollIntervalMs = 12000;

                function currentSearchParams() {
                    const params = new URLSearchParams(window.location.search);
                    if (!params.has('review_scope')) params.set('review_scope', 'all');
                    return params;
                }

                function updateLatestFromDom() {
                    const shell = document.getElementById('attendance-table-shell');
                    if (!shell) return;
                    const stamp = shell.getAttribute('data-latest-updated-at') || '';
                    if (stamp !== '') latest = stamp;
                }

                updateLatestFromDom();

                document.body.addEventListener('htmx:afterSwap', function (event) {
                    if (event.target && event.target.id === 'attendance-ajax-mount') {
                        updateLatestFromDom();
                    }
                });

                setInterval(async function () {
                    const activeEl = document.activeElement;
                    if (activeEl && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA' || activeEl.tagName === 'SELECT')) {
                        return;
                    }

                    const params = currentSearchParams();
                    params.set('poll', '1');
                    const pollUrl = "{{ route('attendance.index') }}?" + params.toString();

                    try {
                        const res = await fetch(pollUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        if (!res.ok) return;
                        const data = await res.json();
                        const nextLatest = data.latest_updated_at || '';
                        if (latest && nextLatest && latest !== nextLatest) {
                            const refreshParams = currentSearchParams();
                            const refreshUrl = "{{ route('attendance.index') }}?" + refreshParams.toString();
                            if (window.htmx) {
                                window.htmx.ajax('GET', refreshUrl, {
                                    target: '#attendance-ajax-mount',
                                    swap: 'innerHTML',
                                    pushURL: false
                                });
                            } else {
                                window.location.reload();
                            }
                            return;
                        }
                        if (nextLatest) latest = nextLatest;
                    } catch (e) {
                        // Silent fail; next cycle can recover.
                    }
                }, pollIntervalMs);
            })();
        </script>
    @endpush
</x-app-layout>
