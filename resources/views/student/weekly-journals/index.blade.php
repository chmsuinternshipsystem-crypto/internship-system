<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Weekly Journal') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My Weekly Journals') }}</h2>
                @if ($deployment)
                    <p class="text-sm text-gray-500">
                        {{ $deployment->company?->name ?? ($deployment ? __('Internal OJT') : __('No company assigned')) }}
                        @if ($deployment->company && ($deployment->company->contact_first_name || $deployment->company->contact_last_name))
                            &middot; {{ __('Supervisor') }}: {{ trim($deployment->company->contact_first_name . ' ' . $deployment->company->contact_last_name) }}
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </x-slot>

    <x-page-card compact>
        @if ($weeklyJournals->isEmpty())
            <div class="empty-state">
                <i class="bi bi-journal"></i>
                <strong>{{ __('No weekly journals yet') }}</strong>
                <p>{{ __('Weeks will be generated automatically once your deployment is active.') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach ($weeklyJournals as $journal)
                    @php
                        $statusCls = match($journal->status) {
                            'reviewed' => 'ring-emerald-400 bg-emerald-50',
                            'submitted' => 'ring-amber-400 bg-amber-50',
                            default => 'ring-gray-200 bg-white hover:ring-emerald-300',
                        };
                        $daysLogged = $journal->activities ? count(array_filter($journal->activities, fn($a) => ! empty($a['tasks']))) : 0;
                        $hasFiles = $journal->files && count($journal->files) > 0;
                    @endphp
                    <a href="/student/weekly-journals/{{ $journal->id }}"
                       class="block rounded-xl ring-1 {{ $statusCls }} p-5 transition-all hover:shadow-md">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-lg font-bold text-gray-800">#{{ $journal->week_number }}</span>
                            <div class="flex items-center gap-1.5">
                                @if ($journal->is_late)
                                    <span class="text-xs font-medium text-red-700 bg-red-50 px-1.5 py-0.5 rounded">{{ __('Late') }}</span>
                                @endif
                                <span class="status-badge text-xs {{ match($journal->status) { 'reviewed' => 'badge-completed', 'submitted' => 'badge-active', default => 'badge-default' } }}">
                                    {{ Str::headline($journal->status) }}
                                </span>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 mb-3">
                            {{ $journal->week_start_date->format('M d') }} – {{ $journal->week_end_date->format('M d, Y') }}
                        </p>
                        <div class="flex items-center gap-3 text-xs text-gray-400">
                            <span><i class="bi bi-list-task me-1"></i>{{ $daysLogged }}/6 {{ __('days') }}</span>
                            @if ($hasFiles)
                                <span><i class="bi bi-paperclip me-1"></i>{{ count($journal->files) }}</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </x-page-card>
</x-app-layout>
