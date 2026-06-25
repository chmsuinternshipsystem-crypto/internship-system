@if ($rows->isEmpty())
    <div class="text-center py-12 text-sm text-gray-500">
        <i class="bi bi-clipboard-check text-2xl text-gray-300 block mb-2"></i>
        <p>{{ __('No compliance data available.') }}</p>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 uppercase text-xs">
                    <th class="px-3 py-2 font-medium">{{ __('Student') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('Section') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('Submitted / Total') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('Status') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($rows as $row)
                    @php
                        $cls = match($row['status']) {
                            'compliant' => 'bg-emerald-50 text-emerald-700',
                            'partially_compliant' => 'bg-amber-50 text-amber-700',
                            'non_compliant' => 'bg-red-50 text-red-700',
                            default => 'bg-gray-50 text-gray-600',
                        };
                        $label = match($row['status']) {
                            'compliant' => __('Complete'),
                            'partially_compliant' => __('In Progress'),
                            'non_compliant' => __('Needs Attention'),
                            default => __('N/A'),
                        };
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <div class="font-medium text-gray-900">{{ $row['student']->name }}</div>
                            <div class="text-xs text-gray-500">{{ $row['student']->student_number }}</div>
                        </td>
                        <td class="px-3 py-2 text-gray-600">{{ $row['student']->section ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-900">{{ $row['submitted'] }} / {{ $row['total'] }}</td>
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $cls }}">
                                {{ $label }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6" hx-boost="true" hx-target="#report-content" hx-swap="innerHTML">
        {{ $rows->withQueryString()->links('pagination::tailwind') }}
    </div>
@endif