<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Partners'), 'url' => route('companies.index')],
    ['label' => $company->name],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">Partners</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Company Details') }}</h2>
                <p class="text-sm text-gray-500">{{ __('View company information and contact details.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="h-full flex flex-col overflow-hidden">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 w-full flex flex-col flex-1 min-h-0">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg flex flex-col flex-1 min-h-0">
                <div class="p-6 text-gray-900 flex flex-col flex-1 min-h-0">
                    {{-- Company info (fixed, no scroll) --}}
                    <div class="space-y-4 shrink-0">
                        <div>
                            <h3 class="text-lg font-semibold">
                                {{ $company->name }}
                                @if ($company->latitude && $company->longitude)
                                    <span class="inline-flex items-center ms-1.5 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-medium text-emerald-700 align-middle">
                                        <i class="bi bi-geo-alt-fill me-1"></i> {{ __('Geofenced') }}
                                    </span>
                                @endif
                            </h3>
                            <p class="text-sm text-gray-600">
                                {{ $company->is_active ? __('Active partner') : __('Inactive partner') }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            @if ($company->industry)
                                <div>
                                    <span class="block text-xs font-semibold text-gray-500">{{ __('Industry') }}</span>
                                    @php $bgColor = $company->industry->color ?? '#6b7280'; @endphp
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-[11px] font-medium text-white mt-0.5" style="background-color: {{ $bgColor }}">
                                        {{ $company->industry->name }}
                                    </span>
                                </div>
                            @endif

                            <div>
                                <span class="block text-xs font-semibold text-gray-500">{{ __('Address') }}</span>
                                <span class="text-sm text-gray-900">
                                    {{ $company->full_address ?? '-' }}
                                </span>
                            </div>

                            <div>
                                <span class="block text-xs font-semibold text-gray-500">{{ __('Contact Person') }}</span>
                                <span class="text-sm text-gray-900">
                                    {{ $company->contact_person_name ?? '-' }}
                                </span>
                            </div>

                            <div>
                                <span class="block text-xs font-semibold text-gray-500">{{ __('Contact Email') }}</span>
                                <span class="text-sm text-gray-900">
                                    {{ $company->contact_email ?? '-' }}
                                </span>
                            </div>

                            <div>
                                <span class="block text-xs font-semibold text-gray-500">{{ __('Contact Phone') }}</span>
                                <span class="text-sm text-gray-900">
                                    {{ \App\Support\PhoneHelper::formatPhone($company->contact_phone) }}
                                </span>
                            </div>

                            @if ($company->latitude && $company->longitude)
                                <div>
                                    <span class="block text-xs font-semibold text-gray-500">{{ __('GPS Location') }}</span>
                                    <span class="text-sm text-gray-900">
                                        {{ $company->latitude }}, {{ $company->longitude }}
                                        <span class="text-xs text-gray-500">({{ __('radius') }}: {{ $company->geofence_radius_meters ?? 100 }}m)</span>
                                    </span>
                                </div>
                            @endif

                            @if ($company->notes)
                                <div>
                                    <span class="block text-xs font-semibold text-gray-500">{{ __('Notes') }}</span>
                                    <span class="text-sm text-gray-900 whitespace-pre-wrap">{{ $company->notes }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Student list (scrollable) --}}
                    <div class="border-t border-gray-100 pt-4 mt-4 flex-1 min-h-0 overflow-y-auto">
                        @include('companies.partials.students-list')
                    </div>

                    {{-- Actions (fixed, no scroll) --}}
                    <div class="pt-4 flex justify-end space-x-2 shrink-0 border-t border-gray-100 mt-4">
                        <a href="{{ route('companies.edit', $company) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
                            {{ __('Edit') }}
                        </a>
                        <a href="{{ route('companies.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                            {{ __('Back to list') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
