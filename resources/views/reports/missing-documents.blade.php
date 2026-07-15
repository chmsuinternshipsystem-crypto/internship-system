<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Report') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Missing / Pending Documents') }}</h2>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('reports.missing-documents', ['export' => 'pdf']) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-pdf me-1"></i>{{ __('Export PDF') }}</a>
                <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('Back') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="layout-section-y">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-page-card compact>
                @if ($students->isEmpty())
                    <div class="empty-state">
                        <i class="bi bi-check-circle"></i>
                        <strong>{{ __('All clear') }}</strong>
                        <p>{{ __('No students have missing or pending mandatory documents.') }}</p>
                    </div>
                @else
                    <p class="text-sm text-gray-600 mb-5">
                        {{ __('Showing :count student(s) with missing or pending mandatory documents.', ['count' => $students->count()]) }}
                    </p>
                    <div class="space-y-5">
                        @foreach ($students as $row)
                            <div class="rounded-xl border border-gray-200 bg-white p-4 sm:p-5 shadow-sm">
                                <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                                    <div>
                                        <h3 class="text-base font-semibold text-gray-900">{{ $row['student']->name }}</h3>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-mono text-xs text-gray-500">{{ $row['student']->student_number }}</span>
                                            <span class="text-gray-300 mx-1">·</span>
                                            {{ $row['student']->program }} / {{ $row['student']->section }}
                                        </p>
                                    </div>
                                </div>
                                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                                    <div class="rounded-lg border border-rose-100 bg-rose-50/60 p-4">
                                        <h4 class="text-xs font-bold uppercase tracking-wide text-rose-900">{{ __('Missing') }}</h4>
                                        <p class="mt-1 text-xs text-rose-800/80">{{ __('No submission on file for these mandatory requirements.') }}</p>
                                        @if (count($row['missing']))
                                            <ul class="mt-3 list-disc space-y-1.5 pl-5 text-sm text-rose-950">
                                                @foreach ($row['missing'] as $name)
                                                    <li>{{ $name }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="mt-3 text-sm text-gray-500">{{ __('None — all mandatory items have at least been started.') }}</p>
                                        @endif
                                    </div>
                                    <div class="rounded-lg border border-sky-100 bg-sky-50/60 p-4">
                                        <h4 class="text-xs font-bold uppercase tracking-wide text-sky-900">{{ __('Pending') }}</h4>
                                        <p class="mt-1 text-xs text-sky-900/80">{{ __('Submitted or in progress; awaiting verification or workflow steps.') }}</p>
                                        @if (count($row['pending']))
                                            <ul class="mt-3 list-disc space-y-1.5 pl-5 text-sm text-sky-950">
                                                @foreach ($row['pending'] as $name)
                                                    <li>{{ $name }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            <p class="mt-3 text-sm text-gray-500">{{ __('None — nothing waiting in pending state.') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-page-card>
        </div>
    </div>
</x-app-layout>
