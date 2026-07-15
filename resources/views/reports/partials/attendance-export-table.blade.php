<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 custom-table">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Student No.') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Section') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Check In') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Time Out') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total (min)') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($records as $att)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $att->check_in_at?->format('M d, Y') }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $att->student?->student_number }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $att->student?->name }}</td>
                    <td class="px-4 py-2 text-sm text-gray-600">{{ $att->student?->section ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $att->check_in_at?->format('h:i A') }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $att->time_out_at?->format('h:i A') ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $att->total_minutes ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm">
                        @php
                            $badgeCls = match(true) {
                                $att->location_unavailable => 'badge-withdrawn',
                                $att->review_required && $att->resolution_status !== 'resolved' => 'badge-pending',
                                $att->review_required && $att->resolution_status === 'resolved' => 'badge-completed',
                                default => 'badge-active',
                            };
                            $label = match(true) {
                                $att->location_unavailable => __('No Location'),
                                $att->review_required && $att->resolution_status !== 'resolved' => __('Pending Review'),
                                $att->review_required && $att->resolution_status === 'resolved' => __('Resolved'),
                                default => __('Normal'),
                            };
                        @endphp
                        <span class="status-badge {{ $badgeCls }}">{{ $label }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-sm text-gray-500">
                        {{ __('No attendance records found for the selected filters.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if ($records->count() > 0)
    <div class="mt-6" hx-boost="true" hx-target="#report-content" hx-swap="innerHTML">
        {{ $records->withQueryString()->links('pagination::tailwind') }}
    </div>
@endif
