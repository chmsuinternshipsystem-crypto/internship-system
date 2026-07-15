@csrf

<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="student_id" class="block text-sm font-medium text-gray-700">
                {{ __('Student') }} <span class="text-red-500">*</span>
            </label>
            <select
                id="student_id"
                name="student_id"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm select2-autocomplete"
                data-placeholder="{{ __('Search student by number or name...') }}"
            >
                <option value="">{{ __('Select a student...') }}</option>
                @foreach ($students as $student)
                    <option value="{{ $student->id }}" @selected(old('student_id', $deployment->student_id ?? $preselectedStudentId ?? '') == $student->id)>
                        {{ $student->student_number }} - {{ $student->name }}
                    </option>
                @endforeach
            </select>
            @error('student_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="company_id" class="block text-sm font-medium text-gray-700">
                {{ __('Company') }} <span class="text-red-500">*</span>
            </label>
            <select
                id="company_id"
                name="company_id"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm select2-autocomplete"
                data-placeholder="{{ __('Search company...') }}"
            >
                <option value="">{{ __('Select a company...') }}</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->id }}" @selected(old('company_id', $deployment->company_id ?? '') == $company->id)>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            @error('company_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="start_date" class="block text-sm font-medium text-gray-700">
                {{ __('Start Date') }}
            </label>
            <input
                id="start_date"
                name="start_date"
                data-flatpickr
                required
                @change="updateStart($el.value)"
                value="{{ old('start_date', optional($deployment->start_date)->format('Y-m-d')) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm"
            />
            @error('start_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="end_date" class="block text-sm font-medium text-gray-700">
                {{ __('End Date') }}
            </label>
            <input
                id="end_date"
                name="end_date"
                data-flatpickr
                @change="updateEnd($el.value)"
                value="{{ old('end_date', optional($deployment->end_date)->format('Y-m-d')) }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm"
            />
            @error('end_date')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4"
         x-data="deploymentStatusPreview()"
         x-init="compute()">
        <div class="md:col-span-2">
            <div class="rounded-lg border px-4 py-3 text-sm"
                 :class="statusClass">
                <p class="font-medium flex items-center gap-1">
                    <i class="bi" :class="statusIcon"></i>
                    {{ __('Status') }}: <span class="font-bold" x-text="statusLabel"></span>
                    <span class="font-normal text-xs opacity-75">(auto-computed)</span>
                </p>
                <p class="mt-1 text-xs" x-text="statusHint"></p>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function deploymentStatusPreview() {
            return {
                startDate: '{{ old('start_date', optional($deployment->start_date)->format('Y-m-d')) }}',
                endDate: '{{ old('end_date', optional($deployment->end_date)->format('Y-m-d')) }}',
                statusLabel: 'Pending',
                statusClass: 'border-blue-200 bg-blue-50 text-blue-950',
                statusIcon: 'bi-info-circle',
                statusHint: '{{ __('Status is automatically set based on dates and document submission.') }}',
                updateStart(val) { this.startDate = val; this.compute(); },
                updateEnd(val) { this.endDate = val; this.compute(); },
                compute() {
                    const today = new Date().toISOString().split('T')[0];
                    if (this.endDate && this.endDate < today) {
                        this.statusLabel = 'Completed';
                        this.statusClass = 'border-emerald-200 bg-emerald-50 text-emerald-950';
                        this.statusIcon = 'bi-check-circle-fill';
                        this.statusHint = '{{ __('End date has passed. Deployment will be marked completed on save.') }}';
                    } else if (this.startDate && this.startDate <= today) {
                        this.statusLabel = 'Active';
                        this.statusClass = 'border-emerald-200 bg-emerald-50 text-emerald-950';
                        this.statusIcon = 'bi-play-circle-fill';
                        this.statusHint = '{{ __('Start date has passed or is today. Deployment will be set to active once all documents are submitted.') }}';
                    } else if (this.startDate && this.startDate > today) {
                        this.statusLabel = 'Pending';
                        this.statusClass = 'border-amber-200 bg-amber-50 text-amber-950';
                        this.statusIcon = 'bi-clock';
                        this.statusHint = '{{ __('Start date is in the future. Deployment will be pending until start date.') }}';
                    } else {
                        this.statusLabel = 'Pending';
                        this.statusClass = 'border-blue-200 bg-blue-50 text-blue-950';
                        this.statusIcon = 'bi-info-circle';
                        this.statusHint = '{{ __('Set a start date to compute status.') }}';
                    }
                },
            }
        }
    </script>
    @endpush

    <div>
        <label for="remarks" class="block text-sm font-medium text-gray-700">
            {{ __('Remarks') }}
        </label>
        <textarea
            id="remarks"
            name="remarks"
            rows="3"
            maxlength="1000"
            class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
        >{{ old('remarks', $deployment->remarks ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">{{ __('Maximum 1000 characters.') }}</p>
        @error('remarks')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex justify-end space-x-2">
    <a href="{{ route('deployments.index') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
        {{ __('Cancel') }}
    </a>

    <button type="submit"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
        {{ $submitLabel ?? __('Save') }}
    </button>
</div>

