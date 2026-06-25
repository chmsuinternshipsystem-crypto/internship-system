<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Weekly Journals'), 'url' => route('weekly-journals.index')],
    ['label' => __('Week ').$weeklyJournal->week_number],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Monitoring') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Weekly Journal') }} #{{ $weeklyJournal->week_number }}
                    <span class="text-sm font-normal text-gray-500">
                        — {{ $weeklyJournal->student->name }} ({{ $weeklyJournal->student->student_number }})
                    </span>
                </h2>
                <p class="text-sm text-gray-500">
                    {{ $weeklyJournal->week_start_date->format('M d, Y') }} – {{ $weeklyJournal->week_end_date->format('M d, Y') }}
                    • {{ $weeklyJournal->deployment?->company?->name ?? ($weeklyJournal->deployment ? __('Internal OJT') : __('No company')) }}
                    @if ($weeklyJournal->is_late)
                        <span class="ml-2 text-xs font-medium text-red-700 bg-red-50 px-1.5 py-0.5 rounded">{{ __('Late') }}</span>
                    @endif
                    <span class="ml-2 status-badge text-xs {{ match($weeklyJournal->status) { 'reviewed' => 'badge-completed', 'submitted' => 'badge-active', default => 'badge-default' } }}">
                        {{ Str::headline($weeklyJournal->status) }}
                    </span>
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('weekly-journals.student', $weeklyJournal->student) }}"
                   class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    <i class="bi bi-list-ul me-1"></i>{{ __('All Journals') }}
                </a>
                <a href="{{ route('weekly-journals.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if ($weeklyJournal->is_late)
                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flex items-center gap-2">
                    <i class="bi bi-clock-history"></i>
                    <span>{{ __('This journal was submitted late (after the week ended).') }}</span>
                </div>
            @endif

            {{-- Student's Journal --}}
            @php
                $dayNames = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $start = $weeklyJournal->week_start_date->copy();
                $activities = $weeklyJournal->activities ?? [];
                $files = $weeklyJournal->files ?? [];
            @endphp

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-md font-semibold text-gray-800 flex items-center gap-2">
                            <i class="bi bi-list-task text-emerald-600"></i>
                            {{ __('Daily Activities') }}
                        </h3>
                        @if ($weeklyJournal->submitted_at)
                            <span class="text-xs text-gray-400">
                                {{ __('Submitted') }}: {{ $weeklyJournal->submitted_at->format('M d, Y h:i A') }}
                            </span>
                        @endif
                    </div>

                    @if (count($activities) === 0 && count($files) === 0 && ! $weeklyJournal->supervisor_name)
                        <p class="text-sm text-gray-500">{{ __('No entries submitted for this week.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-20">{{ __('Day') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-36">{{ __('Date') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Activities / Tasks') }}</th>
                                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase w-48">{{ __('Attachment') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($dayNames as $i => $dayName)
                                        @php
                                            $currentDate = $start->copy()->addDays($i);
                                            $dayActivity = collect($activities)->firstWhere('day', $dayName);
                                            $dayFile = collect($files)->firstWhere('day', $dayName);
                                        @endphp
                                        <tr>
                                            <td class="px-3 py-2 text-sm font-medium text-gray-900 whitespace-nowrap">{{ __($dayName) }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-600 whitespace-nowrap">{{ $currentDate->format('M d, Y') }}</td>
                                            <td class="px-3 py-2 text-sm text-gray-700 whitespace-pre-wrap">{{ $dayActivity['tasks'] ?? '—' }}</td>
                                            <td class="px-3 py-2 text-sm">
                                                @if ($dayFile && isset($dayFile['file_path']))
                                                    <a href="{{ route('weekly-journals.file', ['weeklyJournal' => $weeklyJournal, 'day' => $dayFile['day']]) }}" target="_blank"
                                                       class="inline-flex items-center gap-1 text-xs text-emerald-700 hover:text-emerald-900">
                                                        <i class="bi bi-paperclip"></i>
                                                        <span class="truncate max-w-[140px]">{{ $dayFile['file_name'] ?? __('File') }}</span>
                                                    </a>
                                                @else
                                                    <span class="text-gray-400">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Supervisor --}}
            @if ($weeklyJournal->supervisor_name)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="bi bi-person-badge text-blue-600"></i>
                            {{ __('Supervisor') }}
                        </h3>
                        <p class="text-sm text-gray-700">{{ $weeklyJournal->supervisor_name }}</p>
                    </div>
                </div>
            @endif

            {{-- Remarks --}}
            @if ($weeklyJournal->remarks)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-2">
                            <i class="bi bi-chat-square-text text-amber-600"></i>
                            {{ __('Previous Review Remarks') }}
                        </h3>
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                            <p class="whitespace-pre-wrap">{{ $weeklyJournal->remarks }}</p>
                        </div>
                        @if ($weeklyJournal->reviewer)
                            <p class="mt-2 text-xs text-gray-400">
                                {{ __('Reviewed by') }} {{ $weeklyJournal->reviewer->name }}
                                @if ($weeklyJournal->reviewed_at)
                                    • {{ $weeklyJournal->reviewed_at->format('M d, Y h:i A') }}
                                @endif
                            </p>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Review Panel --}}
            @if ($weeklyJournal->isSubmitted())
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-md font-semibold text-gray-800 flex items-center gap-2 mb-3">
                            <i class="bi bi-check-circle text-emerald-600"></i>
                            {{ __('Review Journal') }}
                        </h3>
                        <form method="POST" action="{{ route('weekly-journals.review', $weeklyJournal) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="remarks" class="block text-sm font-medium text-gray-700">{{ __('Remarks (optional)') }}</label>
                                <textarea name="remarks" id="remarks" rows="3"
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm"
                                          placeholder="{{ __('Add any comments about this journal entry...') }}"></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                                    <i class="bi bi-check-lg me-1"></i>{{ __('Mark as Reviewed') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
