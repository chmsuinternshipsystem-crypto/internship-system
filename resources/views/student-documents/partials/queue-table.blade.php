<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 text-sm">
        <colgroup>
            <col style="width:45%">
            <col style="width:25%">
            <col style="width:30%">
        </colgroup>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Student') }}</th>
                <th class="px-3 py-2.5 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Documents') }}</th>
                <th class="px-3 py-2.5 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100"
               x-data="{
                   expanded: localStorage.getItem('queueExpanded') ? parseInt(localStorage.getItem('queueExpanded')) : null,
                   toggle(id) {
                       this.expanded = this.expanded === id ? null : id;
                       if (this.expanded) { localStorage.setItem('queueExpanded', this.expanded); }
                       else { localStorage.removeItem('queueExpanded'); }
                   }
               }"
               @toggle-expand.window="toggle($event.detail)">
            @forelse ($studentGroups as $group)
                <tr class="hover:bg-gray-50/50 cursor-pointer"
                    @click="$dispatch('toggle-expand', {{ $group['student_id'] }})">
                    <td class="px-3 py-2.5">
                        <div class="flex items-center gap-2">
                            <i class="bi bi-chevron-right text-xs text-gray-400 transition-transform"
                               :class="expanded === {{ $group['student_id'] }} ? 'rotate-90' : ''"></i>
                            <div>
                                <div class="font-semibold text-gray-900">{{ $group['student_number'] }}</div>
                                <div class="text-xs text-gray-500">{{ $group['student_name'] }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 py-2.5">
                        <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-700">
                            {{ $group['doc_count'] }} {{ __('pending') }}
                        </span>
                    </td>
                    <td class="px-3 py-2.5 text-right">
                        <span class="text-xs text-gray-500">{{ __('Click to expand') }}</span>
                    </td>
                </tr>
                @foreach ($group['documents'] as $sd)
                    @php
                        $student = $sd->student;
                        $isLate = $sd->submitted_at && $sd->requiredDocument?->submission_deadline_at && $sd->submitted_at->gt($sd->requiredDocument->submission_deadline_at);
                        $wfStatus = (string) ($sd->workflow_status ?? '');
                        $statusLabel = match($wfStatus) {
                            'pending_review' => __('Pending Review'),
                            'in_review' => __('In Review'),
                            'returned' => __('Returned'),
                            'approved' => __('Approved'),
                            'received' => __('Received'),
                            default => Str::headline($wfStatus ?: 'pending'),
                        };
                        $statusColor = match($wfStatus) {
                            'pending_review', 'received' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'in_review' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'returned' => 'bg-red-50 text-red-700 border-red-200',
                            'approved' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            default => 'bg-gray-50 text-gray-600 border-gray-200',
                        };
                    @endphp
                    <tr x-show="expanded === {{ $group['student_id'] }}" x-cloak
                        class="hover:bg-gray-50/50 bg-gray-50/30">
                        <td class="px-3 py-2 text-sm text-gray-900 pl-10">
                            <span class="inline-flex items-center gap-2">
                                <span class="w-4 border-t border-gray-300 shrink-0"></span>
                                <span class="font-medium">{{ $sd->requiredDocument?->name ?? '—' }}</span>
                            </span>
                            @if ($isLate)
                                <span class="inline-flex items-center ms-1.5 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">{{ __('Late') }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center">
                            <span class="inline-flex rounded-full border px-2 py-0.5 text-xs font-semibold {{ $statusColor }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right whitespace-nowrap">
                            @if ($student)
                                <button type="button"
                                        class="inline-flex items-center gap-1 rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors"
                                        hx-post="{{ route('student-documents.review', ['student' => $student, 'studentDocument' => $sd]) }}"
                                        hx-target="#review-panel-content"
                                        hx-swap="innerHTML"
                                        hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                        hx-on::before-request="window.dispatchEvent(new CustomEvent('open-review-panel'))"
                                        hx-on::after-request="window.dispatchEvent(new CustomEvent('review-panel-loaded'))">
                                    <i class="bi bi-clipboard-check"></i>{{ __('Review') }}
                                </button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="3" class="px-3 py-8 text-center text-sm text-gray-500">
                        <i class="bi bi-inbox text-3xl text-gray-300 block mb-2"></i>
                        {{ __('No documents are waiting for your role right now.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if ($documents->hasPages())
    <div class="px-4 pb-4">@include('partials.htmx-pagination', ['paged' => $documents, 'hxTarget' => '#queue-table-wrapper'])</div>
@endif
