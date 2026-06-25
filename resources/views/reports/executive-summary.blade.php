<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Reports') }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Executive Summary') }}</h2>
            <p class="text-sm text-gray-500">{{ __('Aggregated internship program KPIs for college-level oversight.') }}</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            {{-- Pipeline KPIs --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <x-page-card compact>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Deployment Rate') }}</p>
                    <p class="text-2xl font-bold text-blue-700 mt-1">{{ $deploymentPct }}%</p>
                    <p class="text-xs text-gray-500">{{ $deployed }}/{{ $total }} {{ __('students') }}</p>
                </x-page-card>
                <x-page-card compact>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Completion Rate') }}</p>
                    <p class="text-2xl font-bold text-emerald-700 mt-1">{{ $completionPct }}%</p>
                    <p class="text-xs text-gray-500">{{ $completed }}/{{ $total }} {{ __('students') }}</p>
                </x-page-card>
                <x-page-card compact>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('Certified') }}</p>
                    <p class="text-2xl font-bold text-purple-700 mt-1">{{ $certified }}</p>
                    <p class="text-xs text-gray-500">{{ __('of :total students', ['total' => $total]) }}</p>
                </x-page-card>
                <x-page-card compact>
                    <p class="text-xs uppercase tracking-wide text-gray-500">{{ __('At-Risk Students') }}</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $atRisk }}</p>
                </x-page-card>
            </div>

            {{-- Satisfaction + Compliance --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <x-page-card compact>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('Company Satisfaction') }}</h3>
                    @if ($companySatisfaction->isNotEmpty())
                        <div class="flex items-baseline gap-2 mb-4">
                            <span class="text-3xl font-bold text-gray-900">{{ $avgSatisfaction }}</span>
                            <span class="text-sm text-gray-500">{{ __('average across') }} {{ $companySatisfaction->count() }} {{ __('companies') }}</span>
                        </div>
                        <div class="space-y-2">
                            @foreach ($companySatisfaction->take(5) as $cs)
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-700 truncate">{{ $cs->company->name }}</span>
                                    <span class="font-semibold text-gray-900">{{ round((float) $cs->avg_score, 1) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm text-gray-500">{{ __('No industry evaluations yet.') }}</p>
                    @endif
                </x-page-card>

                <x-page-card compact>
                    <h3 class="text-sm font-semibold text-gray-800 mb-3">{{ __('Document Compliance') }}</h3>
                    <div class="flex items-baseline gap-2 mb-4">
                        <span class="text-3xl font-bold text-gray-900">{{ $mandatoryDocs }}</span>
                        <span class="text-sm text-gray-500">{{ __('mandatory documents') }}</span>
                    </div>
                    <div class="text-sm">
                        <span class="font-semibold text-emerald-700">{{ $compliantStudents }}</span>
                        <span class="text-gray-500"> {{ __('students fully compliant') }}</span>
                    </div>
                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Evaluations Collected') }}</span>
                            <span class="font-semibold text-gray-900">{{ $feedbackCount }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">{{ __('Deployments (last 6 months)') }}</span>
                            <span class="font-semibold text-gray-900">{{ $recentDeployments }}</span>
                        </div>
                    </div>
                </x-page-card>
            </div>

            <p class="text-xs text-gray-400 text-center">{{ __('Generated :time', ['time' => now()->format('M d, Y h:i A')]) }}</p>
        </div>
    </div>
</x-app-layout>
