<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-4 space-y-5">
        @csrf
        @method('patch')

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="form-group-float">
                <input
                    id="last_name"
                    name="last_name"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('last_name', $user->last_name) }}"
                    class="float-input @error('last_name') is-invalid @enderror"
                    placeholder=" "
                    autocomplete="family-name"
                    title="{{ __('Your family name') }}"
                />
                <label for="last_name" class="float-label">
                    {{ __('Last Name') }} <span class="text-red-500">*</span>
                </label>
                <x-input-error class="mt-2" :messages="$errors->get('last_name')" />
            </div>

            <div class="form-group-float">
                <input
                    id="first_name"
                    name="first_name"
                    type="text"
                    required
                    maxlength="255"
                    value="{{ old('first_name', $user->first_name) }}"
                    class="float-input @error('first_name') is-invalid @enderror"
                    placeholder=" "
                    autocomplete="given-name"
                    title="{{ __('Your given name') }}"
                />
                <label for="first_name" class="float-label">
                    {{ __('First Name') }} <span class="text-red-500">*</span>
                </label>
                <x-input-error class="mt-2" :messages="$errors->get('first_name')" />
            </div>

            <div class="form-group-float">
                <input
                    id="middle_name"
                    name="middle_name"
                    type="text"
                    maxlength="255"
                    value="{{ old('middle_name', $user->middle_name) }}"
                    class="float-input @error('middle_name') is-invalid @enderror"
                    placeholder=" "
                    autocomplete="additional-name"
                    title="{{ __('Optional middle name') }}"
                />
                <label for="middle_name" class="float-label">
                    {{ __('Middle Name') }}
                </label>
                <x-input-error class="mt-2" :messages="$errors->get('middle_name')" />
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="form-group-float">
                <input
                    id="email"
                    name="email"
                    type="email"
                    required
                    maxlength="255"
                    value="{{ old('email', $user->email) }}"
                    class="float-input @error('email') is-invalid @enderror"
                    placeholder=" "
                    autocomplete="username"
                    title="{{ __('Used for sign-in and notifications') }}"
                />
                <label for="email" class="float-label">
                    {{ __('Email Address') }} <span class="text-red-500">*</span>
                </label>
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
                <small class="hint-text">{{ __('Used for sign-in and system notifications.') }}</small>
            </div>
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="flex items-center gap-2 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm">
                <i class="bi bi-exclamation-circle text-amber-600"></i>
                <span class="text-amber-800">{{ __('Your email address is unverified.') }}</span>
                <button form="send-verification" class="font-semibold text-amber-700 underline hover:text-amber-900">
                    {{ __('Resend verification') }}
                </button>

                @if (session('status') === 'verification-link-sent')
                    <span class="text-emerald-600 font-medium">{{ __('Sent!') }}</span>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="profile-save-btn">
                <i class="bi bi-check-lg me-1"></i>{{ __('Save Changes') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }"
                   x-show="show"
                   x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm font-medium text-emerald-600">
                    <i class="bi bi-check-circle-fill me-1"></i>{{ __('Saved.') }}
                </p>
            @endif
        </div>
    </form>
</section>
