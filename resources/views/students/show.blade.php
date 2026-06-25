@php
    $canManage = in_array(auth()->user()->role ?? null, \App\Support\InternshipRoles::operationalManagerRoles(), true);
    $tabs = [
        'profile' => ['label' => __('Profile'), 'icon' => 'bi-person'],
        'documents' => ['label' => __('Documents'), 'icon' => 'bi-file-earmark-text'],
        'journals' => ['label' => __('Journals'), 'icon' => 'bi-journal'],
        'dtr' => ['label' => __('DTR'), 'icon' => 'bi-clock'],
        'attendance' => ['label' => __('Attendance'), 'icon' => 'bi-geo-alt'],
        'certificates' => ['label' => __('Certificates'), 'icon' => 'bi-award'],
    ];
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
                    ['label' => __('Students'), 'url' => route('students.index')],
                    ['label' => $student->student_number],
                ]" />
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Student registry') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $student->name }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Student Number') }}: <strong>{{ $student->student_number }}</strong> &bull; {{ __('Section') }} {{ $student->section }}
                    @if ($student->assignedInstructor)
                        &bull; {{ __('Instructor') }}: {{ $student->assignedInstructor->name }}
                    @endif
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12"
         x-data="{ tab: window.location.hash.replace('#', '') || 'profile' }"
         x-init="$watch('tab', val => history.replaceState(null, '', '#' + val))"
    >
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Tab navigation --}}
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-4 overflow-x-auto" aria-label="Tabs">
                    @foreach ($tabs as $key => $tab)
                        <button
                            @click="tab = '{{ $key }}'"
                            class="whitespace-nowrap pb-3 px-3 border-b-2 font-medium text-sm transition-colors"
                            :class="tab === '{{ $key }}' ? 'border-emerald-600 text-emerald-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            :aria-selected="tab === '{{ $key }}'"
                            role="tab"
                        >
                            <i class="{{ $tab['icon'] }} me-1.5"></i>
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </nav>
            </div>

            {{-- Tab content — lazy-loaded via HTMX except Profile --}}
            <div>
                <div x-show="tab === 'profile'" x-cloak role="tabpanel">
                    @include('students.partials.tab-profile')
                </div>
                <div x-show="tab === 'documents'" x-cloak role="tabpanel">
                    @include('students.partials.tab-documents')
                </div>
                <div x-show="tab === 'journals'" x-cloak role="tabpanel"
                     x-init="htmx.ajax('GET', '{{ route('students.tab.journals', $student) }}', {target: '#journals-tab-content', swap: 'innerHTML'})">
                    <div id="journals-tab-content" class="py-8 text-center text-gray-400">
                        <i class="bi bi-arrow-repeat text-2xl animate-spin inline-block"></i>
                        <p class="mt-2 text-sm">{{ __('Loading journals...') }}</p>
                    </div>
                </div>
                <div x-show="tab === 'dtr'" x-cloak role="tabpanel"
                     x-init="
                         fetch('{{ route('students.tab.dtr', $student) }}')
                             .then(r => r.text())
                             .then(html => { document.getElementById('dtr-tab-content').innerHTML = html; })
                             .catch(e => { document.getElementById('dtr-tab-content').innerHTML = '<div class=\'py-8 text-center text-gray-500\'>Failed to load DTR.</div>'; });
                     ">
                    <div id="dtr-tab-content" class="py-8 text-center text-gray-400">
                        <i class="bi bi-arrow-repeat text-2xl animate-spin inline-block"></i>
                        <p class="mt-2 text-sm">{{ __('Loading DTR...') }}</p>
                    </div>
                </div>
                <div x-show="tab === 'attendance'" x-cloak role="tabpanel"
                     x-init="
                         fetch('{{ route('students.tab.attendance', $student) }}')
                             .then(r => r.text())
                             .then(html => { document.getElementById('attendance-tab-content').innerHTML = html; })
                             .catch(e => { document.getElementById('attendance-tab-content').innerHTML = '<div class=\'py-8 text-center text-gray-500\'>Failed to load attendance.</div>'; });
                     ">
                    <div id="attendance-tab-content" class="py-8 text-center text-gray-400">
                        <i class="bi bi-arrow-repeat text-2xl animate-spin inline-block"></i>
                        <p class="mt-2 text-sm">{{ __('Loading attendance...') }}</p>
                    </div>
                </div>
                <div x-show="tab === 'certificates'" x-cloak role="tabpanel"
                     x-init="htmx.ajax('GET', '{{ route('students.tab.certificates', $student) }}', {target: '#certificates-tab-content', swap: 'innerHTML'})">
                    <div id="certificates-tab-content" class="py-8 text-center text-gray-400">
                        <i class="bi bi-arrow-repeat text-2xl animate-spin inline-block"></i>
                        <p class="mt-2 text-sm">{{ __('Loading certificates...') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        window.emptyTabHtml = function(icon, text) {
            return '<div class="py-12 text-center"><i class="bi bi-' + icon + ' text-3xl text-gray-300"></i><p class="mt-2 text-sm text-gray-500">' + text + '</p></div>';
        };
    </script>
    @endpush
</x-app-layout>
