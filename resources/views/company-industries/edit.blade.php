<x-app-layout>
    <x-slot name="header">
        <div>
            <x-breadcrumbs :items="[
                ['label' => __('Partners'), 'url' => route('companies.index')],
                ['label' => __('Industries'), 'url' => route('company-industries.index')],
                ['label' => $companyIndustry->name . ' (' . __('Edit') . ')'],
            ]" />
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Partners') }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Industry') }}</h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('company-industries.update', $companyIndustry) }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @include('company-industries._form', ['submitLabel' => __('Update')])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
