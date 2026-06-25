<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Partners') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Company Industries') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Manage industry classifications for partner companies.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <x-page-header
        :actionHref="route('company-industries.create')"
        actionLabel="{{ __('Add Industry') }}"
    />

    <x-page-card compact>
        <div class="table-wrap">
            <table class="min-w-full divide-y divide-gray-200 custom-table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Color') }}</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($industries as $industry)
                        <tr>
                            <td class="px-4 py-2 text-sm text-gray-900">{{ $industry->name }}</td>
                            <td class="px-4 py-2 text-sm">
                                @if ($industry->color)
                                    <span class="inline-flex items-center gap-1.5">
                                        <span class="h-3.5 w-3.5 rounded-full border border-gray-200" style="background-color: {{ $industry->color }}"></span>
                                        <span class="text-gray-600">{{ $industry->color }}</span>
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-sm">
                                <span class="status-badge {{ $industry->is_active ? 'badge-yes' : 'badge-no' }}">
                                    {{ $industry->is_active ? __('Active') : __('Inactive') }}
                                </span>
                            </td>
                            <td class="px-4 py-2 text-right text-sm font-medium cell-tight">
                                <a href="{{ route('company-industries.edit', $industry) }}" class="text-emerald-600 hover:text-emerald-800">
                                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty-state">
                                <i class="bi bi-tags"></i>
                                <strong>{{ __('No industries found') }}</strong>
                                <p>{{ __('Add an industry to start classifying companies.') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.htmx-pagination', ['paged' => $industries, 'hxTarget' => '#company-industries-mount'])
    </x-page-card>
</x-app-layout>
