<div class="table-wrap">
    <table class="min-w-full divide-y divide-gray-200 custom-table custom-table--fixed">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Score') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Evaluator') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($evaluations as $evaluation)
                @php
                    $supervisorMeta = $evaluation->extractSupervisorMetaFromComments();
                @endphp
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        {{ $evaluation->student?->student_number }}
                        <div class="text-xs text-gray-500">{{ $evaluation->student?->name }}</div>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        @if (($evaluation->evaluation_type ?? 'industry') === 'student_feedback')
                            <span class="inline-flex rounded-full bg-purple-100 px-2 py-0.5 text-xs font-semibold text-purple-700">{{ __('Student Feedback') }}</span>
                        @elseif (($evaluation->evaluation_type ?? 'industry') === 'school')
                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">{{ __('School') }}</span>
                        @else
                            <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">{{ __('Industry') }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm font-semibold text-gray-900 cell-tight">{{ $evaluation->score }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700 cell-wrap">
                        <div>{{ $evaluation->evaluatorDisplayLabel() }}</div>
                        @if (!empty($supervisorMeta['email']) && ($evaluation->evaluation_type ?? 'industry') === 'industry')
                            <div class="text-xs text-gray-500 break-all">{{ $supervisorMeta['email'] }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-700 cell-tight">{{ $evaluation->evaluated_at?->format('M d, Y') ?? '—' }}</td>
                    <td class="px-4 py-2 text-right text-sm font-medium cell-tight">
                        <x-action-menu :id="'evaluation-'.$evaluation->id">
                            <a href="{{ route('evaluations.show', $evaluation) }}">
                                <i class="bi bi-eye"></i> {{ __('View') }}
                            </a>
                            @if ($canManage)
                                <a href="{{ route('evaluations.edit', $evaluation) }}">
                                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                </a>
                                <div class="action-divider"></div>
                                <x-confirm-delete
                                    :action="route('evaluations.destroy', $evaluation)"
                                    :message="__('Are you sure you want to delete this evaluation?')"
                                    :dialog-id="'evaluation-del-'.$evaluation->id"
                                >
                                    <i class="bi bi-trash"></i> {{ __('Delete') }}
                                </x-confirm-delete>
                            @endif
                        </x-action-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <strong>{{ __('No evaluations yet') }}</strong>
                        <p class="max-w-md mx-auto">{{ __('When industry or school evaluations are recorded, they will appear here. Adjust filters if you expected older records.') }}</p>
                        @if (! empty($canManage) && $canManage)
                            <div class="mt-4 flex flex-wrap justify-center gap-2">
                                <a href="{{ route('evaluations.create') }}" class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                                    {{ __('Add evaluation') }}
                                </a>
                                <a href="{{ route('evaluations.hte-links.create') }}" class="inline-flex items-center rounded-md border border-emerald-200 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50">
                                    {{ __('Send HTE link') }}
                                </a>
                            </div>
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('partials.htmx-pagination', ['paged' => $evaluations, 'hxTarget' => '#evaluation-ajax-mount'])
