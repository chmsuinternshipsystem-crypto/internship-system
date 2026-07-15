<div class="table-wrap"
      x-data="{ expanded: localStorage.getItem('wjExpanded') ? parseInt(localStorage.getItem('wjExpanded')) : null, toggle(id) { this.expanded = this.expanded === id ? null : id; localStorage.setItem('wjExpanded', this.expanded); } }">
    @forelse ($students as $student)
        @php
            $studentJournals = $weeklyJournals->get($student->id, collect());
            $total = $studentJournals->count();
            $reviewed = $studentJournals->where('status', 'reviewed')->count();
            $submitted = $studentJournals->where('status', 'submitted')->count();
        @endphp
        <div class="border-b border-gray-200 last:border-b-0">
            <button @click="toggle({{ $student->id }})"
                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-gray-50 transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <i class="bi bi-chevron-right text-gray-400 transition-transform shrink-0"
                       :class="expanded === {{ $student->id }} ? 'rotate-90' : ''"></i>
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $student->name }}</p>
                        <p class="text-xs text-gray-500">{{ $student->student_number }} &middot; {{ __('Section') }} {{ $student->section }}</p>
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
            <div x-show="expanded === {{ $student->id }}" x-cloak x-collapse.duration.200ms>
                <div class="border-t border-gray-100">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($studentJournals as $wj)
                                <tr class="hover:bg-gray-50/50">
                                    <td class="px-4 py-2.5 pl-12 whitespace-nowrap">
                                        <span class="text-xs font-medium text-gray-900">{{ __('Week') }} {{ $wj->week_number }}</span>
                                        <span class="text-xs text-gray-400 ml-2">{{ $wj->week_start_date->format('M d') }} &ndash; {{ $wj->week_end_date->format('M d') }}</span>
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
    @empty
        <div class="empty-state">
            <i class="bi bi-journal-text"></i>
            <strong>{{ __('No weekly journals found') }}</strong>
            <p>{{ __('Nothing here yet.') }}</p>
        </div>
    @endforelse
</div>
@include('partials.htmx-pagination', ['paged' => $students, 'hxTarget' => '#weekly-journals-ajax-mount'])
