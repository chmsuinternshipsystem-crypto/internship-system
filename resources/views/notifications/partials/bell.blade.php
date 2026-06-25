<div
    x-data="notificationBell()"
    x-init="init()"
    @notify.window="addNotification($event.detail)"
    class="relative"
>
    <button @click="toggle()" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none" aria-label="{{ __('Notifications') }}">
        <i class="bi bi-bell text-lg"></i>
        <span
            x-cloak
            x-show="unreadCount > 0"
            x-text="unreadCount > 99 ? '99+' : unreadCount"
            class="absolute -top-0.5 -right-0.5 inline-flex min-w-4 h-4 px-1 items-center justify-center rounded-full bg-red-500 text-white text-[9px] font-bold leading-none"
        ></span>
    </button>

    <div
        x-cloak
        x-show="open"
        @click.outside="open = false"
        class="absolute right-0 mt-1 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50 max-h-96 flex flex-col"
    >
        <div class="flex items-center justify-between px-4 py-2 border-b border-gray-100">
            <span class="text-sm font-semibold text-gray-800">{{ __('Notifications') }}</span>
            <button
                x-show="unreadCount > 0"
                @click="markAllAsRead()"
                class="text-xs text-emerald-600 hover:text-emerald-700 font-medium"
            >
                {{ __('Mark all as read') }}
            </button>
        </div>

        <div class="overflow-y-auto flex-1">
            <template x-for="notification in notifications" :key="notification.id">
                <a
                    :href="notification.action_url || '#'"
                    @click.prevent="visit(notification)"
                    class="block px-4 py-3 border-b border-gray-50 hover:bg-gray-50 transition-colors"
                    :class="{ 'bg-emerald-50/30': !notification.read }"
                >
                    <div class="flex items-start gap-2">
                        <div class="mt-0.5 flex-shrink-0">
                            <template x-if="!notification.read">
                                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mt-1.5"></span>
                            </template>
                            <template x-if="notification.read">
                                <span class="inline-block w-2 h-2 rounded-full bg-transparent mt-1.5"></span>
                            </template>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-medium text-gray-900 truncate" x-text="notification.title"></p>
                            <p class="text-xs text-gray-500 mt-0.5 line-clamp-2" x-text="notification.body"></p>
                            <p class="text-[10px] text-gray-400 mt-1" x-text="notification.created_at"></p>
                        </div>
                    </div>
                </a>
            </template>
            <div x-show="notifications.length === 0" class="px-4 py-8 text-center">
                <i class="bi bi-bell-slash text-gray-300 text-2xl"></i>
                <p class="text-sm text-gray-400 mt-2">{{ __('No notifications yet') }}</p>
            </div>
        </div>

        <a href="{{ route('notifications.index') }}" class="block text-center text-xs text-emerald-600 hover:text-emerald-700 font-medium py-2 border-t border-gray-100 hover:bg-gray-50 transition-colors">
            {{ __('View all notifications') }}
        </a>
    </div>
</div>

@once
<script>
function notificationBell() {
    return {
        open: false,
        unreadCount: 0,
        notifications: [],
        async init() {
            await this.fetchNotifications();
            setInterval(() => this.fetchNotifications(), 30000);
        },
        async fetchNotifications() {
            try {
                const response = await fetch('{{ route("notifications.recent") }}');
                const data = await response.json();
                this.notifications = data.notifications;
                this.unreadCount = data.unread_count;
            } catch (e) {
                // silently fail
            }
        },
        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },
        addNotification(detail) {
            this.notifications.unshift({
                id: 'temp_' + Date.now(),
                ...detail,
                read: false,
                created_at: '{{ __("just now") }}',
            });
            this.unreadCount++;
        },
        async visit(notification) {
            if (!notification.read) {
                try {
                    await fetch('{{ url('/notifications') }}/' + notification.id + '/read', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                } catch (e) {}
                notification.read = true;
                this.unreadCount = Math.max(0, this.unreadCount - 1);
            }
            if (notification.action_url) {
                window.location.href = notification.action_url;
            }
        },
        async markAllAsRead() {
            try {
                await fetch('{{ route("notifications.read-all") }}', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                this.notifications.forEach(n => n.read = true);
                this.unreadCount = 0;
            } catch (e) {}
        },
    };
}
</script>
@endonce
