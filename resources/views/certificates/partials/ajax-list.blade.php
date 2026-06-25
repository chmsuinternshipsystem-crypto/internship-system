<div class="table-wrap">
    <table class="min-w-full divide-y divide-gray-200 custom-table">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Student') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Title') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Issued') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Status') }}</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($certificates as $certificate)
                <tr>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 cell-wrap">
                        {{ $certificate->student->student_number }}
                        <div class="text-xs text-gray-500">{{ $certificate->student->name }}</div>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            {{ Str::headline($certificate->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900 cell-wrap">{{ $certificate->title }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">{{ $certificate->issued_at?->format('M d, Y') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm">
                        @php
                            $cls = match($certificate->status) {
                                'verified' => 'badge-approved',
                                'rejected' => 'badge-rejected',
                                default => 'badge-pending',
                            };
                        @endphp
                        <span class="status-badge {{ $cls }}">{{ Str::headline($certificate->status) }}</span>
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                        <x-action-menu :id="'cert-'.$certificate->id">
                            <a href="{{ route('certificates.show', $certificate) }}">
                                <i class="bi bi-eye"></i> {{ __('View') }}
                            </a>
                            @if ($certificate->status === 'pending')
                                <a href="{{ route('certificates.show', $certificate) }}">
                                    <i class="bi bi-check-circle"></i> {{ __('Verify') }}
                                </a>
                            @endif
                            <a href="{{ route('certificates.download', $certificate) }}">
                                <i class="bi bi-download"></i> {{ __('Download') }}
                            </a>
                        </x-action-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty-state">
                        <i class="bi bi-award"></i>
                        <strong>{{ __('No certificates found') }}</strong>
                        <p>{{ __('Nothing here yet.') }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('partials.htmx-pagination', ['paged' => $certificates, 'hxTarget' => '#certificates-ajax-mount'])