<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="text-xs font-semibold tracking-wide text-emerald-600 uppercase">{{ __('Notifications') }}</p>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Notifications') }}</h2>
            <p class="text-sm text-gray-500">{{ __('View and manage your notifications.') }}</p>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if ($notifications->count() === 0)
                <x-page-card compact>
                    <div class="text-center py-12">
                        <i class="bi bi-bell-slash text-4xl text-gray-300"></i>
                        <p class="mt-3 text-sm text-gray-500">{{ __('No notifications yet') }}</p>
                    </div>
                </x-page-card>
            @else
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-500">{{ $notifications->total() }} {{ __('notification(s)') }}</p>
                    @if ($unreadCount > 0)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn-primary text-xs px-3 py-1.5">{{ __('Mark all as read') }}</button>
                        </form>
                    @endif
                </div>

                <x-page-card compact>
                    <div class="space-y-1">
                        @foreach ($notifications as $notification)
                            @php
                                $data = $notification->data;
                                $actionUrl = $data['action_url'] ?? null;
                                $read = $notification->read();
                            @endphp
                            <div class="rounded-lg border {{ $read ? 'border-gray-200 bg-white' : 'border-emerald-200 bg-emerald-50/50' }} transition-colors">
                                @if ($actionUrl)
                                    <a href="{{ $actionUrl }}" class="block px-4 py-3">
                                @else
                                    <div class="px-4 py-3">
                                @endif
                                    <div class="flex items-start gap-3">
                                        <div class="mt-1 flex-shrink-0">
                                            @if (! $read)
                                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                                            @else
                                                <span class="inline-block w-2.5 h-2.5 rounded-full bg-transparent"></span>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-2">
                                                <p class="text-sm font-semibold {{ $read ? 'text-gray-700' : 'text-gray-900' }}">
                                                    {{ $data['title'] ?? '' }}
                                                </p>
                                                <span class="text-[11px] text-gray-400 whitespace-nowrap flex-shrink-0">
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1">{{ $data['body'] ?? '' }}</p>
                                        </div>
                                    </div>
                                @if ($actionUrl)
                                    </a>
                                @else
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </x-page-card>

                <div class="mt-4">
                    {{ $notifications->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
