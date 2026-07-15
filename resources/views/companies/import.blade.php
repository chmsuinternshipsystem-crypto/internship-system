<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Partners') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Import Partner Companies') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Batch-upload company records from an Excel or CSV file.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12" x-data="importForm()">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('companies.import.store') }}" enctype="multipart/form-data" @submit.prevent="submit">
                        @csrf

                        <div class="rounded-lg bg-amber-50 border border-amber-200 p-4 mb-6 text-sm text-amber-800 space-y-2">
                            <p class="font-semibold">{{ __('Required columns') }}:</p>
                            <ol class="list-decimal list-inside space-y-1 text-amber-700">
                                <li><code class="bg-amber-100 px-1 rounded">name</code> — {{ __('Company name') }}</li>
                                <li><code class="bg-amber-100 px-1 rounded">street_address</code> — {{ __('Street address') }}</li>
                                <li><code class="bg-amber-100 px-1 rounded">barangay</code></li>
                                <li><code class="bg-amber-100 px-1 rounded">city_municipality</code></li>
                            </ol>
                            <p class="font-semibold mt-3">{{ __('Optional columns') }}:</p>
                            <ol class="list-decimal list-inside space-y-1 text-amber-700">
                                <li><code class="bg-amber-100 px-1 rounded">contact_last_name</code>, <code class="bg-amber-100 px-1 rounded">contact_first_name</code>, <code class="bg-amber-100 px-1 rounded">contact_middle_initial</code>, <code class="bg-amber-100 px-1 rounded">contact_name_extension</code></li>
                                <li><code class="bg-amber-100 px-1 rounded">contact_email</code></li>
                                <li><code class="bg-amber-100 px-1 rounded">contact_phone</code></li>
                                <li><code class="bg-amber-100 px-1 rounded">company_industry</code> — {{ __('Must match an existing industry name') }}</li>
                                <li><code class="bg-amber-100 px-1 rounded">notes</code></li>
                                <li><code class="bg-amber-100 px-1 rounded">geofence_radius_meters</code> — {{ __('Default: 100') }}</li>
                            </ol>
                            <p class="mt-3 text-amber-700"><i class="bi bi-info-circle"></i> {{ __('Addresses are automatically geocoded to GPS coordinates for attendance geofencing. Companies with the same name are updated.') }}</p>
                        </div>

                        <div class="mb-4">
                            <x-input-label for="file" :value="__('Upload file')" />
                            <input type="file" name="file" id="file" required accept=".xlsx,.xls,.csv"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                            <p class="mt-1 text-xs text-gray-500">{{ __('Accepted formats: .xlsx, .xls, .csv (max 10MB)') }}</p>
                            @error('file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <a href="{{ route('companies.index') }}"
                               class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 shadow-sm transition-colors">
                                {{ __('Cancel') }}
                            </a>
                            <button type="submit"
                                    :disabled="uploading"
                                    class="inline-flex items-center px-4 py-2.5 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest btn-primary shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                                <i class="bi bi-upload me-1"></i>
                                <span>{{ __('Import') }}</span>
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
                    <a :href="result?.redirect || '{{ route('companies.index') }}'"
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
                            redirect: '{{ route('companies.index') }}',
                        };
                    } finally {
                        this.uploading = false;
                    }
                },
            }
        }
    </script>
</x-app-layout>
