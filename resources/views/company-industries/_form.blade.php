@csrf

@php
    $inputBase = 'mt-1 block w-full rounded-lg border-2 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm';
    $inputOk = 'border-gray-200 focus:border-emerald-600 focus:ring-emerald-600';
    $inputErr = 'border-red-500 focus:border-red-600 focus:ring-red-600';
@endphp

<div>
    <label for="name" class="block text-sm font-medium text-gray-700">
        {{ __('Industry name') }} <span class="text-red-500">*</span>
    </label>
    <input
        id="name"
        name="name"
        type="text"
        required
        maxlength="100"
        value="{{ old('name', $companyIndustry->name ?? '') }}"
        placeholder="{{ __('e.g. Information Technology') }}"
        @class([$inputBase, $inputOk => ! $errors->has('name'), $inputErr => $errors->has('name')])
    />
    <p class="mt-1 text-xs text-gray-500">{{ __('The unique name of the industry. This will create a URL-friendly slug automatically.') }}</p>
    @error('name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div x-data="{ color: '{{ old('color', $companyIndustry->color ?? '') }}' }">
    <label class="block text-sm font-medium text-gray-700 mb-2">
        {{ __('Badge color') }}
    </label>
    <input type="hidden" name="color" x-model="color">
    <div class="flex flex-wrap gap-2">
        @php $swatches = ['#059669', '#2563eb', '#7c3aed', '#db2777', '#dc2626', '#ea580c', '#d97706', '#65a30d', '#0891b2', '#6b7280']; @endphp
        @foreach ($swatches as $swatch)
            <button type="button" @click="color = '{{ $swatch }}'"
                    class="h-8 w-8 rounded-full border-2 transition-all hover:scale-110"
                    :class="color === '{{ $swatch }}' ? 'border-gray-900 ring-2 ring-offset-1 ring-gray-900' : 'border-gray-200'"
                    style="background-color: {{ $swatch }}"
                    :title="'{{ $swatch }}'"></button>
        @endforeach
        <button type="button" @click="color = ''"
                class="h-8 w-8 rounded-full border-2 border-gray-200 flex items-center justify-center text-xs text-gray-400 hover:border-gray-400 transition-colors"
                title="{{ __('No color') }}">
            <i class="bi bi-x"></i>
        </button>
    </div>
    <p class="mt-1.5 text-xs text-gray-500">{{ __('Pick a color or enter a custom hex value:') }}</p>
    <input type="text" x-model="color" maxlength="20" placeholder="#0891b2"
           class="mt-1 block w-full max-w-[200px] rounded-lg border-2 border-gray-200 bg-white px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
    @error('color')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="flex items-start gap-3 rounded-lg border border-amber-100/80 bg-amber-50/40 px-3 py-2.5">
    <input
        id="is_active"
        name="is_active"
        type="checkbox"
        value="1"
        @checked(old('is_active', $companyIndustry->is_active ?? true))
        class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600 border-gray-300 rounded focus:ring-emerald-600"
    />
    <div class="min-w-0">
        <label for="is_active" class="block text-sm font-medium text-gray-800 cursor-pointer">
            {{ __('Active') }}
        </label>
        <p class="mt-0.5 text-xs text-gray-500 leading-snug">{{ __('Inactive industries are hidden from company forms.') }}</p>
    </div>
</div>

<div class="border-t border-gray-200 pt-4 flex flex-col-reverse gap-2.5 sm:flex-row sm:justify-end sm:items-center sm:gap-2">
    <a href="{{ route('company-industries.index') }}"
       class="inline-flex justify-center items-center px-4 py-2 bg-gray-100 border border-gray-200 rounded-lg font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400 transition-colors">
        {{ __('Cancel') }}
    </a>

    <button type="submit"
            class="inline-flex justify-center items-center px-5 py-2 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 btn-primary shadow-sm transition-shadow hover:shadow">
        {{ $submitLabel ?? __('Save') }}
    </button>
</div>
