<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Registry') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Students') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Manage BSIS internship student records, sections, and contact details.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <x-page-header
        :actionHref="$canManage ? route('students.create') : null"
        actionLabel="{{ __('Create Student Profile') }}">
        @if($canManage)
            <a href="{{ route('students.import') }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-wider bg-white hover:bg-gray-50 shadow-sm transition-colors">
                <i class="bi bi-upload"></i>
                {{ __('Import') }}
            </a>
        @endif
    </x-page-header>

    <x-page-card compact>
        <x-search-bar
            :action="route('students.index')"
            :value="$search"
            :placeholder="__('Student number, name, section, contact, or status...')"
            hxTarget="#students-ajax-mount"
            :showClear="$hasActiveFilters"
            sticky
        >
            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-0.5 text-xs font-medium">
                <a href="{{ route('students.index', ['deployment_status' => '', 'section' => $section ?? '', 'search' => $search ?? '', 'my_students' => $myStudents ?? '']) }}"
                   class="px-3 py-1.5 rounded-md transition-colors {{ ($deploymentStatus ?? '') === '' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ __('All') }}
                </a>
                <a href="{{ route('students.index', ['deployment_status' => 'pending', 'section' => $section ?? '', 'search' => $search ?? '', 'my_students' => $myStudents ?? '']) }}"
                   class="px-3 py-1.5 rounded-md transition-colors {{ ($deploymentStatus ?? '') === 'pending' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ __('Pending') }}
                </a>
                <a href="{{ route('students.index', ['deployment_status' => 'deployed', 'section' => $section ?? '', 'search' => $search ?? '', 'my_students' => $myStudents ?? '']) }}"
                   class="px-3 py-1.5 rounded-md transition-colors {{ ($deploymentStatus ?? '') === 'deployed' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                    {{ __('Deployed') }}
                </a>
            </div>
            <span class="filter-label">{{ __('Section') }}</span>
            <select name="section" class="filter-select">
                <option value="">{{ __('All sections') }}</option>
                @foreach (['A', 'B', 'C', 'D'] as $sec)
                    <option value="{{ $sec }}" @selected(($section ?? '') === $sec)>{{ __('Section') }} {{ $sec }}</option>
                @endforeach
            </select>
            @if ((auth()->user()?->role ?? '') === 'instructor')
            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-600 ml-2">
                <input type="checkbox" name="my_students" value="1" @checked($myStudents ?? false) class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600"
                       onchange="this.closest('form').requestSubmit()">
                {{ __('My students') }}
            </label>
            @endif
            <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-600 ml-2">
                <input type="checkbox" name="no_company" value="1" @checked($noCompany ?? false) class="h-4 w-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600"
                       onchange="this.closest('form').requestSubmit()">
                {{ __('No Company') }}
            </label>
        </x-search-bar>
        <div id="students-ajax-mount">
            @include('students.partials.ajax-list')
        </div>
    </x-page-card>
</x-app-layout>
