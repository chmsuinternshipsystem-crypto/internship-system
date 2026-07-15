<x-app-layout>
    @php
        $canVerify = auth()->user()->role === 'instructor';
        $isGenerated = $certificate->is_auto_generated && $certificate->file_path;
        $hasPreview = $certificate->file_path && Storage::disk('public')->exists($certificate->file_path);
    @endphp
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <x-breadcrumbs :items="[
    ['label' => __('Certificates'), 'url' => route('certificates.index')],
    ['label' => $certificate->title],
]" />
<p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Certificates') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $certificate->title }}</h2>
                <p class="text-sm text-gray-500">
                    {{ $certificate->student->name }} ({{ $certificate->student->student_number }})
                    • {{ Str::headline($certificate->type) }}
                    @if ($certificate->reference_number)
                        • {{ $certificate->reference_number }}
                    @endif
                </p>
            </div>
            <div class="flex gap-2">
                @if ($certificate->file_path)
                    <a href="{{ route('staff.certificates.download', $certificate) }}"
                       class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700 shadow-sm btn-lift">
                        <i class="bi bi-download me-1"></i> {{ __('Download PDF') }}
                    </a>
                @endif
                <a href="{{ route('certificates.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Info card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <span class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Status') }}</span>
                            <span class="status-badge mt-1 {{ match($certificate->status) { 'verified' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200', 'rejected' => 'bg-red-50 text-red-700 ring-1 ring-red-200', default => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200' } }}">
                                {{ Str::headline($certificate->status) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Type') }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ Str::headline($certificate->type) }}</span>
                        </div>
                        <div>
                            <span class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Generated') }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $certificate->generated_at?->format('M d, Y') ?? ($certificate->issued_at?->format('M d, Y') ?? '—') }}</span>
                        </div>
                        <div>
                            <span class="block text-[11px] font-semibold uppercase tracking-wider text-gray-500">{{ __('Reference') }}</span>
                            <span class="text-sm font-medium text-gray-900">{{ $certificate->reference_number ?? '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Certificate preview --}}
            @if ($hasPreview)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-800">{{ __('Certificate Preview') }}</h3>
                        @if ($isGenerated)
                        <span class="inline-flex items-center gap-1 text-[11px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-medium">
                            <i class="bi bi-check-circle-fill text-[10px]"></i>
                            {{ __('Auto-generated') }}
                        </span>
                        @endif
                    </div>
                    <div class="p-4">
                        <iframe src="{{ $previewUrl }}" class="w-full rounded-lg border border-gray-200" style="height: 650px; background: #f5f5f5;"></iframe>
                    </div>
                </div>
            @endif

            {{-- Verification section --}}
            @if ($canVerify)
                @if ($certificate->status === 'pending')
                    <div x-data="{ showRegen: false }" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-5 space-y-4">
                            @if ($isGenerated)
                            <div class="flex items-center gap-3 p-3 rounded-lg bg-blue-50 border border-blue-100">
                                <i class="bi bi-info-circle-fill text-blue-600 shrink-0"></i>
                                <div>
                                    <p class="text-sm font-medium text-blue-900">{{ __('Auto-generated certificate ready for review') }}</p>
                                    <p class="text-xs text-blue-700 mt-0.5">{{ __('This certificate was created automatically from deployment data. If the student name, hours, or company are incorrect, use "Regenerate" to rebuild with the latest data — then approve.') }}</p>
                                </div>
                            </div>
                            @endif

                            {{-- Approve/Regenerate form --}}
                            <form method="POST" action="{{ route('certificates.verify', $certificate) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Notes') }}</label>
                                    <textarea name="verification_notes" rows="2" class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm" placeholder="{{ __('Optional notes about this certificate...') }}"></textarea>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <button type="submit" name="action" value="approve"
                                            class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-emerald-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-emerald-700 shadow-sm btn-lift">
                                        <i class="bi bi-check-lg"></i>
                                        {{ __('Approve') }}
                                    </button>
                                    @if ($isGenerated && $certificate->status === 'pending')
                                        <button type="button" @click="showRegen = true"
                                                class="inline-flex items-center gap-1.5 px-5 py-2.5 bg-white border border-gray-300 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50">
                                            <i class="bi bi-arrow-repeat"></i>
                                            {{ __('Regenerate') }}
                                        </button>
                                        <span class="text-xs text-gray-400 ml-1">{{ __('Updates from latest data') }}</span>
                                    @endif
                                </div>
                            </form>

                            {{-- Regenerate confirmation modal --}}
                            <template x-teleport="body">
                                <div x-show="showRegen" x-cloak
                                     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/40 backdrop-blur-sm"
                                     @keydown.escape.window="showRegen = false">
                                    <div x-show="showRegen"
                                         x-transition:enter="ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                         class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6 border border-gray-100"
                                         @click.away="showRegen = false">
                                        <div class="text-center">
                                            <span class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-50 text-amber-600 mb-4 ring-1 ring-amber-100">
                                                <i class="bi bi-arrow-repeat text-2xl"></i>
                                            </span>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Regenerate Certificate') }}</h3>
                                            <p class="mt-2 text-sm text-gray-600">{{ __('This will regenerate the certificate PDF with the latest data. The current version will be replaced.') }}</p>
                                        </div>
                                        <div class="mt-6 flex justify-center gap-3">
                                            <button type="button" @click="showRegen = false"
                                                    class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                                                {{ __('Cancel') }}
                                            </button>
                                            <a href="{{ route('certificates.regenerate', $certificate) }}"
                                               class="px-5 py-2.5 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl transition-colors shadow-sm btn-lift">
                                                <i class="bi bi-arrow-repeat me-1.5"></i>
                                                {{ __('Regenerate') }}
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                @elseif ($certificate->status === 'verified')
                    <div class="bg-white rounded-xl shadow-sm border border-emerald-100 overflow-hidden">
                        <div class="p-5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                                    <i class="bi bi-check-circle-fill text-lg"></i>
                                </span>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-900">{{ __('Certificate Verified') }}</p>
                                    <p class="text-xs text-emerald-700">{{ __('This certificate is approved and available for the student.') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

        </div>
    </div>
</x-app-layout>
