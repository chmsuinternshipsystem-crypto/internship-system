<div class="table-wrap overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 custom-table custom-table--fixed">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Section') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Docs') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Risk') }}</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($studentsWithCompliance as $row)
                @php
                    $student = $row['student'];
                    $status = $row['status'];
                    $submitted = $row['submitted_mandatory'];
                    $total = $row['total_mandatory'];
                    $cls = match($status) {
                        'compliant' => 'badge-compliant',
                        'partially_compliant' => 'badge-partial',
                        'non_compliant' => 'badge-non-compliant',
                        default => 'badge-default',
                    };
                    $label = match($status) {
                        'compliant' => __('Complete'),
                        'partially_compliant' => __('In Progress'),
                        'non_compliant' => __('Needs Submission'),
                        'no_required_documents' => __('No mandatory documents'),
                        default => $status,
                    };
                @endphp
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        {{ $student->student_number }}
                        <div class="text-xs text-gray-500">{{ $student->name }}</div>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        {{ $student->section ?: '—' }}
                    </td>
                    <td class="px-4 py-2 text-sm cell-tight">
                        <span class="status-badge {{ $cls }}">{{ $label }}</span>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-tight">
                        @if ($status === 'no_required_documents')
                            -
                        @else
                            @php
                                $progress = $total > 0 ? (int) round(($submitted / $total) * 100) : 0;
                            @endphp
                            <div class="font-medium text-gray-900">{{ $submitted }} / {{ $total }}</div>
                            <div class="mt-1 h-1.5 w-28 rounded-full bg-gray-100">
                                <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $progress }}%"></div>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        @php
                            $activeFlags = $row['student']->activeRiskFlags ?? collect();
                            $flagCount = $activeFlags->count();
                            $hasFlags = $flagCount > 0;
                        @endphp
                        @if ($hasFlags)
                            @php $isCritical = $row['risk_level'] === 'critical' || $activeFlags->where('severity', 'critical')->isNotEmpty(); @endphp
                            <span class="inline-flex items-center gap-1" title="{{ $flagCount }} flag(s): {{ $activeFlags->pluck('message')->implode('; ') }}">
                                <span class="inline-block w-2.5 h-2.5 rounded-full {{ $isCritical ? 'bg-red-500' : 'bg-amber-500' }}"></span>
                                <span class="text-xs text-gray-500">{{ $flagCount }}</span>
                            </span>
                        @else
                            @php $dotCls = $row['risk_level'] === 'critical' ? 'bg-red-500' : ($row['risk_level'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500'); @endphp
                            <span class="inline-flex items-center gap-1" title="{{ ucfirst($row['risk_level']) }}">
                                <span class="inline-block w-2 h-2 rounded-full {{ $dotCls }}"></span>
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right text-sm font-medium cell-tight">
                        <x-action-menu :id="'compliance-'.$student->id">
                            @if ($canManage)
                                <a href="{{ route('student-documents.edit', ['student' => $student, 'return' => 'compliance']) }}">
                                    <i class="bi bi-clipboard-check"></i> {{ __('Review') }}
                                </a>
                            @endif
                            <a href="{{ route('students.show', $student) }}">
                                <i class="bi bi-person"></i> {{ __('View Profile') }}
                            </a>
                        </x-action-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <strong>{{ __('No records found') }}</strong>
                        <p>{{ __('Nothing here yet.') }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if (isset($paginatedStudents))
    @include('partials.htmx-pagination', ['paged' => $paginatedStudents, 'hxTarget' => '#compliance-ajax-mount'])
@endif

