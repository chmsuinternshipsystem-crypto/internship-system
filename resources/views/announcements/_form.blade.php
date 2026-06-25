@csrf

<div class="space-y-4">
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700">
            {{ __('Title') }}
        </label>
        <input
            id="title"
            name="title"
            type="text"
            required
            maxlength="255"
            value="{{ old('title', $announcement->title ?? '') }}"
            class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
        />
        @error('title')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="body" class="block text-sm font-medium text-gray-700">
            {{ __('Body') }}
        </label>
        <textarea
            id="body"
            name="body"
            rows="5"
            required
            maxlength="3000"
            class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
        >{{ old('body', $announcement->body ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">{{ __('Maximum 3000 characters.') }}</p>
        @error('body')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="visible_to_role" class="block text-sm font-medium text-gray-700">
            {{ __('Visible To (role, optional)') }}
        </label>
        <select
            id="visible_to_role"
            name="visible_to_role"
            class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
        >
            @php $currentRole = old('visible_to_role', $announcement->visible_to_role ?? ''); @endphp
            <option value="" @selected($currentRole === '')>{{ __('All roles') }}</option>
            <option value="all" @selected($currentRole === 'all')>{{ __('All (explicit)') }}</option>
            <option value="student" @selected($currentRole === 'student')>{{ __('Student') }}</option>
            <option value="instructor" @selected($currentRole === 'instructor')>{{ __('Instructor') }}</option>
            <option value="chairperson" @selected($currentRole === 'chairperson')>{{ __('Chairperson') }}</option>
            <option value="dean" @selected($currentRole === 'dean')>{{ __('Dean') }}</option>
        </select>
        @error('visible_to_role')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-gray-500">
            {{ __('Choose target role to avoid invalid audience values.') }}
        </p>
    </div>
</div>

<div class="mt-6 flex justify-end space-x-2">
    <a href="{{ route('announcements.index') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
        {{ __('Cancel') }}
    </a>

    <button type="submit"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
        {{ $submitLabel ?? __('Save') }}
    </button>
</div>

