<x-entry-layout>
    @php
        $otpEmail = trim((string) session('student_otp_email', ''));
    @endphp
    <div class="space-y-4">
        <x-alert-message />

        <div class="text-left">
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">{{ __('Student OTP Verification') }}</h2>
            <p class="mt-1 text-sm text-gray-500">
                {{ __('We sent a 6-digit code to :email. Enter it below to open the student portal.', ['email' => $otpEmail]) }}
            </p>
        </div>

        <form method="POST" action="{{ route('student.otp.verify') }}" class="space-y-4">
            @csrf
            <div class="space-y-1.5">
                <x-input-label for="otp" :value="__('OTP code')" />
                <input
                    id="otp"
                    type="text"
                    name="otp"
                    value="{{ old('otp') }}"
                    required
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    maxlength="6"
                    autocomplete="one-time-code"
                    class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                    placeholder="123456"
                />
                <x-input-error :messages="$errors->get('otp')" class="mt-1" />
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="trust_this_browser" id="trust_this_browser" value="1"
                       class="h-4 w-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-600">
                <label for="trust_this_browser" class="text-sm text-gray-600">{{ __('Remember me for 30 days') }}</label>
            </div>

            <button
                type="submit"
                class="inline-flex w-full justify-center items-center rounded-lg bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
            >
                {{ __('Verify OTP') }}
            </button>
        </form>

        <div class="text-center">
            <form method="POST" action="{{ route('student.otp.resend') }}" class="inline"
                  x-data="{ cooldown: 0, timer: null }"
                  x-init="cooldown = parseInt(sessionStorage.getItem('otp_cooldown') || '0');
                          if (cooldown > 0) {
                              timer = setInterval(() => {
                                  cooldown--;
                                  if (cooldown <= 0) { clearInterval(timer); sessionStorage.removeItem('otp_cooldown'); }
                                  else { sessionStorage.setItem('otp_cooldown', cooldown); }
                              }, 1000);
                          }">
                @csrf
                <button type="submit"
                        x-on:click="if (cooldown > 0) { $event.preventDefault(); return; }
                                   cooldown = 60;
                                   sessionStorage.setItem('otp_cooldown', 60);
                                   if (timer) clearInterval(timer);
                                   timer = setInterval(() => {
                                       cooldown--;
                                       if (cooldown <= 0) { clearInterval(timer); sessionStorage.removeItem('otp_cooldown'); }
                                       else { sessionStorage.setItem('otp_cooldown', cooldown); }
                                   }, 1000);"
                        class="text-sm font-medium text-emerald-600 hover:text-emerald-700 underline underline-offset-2"
                        :class="{ 'opacity-50 cursor-not-allowed': cooldown > 0 }"
                        :disabled="cooldown > 0">
                    <span x-show="cooldown === 0">{{ __('Resend code') }}</span>
                    <span x-show="cooldown > 0">{{ __('Resend in') }} <span x-text="cooldown"></span>s</span>
                </button>
            </form>
        </div>
    </div>
</x-entry-layout>
