@php
    $sectionOptions = ['A', 'B', 'C', 'D'];

    $legacyNameOnly = isset($student->id)
        && $student->last_name === null
        && $student->first_name === null
        && filled($student->name);
@endphp

@csrf

<input type="hidden" name="program" value="BSIS" />
<input type="hidden" name="year_level" value="4" />

<div class="space-y-4"
     x-data="{ sections: { personal: true, contact: false, ojt: false, password: false } }">

    @if ($legacyNameOnly)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
            <p class="m-0 font-medium">{{ __('Structured name required') }}</p>
            <p class="mt-1 m-0 text-xs leading-relaxed text-amber-900">
                {{ __('This record only has a legacy full name on file. Please enter the name below as separate fields. Current value: :name', ['name' => $student->name]) }}
            </p>
        </div>
    @endif

    {{-- Section 1: Personal Information --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <button type="button" @click="sections.personal = !sections.personal"
                class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                    <i class="bi bi-person text-sm"></i>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Personal Information') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('Full name, student number') }}</p>
                </div>
            </div>
            <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.personal ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="sections.personal" x-collapse.duration.200ms>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-5 pb-5">

    {{-- Student Number --}}
    <div class="form-group-float md:col-span-2">
        <input id="student_number" name="student_number" type="text" inputmode="numeric" pattern="[0-9]{8}" maxlength="8" required
               value="{{ old('student_number', $student->student_number ?? '') }}"
               class="float-input @error('student_number') is-invalid @enderror" autocomplete="off" placeholder=" " title="{{ __('e.g. 20230001') }}"/>
        <label for="student_number" class="float-label">{{ __('Student Number') }} <span class="text-red-500">*</span></label>
        @error('student_number') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
        <small class="hint-text">{{ __('8-digit student number (e.g., 20230001)') }}</small>
    </div>

    <div class="form-group-float md:col-span-1">
        <input id="first_name" name="first_name" type="text" required maxlength="120"
               value="{{ old('first_name', $student->first_name ?? '') }}"
               class="float-input @error('first_name') is-invalid @enderror" autocomplete="given-name" placeholder=" " title="{{ __('e.g. Juan') }}"/>
        <label for="first_name" class="float-label">{{ __('First name') }} <span class="text-red-500">*</span></label>
        @error('first_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
    </div>

    <div class="form-group-float md:col-span-1">
        <input id="last_name" name="last_name" type="text" required maxlength="120"
               value="{{ old('last_name', $student->last_name ?? '') }}"
               class="float-input @error('last_name') is-invalid @enderror" autocomplete="family-name" placeholder=" " title="{{ __('e.g. Dela Cruz') }}"/>
        <label for="last_name" class="float-label">{{ __('Last name') }} <span class="text-red-500">*</span></label>
        @error('last_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
    </div>

    <div class="form-group-float md:col-span-1">
        <input id="middle_name" name="middle_name" type="text" maxlength="120"
               value="{{ old('middle_name', $student->middle_name ?? '') }}"
               class="float-input @error('middle_name') is-invalid @enderror" autocomplete="additional-name" placeholder=" " title="{{ __('e.g. Santos') }}"/>
        <label for="middle_name" class="float-label">{{ __('Middle name') }}</label>
        @error('middle_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
        <small class="hint-text">{{ __('Optional. Full middle name.') }}</small>
    </div>

    <div class="form-group-float select-wrap md:col-span-1">
        <select id="name_extension" name="name_extension" class="float-select @error('name_extension') is-invalid @enderror">
            <option value="">{{ __('No extension') }}</option>
            @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $option)
                <option value="{{ $option }}" @selected(old('name_extension', $student->name_extension ?? '') === $option)>{{ $option }}</option>
            @endforeach
        </select>
        <label for="name_extension" class="float-label">{{ __('Extension') }}</label>
        @error('name_extension') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
        <small class="hint-text">{{ __('Optional suffix such as Jr. or III.') }}</small>
    </div>

            </div>
        </div>
    </div>

    {{-- Section 2: Contact & Enrollment --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <button type="button" @click="sections.contact = !sections.contact"
                class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                    <i class="bi bi-envelope text-sm"></i>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Contact & Enrollment') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('Email, phone, section, instructor') }}</p>
                </div>
            </div>
            <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.contact ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="sections.contact" x-collapse.duration.200ms>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-5 pb-5">

    <div class="form-group-float md:col-span-1">
        <input id="email" name="email" type="email" maxlength="255"
               value="{{ old('email', $student->account->email ?? '') }}"
               class="float-input @error('email') is-invalid @enderror" autocomplete="email" placeholder=" " title="{{ __('e.g. juan.delacruz@example.com') }}"/>
        <label for="email" class="float-label">{{ __('Email Address') }}</label>
        @error('email') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
        <small class="hint-text">{{ __('Used for OTP login and notifications.') }}</small>
    </div>

    <div class="form-group-float md:col-span-1">
        <input id="contact_number" name="contact_number" type="text" inputmode="numeric"                                maxlength="11" required
               value="{{ old('contact_number', \App\Support\PhoneHelper::formatPhone($student->contact_number ?? '')) !== '—' ? old('contact_number', \App\Support\PhoneHelper::formatPhone($student->contact_number ?? '')) : '' }}"
               class="float-input @error('contact_number') is-invalid @enderror" autocomplete="off" placeholder=" " title="{{ __('e.g. +63 912 345 6789') }}"
               oninput="this.value = this.value.replace(/[^0-9+\s]/g, '')"/>
        <label for="contact_number" class="float-label">{{ __('Contact Number (PH)') }} <span class="text-red-500">*</span></label>
        @error('contact_number') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
        <small class="hint-text">{{ __('Format: 09XXXXXXXXX (11 digits)') }}</small>
    </div>

    <div class="form-group-float select-wrap md:col-span-1">
        <select id="section" name="section" required class="float-select @error('section') is-invalid @enderror">
            <option value="">{{ __('Select section') }}</option>
            @foreach ($sectionOptions as $option)
                <option value="{{ $option }}" @selected(old('section', $student->section ?? '') === $option)>{{ $option }}</option>
            @endforeach
        </select>
        <label for="section" class="float-label">{{ __('Section') }} <span class="text-red-500">*</span></label>
        @error('section') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
        <small class="hint-text">{{ __('Select the official section only.') }}</small>
    </div>

    <div class="md:col-span-1 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 flex flex-col justify-center">
        <p class="text-sm font-medium text-gray-900 m-0">{{ __('BSIS · Year 4') }}</p>
    </div>

    <div class="form-group-float select-wrap md:col-span-2">
        <select id="assigned_instructor_id" name="assigned_instructor_id" class="float-select @error('assigned_instructor_id') is-invalid @enderror">
            <option value="">{{ __('Select instructor') }}</option>
            @foreach ($instructors as $instructor)
                <option value="{{ $instructor->id }}" @selected((int) old('assigned_instructor_id', $student->assigned_instructor_id ?? auth()->id()) === $instructor->id)>{{ $instructor->name }}</option>
            @endforeach
        </select>
        <label for="assigned_instructor_id" class="float-label">{{ __('Assigned Instructor') }}</label>
        @error('assigned_instructor_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
        <small class="hint-text">{{ __('Defaults to the current user (you) if not specified.') }}</small>
    </div>

            </div>
        </div>
    </div>

    {{-- Section 3: OJT Assignment --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <button type="button" @click="sections.ojt = !sections.ojt"
                class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700">
                    <i class="bi bi-briefcase text-sm"></i>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('OJT Assignment') }}</h3>
                    <p class="text-xs text-gray-500">{{ __('Choose placement type for this student') }}</p>
                </div>
            </div>
            <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.ojt ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="sections.ojt" x-collapse.duration.200ms>
            <div class="px-5 pb-5 space-y-3">
                @php $currentOjt = old('ojt_type', $student->ojt_type ?? 'unplaced'); @endphp
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                    {{ $currentOjt === 'unplaced' ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 hover:bg-gray-50' }}">
                    <input type="radio" name="ojt_type" value="unplaced" @checked($currentOjt === 'unplaced')
                           class="mt-0.5 h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ __('No Placement Yet') }}</p>
                        <p class="text-xs text-gray-500">{{ __('Student has not been assigned to any company or school department. Default state.') }}</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                    {{ $currentOjt === 'internal' ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 hover:bg-gray-50' }}">
                    <input type="radio" name="ojt_type" value="internal" @checked($currentOjt === 'internal')
                           class="mt-0.5 h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ __('Internal OJT (School-based)') }}</p>
                        <p class="text-xs text-gray-500">{{ __('Student will be deployed within the school — no external company required. Company field will be locked.') }}</p>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition-colors
                    {{ $currentOjt === 'external' ? 'border-emerald-300 bg-emerald-50' : 'border-gray-200 hover:bg-gray-50' }}">
                    <input type="radio" name="ojt_type" value="external" @checked($currentOjt === 'external')
                           class="mt-0.5 h-4 w-4 text-emerald-600 focus:ring-emerald-500">
                    <div>
                        <p class="text-sm font-semibold text-gray-900">{{ __('External OJT (With Company)') }}</p>
                        <p class="text-xs text-gray-500">{{ __('Student will be assigned to a partner company for their OJT. Company must be set before deployment activates.') }}</p>
                    </div>
                </label>
            </div>
        </div>
    </div>

    {{-- Section 4: Password --}}
    <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <button type="button" @click="sections.password = !sections.password"
                class="w-full flex items-center justify-between px-5 py-3.5 text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                    <i class="bi bi-lock text-sm"></i>
                </span>
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">{{ __('Account Password') }}</h3>
                    <p class="text-xs text-gray-500">{{ isset($student->id) ? __('Leave blank to keep current password') : __('Defaults to student number if not set') }}</p>
                </div>
            </div>
            <i class="bi bi-chevron-down text-gray-400 transition-transform" :class="sections.password ? 'rotate-180' : ''"></i>
        </button>
        <div x-show="sections.password" x-collapse.duration.200ms>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 px-5 pb-5">

    <div class="form-group-float md:col-span-1">
        <input id="account_password" name="account_password" type="password" minlength="8"
               class="float-input @error('account_password') is-invalid @enderror" autocomplete="new-password" placeholder=" " title="{{ __('Minimum 8 characters') }}"/>
        <label for="account_password" class="float-label">{{ isset($student->id) ? __('New account password') : __('Account password') }}</label>
        @error('account_password') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
        @if (! isset($student->id))
            <small class="hint-text">{{ __('If left blank, default password will be the 8-digit student number.') }}</small>
        @endif
    </div>

    <div class="form-group-float md:col-span-1">
        <input id="account_password_confirmation" name="account_password_confirmation" type="password" minlength="8"
               class="float-input" autocomplete="new-password" placeholder=" " title="{{ __('Repeat password') }}"/>
        <label for="account_password_confirmation" class="float-label">{{ isset($student->id) ? __('Confirm New Password') : __('Confirm password') }}</label>
    </div>

            </div>
        </div>
    </div>

</div>

<div class="mt-6 flex justify-end space-x-2">
    <a href="{{ route('students.index') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
        {{ __('Cancel') }}
    </a>

    <button type="submit"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
        {{ $submitLabel ?? __('Save') }}
    </button>
</div>
