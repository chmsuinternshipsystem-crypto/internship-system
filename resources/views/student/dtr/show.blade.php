<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Daily Time & Tasks Record') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $dtr->date->format('l, M d, Y') }}</h2>
            </div>
            <span class="text-xs text-gray-400 font-medium bg-gray-100 px-2 py-1 rounded-full">
                @if($dtr->source === 'attendance'){{ __('Auto-recorded') }}@else{{ __('Manual') }}@endif
            </span>
        </div>
    </x-slot>

    <div class="max-w-2xl mx-auto">
        <x-page-card compact>
            <div class="space-y-6">
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wide">{{ __('Time Record') }}</p>
                    <div class="grid grid-cols-5 gap-2 sm:gap-3">
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-center">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase mb-1">{{ __('AM Arrival') }}</p>
                            <p class="text-sm sm:text-base font-mono font-semibold text-gray-900">
                                {{ $dtr->am_arrival ? \Carbon\Carbon::parse($dtr->am_arrival)->format('h:i A') : ($dtr->time_in ? \Carbon\Carbon::parse($dtr->time_in)->format('h:i A') : '—') }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-center">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase mb-1">{{ __('AM Departure') }}</p>
                            <p class="text-sm sm:text-base font-mono font-semibold text-gray-900">
                                {{ $dtr->am_departure ? \Carbon\Carbon::parse($dtr->am_departure)->format('h:i A') : '—' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-center">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase mb-1">{{ __('PM Arrival') }}</p>
                            <p class="text-sm sm:text-base font-mono font-semibold text-gray-900">
                                {{ $dtr->pm_arrival ? \Carbon\Carbon::parse($dtr->pm_arrival)->format('h:i A') : '—' }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-gray-200 bg-gray-50 p-3 text-center">
                            <p class="text-[10px] font-semibold text-gray-500 uppercase mb-1">{{ __('PM Departure') }}</p>
                            <p class="text-sm sm:text-base font-mono font-semibold text-gray-900">
                                {{ $dtr->pm_departure ? \Carbon\Carbon::parse($dtr->pm_departure)->format('h:i A') : ($dtr->time_out ? \Carbon\Carbon::parse($dtr->time_out)->format('h:i A') : '—') }}
                            </p>
                        </div>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-center">
                            <p class="text-[10px] font-semibold text-emerald-600 uppercase mb-1">{{ __('Hours') }}</p>
                            <p class="text-sm sm:text-base font-mono font-bold text-emerald-800">{{ $dtr->total_hours ?? '—' }}</p>
                        </div>
                    </div>
                    @php
                        $amMinutes = ($dtr->am_arrival && $dtr->am_departure)
                            ? \Carbon\Carbon::parse($dtr->am_arrival)->diffInMinutes(\Carbon\Carbon::parse($dtr->am_departure))
                            : null;
                        $pmMinutes = ($dtr->pm_arrival && $dtr->pm_departure)
                            ? \Carbon\Carbon::parse($dtr->pm_arrival)->diffInMinutes(\Carbon\Carbon::parse($dtr->pm_departure))
                            : null;
                    @endphp
                    @if($amMinutes || $pmMinutes)
                        <div class="flex items-center justify-center gap-4 mt-2 text-xs text-gray-500">
                            @if($amMinutes)
                                <span>{{ __('AM') }}: <strong>{{ intdiv($amMinutes, 60) }}h {{ $amMinutes % 60 }}m</strong></span>
                            @endif
                            @if($pmMinutes)
                                <span>{{ __('PM') }}: <strong>{{ intdiv($pmMinutes, 60) }}h {{ $pmMinutes % 60 }}m</strong></span>
                            @endif
                        </div>
                    @endif
                </div>

                @if($dtr->tasks)
                    <div class="border-t pt-4">
                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">{{ __('Tasks / Assignments Performed') }}</p>
                        <div class="rounded-xl border border-gray-200 bg-gray-50/50 px-4 py-3">
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed">{{ $dtr->tasks }}</p>
                        </div>
                    </div>
                @endif

                @if($dtr->remarks)
                    <div class="border-t pt-4">
                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">{{ __('Remarks') }}</p>
                        <p class="text-sm text-gray-700 bg-gray-50 rounded-lg px-4 py-2.5">{{ $dtr->remarks }}</p>
                    </div>
                @endif

                @if($dtr->source === 'attendance')
                    <div class="flex items-center gap-2 text-xs text-gray-400 border-t pt-3">
                        <i class="bi bi-info-circle"></i>
                        <span>{{ __('This entry was auto-recorded from your clock-in and clock-out times.') }}</span>
                    </div>
                @endif

                <div class="flex items-center justify-between pt-4 border-t">
                    <a href="{{ route('student.dtr.index', ['year' => $dtr->date->year, 'month' => $dtr->date->month]) }}"
                       class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 transition-colors">
                        <i class="bi bi-arrow-left"></i> {{ __('Back to DTTR') }}
                    </a>
                </div>
            </div>
        </x-page-card>
    </div>
</x-app-layout>