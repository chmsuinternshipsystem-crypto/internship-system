@php
    $weekdayLabels = [__('Sun'), __('Mon'), __('Tue'), __('Wed'), __('Thu'), __('Fri'), __('Sat')];
@endphp

{{-- Month navigator --}}
<div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        <a href="{{ route('dtr.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'section' => $section, 'search' => $search, 'student' => $selectedStudentId]) }}"
           class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
           hx-get="{{ route('dtr.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'section' => $section, 'search' => $search, 'student' => $selectedStudentId]) }}"
           hx-target="#dtr-list-mount" hx-push-url="true">
            <i class="bi bi-chevron-left"></i>
        </a>
        <span class="text-lg font-semibold text-gray-800 px-2">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</span>
        <a href="{{ route('dtr.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'section' => $section, 'search' => $search, 'student' => $selectedStudentId]) }}"
           class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors"
           hx-get="{{ route('dtr.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'section' => $section, 'search' => $search, 'student' => $selectedStudentId]) }}"
           hx-target="#dtr-list-mount" hx-push-url="true">
            <i class="bi bi-chevron-right"></i>
        </a>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('dtr.index') }}"
      class="flex flex-wrap items-center gap-2 mb-4"
      hx-get="{{ route('dtr.index') }}" hx-target="#dtr-list-mount" hx-push-url="true"
      hx-trigger="submit, change from:select, keyup changed delay:400ms from:input[name='search']">
    <div class="relative">
        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
            <i class="bi bi-search text-sm"></i>
        </span>
        <input type="search" name="search" value="{{ $search }}" placeholder="{{ __('Search student...') }}"
               autocomplete="off" class="search-input">
    </div>
    <select name="section" class="filter-select">
        <option value="">{{ __('All sections') }}</option>
        @foreach ($sections as $sec)
            <option value="{{ $sec }}" @selected($section === $sec)>{{ __('Section') }} {{ $sec }}</option>
        @endforeach
    </select>
    <input type="hidden" name="year" value="{{ $year }}">
    <input type="hidden" name="month" value="{{ $month }}">
    <input type="hidden" name="student" value="{{ $selectedStudentId }}">
    <button type="submit" class="search-btn search-btn-htmx"><i class="bi bi-funnel me-1"></i>{{ __('Filter') }}</button>
    @if ($section || $search)
        <a href="{{ route('dtr.index', ['year' => $year, 'month' => $month]) }}" class="search-clear"><i class="bi bi-x-circle me-1"></i>{{ __('Clear') }}</a>
    @endif
