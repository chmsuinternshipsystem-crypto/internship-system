<section x-data="passwordStrength()">
    <form method="post" action="{{ route('password.update') }}" class="mt-4 space-y-5">
        @csrf
        @method('put')

        <div class="form-group-float password-wrap">
            <input
                id="update_password_current_password"
                name="current_password"
                type="password"
                class="float-input @error('current_password', 'updatePassword') is-invalid @enderror"
                placeholder=" "
                autocomplete="current-password"
            />
            <label for="update_password_current_password" class="float-label">
                {{ __('Current Password') }}
            </label>
            <button type="button" class="toggle-password" onclick="togglePass('update_password_current_password', this)">
                <i class="bi bi-eye"></i>
            </button>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="form-group-float password-wrap">
                <input
                    id="update_password_password"
                    name="password"
                    :type="show ? 'text' : 'password'"
                    x-model="password"
                    @input="checkStrength()"
                    class="float-input @error('password', 'updatePassword') is-invalid @enderror"
                    placeholder=" "
                    autocomplete="new-password"
                />
                <label for="update_password_password" class="float-label">
                    {{ __('New Password') }}
                </label>
                <button type="button" @click="show = !show" class="toggle-password">
                    <i class="bi text-lg" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />

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

            <div class="form-group-float password-wrap">
                <input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    :type="showConfirm ? 'text' : 'password'"
                    x-model="confirmPassword"
                    class="float-input @error('password_confirmation', 'updatePassword') is-invalid @enderror"
                    :class="confirmPassword.length > 0 && !match ? 'is-invalid' : ''"
                    placeholder=" "
                    autocomplete="new-password"
                />
                <label for="update_password_password_confirmation" class="float-label">
                    {{ __('Confirm Password') }}
                </label>
                <button type="button" @click="showConfirm = !showConfirm" class="toggle-password">
                    <i class="bi text-lg" :class="showConfirm ? 'bi-eye-slash' : 'bi-eye'"></i>
                </button>
                <div class="mt-1 flex items-center gap-1.5 text-xs" x-cloak x-show="confirmPassword.length > 0">
                    <i class="bi" :class="match ? 'bi-check-circle-fill text-emerald-500' : 'bi-x-circle-fill text-red-500'"></i>
                    <span :class="match ? 'text-emerald-700' : 'text-red-600'" x-text="match ? '{{ __('Passwords match') }}' : '{{ __('Passwords do not match') }}'"></span>
                </div>
                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="profile-save-btn">
                <i class="bi bi-check-lg me-1"></i>{{ __('Update Password') }}
            </button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm font-medium text-emerald-600">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('Password updated.') }}
                </p>
            @endif
        </div>
    </form>
</section>
