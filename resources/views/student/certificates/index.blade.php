<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-baseline sm:justify-between gap-2">
            <div>
                <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Certificates') }}</p>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('My Certificates') }}</h2>
                <p class="text-sm text-gray-500">{{ __('View your uploaded certificates and completion records.') }}</p>
            </div>
        </div>
    </x-slot>

    <x-page-card compact>
        <div class="space-y-3">
            @forelse ($certificates as $certificate)
                <div class="flex items-center justify-between p-4 rounded-xl border border-gray-200 bg-white hover:border-emerald-200 hover:shadow-sm transition-all">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                            <i class="bi bi-award text-emerald-600 text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $certificate->title }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    {{ Str::headline($certificate->type) }}
                                </span>
                                @php
                                    $cls = match($certificate->status) {
                                        'verified' => 'badge-approved',
                                        'rejected' => 'badge-rejected',
                                        default => 'badge-pending',
                                    };
                                @endphp
                                <span class="status-badge {{ $cls }}">{{ Str::headline($certificate->status) }}</span>
                                @if ($certificate->issued_at)
                                    <span class="text-xs text-gray-400">{{ $certificate->issued_at->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <a href="{{ route('student.certificates.show', $certificate) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-emerald-700 rounded-lg hover:bg-emerald-50 transition-colors">
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            @empty
                <div class="empty-state">
                    <i class="bi bi-award"></i>
                    <strong>{{ __('No certificates yet') }}</strong>
                    <p>{{ __('Certificates will appear here once uploaded by your instructor.') }}</p>
                </div>
            @endforelse
        </div>
        @if (method_exists($certificates, 'links'))
            <div class="mt-4">{{ $certificates->links() }}</div>
        @endif
    </x-page-card>
</x-app-layout>
