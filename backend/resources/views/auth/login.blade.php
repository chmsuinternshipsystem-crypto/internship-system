<x-entry-layout>
    <x-alert-message />

    <style>
        .login-fade-in { animation: loginFadeIn 0.5s ease-out both; }
        .login-slide-up { animation: loginSlideUp 0.4s ease-out both; }
        .login-slide-up:nth-child(2) { animation-delay: 0.1s; }
        .login-card-enter { animation: loginSlideUp 0.35s ease-out both; }
        @keyframes loginFadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes loginSlideUp { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }
    </style>

    @php
        $staffLoginRoles = [
            ['key' => 'instructor', 'label' => __('Instructor'), 'placeholder' => 'instructor@chmsu.edu.ph'],
            ['key' => 'chairperson', 'label' => __('Program chairperson'), 'placeholder' => 'chairperson@chmsu.edu.ph'],
            ['key' => 'dean', 'label' => __('Dean'), 'placeholder' => 'dean@chmsu.edu.ph'],
        ];
        $oldRole = old('role');
        $initialPortal = 'choose';
        if ($oldRole === 'student') {
            $initialPortal = 'student';
        } elseif ($oldRole !== null && $oldRole !== '') {
            $initialPortal = 'staff';
        }
        $staffKeys = array_column($staffLoginRoles, 'key');
        $defaultStaffRole = old('role', 'instructor');
        if ($oldRole === 'student' || ! in_array($defaultStaffRole, $staffKeys, true)) {
            $defaultStaffRole = 'instructor';
        }
    @endphp

    <div
        x-data="loginPortal('{{ $initialPortal }}')"
        class="w-full min-w-0 space-y-5"
    >
        {{-- Step 1: separate student vs school staff --}}
        <div
            x-show="portal === 'choose'"
            class="space-y-4"
        >
            <div class="text-left">
                <h2 class="text-xl font-semibold text-gray-900 tracking-tight">{{ __('Welcome') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Choose how you would like to sign in.') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button
                    type="button"
                    @click="portal = 'student'"
                    class="login-choice-card group text-left rounded-xl border-2 border-gray-200 bg-white p-4 shadow-sm transition-all duration-200 hover:border-emerald-500 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 login-card-enter"
                >
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-50 text-emerald-700 group-hover:bg-emerald-100">
                        <i class="bi bi-mortarboard text-xl" aria-hidden="true"></i>
                    </span>
                    <span class="mt-3 block font-semibold text-gray-900">{{ __('Student') }}</span>
                    <span class="mt-1 block text-xs text-gray-500 leading-snug">{{ __('Internship portal: student ID and password.') }}</span>
                </button>

                <button
                    type="button"
                    @click="portal = 'staff'"
                    class="login-choice-card group text-left rounded-xl border-2 border-gray-200 bg-white p-4 shadow-sm transition-all duration-200 hover:border-emerald-500 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 login-card-enter"
                    style="animation-delay: 0.1s;"
                >
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-700 group-hover:bg-slate-200">
                        <i class="bi bi-building-check text-xl" aria-hidden="true"></i>
                    </span>
                    <span class="mt-3 block font-semibold text-gray-900">{{ __('Faculty & staff') }}</span>
                    <span class="mt-1 block text-xs text-gray-500 leading-snug">{{ __('School email, role, and password.') }}</span>
                </button>
            </div>

            <a
                href="{{ route('attendance.check-in') }}"
                class="group flex items-start gap-3 rounded-xl border-2 border-emerald-200 bg-emerald-50/70 p-4 shadow-sm transition-all duration-200 hover:border-emerald-500 hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 login-card-enter" style="animation-delay: 0.2s;"
            >
                <span class="mt-0.5 flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <i class="bi bi-clock-history text-xl" aria-hidden="true"></i>
                </span>
                <span class="block">
                    <span class="block font-semibold text-emerald-900">{{ __('Clock In / Out') }}</span>
                    <span class="mt-1 block text-xs leading-snug text-emerald-800/90">{{ __('Quick attendance station for deployed students (student number + attendance passcode).') }}</span>
                </span>
            </a>
        </div>

        {{-- Student: dedicated form --}}
        <div
            x-show="portal === 'student'"
            class="space-y-4"
        >
            <button
                type="button"
                @click="portal = 'choose'"
                class="inline-flex items-center gap-1 text-sm font-medium text-emerald-700 hover:text-emerald-800 focus:outline-none focus-visible:underline"
            >
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                {{ __('Back') }}
            </button>

            <div class="text-left">
                <h2 class="text-xl font-semibold text-gray-900 tracking-tight">{{ __('Student sign in') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Use your 8-digit student number and portal password. OTP verification is required after sign in.') }}</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="role" value="student" />

                <div class="space-y-1.5">
                    <x-input-label for="student_number" :value="__('Student number')" />
                    <input
                        id="student_number"
                        type="text"
                        name="student_number"
                        value="{{ old('student_number') }}"
                        required
                        autocomplete="username"
                        inputmode="numeric"
                        pattern="[0-9]{8}"
                        maxlength="8"
                        placeholder="20230001"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    />
                    <x-input-error :messages="$errors->get('student_number')" class="mt-1" />
                </div>

                <div class="space-y-1.5">
                    <x-input-label for="password_student" :value="__('Password')" />
                    <div class="relative">
                        <input
                            id="password_student"
                            :type="showPasswordStudent ? 'text' : 'password'"
                            name="password"
                            x-ref="passwordStudent"
                            required
                            autocomplete="current-password"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-3 pr-10 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                        />
                        <button
                            type="button"
                            @click="showPasswordStudent = !showPasswordStudent"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none rounded-r-lg"
                            :aria-label="showPasswordStudent ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                        >
                            <i class="bi text-lg" :class="showPasswordStudent ? 'bi-eye-slash' : 'bi-eye'" aria-hidden="true"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <button
                    type="submit"
                    class="inline-flex w-full justify-center items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    {{ __('Sign in') }}
                </button>

                <a
                    href="{{ route('attendance.check-in') }}"
                    class="inline-flex w-full justify-center items-center rounded-lg border border-emerald-300 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 shadow-sm hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    <i class="bi bi-clock-history me-2"></i>{{ __('Go to Clock In / Out') }}
                </a>
            </form>
        </div>

        {{-- Staff: role dropdown + email --}}
        <div
            x-show="portal === 'staff'"
            class="space-y-4"
        >
            <button
                type="button"
                @click="portal = 'choose'"
                class="inline-flex items-center gap-1 text-sm font-medium text-emerald-700 hover:text-emerald-800 focus:outline-none focus-visible:underline"
            >
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                {{ __('Back') }}
            </button>

            <div class="text-left">
                <h2 class="text-xl font-semibold text-gray-900 tracking-tight">{{ __('Faculty & staff sign in') }}</h2>
                <p class="mt-1 text-sm text-gray-500">{{ __('Select your role, then sign in with your institutional email.') }}</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <x-input-label for="staff_role" :value="__('Your role')" />
                    <select
                        id="staff_role"
                        name="role"
                        required
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    >
                        @foreach ($staffLoginRoles as $r)
                            <option value="{{ $r['key'] }}" @selected($defaultStaffRole === $r['key'])>{{ $r['label'] }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-1" />
                </div>

                <div class="space-y-1.5">
                    <x-input-label for="email" :value="__('Email address')" />
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        maxlength="255"
                        autocomplete="username"
                        placeholder="{{ __('you@chmsu.edu.ph') }}"
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    />
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>

                <div class="space-y-1.5">
                    <x-input-label for="password_staff" :value="__('Password')" />
                    <div class="relative">
                        <input
                            id="password_staff"
                            :type="showPasswordStaff ? 'text' : 'password'"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-3 pr-10 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                        />
                        <button
                            type="button"
                            @click="showPasswordStaff = !showPasswordStaff"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none rounded-r-lg"
                            :aria-label="showPasswordStaff ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
                        >
                            <i class="bi text-lg" :class="showPasswordStaff ? 'bi-eye-slash' : 'bi-eye'" aria-hidden="true"></i>
                        </button>
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>

                <button
                    type="submit"
                    class="inline-flex w-full justify-center items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    {{ __('Sign in') }}
                </button>
            </form>
        </div>
    </div>

    @push('login-scripts')
    <script>
        function loginPortal(initialPortal) {
            return {
                portal: initialPortal,
                showPasswordStudent: false,
                showPasswordStaff: false,
            };
        }
    </script>
    @endpush
</x-entry-layout>
