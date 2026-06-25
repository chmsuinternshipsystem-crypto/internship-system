<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Partners'), 'url' => route('companies.index')],
    ['label' => $company->name.' ('.__('Edit').')'],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">Partners</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Company') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Update company information and manage student assignments.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('companies.update', $company) }}" method="POST">
                        @method('PUT')
                        @include('companies._form', ['company' => $company, 'submitLabel' => __('Update')])
                    </form>
                </div>
            </div>

            @if ($canManage)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-sm font-semibold text-gray-800 mb-1">{{ __('Manage Student Assignments') }}</h3>
                    <p class="text-xs text-gray-500 mb-4">{{ __('Assign pending students to this company or remove existing assignments.') }}</p>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        {{-- Left: Currently assigned --}}
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ __('Currently Assigned') }}</h4>
                            <div id="assigned-list-mount" class="max-h-[360px] overflow-y-auto">
                                @include('companies.partials.assigned-list')
                            </div>
                        </div>

                        {{-- Right: Add students (always visible) --}}
                        <div x-data="{ search: '', myStudents: false, loading: false }">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase mb-3">{{ __('Add Students') }}</h4>
                            <p class="text-xs text-gray-600 mb-3">{{ __('Select students to assign. Only students not yet deployed are shown.') }}</p>

                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <div class="relative flex-1 min-w-[180px]">
                                    <input type="text" x-model="search"
                                           @input.debounce.300ms="loading = true;
                                               fetch('{{ route('companies.students.assignable', $company) }}?search=' + encodeURIComponent(search) + '&my_students=' + (myStudents ? '1' : '0'))
                                               .then(r => r.text())
                                               .then(html => { document.getElementById('assignable-list-mount').innerHTML = html; loading = false; })"
                                           placeholder="{{ __('Search students...') }}"
                                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-xs shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <i class="bi bi-search absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                                </div>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-600 shrink-0" x-show="loading" x-cloak>
                                    <span class="inline-block h-3 w-3 animate-spin rounded-full border-2 border-gray-300 border-t-emerald-600"></span>
                                    {{ __('Loading...') }}
                                </label>
                                <label class="inline-flex items-center gap-1.5 cursor-pointer text-xs text-gray-600 shrink-0">
                                    <input type="checkbox" x-model="myStudents"
                                           @change="loading = true;
                                               fetch('{{ route('companies.students.assignable', $company) }}?search=' + encodeURIComponent(search) + '&my_students=' + (myStudents ? '1' : '0'))
                                               .then(r => r.text())
                                               .then(html => { document.getElementById('assignable-list-mount').innerHTML = html; loading = false; })"
                                           class="h-3.5 w-3.5 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                    {{ __('My Students') }}
                                </label>
                            </div>

                            <div id="assignable-list-mount" class="max-h-[360px] overflow-y-auto border border-gray-200 rounded-lg bg-gray-50 p-3">
                                @include('companies.partials.assignable-list')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
