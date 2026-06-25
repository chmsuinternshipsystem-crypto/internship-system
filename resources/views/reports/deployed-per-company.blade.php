<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Report') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Deployed Students per Company') }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.deployed-per-company', ['export' => 'pdf']) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-pdf me-1"></i>{{ __('Export PDF') }}</a>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="layout-section-y">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @foreach ($companies as $row)
                <x-page-card compact>
                    <h3 class="text-md font-semibold text-gray-900 mb-1">
                        <i class="bi bi-building me-1 text-emerald-600"></i>{{ $row['company']->name }}
                        <span class="text-xs font-normal text-gray-500">({{ $row['deployments']->count() }} {{ __('deployments') }})</span>
                    </h3>
                    @if ($row['company']->address)
                        <p class="text-xs text-gray-500 mb-3">
                            <i class="bi bi-geo-alt me-1"></i>{{ $row['company']->address }}
                        </p>
                    @endif
                    @if ($row['deployments']->isEmpty())
                        <p class="text-sm text-gray-500">{{ __('No deployments for this company.') }}</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 custom-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Student No.') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Start Date') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('End Date') }}</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($row['deployments'] as $dep)
                                        <tr>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $dep->student?->student_number }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $dep->student?->name }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $dep->start_date?->format('M d, Y') }}</td>
                                            <td class="px-4 py-2 text-sm text-gray-900">{{ $dep->end_date?->format('M d, Y') ?? '-' }}</td>
                                            <td class="px-4 py-2 text-sm">
                                                @php
                                                    $cls = match($dep->status) {
                                                        'active' => 'badge-active',
                                                        'completed' => 'badge-completed',
                                                        'withdrawn' => 'badge-withdrawn',
                                                        default => 'badge-default',
                                                    };
                                                @endphp
                                                <span class="status-badge {{ $cls }}">{{ Str::headline($dep->status) }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </x-page-card>
            @endforeach

            @if ($companies->isEmpty())
                <x-page-card compact>
                    <div class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <strong>{{ __('No companies found') }}</strong>
                        <p>{{ __('Add companies and deployments first.') }}</p>
                    </div>
                </x-page-card>
            @endif
        </div>
    </div>
</x-app-layout>
