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
                                <button type="button" @click="$dispatch('open-archive-modal', { id: {{ $deployment->id }}, name: 'Deployment #{{ $deployment->id }}' })"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-3">
                                    <i class="bi bi-archive"></i><span class="hidden sm:inline">{{ __('Archive') }}</span>
                                </button>
                            @endif
                        </x-action-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <div class="flex flex-col items-center justify-center py-8">
                            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gray-50 border border-gray-100 mb-4">
                                <i class="bi bi-briefcase text-2xl text-gray-400"></i>
                            </span>
                            <strong class="text-sm font-semibold text-gray-700">{{ __('No deployments yet') }}</strong>
                            <p class="mt-1 text-sm text-gray-500 max-w-sm">{{ __('Assign students to companies to create deployments.') }}</p>
                            @if ($canManage)
                                <a href="{{ route('deployments.create') }}" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm btn-lift">
                                    <i class="bi bi-plus-lg"></i>
                                    {{ __('Add Deployment') }}
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('partials.htmx-pagination', ['paged' => $deployments, 'hxTarget' => '#deployments-ajax-mount'])

