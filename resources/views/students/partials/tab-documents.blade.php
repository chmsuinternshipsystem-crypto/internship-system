<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-md font-semibold flex items-center gap-1.5">
                <i class="bi bi-file-earmark-text text-gray-400"></i>
                {{ __('Documents') }}
            </h3>
            <a href="{{ route('student-documents.edit', $student) }}"
               class="text-xs font-semibold text-emerald-700 hover:text-emerald-800">
                {{ __('Full document view') }} &rarr;
            </a>
        </div>

        @php
            $requiredDocs = \App\Models\RequiredDocument::orderBy('name')->get();
        @endphp

        @if ($requiredDocs->isEmpty())
            <p class="text-sm text-gray-500">{{ __('No required documents configured.') }}</p>
        @else
            <div class="space-y-2">
                @foreach ($requiredDocs as $rd)
                    @php
                        $studentDoc = $student->documents->firstWhere('required_document_id', $rd->id);
                        $status = $studentDoc?->status ?? 'Missing';
                        $statusColor = match($status) {
                            'Submitted' => 'text-emerald-700 bg-emerald-50',
                            'Approved' => 'text-blue-700 bg-blue-50',
                            'Rejected' => 'text-red-700 bg-red-50',
                            default => 'text-gray-500 bg-gray-50',
                        };
                    @endphp
                    <div class="flex items-center justify-between py-2 px-3 rounded-md {{ $statusColor }}">
                        <div>
                            <span class="text-sm font-medium">{{ $rd->name }}</span>
                            @if ($rd->is_mandatory)
                                <span class="text-[10px] text-gray-400 ml-1">{{ __('Mandatory') }}</span>
                            @endif
                        </div>
                        <span class="text-xs font-semibold">{{ $status }}</span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
