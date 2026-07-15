<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-md font-semibold flex items-center gap-1.5">
                <i class="bi bi-journal text-gray-400"></i>
                {{ __('Weekly Journals') }}
            </h3>
            <a href="{{ route('weekly-journals.student', $student) }}"
               class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                {{ __('Full journal view') }} &rarr;
            </a>
        </div>

        @if ($journals->isEmpty())
            <p class="text-sm text-gray-500">{{ __('No journals yet.') }}</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-2">
                @foreach ($journals as $journal)
                    @php
                        $badgeColor = match($journal->status) {
                            'reviewed' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                            'submitted' => 'bg-blue-100 text-blue-800 border-blue-200',
                            default => 'bg-gray-100 text-gray-600 border-gray-200',
                        };
                    @endphp
                    <a href="{{ route('weekly-journals.show', $journal) }}"
                       class="block border rounded-lg p-3 text-center hover:shadow-sm transition-shadow {{ $badgeColor }}">
                        <span class="block text-sm font-bold">{{ $journal->week_number }}</span>
                        <span class="block text-[10px] mt-0.5 opacity-75">{{ __('Week') }}</span>
                        <span class="block text-[10px] mt-0.5">{{ Str::headline($journal->status) }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
