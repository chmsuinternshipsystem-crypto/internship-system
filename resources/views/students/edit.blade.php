<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Students'), 'url' => route('students.index')],
    ['label' => $student->student_number.' ('.__('Edit').')'],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">Student Registry</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Student Profile') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Update this student\'s personal and academic information.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('students.update', $student) }}" method="POST"
                          x-data="duplicateNameGuard({{ $student->id }})"
                          @submit.prevent="checkBeforeSubmit">
                        @method('PUT')
                        @include('students._form', ['student' => $student, 'submitLabel' => __('Update')])
                    </form>

                    {{-- Duplicate Name Warning Modal --}}
                    <div x-show="showDuplicateModal"
                         x-cloak
                         x-transition.opacity.duration.200ms
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
                         @keydown.escape.window="showDuplicateModal = false">
                        <div class="bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl"
                             @click.outside="showDuplicateModal = false">
                            <button type="button" @click="showDuplicateModal = false"
                                    class="float-right text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="bi bi-x-lg"></i>
                            </button>
                            <div class="text-center">
                                <div class="text-amber-500 text-5xl mb-3"><i class="bi bi-exclamation-triangle-fill"></i></div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ __('Duplicate Name Detected') }}</h3>
                                <p class="mt-2 text-sm text-gray-600">
                                    {{ __('A student named') }} <strong x-text="duplicateDisplayName"></strong> {{ __('already exists in the system.') }}
                                </p>
                                <p class="mt-1 text-sm text-gray-500">{{ __('You can still proceed if this is a different student with the same name.') }}</p>
                            </div>
                            <div class="mt-6 flex justify-center gap-3">
                                <button type="button"
                                        @click="showDuplicateModal = false"
                                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 transition-colors">
                                    {{ __('Cancel') }}
                                </button>
                                <button type="button"
                                        @click="proceedSubmit"
                                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
                                    {{ __('Continue Anyway') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function duplicateNameGuard(excludeId) {
                return {
                    showDuplicateModal: false,
                    formEl: null,
                    duplicateDisplayName: '',
                    checkBeforeSubmit(e) {
                        this.formEl = e.target;
                        const firstName = this.formEl.querySelector('[name="first_name"]')?.value || '';
                        const lastName = this.formEl.querySelector('[name="last_name"]')?.value || '';
                        const middleName = this.formEl.querySelector('[name="middle_name"]')?.value || '';
                        const nameExtension = this.formEl.querySelector('[name="name_extension"]')?.value || '';
                        if (!firstName || !lastName) {
                            this.formEl.submit();
                            return;
                        }
                        const url = '{{ route('students.check-duplicate-name') }}?first_name=' + encodeURIComponent(firstName) + '&last_name=' + encodeURIComponent(lastName) + '&middle_name=' + encodeURIComponent(middleName) + '&name_extension=' + encodeURIComponent(nameExtension) + '&exclude=' + (excludeId || 0);
                        fetch(url)
                            .then(r => r.json())
                            .then(data => {
                                if (data.duplicate) {
                                    this.duplicateDisplayName = lastName + ', ' + firstName + (middleName ? ' ' + middleName : '') + (nameExtension ? ' ' + nameExtension : '');
                                    this.showDuplicateModal = true;
                                } else {
                                    this.formEl.submit();
                                }
                            })
                            .catch(() => {
                                this.formEl.submit();
                            });
                    },
                    proceedSubmit() {
                        this.showDuplicateModal = false;
                        this.formEl.submit();
                    },
                };
            }
        </script>
    @endpush
</x-app-layout>

