<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-md font-semibold flex items-center gap-1.5">
                <i class="bi bi-award text-gray-400"></i>
                {{ __('Certificates') }}
            </h3>
            @if ($canManage)
                <a href="{{ route('certificates.create') }}"
                   class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                    {{ __('Upload certificate') }} &rarr;
                </a>
            @endif
        </div>

        @if ($certificates->isEmpty())
            <p class="text-sm text-gray-500">{{ __('No certificates uploaded yet.') }}</p>
        @else
            <div class="space-y-2">
                @foreach ($certificates as $cert)
                    @php
                        $statusColor = match($cert->status) {
                            'verified' => 'text-emerald-700 bg-emerald-50',
                            'rejected' => 'text-red-700 bg-red-50',
                            default => 'text-amber-700 bg-amber-50',
                        };
                    @endphp
                    <div class="flex items-center justify-between py-2 px-3 rounded-md {{ $statusColor }}">
                        <div>
                            <span class="text-sm font-medium">{{ $cert->title }}</span>
                            <p class="text-xs text-gray-500">
                                {{ $cert->created_at->format('M d, Y') }}
                                @if ($cert->uploader)
                                    &middot; {{ __('by') }} {{ $cert->uploader->name }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs font-semibold">{{ Str::headline($cert->status) }}</span>
                            <a href="{{ route('certificates.download', $cert) }}"
                               class="text-xs text-gray-400 hover:text-gray-600">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
