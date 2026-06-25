@php
    $isStudentPortal = $isStudentPortal ?? false;
    $showRoute = $isStudentPortal ? 'student.messages.show' : 'messages.show';
    $indexRoute = $isStudentPortal ? 'student.messages.index' : 'messages.index';
    $tab = $tab ?? 'inbox';
    $filter = $filter ?? 'all';
    $actorUserId = auth()->id();
    $actorStudentAccountId = session('student_account_id');
@endphp

{{-- Tabs --}}
<div class="flex items-center gap-1 mb-3 bg-gray-100 rounded-lg p-0.5">
    <a href="{{ route($indexRoute, ['tab' => 'inbox', 'filter' => $filter]) }}"
       hx-get="{{ route($indexRoute, ['tab' => 'inbox', 'filter' => $filter]) }}"
       hx-target="#message-inbox-column"
       hx-swap="innerHTML"
       hx-push-url="true"
       class="flex-1 text-center text-xs font-semibold py-1.5 rounded-md transition-colors {{ $tab === 'inbox' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
        <i class="bi bi-inbox me-1"></i>
        {{ __('Inbox') }}
    </a>
    <a href="{{ route($indexRoute, ['tab' => 'archived', 'filter' => $filter]) }}"
       hx-get="{{ route($indexRoute, ['tab' => 'archived', 'filter' => $filter]) }}"
       hx-target="#message-inbox-column"
       hx-swap="innerHTML"
       hx-push-url="true"
       class="flex-1 text-center text-xs font-semibold py-1.5 rounded-md transition-colors {{ $tab === 'archived' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
        <i class="bi bi-archive me-1"></i>
        {{ __('Archived') }}
    </a>
</div>

{{-- Filter pills (only in inbox tab) --}}
@if ($tab === 'inbox')
    <div class="flex items-center gap-1 mb-2">
        <a href="{{ route($indexRoute, ['tab' => $tab, 'filter' => 'all']) }}"
           hx-get="{{ route($indexRoute, ['tab' => $tab, 'filter' => 'all']) }}"
           hx-target="#message-inbox-column"
           hx-swap="innerHTML"
           class="px-3 py-1 text-xs font-medium rounded-full transition-colors {{ $filter === 'all' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:text-gray-700 hover:bg-gray-200' }}">
            {{ __('All') }}
        </a>
        <a href="{{ route($indexRoute, ['tab' => $tab, 'filter' => 'unread']) }}"
           hx-get="{{ route($indexRoute, ['tab' => $tab, 'filter' => 'unread']) }}"
           hx-target="#message-inbox-column"
           hx-swap="innerHTML"
           class="px-3 py-1 text-xs font-medium rounded-full transition-colors {{ $filter === 'unread' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:text-gray-700 hover:bg-gray-200' }}">
            {{ __('Unread') }}
        </a>
        <a href="{{ route($indexRoute, ['tab' => $tab, 'filter' => 'read']) }}"
           hx-get="{{ route($indexRoute, ['tab' => $tab, 'filter' => 'read']) }}"
           hx-target="#message-inbox-column"
           hx-swap="innerHTML"
           class="px-3 py-1 text-xs font-medium rounded-full transition-colors {{ $filter === 'read' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500 hover:text-gray-700 hover:bg-gray-200' }}">
            {{ __('Read') }}
        </a>
    </div>
@endif

{{-- Count --}}
<div class="flex items-center justify-between mb-2">
    <span class="text-[11px] text-gray-400">{{ $threads->total() }} {{ __('conversations') }}</span>
</div>

{{-- Thread list --}}
<div id="message-thread-list" class="bg-white rounded-xl border border-gray-200 shadow-sm divide-y divide-gray-100 max-h-[540px] overflow-y-auto">
    @forelse ($threads as $thread)
        @include('messages.partials.thread-item', [
            'thread' => $thread,
            'actorUserId' => $actorUserId,
            'actorStudentAccountId' => $actorStudentAccountId,
            'isStudentPortal' => $isStudentPortal,
            'tab' => $tab,
            'filter' => $filter,
            'currentPage' => $threads->currentPage(),
        ])
    @empty
        <div class="p-8 text-center">
            <div class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center">
                <i class="bi bi-chat-dots text-2xl text-emerald-500"></i>
            </div>
            <p class="text-sm font-semibold text-gray-800">
                {{ $tab === 'archived'
                    ? __('No archived conversations')
                    : __('No conversations yet') }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
                {{ $tab === 'archived'
                    ? __('Archived conversations will appear here.')
                    : __('Start a new message to begin a conversation.') }}
            </p>
        </div>
    @endforelse
</div>

{{-- Pagination --}}
@if ($threads->hasPages())
    <div class="mt-3">
        @include('partials.htmx-pagination', ['paged' => $threads, 'hxTarget' => '#message-inbox-column'])
    </div>
@endif

<span id="unread-count-val" class="hidden">{{ $unreadMessageCount }}</span>
