<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Deployments'), 'url' => route('deployments.index')],
    ['label' => __('Edit Deployment')],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Deployment Management') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Edit Deployment') }}</h2>
                <p class="text-sm text-gray-500">{{ __('Update this deployment\'s assignment and schedule.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{ route('deployments.update', $deployment) }}" method="POST">
                        @method('PUT')
                        @include('deployments._form', ['deployment' => $deployment, 'submitLabel' => __('Update'), 'preselectedStudentId' => null])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

