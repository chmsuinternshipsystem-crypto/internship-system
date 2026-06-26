<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Settings') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Campus Geofencing') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Configure campus coordinates, public check-in radius, and the campus boundary polygon for student attendance geofencing.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            @php $evalOpen = session('section') === 'evaluation'; @endphp
            <div class="space-y-4"
                 x-data="{ sections: { boundary: true, geofence: false, attendance: false, policy: false, evaluation: {{ $evalOpen ? 'true' : 'false' }}, instructors: false } }">

                {{-- Campus Boundary Polygon --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <button type="button" @click="sections.boundary = !sections.boundary"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                <i class="bi bi-pentagon"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('Campus Boundary Polygon') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('Draw exact campus perimeter for precise geofencing') }}</p>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.boundary ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="sections.boundary" x-collapse.duration.200ms>
                        <form method="POST" action="{{ route('settings.campus.update') }}" class="px-5 pb-5 space-y-4" x-data="campusGeofenceForm">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="boundary">

                            <div class="rounded-lg border border-emerald-100 bg-emerald-50/40 p-4 space-y-4">
                                <p class="text-xs text-emerald-800">
                                    {{ __('Click on the map to draw the exact campus perimeter. Students inside this polygon are marked "on campus." A GPS buffer zone outside the polygon handles device accuracy.') }}
                                </p>

                                <div id="campus-map" class="w-full rounded-lg border border-emerald-200" style="height: 420px;"></div>

                                <div class="flex flex-wrap items-center gap-3 text-xs text-gray-600">
                                    <span>
                                        <i class="bi bi-geo-alt me-0.5 text-emerald-600"></i>
                                        <span x-text="vertexCount + ' ' + '{{ __('vertices placed') }}'"></span>
                                    </span>
                                    <button type="button" @click="undoVertex()" x-show="vertexCount > 0"
                                        class="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white px-2 py-1 font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-1">
                                        <i class="bi bi-arrow-counterclockwise"></i>{{ __('Undo') }}
                                    </button>
                                    <button type="button" @click="clearPolygon()" x-show="vertexCount > 0"
                                        class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-white px-2 py-1 font-semibold text-red-600 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1">
                                        <i class="bi bi-trash3"></i>{{ __('Clear') }}
                                    </button>
                                    <span class="text-gray-400">|</span>
                                    <span class="text-gray-500">
                                        <i class="bi bi-info-circle me-0.5"></i>{{ __('Click the map to add points. At least 3 points are required.') }}
                                    </span>
                                </div>

                                <input type="hidden" name="campus_boundary" x-model="boundaryJson">

                                <div class="max-w-sm">
                                    <x-input-label for="campus_boundary_buffer_meters" :value="__('GPS buffer (meters)')" />
                                    <input id="campus_boundary_buffer_meters" name="campus_boundary_buffer_meters" type="number"
                                        min="5" max="100" step="5" inputmode="numeric"
                                        value="{{ old('campus_boundary_buffer_meters', $campus->campus_boundary_buffer_meters ?? 20) }}"
                                        required
                                        class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" />
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ __('Students detected outside the polygon but within this distance are flagged for review (GPS drift tolerance).') }}
                                    </p>
                                    <x-input-error :messages="$errors->get('campus_boundary_buffer_meters')" class="mt-1" />
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700 shadow-sm">
                                    <i class="bi bi-save me-1.5"></i>
                                    {{ __('Save Boundary') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Fallback Circle Geofence --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <button type="button" @click="sections.geofence = !sections.geofence"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                                <i class="bi bi-bullseye"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('Fallback Circle Geofence') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('Coordinate-based radius check when no polygon is set') }}</p>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.geofence ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="sections.geofence" x-collapse.duration.200ms>
                        <form method="POST" action="{{ route('settings.campus.update') }}" class="px-5 pb-5 space-y-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="geofence">

                            <div class="rounded-lg border border-blue-100 bg-blue-50/40 p-4 space-y-4">
                                <p class="text-xs text-blue-800">
                                    {{ __('Used when no polygon boundary is drawn. These settings also serve as the reference point for the campus map.') }}
                                </p>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="campus_lat" :value="__('Campus Latitude')" />
                                        <input id="campus_lat" name="campus_lat" type="number" step="0.000000000001" min="-90" max="90"
                                            value="{{ old('campus_lat', $campus->campus_lat) }}"
                                            required
                                            class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" />
                                        <x-input-error :messages="$errors->get('campus_lat')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label for="campus_lng" :value="__('Campus Longitude')" />
                                        <input id="campus_lng" name="campus_lng" type="number" step="0.000000000001" min="-180" max="180"
                                            value="{{ old('campus_lng', $campus->campus_lng) }}"
                                            required
                                            class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" />
                                        <x-input-error :messages="$errors->get('campus_lng')" class="mt-1" />
                                    </div>
                                </div>
                                <div class="max-w-sm">
                                    <x-input-label for="campus_radius_meters" :value="__('Public / staff check-in radius (meters)')" />
                                    <input id="campus_radius_meters" name="campus_radius_meters" type="number" min="50" max="2000" step="10"
                                        value="{{ old('campus_radius_meters', $campus->campus_radius_meters) }}"
                                        required
                                        class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" />
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ __('Used for the student-number campus check-in page. Typical values: 150–300 m for a wider campus gate/building area.') }}
                                    </p>
                                    <x-input-error :messages="$errors->get('campus_radius_meters')" class="mt-1" />
                                </div>

                                <p class="text-xs text-gray-600">
                                    <a href="https://www.google.com/maps?q={{ urlencode((string) $campus->campus_lat) }},{{ urlencode((string) $campus->campus_lng) }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="font-semibold text-blue-800 underline hover:text-blue-900">
                                        {{ __('Preview reference point in Google Maps') }}
                                    </a>
                                </p>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700 shadow-sm">
                                    <i class="bi bi-save me-1.5"></i>
                                    {{ __('Save Geofence') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Attendance Time Windows --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <button type="button" @click="sections.attendance = !sections.attendance"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                <i class="bi bi-clock"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('Attendance Time Windows') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('Morning and afternoon clock-in/out schedules') }}</p>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.attendance ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="sections.attendance" x-collapse.duration.200ms>
                        <form method="POST" action="{{ route('settings.campus.update') }}" class="px-5 pb-5 space-y-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="attendance">

                            <div class="space-y-4">
                                <p class="text-xs text-gray-600">{{ __('Define morning and afternoon clock-in/out schedules for students. Each session has a Clock In window and a Clock Out window.') }}</p>

                                {{-- AM Session --}}
                                <div class="rounded-xl border border-emerald-200 bg-emerald-50/30 p-4 space-y-3">
                                    <p class="text-sm font-semibold text-emerald-800 flex items-center gap-1.5">
                                        <i class="bi bi-sun-fill"></i>{{ __('Morning Session (AM)') }}
                                    </p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <i class="bi bi-box-arrow-in-right text-emerald-600 text-sm"></i>
                                                <span class="text-xs font-medium text-gray-700">{{ __('Clock In Window') }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input id="attendance_am_time_in_start" name="attendance_am_time_in_start" data-clockpicker type="text"
                                                       value="{{ old('attendance_am_time_in_start', $campus->attendance_am_time_in_start ? substr($campus->attendance_am_time_in_start, 0, 5) : '06:30') }}"
                                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" />
                                                <span class="text-gray-400 shrink-0">→</span>
                                                <input id="attendance_am_time_in_end" name="attendance_am_time_in_end" data-clockpicker type="text"
                                                       value="{{ old('attendance_am_time_in_end', $campus->attendance_am_time_in_end ? substr($campus->attendance_am_time_in_end, 0, 5) : '08:30') }}"
                                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" />
                                            </div>
                                            <x-input-error :messages="$errors->get('attendance_am_time_in_start')" class="mt-1" />
                                            <x-input-error :messages="$errors->get('attendance_am_time_in_end')" class="mt-1" />
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <i class="bi bi-box-arrow-left text-emerald-600 text-sm"></i>
                                                <span class="text-xs font-medium text-gray-700">{{ __('Clock Out Window') }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input id="attendance_am_time_out_start" name="attendance_am_time_out_start" data-clockpicker type="text"
                                                       value="{{ old('attendance_am_time_out_start', $campus->attendance_am_time_out_start ? substr($campus->attendance_am_time_out_start, 0, 5) : '11:30') }}"
                                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" />
                                                <span class="text-gray-400 shrink-0">→</span>
                                                <input id="attendance_am_time_out_end" name="attendance_am_time_out_end" data-clockpicker type="text"
                                                       value="{{ old('attendance_am_time_out_end', $campus->attendance_am_time_out_end ? substr($campus->attendance_am_time_out_end, 0, 5) : '12:30') }}"
                                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" />
                                            </div>
                                            <x-input-error :messages="$errors->get('attendance_am_time_out_start')" class="mt-1" />
                                            <x-input-error :messages="$errors->get('attendance_am_time_out_end')" class="mt-1" />
                                        </div>
                                    </div>
                                </div>

                                {{-- PM Session --}}
                                <div class="rounded-xl border border-indigo-200 bg-indigo-50/30 p-4 space-y-3">
                                    <p class="text-sm font-semibold text-indigo-800 flex items-center gap-1.5">
                                        <i class="bi bi-moon-fill"></i>{{ __('Afternoon Session (PM)') }}
                                    </p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <i class="bi bi-box-arrow-in-right text-indigo-600 text-sm"></i>
                                                <span class="text-xs font-medium text-gray-700">{{ __('Clock In Window') }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input id="attendance_pm_time_in_start" name="attendance_pm_time_in_start" data-clockpicker type="text"
                                                       value="{{ old('attendance_pm_time_in_start', $campus->attendance_pm_time_in_start ? substr($campus->attendance_pm_time_in_start, 0, 5) : '13:00') }}"
                                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" />
                                                <span class="text-gray-400 shrink-0">→</span>
                                                <input id="attendance_pm_time_in_end" name="attendance_pm_time_in_end" data-clockpicker type="text"
                                                       value="{{ old('attendance_pm_time_in_end', $campus->attendance_pm_time_in_end ? substr($campus->attendance_pm_time_in_end, 0, 5) : '13:30') }}"
                                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" />
                                            </div>
                                            <x-input-error :messages="$errors->get('attendance_pm_time_in_start')" class="mt-1" />
                                            <x-input-error :messages="$errors->get('attendance_pm_time_in_end')" class="mt-1" />
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 mb-1.5">
                                                <i class="bi bi-box-arrow-left text-indigo-600 text-sm"></i>
                                                <span class="text-xs font-medium text-gray-700">{{ __('Clock Out Window') }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input id="attendance_pm_time_out_start" name="attendance_pm_time_out_start" data-clockpicker type="text"
                                                       value="{{ old('attendance_pm_time_out_start', $campus->attendance_pm_time_out_start ? substr($campus->attendance_pm_time_out_start, 0, 5) : '16:30') }}"
                                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" />
                                                <span class="text-gray-400 shrink-0">→</span>
                                                <input id="attendance_pm_time_out_end" name="attendance_pm_time_out_end" data-clockpicker type="text"
                                                       value="{{ old('attendance_pm_time_out_end', $campus->attendance_pm_time_out_end ? substr($campus->attendance_pm_time_out_end, 0, 5) : '17:30') }}"
                                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" />
                                            </div>
                                            <x-input-error :messages="$errors->get('attendance_pm_time_out_start')" class="mt-1" />
                                            <x-input-error :messages="$errors->get('attendance_pm_time_out_end')" class="mt-1" />
                                        </div>
                                    </div>
                                </div>

                                {{-- Grace Period --}}
                                <div class="rounded-lg border border-gray-200 bg-white p-4">
                                    <div class="max-w-xs">
                                        <x-input-label for="attendance_grace_minutes" :value="__('Grace Period (minutes)')" class="flex items-center gap-1.5">
                                            <i class="bi bi-clock-history text-gray-500"></i>
                                        </x-input-label>
                                        <input id="attendance_grace_minutes" name="attendance_grace_minutes" type="number" min="0" max="180"
                                               value="{{ old('attendance_grace_minutes', $campus->attendance_grace_minutes ?? 60) }}"
                                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" />
                                        <p class="mt-1 text-xs text-gray-500">{{ __('Extra time allowed after each Clock Out window ends. Students can still clock out during grace period but will be marked late.') }}</p>
                                        <x-input-error :messages="$errors->get('attendance_grace_minutes')" class="mt-1" />
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-700 shadow-sm">
                                    <i class="bi bi-save me-1.5"></i>
                                    {{ __('Save Time Windows') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Policy & Maintenance --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <button type="button" @click="sections.policy = !sections.policy"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100 text-purple-700">
                                <i class="bi bi-gear"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('Policy & Maintenance') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('Semester, academic year, and system-wide settings') }}</p>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.policy ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="sections.policy" x-collapse.duration.200ms>
                        <form method="POST" action="{{ route('settings.campus.update') }}" class="px-5 pb-5 space-y-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="policy">

                            <div class="rounded-lg border border-purple-100 bg-purple-50/40 p-4 space-y-4">
                                <p class="text-xs text-purple-800">
                                    {{ __('Semester, academic year, and system-wide settings.') }}
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="semester" :value="__('Semester')" />
                                        <select id="semester" name="semester"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                                            <option value="">{{ __('Select semester...') }}</option>
                                            <option value="1st Semester" @selected(old('semester', $campus->semester) === '1st Semester')>{{ __('1st Semester') }}</option>
                                            <option value="2nd Semester" @selected(old('semester', $campus->semester) === '2nd Semester')>{{ __('2nd Semester') }}</option>
                                            <option value="Summer" @selected(old('semester', $campus->semester) === 'Summer')>{{ __('Summer') }}</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('semester')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label for="academic_year" :value="__('Academic Year')" />
                                        <div class="mt-1 flex items-center gap-2">
                                            <select id="academic_year_start" name="academic_year_start"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                                                <option value="">{{ __('Year') }}</option>
                                                @for ($y = now()->year; $y >= 2020; $y--)
                                                    <option value="{{ $y }}" @selected(old('academic_year_start', $campus->academic_year ? explode('-', $campus->academic_year)[0] : '') == $y)>{{ $y }}</option>
                                                @endfor
                                            </select>
                                            <span class="text-gray-400 font-semibold">—</span>
                                            <select id="academic_year_end" name="academic_year_end"
                                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                                                <option value="">{{ __('Year') }}</option>
                                                @php $startYear = old('academic_year_start', $campus->academic_year ? explode('-', $campus->academic_year)[0] : now()->year); @endphp
                                                @for ($y = (int) $startYear + 1; $y <= (int) $startYear + 1; $y++)
                                                    <option value="{{ $y }}" @selected(old('academic_year_end', $campus->academic_year ? explode('-', $campus->academic_year)[1] : '') == $y)>{{ $y }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">{{ __('Select start year; end year auto-fills.') }}</p>
                                        <x-input-error :messages="$errors->get('academic_year')" class="mt-1" />
                                    </div>
                                </div>

                                <div>
                                    <x-input-label for="policy_review_notes" :value="__('Policy Review Notes')" />
                                    <textarea id="policy_review_notes" name="policy_review_notes" rows="4" maxlength="512"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm"
                                              placeholder="{{ __('Internship policy notes, review dates, or reminders...') }}">{{ old('policy_review_notes', $campus->policy_review_notes) }}</textarea>
                                    <x-input-error :messages="$errors->get('policy_review_notes')" class="mt-1" />
                                </div>

                                <div class="flex items-center gap-2">
                                    <input id="maintenance_mode" name="maintenance_mode" type="checkbox" value="1"
                                           @checked(old('maintenance_mode', $campus->maintenance_mode))
                                           class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600" />
                                    <label for="maintenance_mode" class="text-sm text-gray-700">{{ __('Enable maintenance mode') }}</label>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center rounded-lg bg-purple-600 px-4 py-2 text-sm font-semibold text-white hover:bg-purple-700 shadow-sm">
                                    <i class="bi bi-save me-1.5"></i>
                                    {{ __('Save Policy') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Evaluation Criteria --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <button type="button" @click="sections.evaluation = !sections.evaluation"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-100 text-rose-700">
                                <i class="bi bi-list-check"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('Evaluation Criteria') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('Configure performance evaluation rubric items') }}</p>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.evaluation ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="sections.evaluation" x-collapse.duration.200ms>
                        <form method="POST" action="{{ route('settings.campus.update') }}" class="px-5 pb-5 space-y-4">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="evaluation_criteria">

                            <p class="text-xs text-rose-800">
                                {{ __('Toggle criteria on/off or edit their labels. Changes affect all new evaluations.') }}
                            </p>

                            @php
                                $grouped = $evaluationCriteria->groupBy(fn ($c) => $c->category_key);
                            @endphp

                            @foreach ($grouped as $catKey => $items)
                                @php $first = $items->first(); @endphp
                                <div class="rounded-lg border border-gray-200 overflow-hidden">
                                    <div class="bg-gray-50 px-4 py-2.5 border-b border-gray-200 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-semibold text-gray-800">{{ $first->category_label }}</span>
                                            <span class="text-xs text-gray-400">({{ $items->count() }})</span>
                                        </div>
                                    </div>
                                    <div class="divide-y divide-gray-100">
                                        @foreach ($items as $criterion)
                                            <div class="flex items-center gap-3 px-4 py-2 hover:bg-gray-50/50 transition-colors group">
                                                <input type="hidden" name="criteria[{{ $criterion->id }}][id]" value="{{ $criterion->id }}">
                                                <input type="checkbox"
                                                       name="criteria[{{ $criterion->id }}][is_active]"
                                                       value="1"
                                                       @checked($criterion->is_active)
                                                       class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600 shrink-0">
                                                <input type="text"
                                                       name="criteria[{{ $criterion->id }}][item_label]"
                                                       value="{{ $criterion->item_label }}"
                                                       maxlength="255"
                                                       class="flex-1 min-w-0 text-sm rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                                <x-confirm-delete
                                                    :action="route('evaluations.criteria.destroy', $criterion)"
                                                    :message="__('Remove this criterion? It will be hidden from all new evaluations.')"
                                                    :dialog-id="'criterion-del-'.$criterion->id"
                                                    wrapper-class="inline-flex shrink-0"
                                                    class="inline-flex shrink-0"
                                                >
                                                    <i class="bi bi-x text-sm opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                                </x-confirm-delete>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="border-t border-dashed border-gray-200" x-data="{ open: false }">
                                        <div x-show="open" x-cloak class="flex items-center gap-2 px-4 py-2 bg-rose-50/30">
                                            <input type="text" name="new_criteria[{{ $catKey }}][item_label]" maxlength="255" required
                                                   :disabled="!open"
                                                   placeholder="{{ __('New item label...') }}"
                                                   class="flex-1 min-w-0 text-sm rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                            <input type="hidden" name="new_criteria[{{ $catKey }}][category_key]" value="{{ $catKey }}"
                                                   :disabled="!open">
                                            <button type="submit"
                                                    class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700 shadow-sm shrink-0">
                                                <i class="bi bi-plus me-1"></i>{{ __('Add') }}
                                            </button>
                                        </div>
                                        <button type="button" @click="open = !open"
                                                class="w-full flex items-center justify-center gap-1 px-4 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors">
                                            <i class="bi bi-plus-circle" x-show="!open"></i>
                                            <i class="bi bi-dash-circle" x-show="open" x-cloak></i>
                                            <span x-text="open ? '{{ __('Cancel') }}' : '{{ __('Add item') }}'"></span>
                                        </button>
                                    </div>
                                </div>
                            @endforeach

                            <div class="flex justify-end">
                                <button type="submit"
                                        class="inline-flex items-center rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-700 shadow-sm">
                                    <i class="bi bi-save me-1.5"></i>
                                    {{ __('Save Changes') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- Manage Instructors --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                    <button type="button" @click="sections.instructors = !sections.instructors"
                            class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-100 text-sky-700">
                                <i class="bi bi-person-plus"></i>
                            </span>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ __('Manage Instructors') }}</h3>
                                <p class="text-xs text-gray-500">{{ __('Add new instructors to the system') }}</p>
                            </div>
                        </div>
                        <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.instructors ? 'rotate-180' : ''"></i>
                    </button>
                    <div x-show="sections.instructors" x-collapse.duration.200ms>
                        <div class="px-5 pb-5 space-y-4">
                            @if ($instructors->isNotEmpty())
                                <div class="rounded-lg border border-gray-200 divide-y divide-gray-100">
                                    @foreach ($instructors as $instructor)
                                        <div class="flex items-center justify-between px-3 py-2.5">
                                            <div>
                                                <p class="text-sm font-medium text-gray-900">{{ $instructor->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $instructor->email }}</p>
                                            </div>
                                            <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700">
                                                {{ __('Instructor') }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-gray-500">{{ __('No instructors registered yet.') }}</p>
                            @endif

                            <div class="border-t border-gray-200 pt-4">
                                <p class="text-sm font-semibold text-gray-800 mb-3">{{ __('Add New Instructor') }}</p>
                                <form method="POST" action="{{ route('settings.campus.update') }}" class="space-y-3">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="section" value="instructors">
                                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Last Name') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="last_name" id="instructor_last_name" maxlength="120" required autocomplete="off" aria-label="{{ __('Last name') }}"
                                                   value="{{ old('last_name') }}"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-emerald-600 focus:ring-emerald-600 @error('last_name') border-red-500 @enderror"
                                                   placeholder="{{ __('e.g. Dela Cruz') }}">
                                            @error('last_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('First Name') }} <span class="text-red-500">*</span></label>
                                            <input type="text" name="first_name" id="instructor_first_name" maxlength="120" required autocomplete="off" aria-label="{{ __('First name') }}"
                                                   value="{{ old('first_name') }}"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-emerald-600 focus:ring-emerald-600 @error('first_name') border-red-500 @enderror"
                                                   placeholder="{{ __('e.g. Juan') }}">
                                            @error('first_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Middle Name') }}</label>
                                            <input type="text" name="middle_name" id="instructor_middle_name" maxlength="120" autocomplete="off" aria-label="{{ __('Middle name') }}"
                                                   value="{{ old('middle_name') }}"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-emerald-600 focus:ring-emerald-600 @error('middle_name') border-red-500 @enderror"
                                                   placeholder="{{ __('e.g. Santos') }}">
                                            @error('middle_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Email') }} <span class="text-red-500">*</span></label>
                                            <input type="email" name="email" id="instructor_email" maxlength="120" required autocomplete="off" aria-label="{{ __('Email address') }}"
                                                   value="{{ old('email') }}"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-emerald-600 focus:ring-emerald-600 @error('email') border-red-500 @enderror"
                                                   placeholder="{{ __('e.g. juan@example.com') }}">
                                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Password') }} <span class="text-red-500">*</span></label>
                                            <input type="password" name="password" id="instructor_password" minlength="8" required autocomplete="new-password" aria-label="{{ __('Password') }}"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-emerald-600 focus:ring-emerald-600 @error('password') border-red-500 @enderror"
                                                   placeholder="{{ __('Min. 8 characters') }}">
                                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('Confirm Password') }} <span class="text-red-500">*</span></label>
                                            <input type="password" name="password_confirmation" id="instructor_password_confirmation" minlength="8" required autocomplete="new-password" aria-label="{{ __('Confirm password') }}"
                                                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-emerald-600 focus:ring-emerald-600"
                                                   placeholder="{{ __('Repeat password') }}">
                                        </div>
                                    </div>
                                    <div class="flex justify-end">
                                        <button type="submit"
                                                class="inline-flex items-center rounded-lg bg-sky-600 px-4 py-2 text-sm font-semibold text-white hover:bg-sky-700 shadow-sm">
                                            <i class="bi bi-person-plus me-1.5"></i>
                                            {{ __('Add Instructor') }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #campus-map { isolation: isolate; }
        #campus-map .leaflet-container { border-radius: 0.5rem; }
        .vertex-marker {
            background: #059669;
            border: 3px solid #fff;
            border-radius: 50%;
            width: 14px;
            height: 14px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.3);
        }
    </style>
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var startSelect = document.getElementById('academic_year_start');
            var endSelect = document.getElementById('academic_year_end');
            if (startSelect && endSelect) {
                function syncEnd() {
                    var val = parseInt(startSelect.value);
                    endSelect.innerHTML = '';
                    if (val) {
                        var opt = document.createElement('option');
                        opt.value = val + 1;
                        opt.textContent = val + 1;
                        opt.selected = true;
                        endSelect.appendChild(opt);
                    } else {
                        var opt = document.createElement('option');
                        opt.value = '';
                        opt.textContent = '{{ __("Year") }}';
                        endSelect.appendChild(opt);
                    }
                }
                startSelect.addEventListener('change', syncEnd);
                syncEnd();
            }
        });

        document.addEventListener('alpine:init', function () {
            Alpine.data('campusGeofenceForm', function () {
                return {
                    vertices: @json($campus->campus_boundary_vertices),
                    map: null,
                    polygon: null,
                    markers: [],
                    get vertexCount() {
                        return this.vertices.length;
                    },
                    get boundaryJson() {
                        return JSON.stringify(this.vertices);
                    },
                    init() {
                        this.$nextTick(() => this.initMap());
                    },
                    initMap() {
                        const defaultLat = {{ $campus->campus_lat ?? '10.6432144' }};
                        const defaultLng = {{ $campus->campus_lng ?? '122.9394104' }};

                        this.map = L.map('campus-map', {
                            center: [defaultLat, defaultLng],
                            zoom: 17,
                            zoomControl: true,
                        });

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 20,
                            attribution: '&copy; <a href="https://openstreetmap.org/copyright">OpenStreetMap</a>',
                        }).addTo(this.map);

                        if (this.vertices.length >= 3) {
                            this.drawPolygon();
                            this.map.fitBounds(this.polygon.getBounds().pad(0.2));
                        }

                        this.map.on('click', (e) => {
                            this.addVertex(e.latlng.lat, e.latlng.lng);
                        });
                    },
                    addVertex(lat, lng) {
                        this.vertices.push({ lat: lat, lng: lng });
                        this.redraw();
                    },
                    undoVertex() {
                        if (this.vertices.length === 0) return;
                        this.vertices.pop();
                        this.redraw();
                    },
                    clearPolygon() {
                        this.vertices = [];
                        this.redraw();
                    },
                    redraw() {
                        this.clearLayers();
                        if (this.vertices.length >= 3) {
                            this.drawPolygon();
                        } else if (this.vertices.length > 0) {
                            this.drawMarkers();
                        }
                    },
                    drawPolygon() {
                        const latlngs = this.vertices.map(v => [v.lat, v.lng]);
                        this.polygon = L.polygon(latlngs, {
                            color: '#059669',
                            fillColor: '#059669',
                            fillOpacity: 0.12,
                            weight: 2.5,
                        }).addTo(this.map);
                        this.drawMarkers(latlngs);
                    },
                    drawMarkers(latlngs) {
                        const points = latlngs || this.vertices.map(v => [v.lat, v.lng]);
                        points.forEach((ll, i) => {
                            const marker = L.marker(ll, {
                                icon: L.divIcon({
                                    className: 'vertex-marker',
                                    html: '<span style="display:flex;align-items:center;justify-content:center;width:100%;height:100%;font-size:8px;font-weight:bold;color:#fff;">' + (i + 1) + '</span>',
                                    iconSize: [14, 14],
                                    iconAnchor: [7, 7],
                                })
                            }).addTo(this.map);
                            this.markers.push(marker);
                        });
                    },
                    clearLayers() {
                        if (this.polygon) {
                            this.map.removeLayer(this.polygon);
                            this.polygon = null;
                        }
                        this.markers.forEach(m => this.map.removeLayer(m));
                        this.markers = [];
                    },
                };
            });
        });
    </script>
    @endpush
</x-app-layout>
