@csrf

@php
    $inputBase = 'mt-1 block w-full rounded-lg border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm';
    $inputOk = 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500';
    $inputErr = 'border-red-400 focus:border-red-500 focus:ring-red-500';
@endphp

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #company-map { height: 200px; border-radius: 0.5rem; z-index: 1; }
        #company-map .leaflet-container { border-radius: 0.5rem; }
    </style>
@endpush

<div class="space-y-5">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Company Details --}}
        <x-page-card compact class="h-full">
            <div class="flex items-start gap-2.5 mb-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                    <i class="bi bi-building"></i>
                </span>
                <div class="min-w-0 pt-0.5">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Company details') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Official company name and structured address.') }}</p>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <x-input-label for="name" :value="__('Company name')" />
                    <input id="name" name="name" type="text" required maxlength="120"
                           value="{{ old('name', $company->name ?? '') }}"
                           placeholder="e.g. ABC Tech Solutions Inc."
                           @class([$inputBase, $inputOk => ! $errors->has('name'), $inputErr => $errors->has('name')]) />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="street_address" :value="__('Street address')" />
                    <input id="street_address" name="street_address" type="text" required maxlength="255"
                            value="{{ old('street_address', $company->street_address ?? '') }}"
                            placeholder="e.g. Rizal Street, Building 2" maxlength="100"
                           @class([$inputBase, $inputOk => ! $errors->has('street_address'), $inputErr => $errors->has('street_address')]) />
                    <x-input-error :messages="$errors->get('street_address')" class="mt-1" />
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3"
                     x-data="addressCascader({
                         provinceId: {{ old('province_id', $company->province_id ?? 'null') }},
                         cityId: {{ old('city_id', $company->city_id ?? 'null') }},
                         barangayId: {{ old('barangay_id', $company->barangay_id ?? 'null') }},
                         cityName: '{{ addslashes(old('city_municipality', $company->city_municipality ?? '')) }}',
                         barangayName: '{{ addslashes(old('barangay', $company->barangay ?? '')) }}',
                     })">
                    <input type="hidden" name="province_id" x-model="provinceId">
                    <input type="hidden" name="city_municipality" x-model="cityNameText">
                    <input type="hidden" name="barangay" x-model="barangayNameText">
                    <div>
                        <x-input-label for="province" :value="__('Province')" />
                        <select id="province" x-model="provinceId" @change="onProvinceChange" required
                                class="mt-1 block w-full rounded-lg border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">{{ __('Select province') }}</option>
                            <template x-for="p in provinces" :key="p.id">
                                <option :value="p.id" x-text="p.name"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('province_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="city_select" :value="__('City / Municipality')" />
                        <select id="city_select" name="city_id" x-model="cityId" @change="onCityChange" required
                                class="mt-1 block w-full rounded-lg border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">{{ __('Select city') }}</option>
                            <template x-for="c in cities" :key="c.id">
                                <option :value="c.id" x-text="c.name + (c.type ? ' (' + c.type + ')' : '')"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('city_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="barangay_select" :value="__('Barangay')" />
                        <select id="barangay_select" name="barangay_id" x-model="barangayId" @change="onBarangayChange" required
                                class="mt-1 block w-full rounded-lg border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm border-gray-300 focus:border-emerald-500 focus:ring-emerald-500">
                            <option value="">{{ __('Select barangay') }}</option>
                            <template x-for="b in barangays" :key="b.id">
                                <option :value="b.id" x-text="b.name"></option>
                            </template>
                        </select>
                        <x-input-error :messages="$errors->get('barangay_id')" class="mt-1" />
                    </div>
                </div>
            </div>
        </x-page-card>

        {{-- Primary Contact --}}
        <x-page-card compact class="h-full">
            <div class="flex items-start gap-2.5 mb-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-700">
                    <i class="bi bi-person-lines-fill"></i>
                </span>
                <div class="min-w-0 pt-0.5">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Primary contact') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Official contact person details.') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <div>
                    <x-input-label for="contact_last_name" :value="__('Last name')" />
                    <input id="contact_last_name" name="contact_last_name" type="text" required maxlength="60"
                           value="{{ old('contact_last_name', $company->contact_last_name ?? '') }}"
                           placeholder="e.g. Reyes"
                           @class([$inputBase, $inputOk => ! $errors->has('contact_last_name'), $inputErr => $errors->has('contact_last_name')]) />
                    <x-input-error :messages="$errors->get('contact_last_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="contact_first_name" :value="__('First name')" />
                    <input id="contact_first_name" name="contact_first_name" type="text" required maxlength="60"
                           value="{{ old('contact_first_name', $company->contact_first_name ?? '') }}"
                           placeholder="e.g. Juan"
                           @class([$inputBase, $inputOk => ! $errors->has('contact_first_name'), $inputErr => $errors->has('contact_first_name')]) />
                    <x-input-error :messages="$errors->get('contact_first_name')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="contact_middle_initial" :value="__('Middle initial')" />
                    <input id="contact_middle_initial" name="contact_middle_initial" type="text" maxlength="4"
                           value="{{ old('contact_middle_initial', $company->contact_middle_initial ?? '') }}"
                           placeholder="e.g. M."
                           @class([$inputBase, $inputOk => ! $errors->has('contact_middle_initial'), $inputErr => $errors->has('contact_middle_initial')]) />
                    <x-input-error :messages="$errors->get('contact_middle_initial')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="contact_name_extension" :value="__('Extension')" />
                    <select id="contact_name_extension" name="contact_name_extension"
                            @class([$inputBase, $inputOk => ! $errors->has('contact_name_extension'), $inputErr => $errors->has('contact_name_extension')])>
                        <option value="">{{ __('No extension') }}</option>
                        @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $option)
                            <option value="{{ $option }}" @selected(old('contact_name_extension', $company->contact_name_extension ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('contact_name_extension')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="contact_phone" :value="__('Phone number')" />
                    <input id="contact_phone" name="contact_phone" type="text" inputmode="tel"                                maxlength="13"
                           value="{{ old('contact_phone', \App\Support\PhoneHelper::formatPhone($company->contact_phone ?? '')) !== '—' ? old('contact_phone', \App\Support\PhoneHelper::formatPhone($company->contact_phone ?? '')) : '' }}"
                           placeholder="+63 912 345 6789"
                           oninput="this.value = this.value.replace(/[^0-9+\s]/g, '')"
                           @class([$inputBase, $inputOk => ! $errors->has('contact_phone'), $inputErr => $errors->has('contact_phone')]) />
                    <x-input-error :messages="$errors->get('contact_phone')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="contact_email" :value="__('Email')" />
                    <input id="contact_email" name="contact_email" type="email" maxlength="120"
                           value="{{ old('contact_email', $company->contact_email ?? '') }}"
                           placeholder="e.g. hr@company.com"
                           @class([$inputBase, $inputOk => ! $errors->has('contact_email'), $inputErr => $errors->has('contact_email')]) />
                    <x-input-error :messages="$errors->get('contact_email')" class="mt-1" />
                </div>
            </div>
        </x-page-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        {{-- Classification --}}
        <x-page-card compact class="h-full">
            <div class="flex items-start gap-2.5 mb-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-purple-50 text-purple-700">
                    <i class="bi bi-tags"></i>
                </span>
                <div class="min-w-0 pt-0.5">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Classification') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Industry and internal notes.') }}</p>
                </div>
            </div>
            <div class="space-y-3">
                <div>
                    <x-input-label for="company_industry_id" :value="__('Industry')" />
                    <select id="company_industry_id" name="company_industry_id"
                            class="mt-1 block w-full rounded-lg border bg-white px-3 py-2 text-sm text-gray-900 shadow-sm {{ $errors->has('company_industry_id') ? 'border-red-400 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500' }}">
                        <option value="">{{ __('No industry selected') }}</option>
                        @foreach ($industries ?? [] as $option)
                            <option value="{{ $option->id }}" @selected(old('company_industry_id', $company->company_industry_id ?? '') == $option->id)>{{ $option->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('company_industry_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="notes" :value="__('Notes')" />
                    <textarea id="notes" name="notes" rows="3" maxlength="100"
                              placeholder="Internal notes about this company..."
                              @class([$inputBase, $inputOk => ! $errors->has('notes'), $inputErr => $errors->has('notes')])>{{ old('notes', $company->notes ?? '') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500">{{ __('Staff-only, not visible to students.') }}</p>
                    <x-input-error :messages="$errors->get('notes')" class="mt-1" />
                </div>
            </div>
        </x-page-card>

        {{-- GPS Location --}}
        <x-page-card compact class="h-full">
            <div class="flex items-start gap-2.5 mb-4">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700">
                    <i class="bi bi-geo-alt"></i>
                </span>
                <div class="min-w-0 pt-0.5">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('GPS Location') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Set coordinates for geofenced attendance.') }}</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-3 mb-3">
                <div>
                    <x-input-label for="latitude" :value="__('Latitude')" />
                    <input id="latitude" name="latitude" type="text" inputmode="decimal" maxlength="20"
                           value="{{ old('latitude', $company->latitude ?? '') }}"
                           placeholder="10.7370"
                           @class([$inputBase, $inputOk => ! $errors->has('latitude'), $inputErr => $errors->has('latitude')]) />
                    <x-input-error :messages="$errors->get('latitude')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="longitude" :value="__('Longitude')" />
                    <input id="longitude" name="longitude" type="text" inputmode="decimal" maxlength="20"
                           value="{{ old('longitude', $company->longitude ?? '') }}"
                           placeholder="122.6015"
                           @class([$inputBase, $inputOk => ! $errors->has('longitude'), $inputErr => $errors->has('longitude')]) />
                    <x-input-error :messages="$errors->get('longitude')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="geofence_radius_meters" :value="__('Radius (m)')" />
                    <input id="geofence_radius_meters" name="geofence_radius_meters" type="number" min="10" max="5000"
                           value="{{ old('geofence_radius_meters', $company->geofence_radius_meters ?? 100) }}"
                           @class([$inputBase, $inputOk => ! $errors->has('geofence_radius_meters'), $inputErr => $errors->has('geofence_radius_meters')]) />
                    <x-input-error :messages="$errors->get('geofence_radius_meters')" class="mt-1" />
                </div>
                <div class="col-span-3 flex items-start gap-3 rounded-lg border border-gray-200 bg-white px-3 py-2.5">
                    <input id="geofencing_enabled" name="geofencing_enabled" type="checkbox" value="1"
                           @checked(old('geofencing_enabled', $company->geofencing_enabled ?? false))
                           class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600" />
                    <div class="min-w-0">
                        <label for="geofencing_enabled" class="block text-sm font-medium text-gray-800 cursor-pointer">
                            {{ __('Enable location verification') }}
                        </label>
                        <p class="mt-0.5 text-xs text-gray-500 leading-snug">
                            {{ __('When enabled, students must be within the geofence radius to clock in without a flag. Disable for companies with complex locations or unreliable GPS coordinates.') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="space-y-2"
                 x-data="companyGeofence()"
                 x-init="initMap({{ old('latitude', $company->latitude ?? 'null') }}, {{ old('longitude', $company->longitude ?? 'null') }}, {{ old('geofence_radius_meters', $company->geofence_radius_meters ?? 100) }})">
                <div id="company-map" class="border border-gray-200"></div>
                <div class="flex items-center justify-between gap-2">
                    <button type="button" @click="lookupAddress()"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-white border border-emerald-300 rounded-lg hover:bg-emerald-50 transition-colors">
                        <i class="bi bi-search"></i>
                        {{ __('Look up address') }}
                    </button>
                    <span class="text-xs text-gray-400" x-text="lookupStatus"></span>
                </div>
            </div>
        </x-page-card>
    </div>

    {{-- Partnership Status --}}
    <x-page-card compact>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-start gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-700">
                    <i class="bi bi-toggle2-on"></i>
                </span>
                <div class="pt-0.5">
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Partnership status') }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ __('Inactive partners are hidden from new deployments.') }}</p>
                </div>
            </div>
            <label class="relative inline-flex items-center cursor-pointer gap-3">
                <input type="checkbox" name="is_active" value="1"
                       @checked(old('is_active', $company->is_active ?? true))
                       class="sr-only peer">
                <div class="w-10 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-600"></div>
                <span class="text-sm font-medium text-gray-700 peer-checked:text-emerald-700">
                    {{ __('Active') }}
                </span>
            </label>
        </div>
    </x-page-card>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3 pt-2">
        <a href="{{ route('companies.index') }}"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
            {{ __('Cancel') }}
        </a>
        <button type="submit"
                class="inline-flex items-center px-5 py-2 bg-emerald-600 border border-transparent rounded-lg text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">
            {{ $submitLabel ?? __('Save Company') }}
        </button>
    </div>
</div>

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function companyGeofence() {
            return {
                map: null,
                marker: null,
                circle: null,
                lookupStatus: '',
                initMap(lat, lng, radius) {
                    this.$nextTick(() => {
                        const hasCoords = lat !== null && lng !== null;
                        const center = hasCoords ? [lat, lng] : [10.6432, 122.9394];

                        this.map = L.map('company-map', {
                            center: center,
                            zoom: hasCoords ? 17 : 15,
                            zoomControl: true,
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 20,
                            attribution: '&copy; OpenStreetMap',
                        }).addTo(this.map);

                        const latInput = document.getElementById('latitude');
                        const lngInput = document.getElementById('longitude');
                        const radiusInput = document.getElementById('geofence_radius_meters');

                        const updateCircle = () => {
                            if (this.circle) this.circle.setRadius(Number(radiusInput.value) || 100);
                        };

                        if (hasCoords) {
                            this.placeMarker(lat, lng, Number(radiusInput.value) || 100);
                        }

                        this.map.on('click', (e) => {
                            this.placeMarker(e.latlng.lat, e.latlng.lng, Number(radiusInput.value) || 100);
                            latInput.value = e.latlng.lat.toFixed(7);
                            lngInput.value = e.latlng.lng.toFixed(7);
                            latInput.dispatchEvent(new Event('input'));
                            lngInput.dispatchEvent(new Event('input'));
                        });

                        [latInput, lngInput].forEach((input) => {
                            input.addEventListener('input', () => {
                                const newLat = parseFloat(latInput.value);
                                const newLng = parseFloat(lngInput.value);
                                if (!isNaN(newLat) && !isNaN(newLng)) {
                                    this.placeMarker(newLat, newLng, Number(radiusInput.value) || 100);
                                }
                            });
                        });

                        radiusInput.addEventListener('input', updateCircle);
                    });
                },
                placeMarker(lat, lng, radius) {
                    if (this.marker) {
                        this.marker.setLatLng([lat, lng]);
                    } else {
                        this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                        this.marker.on('dragend', () => {
                            const pos = this.marker.getLatLng();
                            document.getElementById('latitude').value = pos.lat.toFixed(7);
                            document.getElementById('longitude').value = pos.lng.toFixed(7);
                        });
                    }

                    if (this.circle) {
                        this.circle.setLatLng([lat, lng]);
                        this.circle.setRadius(radius);
                    } else {
                        this.circle = L.circle([lat, lng], {
                            radius: radius,
                            color: '#059669',
                            fillColor: '#059669',
                            fillOpacity: 0.1,
                            weight: 2,
                        }).addTo(this.map);
                    }
                },
                lookupAddress() {
                    const street = document.querySelector('[name="street_address"]')?.value || '';
                    const citySelect = document.querySelector('#city_select');
                    const barangaySelect = document.querySelector('#barangay_select');
                    const city = citySelect ? citySelect.options[citySelect.selectedIndex]?.text || '' : '';
                    const barangay = barangaySelect ? barangaySelect.options[barangaySelect.selectedIndex]?.text || '' : '';
                    const parts = [street, barangay, city].filter(Boolean);
                    if (parts.length === 0) {
                        this.lookupStatus = '{{ __('Fill in address fields first.') }}';
                        return;
                    }
                    const address = encodeURIComponent(parts.join(', '));
                    this.lookupStatus = '{{ __('Looking up...') }}';
                    fetch('{{ route('companies.geocode') }}?address=' + address)
                        .then(r => r.json())
                        .then(data => {
                            if (data.error) {
                                this.lookupStatus = data.error;
                                return;
                            }
                            document.getElementById('latitude').value = data.lat.toFixed(7);
                            document.getElementById('longitude').value = data.lng.toFixed(7);
                            const radius = Number(document.getElementById('geofence_radius_meters').value) || 100;
                            this.placeMarker(data.lat, data.lng, radius);
                            this.map.setView([data.lat, data.lng], 17);
                            this.lookupStatus = '{{ __('Coordinates set.') }}';
                        })
                        .catch(() => {
                            this.lookupStatus = '{{ __('Geocoding failed.') }}';
                        });
                },
            };
        }

        function addressCascader(initial) {
            return {
                provinces: [],
                cities: [],
                barangays: [],
                provinceId: initial.provinceId || '',
                cityId: initial.cityId || '',
                barangayId: initial.barangayId || '',
                cityNameText: initial.cityName || '',
                barangayNameText: initial.barangayName || '',
                async init() {
                    const res = await fetch('{{ route('address.provinces') }}');
                    this.provinces = await res.json();
                    if (this.provinceId) {
                        await this.onProvinceChange();
                        if (this.cityId) {
                            await this.onCityChange();
                        }
                    }
                },
                async onProvinceChange() {
                    this.cityId = '';
                    this.barangayId = '';
                    this.cityNameText = '';
                    this.barangayNameText = '';
                    this.cities = [];
                    this.barangays = [];
                    if (!this.provinceId) return;
                    const res = await fetch('{{ route('address.cities', ['province' => '__PROVINCE__']) }}'.replace('__PROVINCE__', this.provinceId));
                    this.cities = await res.json();
                },
                async onCityChange() {
                    this.barangayId = '';
                    this.barangayNameText = '';
                    this.barangays = [];
                    const selected = this.cities.find(c => String(c.id) === String(this.cityId));
                    this.cityNameText = selected ? selected.name : '';
                    if (!this.cityId) return;
                    const res = await fetch('{{ route('address.barangays', ['city' => '__CITY__']) }}'.replace('__CITY__', this.cityId));
                    this.barangays = await res.json();
                },
                onBarangayChange() {
                    const selected = this.barangays.find(b => String(b.id) === String(this.barangayId));
                    this.barangayNameText = selected ? selected.name : '';
                },
            };
        }
    </script>
@endpush
