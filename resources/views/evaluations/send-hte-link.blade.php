<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Performance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Send HTE evaluation link') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Pick a deployed student. Supervisor contact fields fill from the company record when available.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-12">
        <x-page-card compact class="lg:col-span-7 border border-gray-200 shadow-sm">
            <div class="mb-4 rounded-lg border border-emerald-100 bg-emerald-50/60 px-4 py-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ __('HTE Link Generator') }}</p>
                <p class="mt-1 text-xs text-emerald-800/90">{{ __('Generate a secure evaluator link and prefill supervisor details from deployment records.') }}</p>
            </div>

            <form method="POST" action="{{ route('evaluations.hte-links.store') }}" class="space-y-4" id="hte-send-form">
                @csrf

                <div class="rounded-lg border border-gray-100 bg-white p-4">
                    <x-input-label for="student_filter" :value="__('Find student quickly')" />
                    <x-text-input id="student_filter" type="text" class="mt-1 block w-full" :value="''" placeholder="{{ __('Type student number or name...') }}" />

                    <x-input-label for="student_id" :value="__('Student (active deployment)')" />
                    <select
                        id="student_id"
                        name="student_id"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 text-sm"
                        onchange="window.hteFillSupervisorFromCompany(this)"
                    >
                        <option value="">{{ __('Select a deployed student') }}</option>
                        @foreach ($students as $student)
                            @php
                                $active = $student->deployments->firstWhere('status', 'active');
                                $company = $active?->company;
                                $supervisorEmail = $company?->contact_email ? trim((string) $company->contact_email) : '';
                                $supervisorName = $company ? (trim((string) ($company->contact_person_name ?? $company->contact_person ?? ''))) : '';
                            @endphp
                            @if ($active)
                                <option
                                    value="{{ $student->id }}"
                                    @selected((string) old('student_id') === (string) $student->id)
                                    data-supervisor-email="{{ e($supervisorEmail) }}"
                                    data-supervisor-name="{{ e($supervisorName) }}"
                                >
                                    {{ $student->student_number }} — {{ $student->name }}
                                    @if ($company?->name)
                                        ({{ $company->name }})
                                    @endif
                                </option>
                            @endif
                        @endforeach
                    </select>
                    @if ($students->isEmpty())
                        <p class="mt-2 text-sm text-amber-800">{{ __('No students with an active deployment. Record a deployment first.') }}</p>
                    @endif
                    @error('student_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 rounded-lg border border-gray-100 bg-white p-4 md:grid-cols-2">
                    <div>
                        <x-input-label for="supervisor_name" :value="__('Supervisor name (optional)')" />
                        <x-text-input id="supervisor_name" name="supervisor_name" type="text" class="mt-1 block w-full" maxlength="255" :value="old('supervisor_name')" />
                        @error('supervisor_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-input-label for="supervisor_email" :value="__('Supervisor email')" />
                        <x-text-input id="supervisor_email" name="supervisor_email" type="email" class="mt-1 block w-full" maxlength="255" :value="old('supervisor_email')" required />
                        @error('supervisor_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="md:col-span-2">
                        <x-input-label for="expires_in_days" :value="__('Link validity (days)')" />
                        <x-text-input id="expires_in_days" name="expires_in_days" type="number" class="mt-1 block w-full" inputmode="numeric" min="1" max="30" :value="old('expires_in_days', 7)" />
                        <p class="mt-1 text-xs text-gray-500">{{ __('Allowed range: 1 to 30 days.') }}</p>
                        @error('expires_in_days')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-2">
                    <a href="{{ route('evaluations.index') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        {{ __('Cancel') }}
                    </a>
                    <button type="submit" class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                        {{ __('Send link') }}
                    </button>
                </div>
            </form>
        </x-page-card>

        <x-page-card compact class="lg:col-span-5 border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between gap-2">
                <div>
                    <p class="text-sm font-semibold text-gray-900">{{ __('Recent links') }}</p>
                    <p class="mt-0.5 text-xs text-gray-500">{{ __('Last 25 generated from this screen.') }}</p>
                </div>
                <span class="inline-flex rounded-full border border-gray-200 bg-gray-50 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wide text-gray-600">
                    {{ __('Live list') }}
                </span>
            </div>
            <div class="mt-3 max-h-[28rem] space-y-2 overflow-y-auto">
                @forelse ($recentLinks as $link)
                    @php
                        $state = $link->used_at
                            ? ['label' => __('Used'), 'cls' => 'bg-slate-100 text-slate-800 border-slate-200']
                            : (($link->expires_at && $link->expires_at->isPast())
                                ? ['label' => __('Expired'), 'cls' => 'bg-rose-50 text-rose-800 border-rose-100']
                                : ['label' => __('Active'), 'cls' => 'bg-emerald-50 text-emerald-800 border-emerald-100']);
                    @endphp
                    <div class="rounded-lg border border-gray-200 bg-gradient-to-br from-white to-gray-50 px-3 py-2.5 shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0">
                                <p class="text-xs font-semibold text-gray-900 truncate">
                                    {{ $link->student?->student_number }} — {{ $link->student?->name ?? '—' }}
                                </p>
                                <p class="text-[11px] text-gray-500 truncate">{{ $link->company?->name ?? '—' }}</p>
                                <p class="text-[11px] text-gray-600 truncate mt-0.5">{{ $link->supervisor_email }}</p>
                            </div>
                            <span class="shrink-0 inline-flex rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide {{ $state['cls'] }}">
                                {{ $state['label'] }}
                            </span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-x-3 gap-y-0.5 text-[11px] text-gray-500">
                            <span>{{ __('Sent') }}: {{ $link->created_at?->format('M j, g:i A') }}</span>
                            @if ($link->sender)
                                <span>{{ __('By') }}: {{ $link->sender->name }}</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-gray-500">{{ __('No links generated yet.') }}</p>
                @endforelse
            </div>
        </x-page-card>
    </div>

    <div class="mt-4">
        <a href="{{ route('evaluations.index') }}" class="inline-flex items-center rounded-md border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 transition-colors">
            <i class="bi bi-arrow-left me-1.5"></i>{{ __('Back to evaluations') }}
        </a>
    </div>

    @push('scripts')
        <script>
            window.hteFillSupervisorFromCompany = function (selectEl) {
                var opt = selectEl.selectedOptions[0];
                var email = document.getElementById('supervisor_email');
                var name = document.getElementById('supervisor_name');
                if (!opt || !email || !name) return;
                if (!opt.value) return;
                var em = opt.getAttribute('data-supervisor-email') || '';
                var nm = opt.getAttribute('data-supervisor-name') || '';
                if (em) email.value = em;
                if (nm) name.value = nm;
            };
            document.addEventListener('DOMContentLoaded', function () {
                var sel = document.getElementById('student_id');
                var filter = document.getElementById('student_filter');
                if (filter && sel) {
                    var originalOptions = Array.prototype.slice.call(sel.options).map(function (o) {
                        return {
                            value: o.value,
                            html: o.innerHTML,
                            text: (o.textContent || '').toLowerCase()
                        };
                    });
                    var keepFirst = originalOptions[0];
                    var rebuild = function (keyword) {
                        var current = sel.value;
                        var q = (keyword || '').toLowerCase().trim();
                        sel.innerHTML = '';
                        [keepFirst].concat(originalOptions.slice(1).filter(function (item) {
                            return q === '' || item.text.indexOf(q) !== -1;
                        })).forEach(function (item) {
                            var opt = document.createElement('option');
                            opt.value = item.value;
                            opt.innerHTML = item.html;
                            if (item.value === current) opt.selected = true;
                            sel.appendChild(opt);
                        });
                    };
                    filter.addEventListener('input', function () {
                        rebuild(filter.value);
                    });
                }
                if (sel && sel.value) {
                    window.hteFillSupervisorFromCompany(sel);
                }
            });
        </script>
    @endpush
</x-app-layout>
