<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Student Portal') }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My Profile') }}</h2>
            <p class="text-sm text-gray-500">{{ __('Manage your account information.') }}</p>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            {{-- Left: Student Info --}}
            <div class="lg:col-span-2">
                <x-page-card compact class="h-full">
                    <div class="flex items-center gap-4 mb-4">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 text-lg font-bold">
                            {{ strtoupper(substr($student->first_name, 0, 1)) }}{{ strtoupper(substr($student->last_name, 0, 1)) }}
                        </span>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $student->name }}</h3>
                            <p class="text-sm text-gray-500">{{ $student->student_number }} · {{ $student->section }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-xs font-semibold text-gray-500">{{ __('Program') }}</p>
                            <p class="text-gray-900">{{ $student->program }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500">{{ __('Year Level') }}</p>
                            <p class="text-gray-900">{{ $student->year_level }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500">{{ __('Contact Number') }}</p>
                            <p class="text-gray-900">{{ \App\Support\PhoneHelper::formatPhone($student->contact_number) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-gray-500">{{ __('Email') }}</p>
                            <p class="text-gray-900">{{ $studentAccount->email ?? '—' }}</p>
                        </div>
                    </div>
                </x-page-card>
            </div>

            {{-- Right: Edit Form --}}
            <div class="lg:col-span-3">
                <x-page-card compact>
                    <h3 class="text-sm font-semibold text-gray-800 mb-4">{{ __('Update Information') }}</h3>
                    <form method="POST" action="{{ route('student.profile.update') }}" class="space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="email" :value="__('Email Address')" />
                            <input id="email" name="email" type="email" maxlength="255"
                                   value="{{ old('email', $studentAccount->email ?? '') }}"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                   placeholder="your@email.com">
                            <x-input-error :messages="$errors->get('email')" class="mt-1" />
                            <p class="mt-1 text-xs text-gray-400">{{ __('Used for OTP verification and notifications.') }}</p>
                        </div>
                        <div>
                            <x-input-label for="contact_number" :value="__('Contact Number')" />
                            <input id="contact_number" name="contact_number" type="text" inputmode="numeric"                                maxlength="11"
                                   value="{{ old('contact_number', \App\Support\PhoneHelper::formatPhone($student->contact_number)) !== '—' ? old('contact_number', \App\Support\PhoneHelper::formatPhone($student->contact_number)) : '' }}"
                                   class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                                   placeholder="+63 912 345 6789">
                            <x-input-error :messages="$errors->get('contact_number')" class="mt-1" />
                            <p class="mt-1 text-xs text-gray-400">{{ __('Format: 09XXXXXXXXX (11 digits)') }}</p>
                        </div>
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-xs font-semibold text-gray-500 mb-1">{{ __('Change Password (optional)') }}</p>
                            <p class="text-xs text-gray-400 mb-3">{{ __('Leave blank to keep your current password.') }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <x-input-label for="password" :value="__('New Password')" />
                                    <input id="password" name="password" type="password" minlength="8"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                                </div>
                                <div>
                                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
                                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="8"
                                           class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors">
                                <i class="bi bi-check-circle"></i>
                                {{ __('Save Changes') }}
                            </button>
                        </div>
                    </form>
                </x-page-card>
            </div>
        </div>
    </div>
</x-app-layout>
