{{-- Students --}}
@if ($type === 'all' || $type === 'students')
    <x-page-card compact>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-800">{{ __('Archived Students') }}</h3>
            @if ($students->total() > 0)
                <span class="text-xs text-gray-500">{{ $students->total() }} {{ __('total') }}</span>
            @endif
        </div>
        @if ($students->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 custom-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Archived') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Reason') }}</th>
                            @if ($canManage)
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($students as $student)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $student->name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $student->archived_at?->format('M d, Y') }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $student->archive_reason ?? '—' }}</td>
                                @if ($canManage)
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="{{ route('archive.students.restore', $student) }}" class="inline" onsubmit="return confirm('{{ __('Restore this student?') }}')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">{{ __('Restore') }}</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $students->links() }}</div>
        @else
            <p class="text-sm text-gray-500 py-4 text-center">{{ __('No archived students.') }}</p>
        @endif
    </x-page-card>
@endif

{{-- Companies --}}
@if ($type === 'all' || $type === 'companies')
    <x-page-card compact>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-800">{{ __('Archived Companies') }}</h3>
            @if ($companies->total() > 0)
                <span class="text-xs text-gray-500">{{ $companies->total() }} {{ __('total') }}</span>
            @endif
        </div>
        @if ($companies->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 custom-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Name') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Archived') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Reason') }}</th>
                            @if ($canManage)
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($companies as $company)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $company->name }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $company->archived_at?->format('M d, Y') }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $company->archive_reason ?? '—' }}</td>
                                @if ($canManage)
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="{{ route('archive.companies.restore', $company) }}" class="inline" onsubmit="return confirm('{{ __('Restore this company?') }}')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">{{ __('Restore') }}</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $companies->links() }}</div>
        @else
            <p class="text-sm text-gray-500 py-4 text-center">{{ __('No archived companies.') }}</p>
        @endif
    </x-page-card>
@endif

{{-- Deployments --}}
@if ($type === 'all' || $type === 'deployments')
    <x-page-card compact>
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-800">{{ __('Archived Deployments') }}</h3>
            @if ($deployments->total() > 0)
                <span class="text-xs text-gray-500">{{ $deployments->total() }} {{ __('total') }}</span>
            @endif
        </div>
        @if ($deployments->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 custom-table">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Student') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('School Year') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Archived') }}</th>
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Reason') }}</th>
                            @if ($canManage)
                                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($deployments as $deployment)
                            <tr>
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $deployment->student?->name ?? '—' }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $deployment->school_year ?? '—' }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $deployment->archived_at?->format('M d, Y') }}</td>
                                <td class="px-3 py-2 text-sm text-gray-600">{{ $deployment->archive_reason ?? '—' }}</td>
                                @if ($canManage)
                                    <td class="px-3 py-2 text-right">
                                        <form method="POST" action="{{ route('archive.deployments.restore', $deployment) }}" class="inline" onsubmit="return confirm('{{ __('Restore this deployment?') }}')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="text-xs font-semibold text-emerald-600 hover:text-emerald-800">{{ __('Restore') }}</button>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-2">{{ $deployments->links() }}</div>
        @else
            <p class="text-sm text-gray-500 py-4 text-center">{{ __('No archived deployments.') }}</p>
        @endif
    </x-page-card>
@endif
