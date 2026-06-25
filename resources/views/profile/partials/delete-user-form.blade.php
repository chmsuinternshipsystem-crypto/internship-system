<section>
    <p class="mt-2 text-sm text-gray-600">
        {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. This action cannot be undone.') }}
    </p>

    <div class="mt-4">
        <button
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-600 text-white text-xs font-semibold uppercase tracking-widest rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >
            <i class="bi bi-trash3"></i>{{ __('Delete Account Permanently') }}
        </button>
    </div>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="bi bi-exclamation-triangle-fill text-red-600 text-lg"></i>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ __('Delete your account?') }}
                    </h2>
                    <p class="text-sm text-gray-500">
                        {{ __('This will permanently remove all your data.') }}
                    </p>
                </div>
            </div>

            <p class="text-sm text-gray-600 mb-4">
                {{ __('Enter your password to confirm this action.') }}
            </p>

            <div class="form-group-float password-wrap">
                <input
                    id="delete_password"
                    name="password"
                    type="password"
                    class="float-input @error('password', 'userDeletion') is-invalid @enderror"
                    placeholder=" "
                />
                <label for="delete_password" class="float-label">
                    {{ __('Your Password') }}
                </label>
                <button type="button" class="toggle-password" onclick="togglePass('delete_password', this)">
                    <i class="bi bi-eye"></i>
                </button>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        x-on:click="$dispatch('close')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition">
                    {{ __('Cancel') }}
                </button>

                <button type="submit"
                        class="inline-flex items-center gap-1 px-4 py-2 bg-red-600 text-white rounded-lg font-semibold text-xs uppercase tracking-widest hover:bg-red-700 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition">
                    <i class="bi bi-trash3"></i>{{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
