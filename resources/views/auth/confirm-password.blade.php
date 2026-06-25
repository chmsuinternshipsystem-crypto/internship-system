<x-entry-layout>
    <div x-data="passwordStrength()">
        <div class="mb-5 text-sm text-gray-600">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="password" :value="__('Password')" />
                <div class="relative mt-1">
                    <input id="password"
                           name="password"
                           :type="show ? 'text' : 'password'"
                           x-model="password"
                           @input="checkStrength()"
                           required
                           autocomplete="current-password"
                           class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pl-3 pr-10 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500">
                    <button type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none rounded-r-lg"
                            :aria-label="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'">
                        <i class="bi text-lg" :class="show ? 'bi-eye-slash' : 'bi-eye'"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end mt-4">
                <x-primary-button>
                    {{ __('Confirm') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</x-entry-layout>
