<div class="table-wrap">
    <table class="min-w-full divide-y divide-gray-200 custom-table custom-table--fixed">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Order') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Phase') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Description') }}</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($requiredDocuments as $doc)
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-tight">{{ $doc->order_index ?? '-' }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">{{ $doc->name }}</td>
                    <td class="px-4 py-2 text-sm text-gray-600 cell-tight">{{ Str::headline($doc->phase ?? 'All') }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900 cell-wrap">
                        @if ($doc->description && mb_strlen($doc->description) > 50)
                            <span>{{ Str::limit($doc->description, 50) }}</span>
                            <a href="{{ route('required-documents.show', $doc) }}" class="ml-1 text-emerald-600 hover:text-emerald-800 text-xs font-medium whitespace-nowrap">{{ __('see more') }}</a>
                        @else
                            {{ $doc->description ?? '-' }}
                        @endif
                    </td>
                    <td class="px-4 py-2 text-right text-sm font-medium cell-tight">
                        <x-action-menu :id="'doc-'.$doc->id">
                            <a href="{{ route('required-documents.show', $doc) }}">
                                <i class="bi bi-eye"></i> {{ __('View') }}
                            </a>
                            @if ($canManage)
                                <a href="{{ route('required-documents.edit', $doc) }}">
                                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                </a>
                                <div class="action-divider"></div>
                                <x-confirm-delete
                                    :action="route('required-documents.destroy', $doc)"
                                    :message="__('Are you sure you want to delete this required document?')"
                                    :dialog-id="'reqdoc-del-'.$doc->id"
                                >
                                    <i class="bi bi-trash"></i> {{ __('Delete') }}
                                </x-confirm-delete>
                            @endif
                        </x-action-menu>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="empty-state">
                        <i class="bi bi-inbox"></i>
                        <strong>{{ __('No records found') }}</strong>
                        <p>{{ __('Nothing here yet.') }}</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@include('partials.htmx-pagination', ['paged' => $requiredDocuments, 'hxTarget' => '#required-documents-ajax-mount'])
