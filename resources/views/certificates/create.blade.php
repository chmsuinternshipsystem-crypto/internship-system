<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Certificates'), 'url' => route('certificates.index')],
    ['label' => __('New Certificate')],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Certificates') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Upload Certificate') }}</h2>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <x-page-card compact>
                <form method="POST" action="{{ route('certificates.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Student') }} <span class="text-red-500">*</span></label>
                        <select name="student_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm select2-autocomplete" data-placeholder="{{ __('Search student...') }}">
                            <option value="">{{ __('Select student...') }}</option>
                            @foreach ($students as $student)
                                <option value="{{ $student->id }}" @selected(old('student_id') == $student->id)>
                                    {{ $student->student_number }} — {{ $student->name }}
                                    @if ($student->deployments->isNotEmpty())
                                        ({{ $student->deployments->first()->company->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('student_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Type') }} <span class="text-red-500">*</span></label>
                        <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                            <option value="completion" @selected(old('type') === 'completion')>{{ __('Completion') }}</option>
                            <option value="merit" @selected(old('type') === 'merit')>{{ __('Merit') }}</option>
                            <option value="attendance" @selected(old('type') === 'attendance')>{{ __('Attendance') }}</option>
                            <option value="special" @selected(old('type') === 'special')>{{ __('Special') }}</option>
                            <option value="other" @selected(old('type') === 'other')>{{ __('Other') }}</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Title') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title') }}" maxlength="255"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm"
                               placeholder="{{ __('e.g. Internship Completion Certificate') }}">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                        <textarea name="description" rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm"
                                  placeholder="{{ __('Optional description...') }}">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Issue Date') }}</label>
                        <input data-flatpickr name="issued_at" value="{{ old('issued_at', now()->format('Y-m-d')) }}"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                        @error('issued_at')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Certificate File') }} <span class="text-red-500">*</span></label>
                        <p class="mt-1 text-xs text-gray-500">{{ __('PDF, JPG, JPEG, or PNG. Max 2MB.') }}</p>
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png"
                               class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                        @error('file')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex justify-end gap-2">
                        <a href="{{ route('certificates.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                            {{ __('Cancel') }}
                        </a>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest btn-primary">
                            {{ __('Upload Certificate') }}
                        </button>
                    </div>
                </form>
            </x-page-card>
        </div>
    </div>
</x-app-layout>