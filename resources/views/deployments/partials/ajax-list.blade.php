<div class="table-wrap overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 custom-table custom-table--fixed">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Company') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Industry') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Dates') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($deployments as $deployment)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        {{ $deployment->student?->student_number }}
                        <div class="text-xs text-gray-500">{{ $deployment->student?->name }}</div>
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">{{ $deployment->company?->name }}</td>
                    <td class="px-4 py-2 text-sm text-gray-600 cell-wrap">{{ $deployment->company?->industry?->name ?? '—' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        {{ $deployment->start_date->format('M d, Y') }}
                        <span class="text-gray-400">–</span>
                        {{ $deployment->end_date ? $deployment->end_date->format('M d, Y') : '—' }}
                    </td>
                    <td class="px-4 py-2 text-sm cell-tight">
                        @php
                            $cls = match($deployment->status) {
                                'active' => 'badge-active',
                                'completed' => 'badge-completed',
                                'withdrawn' => 'badge-withdrawn',
                                default => 'badge-default',
                            };
                        @endphp
                        <span class="status-badge {{ $cls }}">{{ Str::headline($deployment->status) }}</span>
                    </td>
                    <td class="px-4 py-2 text-right text-sm font-medium cell-tight whitespace-nowrap">
                        <x-action-menu :id="'deployment-'.$deployment->id">
                            <a href="{{ route('deployments.show', $deployment) }}"
                               class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-gray-600 rounded-md hover:bg-emerald-50 hover:text-emerald-700 transition-colors">
                                <i class="bi bi-eye"></i><span class="hidden sm:inline">{{ __('View') }}</span>
                            </a>
                            @if ($deployment->student)
                                <a href="{{ route('students.show', $deployment->student) }}"
                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-gray-600 rounded-md hover:bg-blue-50 hover:text-blue-700 transition-colors">
                                    <i class="bi bi-person"></i><span class="hidden sm:inline">{{ __('Student') }}</span>
                                </a>
                            @endif
                            @if ($deployment->company)
                                <a href="{{ route('companies.show', $deployment->company) }}"
                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-gray-600 rounded-md hover:bg-purple-50 hover:text-purple-700 transition-colors">
                                    <i class="bi bi-building"></i><span class="hidden sm:inline">{{ __('Company') }}</span>
                                </a>
                            @endif
                            @if ($canManage)
                                <a href="{{ route('deployments.edit', $deployment) }}"
                                   class="inline-flex items-center gap-1 px-2 py-1 text-xs font-semibold text-gray-600 rounded-md hover:bg-amber-50 hover:text-amber-700 transition-colors">
                                    <i class="bi bi-pencil"></i><span class="hidden sm:inline">{{ __('Edit') }}</span>
                                </a>
                                <span class="w-px h-4 bg-gray-200 mx-0.5"></span>
                                <x-confirm-delete
                                    :action="route('deployments.destroy', $deployment)"
                                    :message="__('Are you sure you want to delete this deployment?')"
                                    :dialog-id="'deployment-del-'.$deployment->id"
                                >
                                    <i class="bi bi-trash"></i><span class="hidden sm:inline">{{ __('Delete') }}</span>
                                </x-confirm-delete>
                            @endif
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
@include('partials.htmx-pagination', ['paged' => $deployments, 'hxTarget' => '#deployments-ajax-mount'])

