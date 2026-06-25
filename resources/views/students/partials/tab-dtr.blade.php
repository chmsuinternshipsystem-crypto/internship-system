<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-md font-semibold flex items-center gap-1.5">
                <i class="bi bi-clock text-gray-400"></i>
                {{ __('Daily Time Record') }}
            </h3>
        </div>

        @if ($dtrRecords->isEmpty())
            <p class="text-sm text-gray-500">{{ __('No DTR records found.') }}</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-xs font-medium text-gray-500 uppercase border-b border-gray-200">
                            <th class="px-3 py-2">{{ __('Day') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('AM Arr') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('AM Dep') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('PM Arr') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('PM Dep') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Hours') }}</th>
                            <th class="px-3 py-2">{{ __('Tasks') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($dtrRecords as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 whitespace-nowrap text-gray-900 font-medium">
                                    {{ $record->date?->format('M d, Y') ?? __('No date') }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center font-mono text-gray-700">
                                    {{ $record->am_arrival ? $record->am_arrival->format('h:i A') : '—' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center font-mono text-gray-700">
                                    {{ $record->am_departure ? $record->am_departure->format('h:i A') : '—' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center font-mono text-gray-700">
                                    {{ $record->pm_arrival ? $record->pm_arrival->format('h:i A') : '—' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center font-mono text-gray-700">
                                    {{ $record->pm_departure ? $record->pm_departure->format('h:i A') : '—' }}
                                </td>
                                <td class="px-3 py-2 whitespace-nowrap text-center font-mono font-medium text-emerald-700">
                                    {{ $record->total_minutes ? intdiv($record->total_minutes, 60) . 'h ' . ($record->total_minutes % 60) . 'm' : '—' }}
                                </td>
                                <td class="px-3 py-2 text-sm max-w-[160px]">
                                    @if ($record->tasks)
                                        <span class="text-gray-700 truncate block" title="{{ $record->tasks }}">{{ $record->tasks }}</span>
                                    @else
                                        <span class="text-gray-300">&mdash;</span>
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
