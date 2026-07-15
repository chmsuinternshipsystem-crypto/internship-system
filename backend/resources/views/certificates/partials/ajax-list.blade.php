<div class="table-wrap">
    <table class="min-w-full divide-y divide-gray-200 custom-table">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Ref. No.') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Source') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($certificates as $certificate)
                @php $isAuto = $certificate->is_auto_generated; @endphp
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 cell-wrap">
                        {{ $certificate->student->student_number }}
                        <div class="text-xs text-gray-500">{{ $certificate->student->name }}</div>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600 font-mono text-[11px]">
                        {{ $certificate->reference_number ?? '—' }}
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                        @if ($isAuto)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 ring-1 ring-emerald-200">
                                <i class="bi bi-magic text-[10px]"></i>
                                {{ __('Auto') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-50 px-2 py-0.5 text-[11px] font-medium text-gray-600 ring-1 ring-gray-200">
                                <i class="bi bi-upload text-[10px]"></i>
                                {{ __('Uploaded') }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                        @php
                            $cls = match($certificate->status) {
                                'verified' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200',
                                'rejected' => 'bg-red-50 text-red-700 ring-1 ring-red-200',
                                default => 'bg-amber-50 text-amber-700 ring-1 ring-amber-200',
                            };
                        @endphp
                        <span class="status-badge {{ $cls }}">{{ Str::headline($certificate->status) }}</span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                        <x-action-menu :id="'cert-'.$certificate->id">
                            <a href="{{ route('certificates.show', $certificate) }}">
                                <i class="bi bi-eye"></i>
                                @if ($certificate->status === 'pending' && $certificate->is_auto_generated) {{ __('Review') }} @else {{ __('View') }} @endif
                            </a>
                            @if ($certificate->file_path)
                                <a href="{{ route('staff.certificates.download', $certificate) }}">
                                    <i class="bi bi-download"></i> {{ __('Download PDF') }}
                                </a>
                            @endif
                        </x-action-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <div class="flex flex-col items-center justify-center py-8">
                            <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50 border border-emerald-100 mb-4">
                                <i class="bi bi-award text-2xl text-emerald-400"></i>
                            </span>
                            <strong class="text-sm font-semibold text-gray-700">{{ __('No certificates yet') }}</strong>
                            <p class="mt-1 text-sm text-gray-500 max-w-sm">{{ __('Completion certificates are auto-generated when students finish 600 hours. They will appear here for review.') }}</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('partials.htmx-pagination', ['paged' => $certificates, 'hxTarget' => '#certificates-ajax-mount'])