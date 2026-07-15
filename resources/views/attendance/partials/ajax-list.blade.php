@if (in_array(auth()->user()->role ?? '', ['instructor', 'chairperson'], true))
<div x-data="batchSelect()">
@endif

{{-- Summary cards --}}
<div class="mb-3 grid grid-cols-2 gap-2 lg:grid-cols-4">
    <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-amber-800">{{ __('Needs attention') }}</p>
        <p class="mt-1 text-lg font-semibold text-amber-900">{{ (int) ($summary['needs_attention'] ?? 0) }}</p>
    </div>
    <div class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-sky-800">{{ __('Open time-ins') }}</p>
        <p class="mt-1 text-lg font-semibold text-sky-900">{{ (int) ($summary['open_time_ins'] ?? 0) }}</p>
    </div>
    <div class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-2">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-800">{{ __('Off campus') }}</p>
        <p class="mt-1 text-lg font-semibold text-rose-900">{{ (int) ($summary['off_campus'] ?? 0) }}</p>
    </div>
    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2">
        <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-800">{{ __('Resolved') }}</p>
        <p class="mt-1 text-lg font-semibold text-emerald-900">{{ (int) ($summary['resolved'] ?? 0) }}</p>
    </div>
</div>

<div id="attendance-table-shell" class="-mx-1 rounded-xl border border-gray-200 bg-white sm:mx-0" data-latest-updated-at="{{ optional($latestUpdatedAt)->toIso8601String() }}">

    @if (in_array(auth()->user()->role ?? '', ['instructor', 'chairperson'], true))
        {{-- Batch action bar --}}
        <div x-cloak x-show="selected.length > 0"
             class="bg-emerald-50 border-b border-emerald-200 px-4 py-2.5 flex items-center justify-between">
            <span class="text-sm font-medium text-emerald-900" x-text="selected.length + ' selected'"></span>
            <div class="flex items-center gap-2">
                <button @click="showResolveModal = true" class="btn-primary text-xs px-3 py-1.5">
                    <i class="bi bi-check-circle me-1"></i>{{ __('Mark Resolved') }}
                </button>
                <button @click="clear()" class="text-sm text-gray-500 hover:text-gray-700 px-3 py-1.5">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>

        {{-- Batch Resolve Modal --}}
        <div x-show="showResolveModal" x-cloak
             class="fixed inset-0 z-[100] flex items-center justify-center p-4"
             @keydown.escape.window="showResolveModal = false">
            <div class="absolute inset-0 bg-gray-900/50" @click="showResolveModal = false"></div>
            <div class="relative z-10 w-full max-w-sm rounded-xl border border-gray-200 bg-white p-5 shadow-xl">
                <h3 class="text-base font-semibold text-gray-900">{{ __('Batch Resolve') }}</h3>
                <p class="mt-2 text-sm text-gray-600">
                    {{ __('Mark') }} <strong x-text="selected.length"></strong> {{ __('attendance record(s) as resolved.') }}
                </p>
                <form method="POST" action="{{ route('batch.attendance') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="action" value="resolve">
                    <template x-for="id in selected" :key="'att-' + id">
                        <input type="hidden" name="ids[]" :value="id">
                    </template>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Resolution Note (optional)') }}</label>
                        <input type="text" name="resolution_note" maxlength="255"
                               placeholder="{{ __('e.g. Student was inside the campus') }}"
                               class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showResolveModal = false"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                            {{ __('Cancel') }}
                        </button>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                            {{ __('Confirm Resolve') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <table class="w-full border-collapse text-left text-sm text-gray-900">
        <thead>
            <tr class="border-b border-gray-200 bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500">
                @if (in_array(auth()->user()->role ?? '', ['instructor', 'chairperson'], true))
                    <th scope="col" class="px-2 py-2.5 w-10">
                        <input type="checkbox" @change="toggleAll($event.target.checked)"
                               :checked="selected.length === allItemIds.length && allItemIds.length > 0"
                               class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600">
                    </th>
                @endif
                <th scope="col" class="px-2 py-2.5 sm:px-3">{{ __('Student') }}</th>
                <th scope="col" class="px-2 py-2.5 sm:px-3">{{ __('Time in / out') }}</th>
                <th scope="col" class="px-2 py-2.5 sm:px-3">{{ __('Hours') }}</th>
                <th scope="col" class="px-2 py-2.5 sm:px-3">{{ __('Status') }}</th>
                @if (in_array(auth()->user()->role ?? '', ['instructor', 'chairperson'], true))
                    <th scope="col" class="px-2 py-2.5 sm:px-3">{{ __('Review') }}</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse ($attendances as $attendance)
                <tr class="align-top hover:bg-gray-50/50 transition-colors">
                    @if (in_array(auth()->user()->role ?? '', ['instructor', 'chairperson', 'dean'], true))
                        <td class="px-2 py-2.5 w-10">
                            <input type="checkbox"
                                   value="{{ $attendance->id }}"
                                   x-model="selected"
                                   x-init="$nextTick(() => { if (!allItemIds.includes({{ $attendance->id }})) allItemIds.push({{ $attendance->id }}); })"
                                   class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600">
                        </td>
                    @endif
                    <td class="px-2 py-2.5 sm:px-3">
                        <div class="truncate font-medium tabular-nums" title="{{ $attendance->student?->student_number }}">
                            {{ $attendance->student?->student_number ?? '—' }}
                        </div>
                        <div class="truncate text-xs text-gray-500" title="{{ $attendance->student?->name }}">
                            {{ $attendance->student?->name ?? '—' }}
                        </div>
                    </td>
                    <td class="px-2 py-2.5 sm:px-3">
                        <div class="space-y-1 leading-snug">
                            <div>
                                <span class="text-gray-400">{{ __('In') }}</span>
                                <span class="tabular-nums text-gray-900">{{ $attendance->check_in_at->format('g:i A, M j') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400">{{ __('Out') }}</span>
                                @if ($attendance->time_out_at)
                                    <span class="tabular-nums text-gray-900">{{ $attendance->time_out_at->format('g:i A, M j') }}</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-2 py-2.5 sm:px-3 tabular-nums">
                        @if ($attendance->total_minutes !== null)
                            {{ number_format($attendance->total_minutes / 60, 1) }}{{ __('h') }}
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-2 py-2.5 sm:px-3">
                        <div class="flex flex-wrap items-center gap-1">
                        @if ($attendance->geofence_status === 'location_unavailable')
                            <span class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-gray-50 px-1.5 py-0.5 text-xs font-medium text-gray-700">
                                <i class="bi bi-question-circle shrink-0"></i>
                                <span class="truncate">{{ __('Unverified') }}</span>
                            </span>
                        @elseif($attendance->geofence_status === 'inside_pass')
                            <span class="inline-flex items-center gap-1 rounded-md border border-emerald-100 bg-emerald-50 px-1.5 py-0.5 text-xs font-medium text-emerald-800">
                                <i class="bi bi-check-circle shrink-0"></i>
                                <span class="truncate">{{ __('On Site') }}</span>
                            </span>
                        @elseif($attendance->geofence_status === 'near_boundary_review')
                            <span class="inline-flex items-center gap-1 rounded-md border border-amber-100 bg-amber-50 px-1.5 py-0.5 text-xs font-medium text-amber-900">
                                <i class="bi bi-exclamation-triangle shrink-0"></i>
                                <span class="truncate">{{ __('Boundary') }}</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-md border border-red-100 bg-red-50 px-1.5 py-0.5 text-xs font-medium text-red-800">
                                <i class="bi bi-exclamation-circle shrink-0"></i>
                                <span class="truncate">{{ __('Off Site') }}</span>
                            </span>
                        @endif
                        @if ($attendance->time_outside_window)
                            <span class="inline-flex items-center gap-1 rounded-md border border-orange-100 bg-orange-50 px-1.5 py-0.5 text-xs font-medium text-orange-800">
                                <i class="bi bi-clock-history shrink-0"></i>
                                <span class="truncate">{{ __('Late') }}</span>
                            </span>
                        @endif
                        @if ($attendance->accuracy_suspicious)
                            <span class="inline-flex items-center gap-1 rounded-md border border-purple-100 bg-purple-50 px-1.5 py-0.5 text-xs font-medium text-purple-800" title="{{ __('Suspicious GPS accuracy') }}">
                                <i class="bi bi-shield-shaded shrink-0"></i>
                                <span class="truncate">{{ __('Suspicious') }}</span>
                            </span>
                        @endif
                        </div>
                    </td>
                    @if (in_array(auth()->user()->role ?? '', ['instructor', 'chairperson'], true))
                        <td class="px-2 py-2.5 sm:px-3 align-top">
                            @if ($attendance->resolution_status === 'resolved')
                                <div class="text-xs text-gray-600">
                                    <span class="font-medium text-emerald-800">{{ __('Resolved') }}</span>
                                    @if ($attendance->resolver)
                                        <div>{{ $attendance->resolver->name }}</div>
                                    @endif
                                    <div class="tabular-nums text-gray-500">{{ $attendance->resolved_at?->format('M j, g:i A') }}</div>
                                    @if ($attendance->resolution_note)
                                        <div class="mt-1 text-gray-600">{{ $attendance->resolution_note }}</div>
                                    @endif
                                </div>
                            @elseif ($attendance->review_required && $attendance->resolution_status === 'pending')
                                <span class="text-xs font-semibold text-amber-800">{{ __('Needs attention') }}</span>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ in_array(auth()->user()->role ?? '', ['instructor', 'chairperson'], true) ? 6 : 4 }}" class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <strong>{{ __('No attendance records yet') }}</strong>
                        <p>{{ __('Once students start time in/time out, records will appear here.') }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@include('partials.htmx-pagination', ['paged' => $attendances, 'hxTarget' => '#attendance-ajax-mount'])

@if (in_array(auth()->user()->role ?? '', ['instructor', 'chairperson'], true))
</div>

@once
<script>
function batchSelect() {
    return {
        selected: [],
        allItemIds: [],
        showResolveModal: false,
        toggleAll(checked) {
            this.selected = checked ? [...this.allItemIds] : [];
        },
        clear() {
            this.selected = [];
            this.showResolveModal = false;
        },
    };
}
</script>
@endonce
@endif
