<x-entry-layout>
    <div class="max-w-md mx-auto">
        {{-- Header --}}
        <div class="mb-6 text-center">
            <div class="mb-3 flex items-center justify-between">
                @if ($student)
                    <a href="{{ route('student.dashboard') }}"
                       class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                        <i class="bi bi-arrow-left-circle"></i>
                        {{ __('Dashboard') }}
                    </a>
                    <form method="POST" action="{{ route('student.logout') }}" class="inline">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 py-1 text-xs font-semibold text-gray-600 hover:bg-gray-50 cursor-pointer">
                            <i class="bi bi-box-arrow-right"></i>
                            {{ __('Sign out') }}
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-1 rounded-md border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
                        <i class="bi bi-arrow-left-circle"></i>
                        {{ __('Sign In') }}
                    </a>
                @endif
            </div>

            @if ($result)
                @php
                    $isTimeIn = in_array($result['action'], ['am_time_in', 'pm_time_in', 'time_in'], true);
                    $isTimeOut = in_array($result['action'], ['am_time_out', 'pm_time_out', 'time_out'], true);
                    $resultSession = str_starts_with((string) ($result['action'] ?? ''), 'pm') ? 'pm' : 'am';
                    $bgGrad = $resultSession === 'pm' ? 'from-indigo-50 via-indigo-50/50 to-white' : 'from-emerald-50 via-emerald-50/50 to-white';
                    $accentColor = $resultSession === 'pm' ? 'indigo' : 'emerald';
                @endphp
                <div class="mb-2 inline-flex items-center justify-center w-16 h-16 rounded-full {{ $isTimeIn ? 'bg-emerald-100' : 'bg-sky-100' }}">
                    <i class="bi bi-check-circle-fill text-3xl {{ $isTimeIn ? 'text-emerald-600' : 'text-sky-600' }}"></i>
                </div>
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ $result['action_label'] }} {{ __('Recorded') }}
                </h2>
            @else
                @php
                    $resultSession = $sessionType ?? 'am';
                    $bgGrad = $resultSession === 'pm' ? 'from-indigo-50 via-indigo-50/50 to-white' : 'from-emerald-50 via-emerald-50/50 to-white';
                    $accentColor = $resultSession === 'pm' ? 'indigo' : 'emerald';
                @endphp
                <h2 class="text-xl font-semibold text-gray-800">
                    {{ __('Clock In / Clock Out Station') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('Use your location, student number, and passcode to record time in/out.') }}
                </p>
            @endif
        </div>

        <x-alert-message />

        @if ($result)
            {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ Result Card â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
            @php
                $statusMap = [
                    'inside_pass' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'icon' => 'bi-check-circle-fill', 'label' => __('On Site')],
                    'near_boundary_review' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-700', 'icon' => 'bi-exclamation-triangle-fill', 'label' => __('Boundary')],
                    'outside_flagged' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-700', 'icon' => 'bi-x-circle-fill', 'label' => __('Off Site')],
                    'location_unavailable' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'icon' => 'bi-question-circle-fill', 'label' => __('Unverified')],
                    'time_outside_window' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700', 'icon' => 'bi-clock-fill', 'label' => __('Late')],
                ];
                $sc = $statusMap[$result['geofence_status']] ?? $statusMap['location_unavailable'];
            @endphp

            <div class="rounded-xl border border-gray-200 bg-gradient-to-b {{ $bgGrad }} p-6 shadow-sm text-center">
                <div class="mb-3 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $resultSession === 'pm' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                    <i class="bi {{ $resultSession === 'pm' ? 'bi-moon-fill' : 'bi-sun-fill' }}"></i>
                    {{ $resultSession === 'pm' ? __('PM Session') : __('AM Session') }}
                </div>
                <p class="text-3xl font-bold text-gray-900">{{ $result['time'] }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $result['date'] }}</p>

                <span class="inline-flex items-center gap-1.5 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }} px-3 py-1 text-xs font-semibold mt-3">
                    <i class="bi {{ $sc['icon'] }}"></i>
                    {{ $sc['label'] }}
                </span>

                @if ($result['total_minutes'] !== null)
                    @php $hours = intdiv($result['total_minutes'], 60); $mins = $result['total_minutes'] % 60; @endphp
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Total Time Rendered') }}</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $hours }}h {{ $mins }}m</p>
                    </div>
                @endif

                <div class="mt-4 pt-4 border-t border-gray-100 text-sm text-gray-600">
                    <p>{{ $result['student_name'] }}</p>
                    <p class="text-gray-400">{{ $result['student_number'] }}</p>
                </div>

                <p class="mt-4 text-sm text-gray-600">
                    @if ($isTimeIn)
                        {{ __('Your :session has started.', ['session' => $result['action_label']]) }}
                    @elseif ($result['action'] === 'pm_time_out')
                        {{ __('Your attendance for today is complete.') }}
                    @else
                        {{ __('Your :session is complete.', ['session' => $result['action_label']]) }}
                    @endif
                </p>

                <div class="mt-5 space-y-2">
                    @if ($isTimeIn && $student && $todayStatus === 'checked_in')
                        <a href="{{ route('attendance.check-in', ['action' => 'time_out']) }}"
                           class="block w-full rounded-md bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white text-center shadow-sm hover:bg-sky-700 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 transition">
                            <i class="bi bi-box-arrow-left me-2"></i>{{ __('Time Out Now') }}
                        </a>
                    @endif

                    <a href="{{ route('attendance.check-in') }}"
                       class="block w-full rounded-md border border-gray-200 bg-white px-4 py-2 text-xs font-semibold text-gray-500 text-center hover:text-gray-700 hover:border-gray-300 focus:outline-none transition">
                        <i class="bi bi-arrow-repeat me-1"></i>{{ __('Make Another Entry') }}
                    </a>
                </div>
            </div>
        @else
            {{-- Form --}}
            <div class="rounded-xl border border-gray-200 bg-gradient-to-b {{ $bgGrad }} p-5 shadow-sm">
                <div class="mb-4 inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-semibold {{ $sessionType === 'pm' ? 'bg-indigo-100 text-indigo-700' : 'bg-emerald-100 text-emerald-700' }}">
                    <i class="bi {{ $sessionType === 'pm' ? 'bi-moon-fill' : 'bi-sun-fill' }}"></i>
                    @if ($sessionType === 'pm')
                        {{ __('PM Session') }}
                    @else
                        {{ __('AM Session') }}
                    @endif
                    @if ($sessionLabel)
                        <span class="opacity-70">&middot; {{ $sessionLabel }}</span>
                    @endif
                </div>
            <form method="POST" action="{{ route('attendance.store') }}" x-data="attendanceCheckIn()">
                @csrf

                <div class="space-y-4">
                    {{-- Location --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">
                            <i class="bi bi-geo-alt me-1"></i>{{ __('Your Location') }}
                        </p>
                        <div :class="{
                            'border-gray-300 bg-gray-50': locationStatus === 'idle',
                            'border-amber-300 bg-amber-50': locationStatus === 'loading',
                            'border-emerald-300 bg-emerald-50': locationStatus === 'captured',
                            'border-rose-300 bg-rose-50': locationStatus === 'denied' || locationStatus === 'unsupported',
                        }" class="rounded-lg border-2 px-3 py-3 transition-colors duration-200">
                            <div class="flex items-start gap-2">
                                <div class="mt-0.5">
                                    <i :class="{
                                        'bi-geo-alt text-gray-400': locationStatus === 'idle',
                                        'bi-arrow-repeat text-amber-500 animate-spin': locationStatus === 'loading',
                                        'bi-check-circle-fill text-emerald-500': locationStatus === 'captured',
                                        'bi-exclamation-triangle-fill text-rose-500': locationStatus === 'denied',
                                        'bi-exclamation-triangle-fill text-rose-500': locationStatus === 'unsupported',
                                    }" class="bi"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold" :class="{
                                        'text-gray-700': locationStatus === 'idle',
                                        'text-amber-700': locationStatus === 'loading',
                                        'text-emerald-700': locationStatus === 'captured',
                                        'text-rose-700': locationStatus === 'denied' || locationStatus === 'unsupported',
                                    }" x-text="locationText()"></p>
                                    <p class="mt-0.5 text-[11px] text-gray-500" x-show="locationStatus === 'captured'" x-text="'Lat: ' + latitude + ', Lng: ' + longitude + (accuracyMeters ? ' (Â±' + Math.round(Number(accuracyMeters)) + 'm)' : '')"></p>
                                    <p class="mt-0.5 text-[11px] text-rose-600" x-show="locationStatus === 'denied' || locationStatus === 'unsupported'">
                                        {{ __('Your entry will be recorded and marked for review.') }}
                                    </p>
                                </div>
                                <button type="button" @click="getLocation()" x-show="locationStatus !== 'loading'"
                                    class="inline-flex items-center rounded-md border border-emerald-500 bg-white px-2.5 py-1.5 text-[11px] font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                    <i class="bi bi-crosshair me-1"></i>{{ __('Use my location') }}
                                </button>
                                <button type="button" disabled x-show="locationStatus === 'loading'"
                                    class="inline-flex items-center rounded-md border border-gray-300 bg-gray-100 px-2.5 py-1.5 text-[11px] font-semibold text-gray-400 cursor-not-allowed">
                                    <i class="bi bi-arrow-repeat me-1 animate-spin"></i>{{ __('Capturing...') }}
                                </button>
                            </div>
                        </div>
                        <input type="hidden" name="latitude" x-model="latitude">
                        <input type="hidden" name="longitude" x-model="longitude">
                        <input type="hidden" name="accuracy_meters" x-model="accuracyMeters">
                    </div>

                    {{-- Action Toggle --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">
                            <i class="bi bi-clock me-1"></i>{{ __('Action') }}
                        </p>
                        <div class="grid grid-cols-2 gap-2 rounded-lg border border-gray-200 bg-gray-50 p-1">
                            <button type="button" @click="entryType = 'time_in'"
                                :class="entryType === 'time_in' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-100'"
                                class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold transition">
                                <i class="bi bi-box-arrow-in-right me-2"></i>{{ __('Time In') }}
                            </button>
                            <button type="button" @click="entryType = 'time_out'"
                                :class="entryType === 'time_out' ? 'bg-sky-600 text-white shadow-sm' : 'bg-white text-gray-700 hover:bg-gray-100'"
                                class="inline-flex items-center justify-center rounded-md px-3 py-2 text-sm font-semibold transition">
                                <i class="bi bi-box-arrow-left me-2"></i>{{ __('Time Out') }}
                            </button>
                        </div>
                        <p class="mt-1 text-[11px] text-gray-500" x-show="entryType === 'time_in'">
                            {{ __('Use this when you are starting your duty.') }}
                        </p>
                        <p class="mt-1 text-[11px] text-gray-500" x-show="entryType === 'time_out'">
                            {{ __('Use this when you are ending your duty.') }}
                        </p>
                        <input type="hidden" name="attendance_action" x-model="entryType">
                        <x-input-error :messages="$errors->get('attendance_action')" class="mt-1" />
                    </div>

                    @if ($student)
                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                            <p class="font-medium flex items-center gap-1.5">
                                <i class="bi bi-check-circle-fill text-emerald-600"></i>
                                {{ __('Signed in as') }} <strong>{{ $student->name }}</strong>
                                <span class="text-emerald-600 font-mono">({{ $student->student_number }})</span>
                            </p>
                            <p class="text-xs text-emerald-700 mt-0.5">{{ __('No passcode needed — your session handles verification.') }}</p>
                        </div>
                    @else
                        <div>
                            <x-input-label for="student_number" :value="__('Student Number')" />
                            <input id="student_number" name="student_number" type="text" inputmode="numeric" pattern="[0-9]{8}" maxlength="8"
                                value="{{ old('student_number') }}" required
                                class="block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                placeholder="{{ __('e.g. 20230001') }}"
                                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,8)" />
                            <x-input-error :messages="$errors->get('student_number')" class="mt-1" />
                        </div>

                        <div x-data="{ showPasscode: false }">
                            <x-input-label for="passcode" :value="__('Attendance Passcode')" />
                            <div class="relative">
                                <input id="passcode" name="passcode" :type="showPasscode ? 'text' : 'password'"
                                    inputmode="numeric" pattern="[0-9]{6}" maxlength="6"
                                    value="{{ old('passcode') }}" required
                                    class="block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 pr-10 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 transition-colors"
                                    placeholder="{{ __('6-digit passcode') }}"
                                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)" />
                                <button type="button" @click="showPasscode = !showPasscode"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <i class="bi" :class="showPasscode ? 'bi-eye-slash' : 'bi-eye'"></i>
                                </button>
                            </div>
                            <x-input-error :messages="$errors->get('passcode')" class="mt-1" />
                        </div>
                    @endif
                </div>

                {{-- Submit Button --}}
                <div class="mt-6">
                    <button type="submit"
                        :class="entryType === 'time_out' ? 'bg-sky-600 hover:bg-sky-700 focus:ring-sky-500' : 'bg-emerald-600 hover:bg-emerald-700 focus:ring-emerald-500'"
                        class="inline-flex w-full justify-center items-center rounded-md px-4 py-2.5 font-semibold text-sm text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 transition">
                        <i class="bi me-2" :class="entryType === 'time_out' ? 'bi-box-arrow-left' : 'bi-box-arrow-in-right'"></i>
                        <span x-text="entryType === 'time_out' ? '{{ __('Submit Time Out') }}' : '{{ __('Submit Time In') }}'"></span>
                    </button>
                </div>
            </form>
            </div>
        @endif
    </div>

    @push('login-scripts')
    <script>
        function attendanceCheckIn() {
            return {
                entryType: '{{ old('attendance_action', request('action', 'time_in')) }}',
                latitude: '',
                longitude: '',
                accuracyMeters: '',
                locationStatus: 'idle',
                locationText() {
                    const texts = {
                        idle: '{{ __('Tap the button to capture your location.') }}',
                        loading: '{{ __('Requesting location...') }}',
                        captured: '{{ __('Location captured') }}',
                        denied: '{{ __('Location access denied') }}',
                        unsupported: '{{ __('Geolocation not supported') }}',
                    };
                    return texts[this.locationStatus] || '';
                },
                getLocation() {
                    if (!navigator.geolocation) {
                        this.locationStatus = 'unsupported';
                        return;
                    }
                    this.locationStatus = 'loading';
                    navigator.geolocation.getCurrentPosition(
                        (pos) => {
                            this.latitude = pos.coords.latitude;
                            this.longitude = pos.coords.longitude;
                            this.accuracyMeters = pos.coords.accuracy || '';
                            this.locationStatus = 'captured';
                        },
                        () => {
                            this.locationStatus = 'denied';
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                },
                init() {
                    if (navigator.geolocation) {
                        navigator.geolocation.getCurrentPosition(
                            (pos) => {
                                this.latitude = pos.coords.latitude;
                                this.longitude = pos.coords.longitude;
                                this.accuracyMeters = pos.coords.accuracy || '';
                                this.locationStatus = 'captured';
                            },
                            () => {
                                this.locationStatus = 'idle';
                            },
                            { enableHighAccuracy: true, timeout: 5000, maximumAge: 60000 }
                        );
                    } else {
                        this.locationStatus = 'unsupported';
                    }
                }
            }
        }
    </script>
    @endpush
</x-entry-layout>

