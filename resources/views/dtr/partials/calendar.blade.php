<div class="space-y-4">
    {{-- Month Navigation --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('dtr.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
           hx-get="{{ route('dtr.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
           hx-target="#dtr-calendar-mount"
           class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            <i class="bi bi-chevron-left"></i> {{ $prevMonth->format('M Y') }}
        </a>
        <h3 class="text-lg font-semibold text-gray-900">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h3>
        <a href="{{ route('dtr.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
           hx-get="{{ route('dtr.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
           hx-target="#dtr-calendar-mount"
           class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
            {{ $nextMonth->format('M Y') }} <i class="bi bi-chevron-right"></i>
        </a>
    </div>

    {{-- Calendar Grid --}}
    <div class="grid grid-cols-7 gap-px bg-gray-200 rounded-lg overflow-hidden shadow-sm">
        {{-- Day Headers --}}
        @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $day)
            <div class="bg-gray-50 px-2 py-1.5 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                {{ __($day) }}
            </div>
        @endforeach

        {{-- Empty cells before first day --}}
        @php
            $firstDayOfWeek = ($startOfMonth->dayOfWeek === 0) ? 6 : $startOfMonth->dayOfWeek - 1;
        @endphp
        @for ($i = 0; $i < $firstDayOfWeek; $i++)
            <div class="bg-gray-50/50 min-h-[80px]"></div>
        @endfor

        {{-- Day Cells --}}
        @for ($d = 1; $d <= $startOfMonth->daysInMonth; $d++)
            @php
                $date = Carbon\Carbon::create($year, $month, $d);
                $dateKey = $date->format('Y-m-d');
                $dayRecords = $attendanceByDate[$dateKey] ?? [];
                $isToday = $date->isToday();
                $count = count($dayRecords);
            @endphp
            <div class="bg-white min-h-[80px] p-1.5 {{ $isToday ? 'ring-2 ring-emerald-400 ring-inset' : '' }}">
                <div class="text-xs font-semibold {{ $isToday ? 'text-emerald-600' : 'text-gray-500' }} mb-1">
                    {{ $d }}
                    @if ($count > 0)
                        <span class="text-[10px] text-gray-400 font-normal">({{ $count }})</span>
                    @endif
                </div>
                @if ($count > 0)
                    <div class="space-y-0.5">
                        @foreach ($dayRecords as $record)
                            <a href="{{ route('dtr.show', $record) }}"
                               class="block text-[10px] leading-tight px-1 py-0.5 rounded {{ $record->total_minutes && $record->total_minutes >= 480 ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }} hover:underline truncate"
                               title="{{ $record->student?->name }} — {{ $record->total_minutes ? $record->total_minutes.' min' : __('Incomplete') }}">
                                <i class="bi bi-person"></i>
                                {{ $record->student?->name }}
                                @if ($record->total_minutes)
                                    <span class="text-[9px] opacity-60">{{ round($record->total_minutes / 60, 1) }}h</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        @endfor
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-4 text-xs text-gray-500">
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-emerald-50 border border-emerald-200"></span> {{ __('Full day (8h+)') }}</span>
        <span class="inline-flex items-center gap-1"><span class="w-3 h-3 rounded bg-amber-50 border border-amber-200"></span> {{ __('Partial / Incomplete') }}</span>
    </div>
</div>

{{-- Summary Bar --}}
<div class="mt-4 pt-3 border-t border-gray-100 text-xs text-gray-500">
    {{ __('Showing :count students with attendance records in :month.', [
        'count' => count($attendanceByDate),
        'month' => Carbon\Carbon::create($year, $month, 1)->format('F Y'),
    ]) }}
</div>
