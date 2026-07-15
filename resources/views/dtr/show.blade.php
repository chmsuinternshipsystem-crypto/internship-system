<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
                    ['label' => __('Daily Time Records'), 'url' => route('dtr.index')],
                    ['label' => $dtr->student?->student_number ?? __('Record')],
                ]" />
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Monitoring') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $dtr->student?->name }} — {{ $dtr->date->format('M d, Y') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Student') }}</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $dtr->student?->name }} ({{ $dtr->student?->student_number }})</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Company') }}</span>
                            <span class="text-sm text-gray-900">{{ $dtr->deployment?->company?->name ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Date') }}</span>
                            <span class="text-sm text-gray-900">{{ $dtr->date->format('l, M d, Y') }}</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <p class="text-xs font-semibold text-gray-500 mb-3 uppercase tracking-wide">{{ __('Time Record') }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2 sm:gap-3">
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

                    @if ($dtr->tasks)
                        <div class="border-t pt-4">
                            <span class="block text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">{{ __('Tasks / Assignments Performed') }}</span>
                            <p class="text-sm text-gray-700 whitespace-pre-wrap leading-relaxed bg-gray-50/50 rounded-xl border border-gray-200 px-4 py-3">{{ $dtr->tasks }}</p>
                        </div>
                    @endif

                    @if ($dtr->remarks)
                        <div class="border-t pt-4">
                            <span class="block text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">{{ __('Remarks') }}</span>
                            <p class="text-sm text-gray-700 bg-gray-50 rounded-lg px-4 py-2.5">{{ $dtr->remarks }}</p>
                        </div>
                    @endif

                    <div class="pt-4">
                        <a href="{{ route('dtr.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                            {{ __('Back to list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>