@php
    $actorRole = (string) (auth()->user()?->role ?? '');
    $lastAction = fn ($sd) => $sd?->actions?->first();
    $auditLabel = function ($sd) use ($lastAction) {
        $action = $lastAction($sd);
        if (! $action) return null;
        $label = str($action->action)->replace('_', ' ')->title();
        $actorName = $action->actor?->name;
        return $actorName ? "{$label} · {$actorName} · {$action->acted_at->diffForHumans()}" : "{$label} · {$action->acted_at->diffForHumans()}";
    };
    $phaseLabels = [
        'pre' => __('Pre-Deployment'),
        'monitoring' => __('Monitoring'),
        'all' => __('General'),
    ];
@endphp
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 custom-table text-xs">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-2 py-1.5 text-center text-[11px] font-medium text-gray-500 uppercase tracking-wider w-10">
                    <span class="sr-only">{{ __('Status') }}</span>
                </th>
                <th class="px-3 py-1.5 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                    {{ __('Document') }}
                </th>
                <th class="px-3 py-1.5 text-left text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                    {{ __('Status') }}
                </th>
                <th class="px-3 py-1.5 text-right text-[11px] font-medium text-gray-500 uppercase tracking-wider">
                    {{ __('Action') }}
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach ($groupedDocs as $phase => $docs)
                @php
                    $totalInPhase = $docs->count();
                    $completedInPhase = $docs->filter(fn ($d) => ($existing->get($d->id)?->workflow_status ?? '') === 'completed')->count();
                @endphp
                <tr class="bg-gray-50/80">
                    <td colspan="4" class="px-3 py-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if (isset($phaseLabels[$phase]))
                                <span class="inline-flex items-center rounded-md bg-emerald-100 px-2 py-0.5 text-[11px] font-semibold text-emerald-800">
                                    {{ $phaseLabels[$phase] }}
                                </span>
                                @endif
                                <span class="text-[11px] text-gray-500 font-medium">
                                    {{ $completedInPhase }} / {{ $totalInPhase }} {{ __('completed') }}
                                </span>
                            </div>
                        </div>
                    </td>
                </tr>
                @foreach ($docs as $doc)
                @php
                    $studentDoc = $existing->get($doc->id);
                    $isWorkflow = ($studentDoc?->workflow_template_id ?? $doc->workflow_template_id ?? 0) > 0;
                    $hasFile = $studentDoc && $studentDoc->file_path && Storage::disk('public')->exists($studentDoc->file_path);
                    $wfDone = $studentDoc && ($studentDoc->workflow_status ?? '') === 'completed';
                    $wfInReview = $studentDoc && $isWorkflow && in_array((string) ($studentDoc->workflow_status ?? ''), ['received', 'under_review', 'pending_review'], true);
                    $displayStatus = $wfDone ? __('Completed') : ($studentDoc?->status ?? __('Pending'));
                @endphp
                <tr id="doc-req-{{ $doc->id }}" class="hover:bg-gray-50/50 transition-colors">
                    <td class="px-2 py-2.5 align-top text-center w-10">
                        @if ($wfDone)
                            <i class="bi bi-check-circle-fill text-emerald-600 text-base" title="{{ __('Completed') }}"></i>
                        @elseif ($wfInReview)
                            <i class="bi bi-hourglass-split text-amber-500 text-base" title="{{ __('In review') }}"></i>
                        @elseif ($hasFile)
                            <i class="bi bi-upload text-sky-600 text-base" title="{{ __('File uploaded') }}"></i>
                        @else
                            <i class="bi bi-file-earmark-text text-gray-300 text-base" title="{{ __('Not submitted') }}"></i>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-xs text-gray-900 cell-wrap align-top">
                        <div class="font-semibold">{{ $doc->name }}</div>
                        @if ($doc->description)
                            <div class="text-[11px] text-gray-500 mt-0.5">{{ $doc->description }}</div>
                        @endif
                        @if ($hasFile)
                            <a href="{{ route('student-documents.download', ['student' => $student, 'studentDocument' => $studentDoc]) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1 mt-1.5 text-[11px] font-medium text-emerald-700 hover:text-emerald-800 hover:underline">
                                <i class="bi bi-file-earmark-check"></i>{{ basename($studentDoc->file_path) }}
                            </a>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-xs align-top">
                        @if ($wfDone)
                            <span class="status-badge badge-completed">{{ __('Completed') }}</span>
                        @elseif ($isWorkflow && $wfInReview)
                            <span class="inline-flex rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">
                                {{ str($studentDoc->workflow_status ?? 'received')->replace('_', ' ')->title() }}
                            </span>
                        @elseif ($hasFile)
                            <span class="status-badge badge-completed">{{ __('Submitted') }}</span>
                        @else
                            <span class="status-badge badge-default">{{ __('Pending') }}</span>
                        @endif
                    </td>
                    <td class="px-3 py-2.5 text-xs text-right align-top cell-tight">
                        @if ($wfDone)
                            <span class="text-[11px] text-gray-400 font-medium">{{ __('Complete') }}</span>
                            @php $label = $auditLabel($studentDoc); @endphp
                            @if ($label)
                                <div class="mt-0.5 text-[10px] text-gray-400">{{ $label }}</div>
                            @endif
                        @elseif ($isWorkflow && !$hasFile)
                            <span class="text-[11px] text-gray-400 italic">{{ __('Awaiting upload') }}</span>
                        @elseif ($isWorkflow && $hasFile)
                            <button type="button"
                                    class="inline-flex items-center text-emerald-700 hover:text-emerald-800 text-[11px] font-semibold"
                                    hx-post="{{ route('student-documents.review', ['student' => $student, 'studentDocument' => $studentDoc]) }}"
                                    hx-target="#review-panel-content"
                                    hx-swap="innerHTML"
                                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                    hx-on::before-request="window.dispatchEvent(new CustomEvent('open-panel'))"
                                    hx-on::after-request="window.dispatchEvent(new CustomEvent('panel-loaded'))">
                                <i class="bi bi-clipboard-check me-1"></i>{{ __('Review') }}
                            </button>
                            @php $label = $auditLabel($studentDoc); @endphp
                            @if ($label)
                                <div class="mt-0.5 text-[10px] text-gray-400">{{ $label }}</div>
                            @endif
                        @elseif (!$isWorkflow && $hasFile)
                            <button type="button"
                                    class="inline-flex items-center text-emerald-700 hover:text-emerald-800 text-[11px] font-semibold"
                                    hx-get="{{ route('student-documents.preview', ['student' => $student, 'studentDocument' => $studentDoc]) }}"
                                    hx-target="#review-panel-content"
                                    hx-swap="innerHTML"
                                    hx-on::before-request="window.dispatchEvent(new CustomEvent('open-panel'))"
                                    hx-on::after-request="window.dispatchEvent(new CustomEvent('panel-loaded'))">
                                <i class="bi bi-eye me-1"></i>{{ __('Review') }}
                            </button>
                            @php $label = $auditLabel($studentDoc); @endphp
                            @if ($label)
                                <div class="mt-0.5 text-[10px] text-gray-400">{{ $label }}</div>
                            @endif
                        @else
                            <button type="button"
                                    class="inline-flex items-center text-sky-700 hover:text-sky-800 text-[11px] font-semibold"
                                    hx-get="{{ route('student-documents.upload-panel', ['student' => $student, 'requiredDocument' => $doc]) }}"
                                    hx-target="#review-panel-content"
                                    hx-swap="innerHTML"
                                    hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'
                                    hx-on::before-request="window.dispatchEvent(new CustomEvent('open-panel'))"
                                    hx-on::after-request="window.dispatchEvent(new CustomEvent('panel-loaded'))">
                                <i class="bi bi-upload me-1"></i>{{ __('Upload') }}
                            </button>
                        @endif
                    </td>
                </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    @include('partials.htmx-pagination', ['paged' => $documentsPaginator, 'hxTarget' => '#docs-table-wrapper', 'hxPushUrl' => true])
</div>