</form>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- Left: Student list --}}
    <div class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <div class="px-3 py-2 bg-gray-50 border-b border-gray-200">
                <p class="text-xs font-semibold text-gray-500 uppercase">{{ __('Deployed Students') }}</p>
            </div>
            @if ($students->isNotEmpty())
                <div class="divide-y divide-gray-100 max-h-[500px] overflow-y-auto">
                    @foreach ($students as $student)
                        @php $isActive = (int) $selectedStudentId === (int) $student->id; @endphp
                        <a href="{{ route('dtr.index', ['year' => $year, 'month' => $month, 'section' => $section, 'search' => $search, 'student' => $student->id]) }}"
                           class="block px-3 py-2.5 text-sm hover:bg-emerald-50 transition-colors {{ $isActive ? 'bg-emerald-50 border-l-2 border-emerald-600 font-medium text-emerald-900' : 'text-gray-700 border-l-2 border-transparent' }}"
                           hx-get="{{ route('dtr.index', ['year' => $year, 'month' => $month, 'section' => $section, 'search' => $search, 'student' => $student->id]) }}"
                           hx-target="#dtr-list-mount" hx-push-url="true">
                            <span class="block truncate">{{ $student->name }}</span>
                            <span class="block text-xs text-gray-400 mt-0.5">{{ $student->student_number }} · {{ __('Section') }} {{ $student->section }}</span>
                        </a>
                    @endforeach
                </div>
                <div class="px-3 py-2 border-t border-gray-200 text-xs text-gray-500">
                    {{ $students->links() }}
                </div>
            @else
                <div class="px-3 py-8 text-center text-sm text-gray-500">
                    <i class="bi bi-people text-2xl text-gray-300 block mb-2"></i>
                    <p>{{ __('No deployed students found.') }}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Right: Selected student DTR --}}
    <div class="lg:col-span-2">
        @if ($selectedStudent)
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden flex flex-col max-h-[500px]">
                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between shrink-0">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ $selectedStudent->name }}</p>
                        <p class="text-xs text-gray-500">{{ $selectedStudent->student_number }} · {{ __('Section') }} {{ $selectedStudent->section }}</p>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <span class="text-gray-500">{{ __('Present') }}: <strong>{{ $presentDays }}</strong></span>
                        <span class="text-gray-500">{{ __('Hours') }}: <strong>{{ $totalHours }}</strong></span>
                    </div>
                </div>
                @if ($studentRecords->isNotEmpty())
                    <div class="overflow-y-auto flex-1">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="bg-gray-50 font-semibold text-gray-500 uppercase">
                                    <th class="px-2 py-2">{{ __('Date') }}</th>
                                    <th class="px-2 py-2">{{ __('Day') }}</th>
                                    <th class="px-2 py-2 text-center">{{ __('AM Arr') }}</th>
                                    <th class="px-2 py-2 text-center">{{ __('AM Dep') }}</th>
                                    <th class="px-2 py-2 text-center">{{ __('PM Arr') }}</th>
                                    <th class="px-2 py-2 text-center">{{ __('PM Dep') }}</th>
                                    <th class="px-2 py-2 text-center">{{ __('Hours') }}</th>
                                    <th class="px-2 py-2">{{ __('Tasks') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($studentRecords as $record)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-2 py-1.5 whitespace-nowrap text-gray-900">{{ $record->date->format('M d') }}</td>
                                        <td class="px-2 py-1.5 whitespace-nowrap text-gray-600">{{ $weekdayLabels[$record->date->dayOfWeek] ?? '' }}</td>
                                        <td class="px-2 py-1.5 whitespace-nowrap text-center font-mono">{{ $record->am_arrival ? Carbon\Carbon::parse($record->am_arrival)->format('h:i A') : '—' }}</td>
                                        <td class="px-2 py-1.5 whitespace-nowrap text-center font-mono">{{ $record->am_departure ? Carbon\Carbon::parse($record->am_departure)->format('h:i A') : '—' }}</td>
                                        <td class="px-2 py-1.5 whitespace-nowrap text-center font-mono">{{ $record->pm_arrival ? Carbon\Carbon::parse($record->pm_arrival)->format('h:i A') : '—' }}</td>
                                        <td class="px-2 py-1.5 whitespace-nowrap text-center font-mono">{{ $record->pm_departure ? Carbon\Carbon::parse($record->pm_departure)->format('h:i A') : '—' }}</td>
                                        <td class="px-2 py-1.5 whitespace-nowrap text-center font-mono font-medium">{{ $record->total_hours ?? '—' }}</td>
                                        <td class="px-2 py-1.5 text-xs max-w-[120px]">
                                            @if($record->tasks)
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
                @else
                    <div class="flex-1 flex items-center justify-center px-4 py-8 text-sm text-gray-500">
                        <div class="text-center">
                            <i class="bi bi-calendar-x text-2xl text-gray-300 block mb-2"></i>
                            <p>{{ __('No DTR records for this month.') }}</p>
                        </div>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-white border border-gray-200 rounded-lg px-4 py-12 text-center">
                <i class="bi bi-person-check text-3xl text-gray-300 block mb-3"></i>
                <p class="text-sm text-gray-500">{{ __('Select a student from the list to view their Daily Time Record.') }}</p>
            </div>
        @endif
    </div>
</div>
