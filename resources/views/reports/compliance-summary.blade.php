<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Report') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Compliance Summary') }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.compliance-summary', ['export' => 'pdf']) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-pdf me-1"></i>{{ __('Export PDF') }}</a>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="layout-section-y">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="stat-card" style="border-left-color:#3b82f6">
                    <div class="stat-card-body">
                        <div>
                            <div class="stat-label">{{ __('TOTAL') }}</div>
                            <div class="stat-number">{{ $summary['total'] }}</div>
                        </div>
                        <div class="stat-icon" style="background:#3b82f6"><i class="bi bi-people-fill"></i></div>
                    </div>
                </div>
                <div class="stat-card" style="border-left-color:#1a6b3c">
                    <div class="stat-card-body">
                        <div>
                                <div class="stat-label">{{ __('COMPLETE') }}</div>
                            <div class="stat-number">{{ $summary['compliant'] }}</div>
                        </div>
                        <div class="stat-icon" style="background:#1a6b3c"><i class="bi bi-check-circle-fill"></i></div>
                    </div>
                </div>
                <div class="stat-card" style="border-left-color:#f97316">
                    <div class="stat-card-body">
                        <div>
                            <div class="stat-label">{{ __('PARTIAL') }}</div>
                            <div class="stat-number">{{ $summary['partially_compliant'] }}</div>
                        </div>
                        <div class="stat-icon" style="background:#f97316"><i class="bi bi-exclamation-circle-fill"></i></div>
                    </div>
                </div>
                <div class="stat-card" style="border-left-color:#dc3545">
                    <div class="stat-card-body">
                        <div>
                                <div class="stat-label">{{ __('NEEDS ATTENTION') }}</div>
                            <div class="stat-number">{{ $summary['non_compliant'] }}</div>
                        </div>
                        <div class="stat-icon" style="background:#dc3545"><i class="bi bi-x-circle-fill"></i></div>
                    </div>
                </div>
            </div>

            <x-page-card compact>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 custom-table">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Student No.') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Program / Section') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Submitted / Total') }}</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Status') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($rows as $row)
                                @php
                                    $cls = match($row['status']) {
                                        'compliant' => 'badge-compliant',
                                        'partially_compliant' => 'badge-partial',
                                        'non_compliant' => 'badge-non-compliant',
                                        default => 'badge-default',
                                    };
                                    $label = match($row['status']) {
                                        'compliant' => __('Complete'),
                                        'partially_compliant' => __('In Progress'),
                                        'non_compliant' => __('Needs Attention'),
                                        default => __('N/A'),
                                    };
                                @endphp
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $row['student']->student_number }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $row['student']->name }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $row['student']->program }} / {{ $row['student']->section }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-900">{{ $row['submitted'] }} / {{ $row['total'] }}</td>
                                    <td class="px-4 py-2 text-sm"><span class="status-badge {{ $cls }}">{{ $label }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-state">
                                        <i class="bi bi-inbox"></i>
                                        <strong>{{ __('No students found') }}</strong>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-page-card>
        </div>
    </div>
</x-app-layout>
