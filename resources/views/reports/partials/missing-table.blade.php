@if ($students->isEmpty())
    <div class="text-center py-12 text-sm text-gray-500">
        <i class="bi bi-check-circle text-2xl text-gray-300 block mb-2"></i>
        <p>{{ __('All clear — no missing or pending documents.') }}</p>
    </div>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 uppercase text-xs">
                    <th class="px-3 py-2 font-medium">{{ __('Student') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('Section') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('Missing') }}</th>
                    <th class="px-3 py-2 font-medium">{{ __('Pending') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($students as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">
                            <div class="font-medium text-gray-900">{{ $row['student']->name }}</div>
                            <div class="text-xs text-gray-500">{{ $row['student']->student_number }}</div>
                        </td>
                        <td class="px-3 py-2 text-gray-600">{{ $row['student']->section }}</td>
                        <td class="px-3 py-2">
                            @php $missingCount = count($row['missing']); @endphp
                            @if ($missingCount > 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-red-50 text-red-700 px-2 py-0.5 text-xs font-medium"
                                      title="{{ implode("\n", $row['missing']) }}">
                                    {{ $missingCount }} {{ __('missing') }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            @php $pendingCount = count($row['pending']); @endphp
                            @if ($pendingCount > 0)
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 text-amber-700 px-2 py-0.5 text-xs font-medium"
                                      title="{{ implode("\n", $row['pending']) }}">
                                    {{ $pendingCount }} {{ __('pending') }}
                                </span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6" hx-boost="true" hx-target="#report-content" hx-swap="innerHTML">
        {{ $students->withQueryString()->links('pagination::tailwind') }}
    </div>
@endif
