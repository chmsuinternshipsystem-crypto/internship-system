<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Account') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Profile & Security') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Manage your basic information, sign‑in details, and account safety.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-7xl mx-auto space-y-6">

        {{-- ── Account Overview Banner ── --}}
        <div class="profile-banner">
            <div class="flex items-center gap-4">
                <div class="profile-avatar">
                    <i class="bi bi-person-fill"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">{{ Auth::user()->name }}</h3>
                    <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 mt-4 sm:mt-0">
                <span class="profile-role-badge">
                    <i class="bi bi-shield-check me-1"></i>
                    {{ Auth::user()->role ? Str::headline(Auth::user()->role) : __('Not set') }}
                </span>
                @if (Auth::user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! Auth::user()->hasVerifiedEmail())
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                        <i class="bi bi-exclamation-circle"></i> {{ __('Unverified') }}
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                        <i class="bi bi-patch-check-fill"></i> {{ __('Verified') }}
                    </span>
                @endif
                <span class="text-xs text-gray-400">
                    {{ __('Updated') }} {{ Auth::user()->updated_at?->format('M d, Y h:i A') ?? '—' }}
                </span>
            </div>
        </div>

        {{-- ── Profile Information ── --}}
        <div class="profile-section-card">
            <div class="profile-section-header">
                <div class="profile-section-icon" style="background: #ecfdf5; color: #059669;">
                    <i class="bi bi-person-gear"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Profile Information') }}</h3>
                    <p class="text-xs text-gray-500">{{ __("Update your account's display name and email address.") }}</p>
                </div>
            </div>
            @include('profile.partials.update-profile-information-form')
        </div>

        {{-- ── Update Password ── --}}
        <div class="profile-section-card">
            <div class="profile-section-header">
                <div class="profile-section-icon" style="background: #eff6ff; color: #2563eb;">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-gray-900">{{ __('Update Password') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('Use a strong, unique password to keep your account secure.') }}</p>
                </div>
            </div>
            @include('profile.partials.update-password-form')
        </div>

        {{-- ── Delete Account ── --}}
        <div class="profile-section-card profile-danger-card">
            <div class="profile-section-header">
                <div class="profile-section-icon" style="background: #fef2f2; color: #dc2626;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div>
                    <h3 class="text-base font-semibold text-red-800">{{ __('Danger Zone') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('Irreversible actions. Proceed with caution.') }}</p>
                </div>
            </div>
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</x-app-layout>
