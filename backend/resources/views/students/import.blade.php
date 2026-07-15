<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">
                    {{ __('Registry') }}
                </p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Import Students from Excel') }}
                </h2>
                <p class="text-sm text-gray-500">
                    {{ __('Upload an Excel file (.xlsx, .xls, .csv) with officially enrolled OJT students from the Registrar.') }}
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="importForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                        <p class="m-0 font-medium">{{ __('Required Columns') }}</p>
                        <ul class="mt-2 m-0 list-disc list-inside space-y-1 text-xs leading-relaxed">
                            <li>{{ __('Student Number (8 digits)') }}</li>
                            <li>{{ __('Last Name') }}</li>
                            <li>{{ __('First Name') }}</li>
                            <li>{{ __('Section (A, B, C, D)') }}</li>
                            <li>{{ __('Contact Number (Philippine mobile, e.g., 09123456789)') }}</li>
                        </ul>
                        <p class="mt-2 m-0 font-medium">{{ __('Optional Columns') }}</p>
                        <ul class="mt-1 m-0 list-disc list-inside space-y-1 text-xs leading-relaxed">
                            <li>{{ __('Middle Name') }}</li>
                            <li>{{ __('Name Extension (e.g., Jr., Sr., II)') }}</li>
                            <li>{{ __('Email') }}</li>
                        </ul>
                        <p class="mt-2 m-0 text-xs text-amber-800">
                            {{ __('Program and Year Level are fixed to BSIS / 4th Year. Duplicate student numbers will update existing records. All students get a pending deployment with no company assignment — assign companies later from the company page.') }}
                        </p>
                    </div>

                    <form method="POST" action="{{ route('students.import.store') }}" enctype="multipart/form-data" class="space-y-4" @submit.prevent="submit">
                        @csrf

                        <div class="space-y-1.5">
                            <x-input-label for="file" :value="__('Excel File')" />
                            <input
                                id="file"
                                type="file"
                                name="file"
                                required
                                accept=".xlsx,.xls,.csv"
                                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 shadow-sm focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500"
                            />
                            <x-input-error :messages="$errors->get('file')" class="mt-1" />
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('Maximum file size: 10MB. Supported formats: .xlsx, .xls, .csv') }}
                            </p>
                        </div>

                        <div class="flex justify-end space-x-2">
                            <a href="{{ route('students.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                                {{ __('Cancel') }}
                            </a>

                            <button type="submit"
                                    :disabled="uploading"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="bi bi-upload me-1"></i>
                                <span>{{ __('Import Students') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Loading Overlay --}}
        <div x-show="uploading"
             x-cloak
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60">
            <div class="bg-white rounded-xl p-8 max-w-sm w-full mx-4 shadow-2xl text-center">
                <div class="mb-5">
                    <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-emerald-500 rounded-full indeterminate-bar"></div>
                    </div>
                </div>
                <p class="text-sm font-semibold text-gray-700">{{ __('Importing file...') }}</p>
                <p class="text-xs text-gray-400 mt-1.5">{{ __('Please wait while the file is being processed.') }}</p>
            </div>
        </div>

        {{-- Result Modal --}}
        <div x-show="result"
             x-cloak
             x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/60"
             @keydown.escape.window="result = null">
            <div class="absolute inset-0" @click="result = null"></div>
            <div class="relative bg-white rounded-xl p-6 max-w-md w-full mx-4 shadow-2xl">
                <button type="button" @click="result = null"
                        class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
                <div class="text-center">
                    <template x-if="result?.statusType === 'warning'">
                        <div class="text-amber-500 text-5xl mb-3"><i class="bi bi-exclamation-triangle-fill"></i></div>
                    </template>
                    <template x-if="result?.statusType !== 'warning'">
                        <div class="text-emerald-500 text-5xl mb-3"><i class="bi bi-check-circle-fill"></i></div>
                    </template>
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Import Complete') }}</h3>
                    <p class="mt-2 text-sm text-gray-600" x-text="result?.message"></p>
                </div>
                <div class="mt-6 flex justify-center">
                    <a :href="result?.redirect || '{{ route('students.index') }}'"
                       class="inline-flex items-center px-6 py-2.5 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest btn-primary shadow-sm transition-colors">
                        {{ __('Go to List') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes indeterminate {
            0% { transform: translateX(-100%); }
            50% { transform: translateX(200%); }
            100% { transform: translateX(500%); }
        }
        .indeterminate-bar {
            animation: indeterminate 1.8s ease-in-out infinite;
            width: 25%;
        }
    </style>

    <script>
        function importForm() {
            return {
                uploading: false,
                result: null,
                async submit(e) {
                    this.uploading = true;
                    this.result = null;
                    const form = e.target;
                    const formData = new FormData(form);
                    try {
                        const res = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        });
                        const data = await res.json();
                        this.result = { message: data.message, statusType: data.status_type, redirect: data.redirect };
                    } catch {
                        this.result = {
                            message: '{{ __('An error occurred during import. Please try again.') }}',
                            statusType: 'error',
                            redirect: '{{ route('students.index') }}',
                        };
                    } finally {
                        this.uploading = false;
                    }
                },
            }
        }
    </script>
</x-app-layout>
