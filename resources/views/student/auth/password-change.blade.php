<x-entry-layout>
    <div class="space-y-6" x-data="passwordStrength()">
        <div class="text-center">
            <h2 class="text-xl font-bold text-gray-900">{{ __('Set Your Password') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('This is your first login. Please set a new password.') }}</p>
        </div>

        @error('password')
            <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 flex items-start gap-2">
                <i class="bi bi-exclamation-triangle mt-0.5 flex-shrink-0"></i>
                <span>{{ $message }}</span>
            </div>
        @enderror

        <form method="POST" action="{{ route('student.password.update') }}" class="space-y-5">
            @csrf

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    {{ __('New Password') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative mt-1">
                    <input id="password"
                           name="password"
                           :type="show ? 'text' : 'password'"
                           x-model="password"
                           @input="checkStrength()"
                           required
                           autocomplete="new-password"
                           class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-3 pr-10 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                           placeholder="{{ __('Enter new password') }}">
                    <button type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none rounded-r-lg"
                            :aria-label="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                        <i class="bi text-lg" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                </div>

                {{-- Strength bar --}}
                <div class="mt-2 h-1.5 w-full rounded-full bg-gray-200" x-cloak x-show="password.length > 0">
                    <div class="h-1.5 rounded-full transition-all duration-300"
                         :style="'width: ' + strength + '%'"
                         :class="strength <= 30 ? 'bg-red-500' : strength <= 60 ? 'bg-orange-500' : 'bg-emerald-500'"></div>
                </div>
                <p class="mt-1 text-xs text-gray-500" x-text="label.text" x-cloak x-show="password.length > 0"></p>

                {{-- Requirements checklist --}}
                <div class="mt-3 space-y-1" x-cloak x-show="password.length > 0">
                    <div class="flex items-center gap-2 text-xs" :class="requirements.minLength ? 'text-emerald-700' : 'text-gray-500'">
                        <i class="bi" :class="requirements.minLength ? 'bi-check-circle-fill text-emerald-500' : 'bi-circle text-gray-300'"></i>
                        {{ __('At least 8 characters') }}
                    </div>
                    <div class="flex items-center gap-2 text-xs" :class="requirements.hasUpper ? 'text-emerald-700' : 'text-gray-500'">
                        <i class="bi" :class="requirements.hasUpper ? 'bi-check-circle-fill text-emerald-500' : 'bi-circle text-gray-300'"></i>
                        {{ __('One uppercase letter') }}
                    </div>
                    <div class="flex items-center gap-2 text-xs" :class="requirements.hasLower ? 'text-emerald-700' : 'text-gray-500'">
                        <i class="bi" :class="requirements.hasLower ? 'bi-check-circle-fill text-emerald-500' : 'bi-circle text-gray-300'"></i>
                        {{ __('One lowercase letter') }}
                    </div>
                    <div class="flex items-center gap-2 text-xs" :class="requirements.hasNumber ? 'text-emerald-700' : 'text-gray-500'">
                        <i class="bi" :class="requirements.hasNumber ? 'bi-check-circle-fill text-emerald-500' : 'bi-circle text-gray-300'"></i>
                        {{ __('One number') }}
                    </div>
                    <div class="flex items-center gap-2 text-xs" :class="requirements.hasSpecial ? 'text-emerald-700' : 'text-gray-500'">
                        <i class="bi" :class="requirements.hasSpecial ? 'bi-check-circle-fill text-emerald-500' : 'bi-circle text-gray-300'"></i>
                        {{ __('One special character') }}
                    </div>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    {{ __('Confirm Password') }} <span class="text-red-500">*</span>
                </label>
                <div class="relative mt-1">
                    <input id="password_confirmation"
                           name="password_confirmation"
                           :type="showConfirm ? 'text' : 'password'"
                           x-model="confirmPassword"
                           required
                           autocomplete="new-password"
                           class="block w-full rounded-lg border bg-white py-2.5 pl-3 pr-10 text-gray-900 shadow-sm focus:ring-1 sm:text-sm"
                           :class="confirmPassword.length > 0 && !match ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500'"
                           placeholder="{{ __('Confirm new password') }}">
                    <button type="button"
                            @click="showConfirm = !showConfirm"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none rounded-r-lg"
                            :aria-label="showConfirm ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                        <i class="bi text-lg" :class="showConfirm ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                </div>
                <div class="mt-1 flex items-center gap-1.5 text-xs" x-cloak x-show="confirmPassword.length > 0">
                    <i class="bi" :class="match ? 'bi-check-circle-fill text-emerald-500' : 'bi-x-circle-fill text-red-500'"></i>
                    <span :class="match ? 'text-emerald-700' : 'text-red-600'" x-text="match ? '{{ __('Passwords match') }}' : '{{ __('Passwords do not match') }}'"></span>
                </div>
            </div>

            <button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wider transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary"
                    :disabled="password.length < 8 || !match"
                    :class="password.length < 8 || !match ? 'opacity-50 cursor-not-allowed' : ''">
                {{ __('Save Password') }}
            </button>
        </form>
    </div>
</x-entry-layout>
