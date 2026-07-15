<x-app-layout>
    @php
        $canVerify = auth()->user()->role === 'instructor';
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
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('certificates.download', $certificate) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    <i class="bi bi-download me-1"></i> {{ __('Download') }}
                </a>
                <a href="{{ route('certificates.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-300">
                    {{ __('Back') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Status') }}</span>
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
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Issue Date') }}</span>
                            <span class="text-sm text-gray-900">{{ $certificate->issued_at?->format('M d, Y') ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Company') }}</span>
                            <span class="text-sm text-gray-900">{{ $certificate->deployment?->company?->name ?? '—' }}</span>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Uploaded By') }}</span>
                            <span class="text-sm text-gray-900">{{ $certificate->uploader?->name ?? '—' }}</span>
                        </div>
                    </div>

                    @if ($certificate->description)
                        <div class="border-t pt-4">
                            <span class="block text-xs font-semibold text-gray-500">{{ __('Description') }}</span>
                            <p class="mt-1 text-sm text-gray-700">{{ $certificate->description }}</p>
                        </div>
                    @endif

                    @if ($certificate->verification_notes)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                            <p class="font-medium">{{ __('Verification Notes') }}</p>
                            <p class="mt-1">{{ $certificate->verification_notes }}</p>
                        </div>
                    @endif

                    @if ($canVerify && $certificate->status === 'pending' && $certificate->file_path)
                        <div class="border-t pt-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">{{ __('Verify Certificate') }}</h4>
                            <iframe src="{{ route('certificates.download', $certificate) }}" class="w-full rounded border mb-4" style="height: 400px;"></iframe>
                            <form method="POST" action="{{ route('certificates.verify', $certificate) }}" class="space-y-3">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Verification Notes') }}</label>
                                    <textarea name="verification_notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600 sm:text-sm" placeholder="{{ __('Optional notes...') }}"></textarea>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" name="action" value="approve"
                                            class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-700">
                                        {{ __('Verify') }}
                                    </button>
                                    <button type="submit" name="action" value="reject"
                                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                        {{ __('Reject') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>