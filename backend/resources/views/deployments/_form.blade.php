@csrf

<div class="space-y-4"
     x-data="deploymentStatusPreview()"
     x-init="compute()">
    {{-- Top row: Student | Company | Status --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Student card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                <i class="bi bi-person text-emerald-600"></i>
                {{ __('Student') }}
            </div>
            @if ($deployment->student)
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $deployment->student->name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $deployment->student->student_number }}
                    <span class="mx-1">&middot;</span>
                    {{ __('Section') }} {{ $deployment->student->section }}
                </p>
                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                    @php $ojtType = $deployment->student->ojt_type; @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-medium
                        {{ $ojtType === 'external' ? 'bg-emerald-100 text-emerald-800' : ($ojtType === 'internal' ? 'bg-indigo-100 text-indigo-800' : 'bg-gray-100 text-gray-600') }}">
                        {{ $ojtType === 'external' ? __('External OJT') : ($ojtType === 'internal' ? __('Internal OJT') : __('No Placement')) }}
                    </span>
                    @if ($deployment->student->assignedInstructor)
                        <span class="text-gray-500">
                            <i class="bi bi-person-badge"></i>
                            {{ $deployment->student->assignedInstructor->name }}
                        </span>
                    @endif
                </div>
            @else
                <p class="text-sm text-gray-400">{{ __('No student assigned.') }}</p>
            @endif
        </div>

        {{-- Company card --}}
        <div class="rounded-lg border border-gray-200 bg-white p-4">
            <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                <i class="bi bi-building text-blue-600"></i>
                {{ __('Company') }}
            </div>
            @if ($deployment->company)
                <p class="text-sm font-semibold text-gray-900 truncate">{{ $deployment->company->name }}</p>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ $deployment->company->industry?->name ?? __('No industry') }}
                </p>
            @else
                <p class="text-sm text-gray-500">
                    <i class="bi bi-dash-circle mr-1"></i>
                    {{ __('No company assigned') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ __('Assign one from the company edit page.') }}</p>
            @endif
        </div>

        {{-- Status preview --}}
        <div class="rounded-lg border px-4 py-2.5 text-sm" :class="statusClass">
            <div class="flex items-center gap-1.5 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">
                <i class="bi bi-activity text-amber-600"></i>
                {{ __('Status') }}
            </div>
            <p class="font-semibold flex items-center gap-1">
                <i class="bi" :class="statusIcon"></i>
                <span x-text="statusLabel"></span>
            </p>
            <p class="mt-0.5 text-xs opacity-75" x-text="statusHint"></p>
        </div>
    </div>

    {{-- Date row --}}
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

    {{-- Remarks --}}
    <div>
        <label for="remarks" class="block text-sm font-medium text-gray-700">
            {{ __('Remarks') }}
        </label>
        <textarea
            id="remarks"
            name="remarks"
            rows="2"
            maxlength="100"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm"
        >{{ old('remarks', $deployment->remarks ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">{{ __('Maximum 100 characters.') }}</p>
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

@push('scripts')
<script>
    function deploymentStatusPreview() {
        return {
            startDate: '{{ old('start_date', optional($deployment->start_date)->format('Y-m-d')) }}',
            endDate: '{{ old('end_date', optional($deployment->end_date)->format('Y-m-d')) }}',
            statusLabel: 'Pending',
            statusClass: 'border-blue-200 bg-blue-50 text-blue-950',
            statusIcon: 'bi-info-circle',
            statusHint: '{{ __('Set a start date to compute status.') }}',
            updateStart(val) { this.startDate = val; this.compute(); },
            updateEnd(val) { this.endDate = val; this.compute(); },
            compute() {
                const today = new Date().toISOString().split('T')[0];
                if (this.endDate && this.endDate < today) {
                    this.statusLabel = '{{ __('Completed') }}';
                    this.statusClass = 'border-emerald-200 bg-emerald-50 text-emerald-950';
                    this.statusIcon = 'bi-check-circle-fill';
                    this.statusHint = '{{ __('End date has passed.') }}';
                } else if (this.startDate && this.startDate <= today) {
                    this.statusLabel = '{{ __('Active') }}';
                    this.statusClass = 'border-emerald-200 bg-emerald-50 text-emerald-950';
                    this.statusIcon = 'bi-play-circle-fill';
                    this.statusHint = '{{ __('Start date has passed.') }}';
                } else if (this.startDate && this.startDate > today) {
                    this.statusLabel = '{{ __('Pending') }}';
                    this.statusClass = 'border-amber-200 bg-amber-50 text-amber-950';
                    this.statusIcon = 'bi-clock';
                    this.statusHint = '{{ __('Start date is in the future.') }}';
                } else {
                    this.statusLabel = '{{ __('Pending') }}';
                    this.statusClass = 'border-blue-200 bg-blue-50 text-blue-950';
                    this.statusIcon = 'bi-info-circle';
                    this.statusHint = '{{ __('Set a start date.') }}';
                }
            },
        }
    }
</script>
@endpush
