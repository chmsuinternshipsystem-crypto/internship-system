<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Deployments'), 'url' => route('deployments.index')],
    ['label' => $deployment->student?->student_number ?? __('Deployment')],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Deployment Management') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Deployment Details') }}</h2>
                <p class="text-sm text-gray-500">{{ __('View student-company assignment and schedule information.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold">
                            {{ $deployment->student?->student_number }} - {{ $deployment->student?->name }}
                        </h3>
                        <p class="text-sm text-gray-600">
                            {{ $deployment->company?->name }}
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Start Date') }}</span>
                            <span class="text-sm text-gray-900">
                                {{ $deployment->start_date->format('M d, Y') }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('End Date') }}</span>
                            <span class="text-sm text-gray-900">
                                {{ $deployment->end_date ? $deployment->end_date->format('M d, Y') : '-' }}
                            </span>
                        </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-500">{{ __('Status') }}</span>
                        @php
                            $cls = match($deployment->status) {
                                'active' => 'badge-active',
                                'completed' => 'badge-completed',
                                'withdrawn' => 'badge-withdrawn',
                                default => 'badge-default',
                            };
                        @endphp
                        <span class="status-badge {{ $cls }}">{{ Str::headline($deployment->status) }}</span>
                    </div>

                    </div>

                    <div>
                        <span class="block text-xs font-semibold text-gray-500">{{ __('Remarks') }}</span>
                        <p class="text-sm text-gray-900">
                            {{ $deployment->remarks ?? '-' }}
                        </p>
                    </div>

                    @php
                        $backListHref = match (request()->query('return')) {
                            'student' => $deployment->student ? route('students.show', $deployment->student) : route('deployments.index'),
                            'company' => $deployment->company ? route('companies.show', $deployment->company) : route('deployments.index'),
                            default => route('deployments.index'),
                        };
                        $backListLabel = match (request()->query('return')) {
                            'student' => __('Back to student'),
                            'company' => __('Back to company'),
                            default => __('Back to deployments'),
                        };
                    @endphp
                    <div class="pt-4 flex justify-end space-x-2">
                        <a href="{{ route('deployments.edit', $deployment) }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
                            {{ __('Edit') }}
                        </a>
                        <a href="{{ $backListHref }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                            {{ $backListLabel }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

