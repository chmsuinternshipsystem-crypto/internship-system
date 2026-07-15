<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Deployments'), 'url' => route('deployments.index')],
    ['label' => __('Add Deployment')],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Deployment Management') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Deployment') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Assign a student to a partner company for OJT.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form action="{{ route('deployments.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                                        <i class="bi bi-person text-sm"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Student') }}</h3>
                                        <p class="text-xs text-gray-500">{{ __('Only unassigned or unplaced students are shown') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-5 py-4">
                                <select id="student_id" name="student_id" required
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                                    <option value="">{{ __('Select a student...') }}</option>
                                    @foreach ($students as $student)
                                        <option value="{{ $student->id }}" @selected((int) old('student_id', 0) === $student->id)>
                                            {{ $student->student_number }} — {{ $student->last_name }}, {{ $student->first_name }} ({{ __('Section') }} {{ $student->section }})
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('student_id')" class="mt-1" />
                            </div>
                        </div>

                        <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                            <div class="bg-gray-50 px-5 py-3 border-b border-gray-200">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-blue-100 text-blue-700">
                                        <i class="bi bi-building text-sm"></i>
                                    </span>
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-900">{{ __('Company (optional)') }}</h3>
                                        <p class="text-xs text-gray-500">{{ __('Choose a partner company or assign later') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-5 py-4">
                                <select id="company_id" name="company_id"
                                        class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm">
                                    <option value="">{{ __('No company (assign later)') }}</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}" @selected((int) old('company_id', 0) === $company->id)>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('company_id')" class="mt-1" />
                                <p class="mt-1 text-xs text-gray-500">
                                    {{ __('Deployment will be created as pending. Add a company now or assign one from the company\'s edit page later.') }}
                                </p>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3">
                            <a href="{{ route('deployments.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest btn-primary">
                                {{ __('Create Deployment') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
