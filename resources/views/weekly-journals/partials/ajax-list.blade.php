<div class="table-wrap"
     x-data="{ expanded: localStorage.getItem('wjExpanded') ? parseInt(localStorage.getItem('wjExpanded')) : null, toggle(id) { this.expanded = this.expanded === id ? null : id; } }">
    @php $currentStudentId = null; $studentJournals = []; @endphp
    @forelse ($weeklyJournals as $journal)
        @php
            $sid = $journal->student_id;
            if ($sid !== $currentStudentId) {
                if ($currentStudentId !== null) {
                    $groupId = $currentStudentId;
                    $total = count($studentJournals);
                    $reviewed = count(array_filter($studentJournals, fn ($j) => $j->status === 'reviewed'));
                    $submitted = count(array_filter($studentJournals, fn ($j) => $j->status === 'submitted'));
                    $first = $studentJournals[0];
                    $student = $first->student;
        @endphp
        <div class="border-b border-gray-200 last:border-b-0">
            <button @click="toggle({{ $groupId }})"
                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <i class="bi bi-chevron-right text-gray-400 transition-transform shrink-0"
                       :class="expanded === {{ $groupId }} ? 'rotate-90' : ''"></i>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $student->name }}</p>
                        <p class="text-xs text-gray-500">{{ $student->student_number }} · {{ __('Section') }} {{ $student->section }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0 ml-3">
                    <span class="text-xs text-gray-500 whitespace-nowrap">{{ $reviewed }}/{{ $total }} {{ __('reviewed') }}</span>
                    @if ($submitted > 0)
                        <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-[11px] font-semibold">
                            {{ $submitted }} {{ __('pending') }}
                        </span>
                    @endif
                </div>
            </button>
            <div x-show="expanded === {{ $groupId }}" x-cloak x-collapse.duration.200ms>
                <div class="border-t border-gray-100">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($studentJournals as $wj)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-2.5 pl-12 whitespace-nowrap">
                                        <span class="text-xs font-medium text-gray-900">{{ __('Week') }} {{ $wj->week_number }}</span>
                                        <span class="text-xs text-gray-400 ml-2">{{ $wj->week_start_date->format('M d') }} – {{ $wj->week_end_date->format('M d') }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <div class="flex items-center gap-1.5">
                                            <span class="status-badge {{ match($wj->status) { 'reviewed' => 'badge-completed', 'submitted' => 'badge-active', default => 'badge-default' } }}">
                                                {{ Str::headline($wj->status) }}
                                            </span>
                                            @if ($wj->is_late)
                                                <span class="text-[10px] font-medium text-red-600 bg-red-50 px-1.5 py-0.5 rounded">{{ __('Late') }}</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                        <a href="{{ route('weekly-journals.show', $wj) }}"
                                           class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-semibold text-gray-600 hover:text-emerald-700 hover:bg-emerald-50 transition-colors">
                                            <i class="bi bi-eye"></i>
                                            {{ $wj->status === 'submitted' ? __('Review') : __('View') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @php
                }
                $currentStudentId = $sid;
                $studentJournals = [];
            }
            $studentJournals[] = $journal;
        @endphp
    @empty
        <div class="empty-state">
            <i class="bi bi-journal-text"></i>
            <strong>{{ __('No weekly journals found') }}</strong>
            <p>{{ __('Nothing here yet.') }}</p>
        </div>
    @endforelse

    @php
        if ($currentStudentId !== null) {
            $groupId = $currentStudentId;
            $total = count($studentJournals);
            $reviewed = count(array_filter($studentJournals, fn ($j) => $j->status === 'reviewed'));
            $submitted = count(array_filter($studentJournals, fn ($j) => $j->status === 'submitted'));
            $first = $studentJournals[0];
            $student = $first->student;
    @endphp
    <div class="border-b border-gray-200 last:border-b-0">
        <button @click="toggle({{ $groupId }})"
                class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-3 min-w-0">
                <i class="bi bi-chevron-right text-gray-400 transition-transform shrink-0"
                   :class="expanded === {{ $groupId }} ? 'rotate-90' : ''"></i>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $student->name }}</p>
                    <p class="text-xs text-gray-500">{{ $student->student_number }} · {{ __('Section') }} {{ $student->section }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 ml-3">
                <span class="text-xs text-gray-500 whitespace-nowrap">{{ $reviewed }}/{{ $total }} {{ __('reviewed') }}</span>
                @if ($submitted > 0)
                    <span class="inline-flex items-center rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-[11px] font-semibold">
                        {{ $submitted }} {{ __('pending') }}
                    </span>
                @endif
            </div>
        </button>
        <div x-show="expanded === {{ $groupId }}" x-cloak x-collapse.duration.200ms>
            <div class="border-t border-gray-100">
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($studentJournals as $wj)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-2.5 pl-12 whitespace-nowrap">
                                    <span class="text-xs font-medium text-gray-900">{{ __('Week') }} {{ $wj->week_number }}</span>
                                    <span class="text-xs text-gray-400 ml-2">{{ $wj->week_start_date->format('M d') }} – {{ $wj->week_end_date->format('M d') }}</span>
                                </td>
                                <td class="px-4 py-2.5 whitespace-nowrap">
                                    <div class="flex items-center gap-1.5">
                                        <span class="status-badge {{ match($wj->status) { 'reviewed' => 'badge-completed', 'submitted' => 'badge-active', default => 'badge-default' } }}">
                                            {{ Str::headline($wj->status) }}
                                        </span>
                                        @if ($wj->is_late)
                                            <span class="text-[10px] font-medium text-red-600 bg-red-50 px-1.5 py-0.5 rounded">{{ __('Late') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                    <a href="{{ route('weekly-journals.show', $wj) }}"
                                       class="inline-flex items-center gap-1 rounded-md px-2.5 py-1.5 text-xs font-semibold text-gray-600 hover:text-emerald-700 hover:bg-emerald-50 transition-colors">
                                        <i class="bi bi-eye"></i>
                                        {{ $wj->status === 'submitted' ? __('Review') : __('View') }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @php
        }
    @endphp
</div>
@include('partials.htmx-pagination', ['paged' => $weeklyJournals, 'hxTarget' => '#weekly-journals-ajax-mount'])