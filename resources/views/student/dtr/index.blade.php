<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Daily Time & Tasks Record') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('DTTR') }} &middot; {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</h2>
                <p class="text-sm text-gray-500">{{ $deployment->company?->name ?? ($isSchoolBased ? __('School-based') : $deployment->status) }}</p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <div class="flex items-center bg-white rounded-lg border border-gray-200 shadow-sm">
                    <a href="{{ route('student.dtr.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}"
                       class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-l-lg transition-colors">
                        <i class="bi bi-chevron-left"></i>
                    </a>
                    <span class="px-2 text-xs font-semibold text-gray-600 select-none">{{ \Carbon\Carbon::create($year, $month, 1)->format('M') }}</span>
                    <a href="{{ route('student.dtr.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}"
                       class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-r-lg transition-colors">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </x-slot>

    @php
        $totalMinutes = $dtrRecords->sum('total_minutes');
        $totalHours = $totalMinutes > 0 ? sprintf('%dh %02dm', intdiv($totalMinutes, 60), $totalMinutes % 60) : '0h 00m';
        $presentDays = $dtrRecords->count();
        $daysInMonth = $startOfMonth->daysInMonth;
        $sortedRecords = $dtrRecords->sortBy('date');
        $today = now()->format('Y-m-d');
        $monthStatus = $signedDttr ? 'completed' : (now()->format('Y-m') > "$year-$month" ? 'overdue' : 'pending');
        $weekdayLabels = [__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')];
    @endphp

    {{-- Summary bar --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3 flex-wrap">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium ring-1 ring-emerald-200/50">
                <i class="bi bi-check-circle-fill text-[10px]"></i>
                <span>{{ $presentDays }}/{{ $daysInMonth }} <span class="font-normal text-emerald-500">{{ __('days') }}</span></span>
            </span>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 text-sm font-medium ring-1 ring-blue-200/50">
                <i class="bi bi-clock-fill text-[10px]"></i>
                <span>{{ $totalHours }}</span>
            </span>
            @if($signedDttr)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-sm font-medium ring-1 ring-emerald-200/50">
                    <i class="bi bi-check-circle-fill text-[10px]"></i>
                    <span>{{ __('Submitted') }}</span>
                </span>
            @elseif($monthStatus === 'overdue')
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-sm font-medium ring-1 ring-rose-200/50">
                    <i class="bi bi-exclamation-circle-fill text-[10px]"></i>
                    <span>{{ __('Overdue') }}</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium ring-1 ring-amber-200/50">
                    <i class="bi bi-hourglass-split text-[10px]"></i>
                    <span>{{ __('Pending') }}</span>
                </span>
            @endif
        </div>

        {{-- Monthly actions --}}
        <div class="flex items-center gap-2 flex-wrap"></div>
    </div>

    {{-- DTTR Table --}}
    <x-page-card compact>
        @if($presentDays === 0)
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center mb-4">
                    <i class="bi bi-clock-history text-2xl text-blue-400"></i>
                </div>
                <p class="text-sm font-medium text-gray-700">{{ __('No attendance records found for this month.') }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ __('Clock in to generate DTTR entries automatically.') }}</p>
            </div>
        @else
            <div class="overflow-x-auto -mx-1">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">
                            <th rowspan="2" class="px-2 py-2 text-left w-14 border-b border-gray-200">{{ __('Day') }}</th>
                            <th colspan="2" class="px-2 py-2 text-center w-[88px] bg-amber-50/50 border-b border-gray-200">{{ __('AM Session') }}</th>
                            <th colspan="2" class="px-2 py-2 text-center w-[88px] bg-blue-50/50 border-b border-gray-200">{{ __('PM Session') }}</th>
                            <th rowspan="2" class="px-2 py-2 text-center w-[72px] border-b border-gray-200">{{ __('Hours') }}</th>
                            <th rowspan="2" class="px-2 py-2 text-left min-w-[160px] border-b border-gray-200">
                                {{ __('Tasks') }}
                                <span class="inline-flex items-center gap-0.5 ms-1 text-[10px] font-normal text-emerald-600 normal-case">
                                    <i class="bi bi-pencil"></i>
                                </span>
                            </th>
                        </tr>
                        <tr class="text-[10px] font-semibold text-gray-400 uppercase tracking-widest">
                            <th class="px-1 py-1 text-center w-[88px] bg-amber-50/30">{{ __('Arr') }}</th>
                            <th class="px-1 py-1 text-center w-[88px] bg-amber-50/30">{{ __('Dep') }}</th>
                            <th class="px-1 py-1 text-center w-[88px] bg-blue-50/30">{{ __('Arr') }}</th>
                            <th class="px-1 py-1 text-center w-[88px] bg-blue-50/30">{{ __('Dep') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @php $grandTotal = 0; @endphp
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $date = \Carbon\Carbon::create($year, $month, $day);
                                $dateStr = $date->format('Y-m-d');
                                $isToday = $dateStr === $today;
                                $isWeekend = $date->isWeekend();
                                $dayOfWeek = (int) $date->format('w');
                                $weekdayAbbr = $weekdayLabels[$dayOfWeek];

                                $record = $sortedRecords->firstWhere(fn($r) => (int) $r->date->format('j') === $day);
                                $hasRecord = $record !== null;

                                if ($hasRecord) {
                                    $rowCls = 'cursor-pointer transition-colors hover:bg-gray-50/50';
                                    if ($isToday) $rowCls .= ' bg-emerald-50/30';
                                    elseif ($isWeekend) $rowCls .= ' bg-gray-50/50';

                                    $amIn = $record->am_arrival ? \Carbon\Carbon::parse($record->am_arrival)->format('h:i A') : ($record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '');
                                    $amOut = $record->am_departure ? \Carbon\Carbon::parse($record->am_departure)->format('h:i A') : '';
                                    $pmIn = $record->pm_arrival ? \Carbon\Carbon::parse($record->pm_arrival)->format('h:i A') : '';
                                    $pmOut = $record->pm_departure ? \Carbon\Carbon::parse($record->pm_departure)->format('h:i A') : ($record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '');
                                    $mins = $record->total_minutes ?? 0;
                                    $grandTotal += $mins;
                                    $hrs = $mins > 0 ? sprintf('%dh %02dm', intdiv($mins, 60), $mins % 60) : '';
                                    $tasksText = $record->tasks ?? '';
                                } else {
                                    $rowCls = '';
                                    if ($isToday) $rowCls = ' bg-emerald-50/30';
                                    elseif ($isWeekend) $rowCls = ' bg-gray-50/50';
                                    $amIn = ''; $amOut = ''; $pmIn = ''; $pmOut = ''; $hrs = ''; $tasksText = '';
                                }
                            @endphp
                            <tr class="{{ $rowCls }}"
                                @if($hasRecord)
                                    @click="showDetail('{{ $date->format('l') }}', {{ $day }}, '{{ $amIn }}', '{{ $amOut }}', '{{ $pmIn }}', '{{ $pmOut }}', '{{ $hrs }}', '{{ addslashes($tasksText) }}', {{ $record->id }})"
                                @endif>
                                <td class="px-2 py-2 whitespace-nowrap align-top">
                                    <div class="flex items-baseline gap-1.5">
                                        <span class="text-sm font-semibold {{ $isToday ? 'text-emerald-700' : 'text-gray-900' }}">{{ $day }}</span>
                                        <span class="text-[10px] font-medium {{ $isWeekend ? 'text-gray-300' : 'text-gray-400' }} uppercase">{{ $weekdayAbbr }}</span>
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-center whitespace-nowrap">
                                    @if($amIn)
                                        <span class="text-sm font-mono font-medium text-gray-800">{{ $amIn }}</span>
                                    @else
                                        <span class="text-sm text-gray-300">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center whitespace-nowrap">
                                    @if($amOut)
                                        <span class="text-sm font-mono font-medium text-gray-800">{{ $amOut }}</span>
                                    @else
                                        <span class="text-sm text-gray-300">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center whitespace-nowrap">
                                    @if($pmIn)
                                        <span class="text-sm font-mono font-medium text-gray-800">{{ $pmIn }}</span>
                                    @else
                                        <span class="text-sm text-gray-300">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center whitespace-nowrap">
                                    @if($pmOut)
                                        <span class="text-sm font-mono font-medium text-gray-800">{{ $pmOut }}</span>
                                    @else
                                        <span class="text-sm text-gray-300">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-center whitespace-nowrap">
                                    @if($hrs)
                                        <span class="text-sm font-semibold text-emerald-700">{{ $hrs }}</span>
                                    @else
                                        <span class="text-sm text-gray-300">&mdash;</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2 text-sm max-w-[220px]">
                                    @if ($isToday)
                                        <div @click.stop
                                             x-data="{ editing: false, val: @js($tasksText), saving: false, orig: @js($tasksText) }">
                                            <div x-show="!editing" @click="editing = true; $nextTick(() => { if ($refs.input) $refs.input.focus(); })"
                                                 class="cursor-text block truncate rounded px-1 -mx-1 transition-colors hover:bg-emerald-50 hover:ring-1 hover:ring-emerald-200"
                                                 :class="val ? 'text-gray-600' : 'text-gray-400 italic'">
                                                <template x-if="val">
                                                    <span x-text="val" class="not-italic"></span>
                                                </template>
                                                <template x-if="!val">
                                                    <span>{{ __('Add tasks...') }} <i class="bi bi-pencil text-[10px] not-italic"></i></span>
                                                </template>
                                            </div>
                                            <div x-show="editing" x-cloak class="flex items-center gap-1">
                                                <input type="text" x-model="val" x-ref="input" maxlength="100"
                                                       @blur="if (val !== orig) { saving = true; fetch('{{ route('student.dtr.tasks') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }, body: JSON.stringify({ date: '{{ $dateStr }}', tasks: val }) }).then(r => r.json()).then(d => { if (d.success) { orig = val; if (window.showToast) window.showToast('{{ __('Task saved.') }}', 'success', 2000); } saving = false; editing = false; }).catch(() => { saving = false; }); } else { editing = false; }"
                                                       @keydown.enter="$el.blur()"
                                                       @keydown.escape="val = orig; editing = false"
                                                       class="w-full text-xs border border-emerald-300 rounded px-1.5 py-1 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 outline-none">
                                                <span x-show="saving" class="text-[10px] text-emerald-600 shrink-0">
                                                    <i class="bi bi-arrow-repeat animate-spin"></i>
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        @if($tasksText)
                                            <span class="text-gray-600 text-sm block truncate">{{ $tasksText }}</span>
                                        @else
                                            <span class="text-gray-300">&mdash;</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endfor
                        @php
                            $totalH = intdiv($grandTotal, 60);
                            $totalM = $grandTotal % 60;
                        @endphp
                        <tr class="bg-gray-50/80">
                            <td colspan="5" class="px-2 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">{{ __('Total No. of Hours') }}:</td>
                            <td class="px-2 py-3 text-center font-bold text-emerald-800 whitespace-nowrap">{{ sprintf('%dh %02dm', $totalH, $totalM) }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="mt-3 flex items-center gap-4 border-t pt-3 text-xs text-gray-400 flex-wrap">
                <span class="inline-flex items-center gap-1.5">
                    <i class="bi bi-info-circle"></i>
                    <span>{{ __('Click a day to view details.') }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <i class="bi bi-pencil text-emerald-500"></i>
                    <span>{{ __('Click the Tasks column to add or edit tasks for any day.') }}</span>
                </span>
            </div>
        @endif
    </x-page-card>

    {{-- Detail Modal --}}
    <div x-data="{
        open: false,
        dayName: '', dayNum: 0, amIn: '', amOut: '', pmIn: '', pmOut: '',
        hrs: '', tasks: '', entryId: null,
        showDetail(dayName, dayNum, amIn, amOut, pmIn, pmOut, hrs, tasks, entryId) {
            this.entryId = entryId;
            this.dayName = dayName;
            this.dayNum = dayNum;
            this.amIn = amIn;
            this.amOut = amOut;
            this.pmIn = pmIn;
            this.pmOut = pmOut;
            this.hrs = hrs;
            this.tasks = tasks;
            this.open = true;
        }
    }" x-show="open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         @keydown.escape.window="open = false"
         x-transition:enter="transition duration-200 ease-out"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-150 ease-in"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" @click="open = false"></div>
        {{-- Panel --}}
        <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
             @click.outside="open = false"
             x-transition:enter="transition duration-200 ease-out"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition duration-150 ease-in"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <p class="text-xs text-gray-500 font-medium" x-text="dayName"></p>
                    <h3 class="text-lg font-semibold text-gray-900" x-text="'{{ __('Day') }} ' + dayNum"></h3>
                </div>
                <button @click="open = false" class="w-8 h-8 rounded-full flex items-center justify-center text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                    <i class="bi bi-x-lg text-sm"></i>
                </button>
            </div>
            {{-- Body --}}
            <div class="px-5 py-4 space-y-4">
                {{-- Time grid --}}
                <div class="grid grid-cols-5 gap-2">
                    <div class="rounded-lg border border-gray-200 bg-amber-50/30 p-2.5 text-center">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase">{{ __('AM Arr') }}</p>
                        <p class="text-sm font-mono font-semibold text-gray-900 mt-0.5" x-text="amIn || '\u2014'"></p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-amber-50/30 p-2.5 text-center">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase">{{ __('AM Dep') }}</p>
                        <p class="text-sm font-mono font-semibold text-gray-900 mt-0.5" x-text="amOut || '\u2014'"></p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-blue-50/30 p-2.5 text-center">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase">{{ __('PM Arr') }}</p>
                        <p class="text-sm font-mono font-semibold text-gray-900 mt-0.5" x-text="pmIn || '\u2014'"></p>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-blue-50/30 p-2.5 text-center">
                        <p class="text-[10px] font-semibold text-gray-500 uppercase">{{ __('PM Dep') }}</p>
                        <p class="text-sm font-mono font-semibold text-gray-900 mt-0.5" x-text="pmOut || '\u2014'"></p>
                    </div>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-2.5 text-center">
                        <p class="text-[10px] font-semibold text-emerald-600 uppercase">{{ __('Hours') }}</p>
                        <p class="text-sm font-mono font-bold text-emerald-800 mt-0.5" x-text="hrs || '\u2014'"></p>
                    </div>
                </div>
                {{-- Tasks --}}
                <div x-show="tasks">
                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">{{ __('Tasks') }}</p>
                    <div class="rounded-lg border border-gray-200 bg-gray-50/50 px-3 py-2.5">
                        <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed" x-text="tasks"></p>
                    </div>
                </div>
            </div>
            {{-- Footer --}}
            <div class="flex items-center justify-end gap-2 px-5 py-3 border-t border-gray-100 bg-gray-50/50">
                <button @click="open = false"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest btn-primary shadow-sm transition-colors">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>
</x-app-layout>