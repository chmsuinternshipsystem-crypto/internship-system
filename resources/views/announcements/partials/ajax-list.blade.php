<div class="overflow-x-auto overflow-y-visible">
    <table class="min-w-full divide-y divide-gray-200 custom-table">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Title') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Visible to') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Author') }}</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Posted') }}</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($announcements as $announcement)
                @php
                    $target = strtolower(trim((string) ($announcement->visible_to_role ?? '')));
                    $audienceLabel = match ($target) {
                        '', 'all' => __('All audiences'),
                        'student' => __('Student'),
                        'instructor' => __('Instructor'),
                        'chairperson' => __('Chairperson'),
                        'dean' => __('Dean'),
                        default => ucfirst($target),
                    };
                @endphp
                <tr>
                    <td class="px-4 py-2 text-sm text-gray-900">
                        <div class="font-medium">{{ $announcement->title }}</div>
                        @php
                            $preview = Str::limit(strip_tags((string) ($announcement->body ?? '')), 120);
                            $titleNorm = strtolower(trim((string) ($announcement->title ?? '')));
                            $previewNorm = strtolower(trim($preview));
                        @endphp
                        @if ($preview !== '' && $previewNorm !== $titleNorm)
                            <div class="mt-1.5 text-xs text-gray-500 line-clamp-2 break-words">{{ $preview }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $audienceLabel }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $announcement->author?->name ?? '—' }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-700">{{ $announcement->created_at?->format('M d, Y') }}</td>
                    <td class="px-4 py-2 whitespace-nowrap text-right text-sm font-medium">
                        <x-action-menu :id="'announcement-'.$announcement->id">
                            <a href="{{ route('announcements.show', $announcement) }}">
                                <i class="bi bi-eye"></i> {{ __('View') }}
                            </a>
                            @if ($canManage)
                                <a href="{{ route('announcements.edit', $announcement) }}">
                                    <i class="bi bi-pencil"></i> {{ __('Edit') }}
                                </a>
                                <div class="action-divider"></div>
                                <x-confirm-delete
                                    :action="route('announcements.destroy', $announcement)"
                                    :message="__('Are you sure you want to delete this announcement?')"
                                    :dialog-id="'announcement-del-'.$announcement->id"
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
@include('partials.htmx-pagination', ['paged' => $announcements, 'hxTarget' => '#announcement-ajax-mount'])
