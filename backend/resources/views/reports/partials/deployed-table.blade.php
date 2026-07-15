@if ($companies->isEmpty())
    <div class="text-center py-12 text-sm text-gray-500">
        <i class="bi bi-building text-2xl text-gray-300 block mb-2"></i>
        <p>{{ __('No companies found.') }}</p>
    </div>
@else
    <div class="space-y-4">
        @foreach ($companies as $row)
            <div class="rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-semibold text-gray-900">
                        <i class="bi bi-building me-1 text-emerald-600"></i>{{ $row['company']->name }}
                        <span class="text-xs font-normal text-gray-500">({{ $row['deployments']->count() }})</span>
                    </h4>
                    @if ($row['company']->address)
                        <span class="text-xs text-gray-400">{{ $row['company']->address }}</span>
                    @endif
                </div>
                @if ($row['deployments']->isEmpty())
                    <p class="text-xs text-gray-500">{{ __('No deployments.') }}</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs">
                            <thead>
                                <tr class="text-left text-gray-500 uppercase">
                                    <th class="px-3 py-1.5 font-medium">{{ __('Student') }}</th>
                                    <th class="px-3 py-1.5 font-medium">{{ __('Start') }}</th>
                                    <th class="px-3 py-1.5 font-medium">{{ __('End') }}</th>
                                    <th class="px-3 py-1.5 font-medium">{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($row['deployments'] as $dep)
                                    <tr>
                                        <td class="px-3 py-1.5">{{ $dep->student?->name ?? '—' }}</td>
                                        <td class="px-3 py-1.5 text-gray-600">{{ $dep->start_date?->format('M d, Y') ?? '—' }}</td>
                                        <td class="px-3 py-1.5 text-gray-600">{{ $dep->end_date?->format('M d, Y') ?? '—' }}</td>
                                        <td class="px-3 py-1.5">
                                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium
                                                {{ $dep->status === 'active' ? 'bg-emerald-50 text-emerald-700' : ($dep->status === 'completed' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700') }}">
                                                {{ ucfirst($dep->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
    <div class="mt-6" hx-boost="true" hx-target="#report-content" hx-swap="innerHTML">
        {{ $companies->withQueryString()->links('pagination::tailwind') }}
    </div>
@endif
