<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Certificates') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $certificate->title }}</h2>
                <p class="text-sm text-gray-500">{{ Str::headline($certificate->type) }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto">
        <x-page-card compact>
            <div class="space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500">{{ __('Status') }}</p>
                        @php
                            $cls = match($certificate->status) {
                                'verified' => 'badge-approved',
                                'rejected' => 'badge-rejected',
                                default => 'badge-pending',
                            };
                        @endphp
                        <span class="status-badge {{ $cls }} mt-1">{{ Str::headline($certificate->status) }}</span>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500">{{ __('Issue Date') }}</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $certificate->issued_at?->format('M d, Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-500">{{ __('Company') }}</p>
                        <p class="text-sm text-gray-900 mt-1">{{ $certificate->deployment?->company?->name ?? '—' }}</p>
                    </div>
                </div>

                @if ($certificate->description)
                    <div class="border-t pt-4">
                        <p class="text-xs font-semibold text-gray-500">{{ __('Description') }}</p>
                        <p class="mt-1 text-sm text-gray-700">{{ $certificate->description }}</p>
                    </div>
                @endif

                @if ($certificate->verification_notes)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                        <p class="font-medium">{{ __('Verification Notes') }}</p>
                        <p class="mt-1">{{ $certificate->verification_notes }}</p>
                    </div>
                @endif

                @if ($certificate->file_path)
                    @php
                        $isImage = preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $certificate->file_path);
                        $isPdf = preg_match('/\.pdf$/i', $certificate->file_path);
                    @endphp
                    <div class="border-t pt-4">
                        @if ($isImage)
                            <div class="flex justify-center mb-4">
                                <img src="/storage/{{ $certificate->file_path }}"
                                     class="max-w-full max-h-96 rounded-lg border border-gray-200 shadow-sm object-contain"
                                     loading="lazy"
                                     alt="{{ $certificate->title }}">
                            </div>
                        @elseif ($isPdf)
                            <div class="mb-4 rounded-lg border border-gray-200 overflow-hidden" style="height: 500px;">
                                <iframe src="/storage/{{ $certificate->file_path }}"
                                        class="w-full h-full border-0"
                                        title="{{ $certificate->title }}"></iframe>
                            </div>
                        @endif
                        <div class="flex justify-center">
                            <a href="{{ route('student.certificates.download', $certificate) }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 bg-emerald-600 border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-emerald-700 transition-colors">
                                <i class="bi bi-download"></i>
                                {{ __('Download Certificate') }}
                            </a>
                        </div>
                    </div>
                @endif

                <div class="flex justify-between items-center pt-2 border-t">
                    <a href="{{ route('student.certificates.index') }}"
                       class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-gray-900">
                        <i class="bi bi-arrow-left"></i> {{ __('Back to Certificates') }}
                    </a>
                </div>
            </div>
        </x-page-card>
    </div>
</x-app-layout>
