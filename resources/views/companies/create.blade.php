<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">Partners</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Company') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Register a new internship partner company.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('companies.store') }}" method="POST">
                        @include('companies._form', ['company' => new \App\Models\Company(), 'submitLabel' => __('Create')])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

