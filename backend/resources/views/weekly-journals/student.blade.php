<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Weekly Journals'), 'url' => route('weekly-journals.index')],
    ['label' => $student->name],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Monitoring') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $student->name }} ({{ $student->student_number }})
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Section') }} {{ $student->section }} • {{ $student->program }}
                    @if ($student->deployments()->whereIn('status', ['active', 'completed'])->exists())
                        • {{ $student->deployments()->whereIn('status', ['active', 'completed'])->latest()->first()->company->name ?? '' }}
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('students.show', $student) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    <i class="bi bi-person me-1"></i>{{ __('Student Profile') }}
                </a>
                <a href="{{ route('weekly-journals.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    {{ __('Back to List') }}
                </a>
            </div>
        </div>
    </x-slot>

    <x-page-card compact>
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                <i class="bi bi-journal-text text-emerald-600"></i>
                {{ __('Weekly Journals') }}
            </h3>
            @php
                $total = $journals->count();
                $reviewed = $journals->where('status', 'reviewed')->count();
            @endphp
            @if ($total > 0)
                <span class="text-sm text-gray-500">
                    {{ $reviewed }}/{{ $total }} {{ __('reviewed') }}
                    ({{ (int) round(($reviewed / $total) * 100) }}%)
                </span>
            @endif
        </div>

        @if ($journals->isEmpty())
            <div class="empty-state">
                <i class="bi bi-journal"></i>
                <strong>{{ __('No weekly journals yet') }}</strong>
                <p>{{ __('This student has not created any weekly journal entries.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 custom-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Week') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date Range') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Days Logged') }}</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Supervisor') }}</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($journals as $journal)
                            @php
                                $daysLogged = $journal->activities ? count(array_filter($journal->activities, fn($a) => ! empty($a['tasks']))) : 0;
                            @endphp
                            <tr>
                                <td class="px-4 py-2 whitespace-nowrap text-sm font-medium text-gray-900">#{{ $journal->week_number }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                    {{ $journal->week_start_date->format('M d') }} – {{ $journal->week_end_date->format('M d, Y') }}
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm">
                                    <div class="flex items-center gap-1.5">
                                        <span class="status-badge {{ match($journal->status) { 'reviewed' => 'badge-completed', 'submitted' => 'badge-active', default => 'badge-default' } }}">
                                            {{ Str::headline($journal->status) }}
                                        </span>
                                        @if ($journal->is_late)
                                            <span class="text-xs font-medium text-red-700 bg-red-50 px-1.5 py-0.5 rounded">{{ __('Late') }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ $daysLogged }}/6</td>
                                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">{{ $journal->supervisor_name ?? '—' }}</td>
                                <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                                    <x-action-menu :id="'wj-'.$journal->id">
                                        <a href="{{ route('weekly-journals.show', $journal) }}">
                                            <i class="bi bi-eye"></i> {{ __('View') }}
                                        </a>
                                    </x-action-menu>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-page-card>
</x-app-layout>
