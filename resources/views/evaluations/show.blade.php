<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Evaluations'), 'url' => route('evaluations.index')],
    ['label' => __('Evaluation #').$evaluation->id],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Performance') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Evaluation Details') }}</h2>
                <p class="text-sm text-gray-500">{{ __('View student performance evaluation and score.') }}</p>
            </div>
        </div>
    </x-slot>

    @php
        $supervisorMeta = $evaluation->extractSupervisorMetaFromComments();
        $displayComment = $evaluation->cleanCommentsForDisplay();
    @endphp

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-100 px-6 py-4">
                    <h3 class="text-xl font-semibold text-gray-900">
                        {{ $evaluation->student?->student_number }} - {{ $evaluation->student?->name }}
                    </h3>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-gray-600">
                        <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                            {{ __('Score') }}: {{ $evaluation->score }}
                        </span>
                        @if (($evaluation->evaluation_type ?? 'industry') === 'student_feedback')
                            <span class="inline-flex items-center rounded-full border border-purple-100 bg-purple-50 px-2.5 py-1 text-xs font-medium text-purple-700">
                                {{ __('Student Feedback') }}
                            </span>
                        @elseif (($evaluation->evaluation_type ?? 'industry') === 'school')
                            <span class="inline-flex items-center rounded-full border border-blue-100 bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                {{ __('School Instructor') }}
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full border border-amber-100 bg-amber-50 px-2.5 py-1 text-xs font-medium text-amber-700">
                                {{ __('Industry Supervisor') }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="p-6 text-gray-900 space-y-5">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                            <span class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Company') }}</span>
                            <span class="mt-1 block text-sm font-medium text-gray-900">{{ $evaluation->company?->name ?? '—' }}</span>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                            <span class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Evaluator') }}</span>
                            <span class="mt-1 block text-sm font-medium text-gray-900">{{ $evaluation->evaluatorDisplayLabel() }}</span>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                            <span class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Evaluated At') }}</span>
                            <span class="mt-1 block text-sm font-medium text-gray-900">{{ $evaluation->evaluated_at?->format('M d, Y') ?? '—' }}</span>
                        </div>
                        <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3">
                            <span class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Evaluation ID') }}</span>
                            <span class="mt-1 block text-sm font-medium text-gray-900">#{{ $evaluation->id }}</span>
                        </div>
                    </div>

                    @if (($evaluation->evaluation_type ?? 'industry') === 'industry')
                        <div class="rounded-lg border border-emerald-100 bg-emerald-50/70 px-4 py-3">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ __('HTE Supervisor Identity') }}</p>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                <p class="text-sm text-emerald-900"><span class="font-semibold">{{ __('Name') }}:</span> {{ $supervisorMeta['name'] ?? '—' }}</p>
                                <p class="text-sm text-emerald-900 break-all"><span class="font-semibold">{{ __('Email') }}:</span> {{ $supervisorMeta['email'] ?? '—' }}</p>
                            </div>
                        </div>
                    @endif

                    <div>
                        <span class="block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Comments') }}</span>
                        <div class="mt-2 rounded-lg border border-gray-200 bg-white px-4 py-3">
                            @if ($displayComment)
                                <p class="text-sm text-gray-900 whitespace-pre-line">{{ $displayComment }}</p>
                            @else
                                <p class="text-sm text-gray-500">{{ __('No additional comments provided.') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="pt-2 flex flex-wrap justify-end gap-2">
                        <a href="{{ route('evaluations.index') }}"
                           class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2">
                            {{ __('Back to list') }}
                        </a>
                        @can('manage', App\Models\Evaluation::class)
                            <a href="{{ route('evaluations.edit', $evaluation) }}"
                               class="inline-flex items-center rounded-md border border-transparent px-4 py-2 font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 btn-primary">
                                {{ __('Edit') }}
                            </a>
                        @endcan
                        <a href="{{ route('evaluations.export.docx', $evaluation) }}"
                           class="inline-flex items-center rounded-md border border-emerald-200 bg-white px-4 py-2 font-semibold text-xs text-emerald-700 uppercase tracking-widest hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                            <i class="bi bi-download me-1"></i>{{ __('Export DOCX') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

