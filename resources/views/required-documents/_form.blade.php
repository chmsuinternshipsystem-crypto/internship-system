@csrf

<div class="space-y-4">
    <div>
        <label for="name" class="block text-sm font-medium text-gray-700">
            {{ __('Document Name') }}
        </label>
        <input
            id="name"
            name="name"
            type="text"
            required
            maxlength="255"
            value="{{ old('name', $requiredDocument->name ?? '') }}"
            class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
            placeholder="Endorsement Letter, MOA, DTR, Final Report, etc."
        />
        @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="description" class="block text-sm font-medium text-gray-700">
            {{ __('Description (optional)') }}
        </label>
        <textarea
            id="description"
            name="description"
            rows="3"
            maxlength="1000"
            class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
        >{{ old('description', $requiredDocument->description ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">{{ __('Maximum 1000 characters.') }}</p>
        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="order_slot" class="block text-sm font-medium text-gray-700">
                {{ __('Display order (list position)') }}
            </label>
            @php
                $orderSlotValue = old('order_slot', ($requiredDocument->exists ?? false) ? (string) ($requiredDocument->order_index ?? '') : '');
            @endphp
            <select
                id="order_slot"
                name="order_slot"
                class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
            >
                @foreach ($orderSlotChoices ?? [] as $value => $label)
                    <option value="{{ $value }}" @selected((string) $orderSlotValue === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500">
                {{ __('Choose a slot (1–:max). «Auto» appends at the end. Order is renumbered after save so numbers stay unique.', ['max' => \App\Support\RequiredDocumentOrdering::MAX_ORDER]) }}
            </p>
            @error('order_slot')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <input type="hidden" name="is_mandatory" value="1">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label for="company_id" class="block text-sm font-medium text-gray-700">
                {{ __('Checklist Scope') }}
            </label>
            <select
                id="company_id"
                name="company_id"
                class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 select2-autocomplete"
                data-placeholder="{{ __('Search company...') }}"
            >
                <option value="">{{ __('All companies (global requirement)') }}</option>
                @foreach ($companies ?? [] as $company)
                    <option value="{{ $company->id }}" @selected((string) old('company_id', $requiredDocument->company_id ?? '') === (string) $company->id)>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            @error('company_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="phase" class="block text-sm font-medium text-gray-700">
                {{ __('Phase') }}
            </label>
            <select id="phase" name="phase"
                    class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="all" @selected(old('phase', $requiredDocument->phase ?? 'all') === 'all')>{{ __('All phases') }}</option>
                <option value="pre" @selected(old('phase', $requiredDocument->phase ?? 'all') === 'pre')>{{ __('Pre-Requirements') }}</option>
                <option value="monitoring" @selected(old('phase', $requiredDocument->phase ?? 'all') === 'monitoring')>{{ __('Monitoring') }}</option>
            </select>
            @error('phase')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>


    </div>

    <div>
        <label for="submission_deadline_at" class="block text-sm font-medium text-gray-700">
            {{ __('Submission deadline (optional)') }}
        </label>
        <input
            id="submission_deadline_at"
            name="submission_deadline_at"
            data-flatpickr="datetime"
            value="{{ old('submission_deadline_at', ($requiredDocument->submission_deadline_at ?? null)?->format('Y-m-d\\TH:i')) }}"
            class="mt-1 block w-full rounded-md border-2 border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
        />
        @error('submission_deadline_at')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-6 flex justify-end space-x-2">
    <a href="{{ route('required-documents.index') }}"
       class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
        {{ __('Cancel') }}
    </a>

    <button type="submit"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
        {{ $submitLabel ?? __('Save') }}
    </button>
</div>

