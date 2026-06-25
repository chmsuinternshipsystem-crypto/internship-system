<div class="table-wrap overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 custom-table custom-table--fixed">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Industry') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Contact Person') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Address') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Contact Details') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Location') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($companies as $company)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        {{ $company->name }}
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-600 cell-wrap">{{ $company->industry?->name ?? '—' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">{{ $company->contact_person_name ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        @if ($company->full_address && mb_strlen($company->full_address) > 40)
                            <span>{{ \Illuminate\Support\Str::limit($company->full_address, 40) }}</span>
                            <a href="{{ route('companies.show', $company) }}" class="ml-1 text-emerald-600 hover:text-emerald-800 text-xs font-medium whitespace-nowrap">{{ __('see more') }}</a>
                        @else
                            {{ $company->full_address ?? '-' }}
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        <div>{{ $company->contact_email ?? '-' }}</div>
                        <div class="text-xs text-gray-500">{{ \App\Support\PhoneHelper::formatPhone($company->contact_phone) }}</div>
                    </td>
                    <td class="px-4 py-2 text-sm cell-tight">
                        @if ($company->geofencing_enabled && $company->latitude && $company->longitude)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-700" title="{{ __('Geofence radius: :radius m', ['radius' => $company->geofence_radius_meters ?? 100]) }}">
                                <i class="bi bi-check-circle-fill text-emerald-500 text-[10px]"></i> {{ __('ON') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-500">
                                <i class="bi bi-dash-circle text-gray-400 text-[10px]"></i> {{ __('OFF') }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-sm cell-tight">
                        <span class="status-badge {{ $company->is_active ? 'badge-yes' : 'badge-no' }}">
                            {{ $company->is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </td>
                    <td class="px-4 py-2 text-right text-sm font-medium cell-tight">
                        <x-action-menu :id="'company-'.$company->id">
                            <a href="{{ route('companies.show', $company) }}">
                                <i class="bi bi-eye"></i> {{ __('View') }}
                            </a>
                            @if ($canManage)
                                <a href="{{ route('companies.edit', $company) }}">
                                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                </a>
                                <div class="action-divider"></div>
                                <x-confirm-delete
                                    :action="route('companies.destroy', $company)"
                                    :message="__('Are you sure you want to delete this company?')"
                                    :dialog-id="'company-del-'.$company->id"
                                >
                                    <i class="bi bi-trash"></i> {{ __('Delete') }}
                                </x-confirm-delete>
                            @endif
                        </x-action-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <strong>{{ __('No records found') }}</strong>
                        <p>{{ __('Nothing here yet.') }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('partials.htmx-pagination', ['paged' => $companies, 'hxTarget' => '#companies-ajax-mount'])

