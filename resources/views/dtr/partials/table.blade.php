<table class="min-w-full divide-y divide-gray-200 custom-table">
    <thead class="bg-gray-50">
        <tr>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Student') }}</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Time In') }}</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Time Out') }}</th>
            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Total') }}</th>
            <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Actions') }}</th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse ($records as $record)
            <tr>
                <td class="px-4 py-2 text-sm text-gray-900">
                    {{ $record->student?->student_number }}
                    <div class="text-xs text-gray-500">{{ $record->student?->name }}</div>
                </td>
                <td class="px-4 py-2 text-sm text-gray-900">{{ $record->date->format('M d, Y') }}</td>
                <td class="px-4 py-2 text-sm text-gray-900">{{ $record->time_in ? \Carbon\Carbon::parse($record->time_in)->format('h:i A') : '-' }}</td>
                <td class="px-4 py-2 text-sm text-gray-900">{{ $record->time_out ? \Carbon\Carbon::parse($record->time_out)->format('h:i A') : '-' }}</td>
                <td class="px-4 py-2 text-sm text-gray-900">{{ $record->total_hours ?? '-' }}</td>
                <td class="px-4 py-2 text-right text-sm font-medium">
                    <a href="{{ route('dtr.show', $record) }}"
                       class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        <i class="bi bi-eye me-1"></i>{{ __('View') }}
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <strong>{{ __('No records found') }}</strong>
                    <p>{{ __('No Daily Time Records match your filters.') }}</p>
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
<div class="mt-4">
    {{ $records->links() }}
</div>
