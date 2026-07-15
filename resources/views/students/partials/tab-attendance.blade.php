<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-md font-semibold flex items-center gap-1.5">
                <i class="bi bi-geo-alt text-gray-400"></i>
                {{ __('Attendance') }}
            </h3>
        </div>

        @if ($attendances->isEmpty())
            <p class="text-sm text-gray-500">{{ __('No attendance records yet.') }}</p>
        @else
            <div class="space-y-3">
                @foreach ($attendances as $attendance)
                    <div class="rounded-lg border border-gray-200 p-3">
                        <div class="text-xs font-semibold text-gray-500 mb-2">
                            {{ $attendance->check_in_at?->format('l, M d, Y') ?? 'Unknown date' }}
                        </div>
                        <div class="space-y-1.5">
                            @if ($attendance->am_check_in || $attendance->check_in_at)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5">
                                        <i class="bi bi-sun-fill text-emerald-600"></i>
                                        <span class="font-medium text-gray-700">{{ __('AM Clock In') }}</span>
                                    </span>
                                    <span>{{ $attendance->am_check_in?->format('h:i A') ?? $attendance->check_in_at?->format('h:i A') ?? '--' }}</span>
                                </div>
                            @endif
                            @if ($attendance->am_check_out)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5">
                                        <i class="bi bi-sun text-amber-600"></i>
                                        <span class="font-medium text-gray-700">{{ __('AM Clock Out') }}</span>
                                    </span>
                                    <span>{{ $attendance->am_check_out->format('h:i A') }}</span>
                                </div>
                            @endif
                            @if ($attendance->pm_check_in)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5">
                                        <i class="bi bi-moon-fill text-indigo-600"></i>
                                        <span class="font-medium text-gray-700">{{ __('PM Clock In') }}</span>
                                    </span>
                                    <span>{{ $attendance->pm_check_in->format('h:i A') }}</span>
                                </div>
                            @endif
                            @if ($attendance->pm_check_out || $attendance->time_out_at)
                                <div class="flex items-center justify-between text-xs">
                                    <span class="flex items-center gap-1.5">
                                        <i class="bi bi-moon text-indigo-600"></i>
                                        <span class="font-medium text-gray-700">{{ __('PM Clock Out') }}</span>
                                    </span>
                                    <span>{{ $attendance->pm_check_out?->format('h:i A') ?? $attendance->time_out_at?->format('h:i A') ?? '--' }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Geofence status --}}
                        <div class="mt-2 pt-2 border-t border-gray-100 flex items-center gap-3 text-xs text-gray-500">
                            @php
                                $geoIcon = match($attendance->geofence_status) {
                                    'inside_pass' => 'bi-check-circle-fill text-emerald-600',
                                    'near_boundary_review' => 'bi-exclamation-triangle-fill text-amber-500',
                                    'outside_flagged' => 'bi-x-circle-fill text-red-500',
                                    default => 'bi-question-circle-fill text-gray-400',
                                };
                                $geoLabel = match($attendance->geofence_status) {
                                    'inside_pass' => __('On Site'),
                                    'near_boundary_review' => __('Near Boundary'),
                                    'outside_flagged' => __('Off Site'),
                                    default => __('Unknown'),
                                };
                            @endphp
                            <span><i class="{{ $geoIcon }}"></i> {{ $geoLabel }}</span>
                            @if ($attendance->total_minutes)
                                <span class="font-medium">{{ intdiv($attendance->total_minutes, 60) }}h {{ $attendance->total_minutes % 60 }}m</span>
                            @endif
                            @if ($attendance->time_outside_window)
                                <span class="text-amber-600 font-medium">{{ __('Late') }}</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
