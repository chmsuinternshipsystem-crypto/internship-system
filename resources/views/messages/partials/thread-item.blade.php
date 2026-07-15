@php
    $showRoute = $isStudentPortal ? 'student.messages.show' : 'messages.show';
    $toggleReadRoute = $isStudentPortal ? 'student.messages.toggle-read' : 'messages.toggle-read';
    $toggleArchiveRoute = $isStudentPortal ? 'student.messages.toggle-archive' : 'messages.toggle-archive';
    $filter = $filter ?? 'all';
    $currentPage = $currentPage ?? 1;

    $p = $thread->participants->first(function ($row) use ($actorUserId, $actorStudentAccountId) {
        if ($actorUserId) {
            return (int) $row->user_id === (int) $actorUserId;
        }
        return $actorStudentAccountId && (int) $row->student_account_id === (int) $actorStudentAccountId;
    });
    $lastReadAt = $p?->last_read_at;
    $archivedAt = $p?->archived_at;
    $latest = $thread->latestMessage;
    $isMine = $latest && (
        ($actorUserId && (int) $latest->sender_id === (int) $actorUserId)
        || ($actorStudentAccountId && (int) ($latest->sender_student_account_id ?? 0) === (int) $actorStudentAccountId)
    );
    $isUnread = $latest && ! $isMine && (! $lastReadAt || $latest->created_at->gt($lastReadAt));

    $otherParticipant = $thread->participants->first(function ($row) use ($actorUserId, $actorStudentAccountId) {
        if ($actorUserId) {
            return $row->user_id && (int) $row->user_id !== (int) $actorUserId;
        }
        return $row->student_account_id && (int) $row->student_account_id !== (int) $actorStudentAccountId;
    });
    $isGroup = $thread->participants->count() > 2;
    $avatarName = $isGroup ? $thread->subject : ($otherParticipant?->displayName() ?? $thread->subject);
    $initial = strtoupper(substr($avatarName, 0, 1));
    $colors = ['bg-emerald-500', 'bg-blue-500', 'bg-violet-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500'];
    $colorIdx = abs(crc32((string) $thread->id)) % count($colors);
    $subjectLower = strtolower($thread->subject);
    $participantsLabel = strtolower($thread->participantNamesLabel());
@endphp
<div id="thread-{{ $thread->id }}"
     x-show="!search ||
         '{{ $subjectLower }}'.includes(search.toLowerCase()) ||
         '{{ $participantsLabel }}'.includes(search.toLowerCase())"
     class="group relative">
    <a href="{{ route($showRoute, $thread) }}?tab={{ $tab }}&filter={{ $filter }}"
       hx-get="{{ route($showRoute, $thread) }}?partial=1&tab={{ $tab }}&filter={{ $filter }}"
       hx-target="#message-conversation-panel"
       hx-push-url="true"
       hx-indicator="#conversation-loading"
       @click="$event.preventDefault(); $el.closest('#message-thread-list')?.querySelectorAll('.thread-active').forEach(t => t.classList.remove('thread-active', 'bg-emerald-50/80', 'border-l-emerald-600')); $el.parentElement.classList.add('thread-active', 'bg-emerald-50/80', 'border-l-emerald-600')"
       class="block px-4 py-3.5 border-l-2 border-transparent hover:bg-emerald-50/50 transition-colors {{ $isUnread ? 'bg-emerald-50/60' : 'bg-white' }}">
        <div class="flex items-start gap-3">
            <span class="inline-flex items-center justify-center w-9 h-9 rounded-full {{ $colors[$colorIdx] }} text-white text-sm font-bold shrink-0">
                {{ $initial }}
            </span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-sm font-semibold {{ $isUnread ? 'text-gray-900' : 'text-gray-700' }} truncate">
                        {{ $thread->subject }}
                    </span>
                    @if ($isUnread && $tab === 'inbox')
                        <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 shrink-0"></span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-gray-500 truncate">
                    {{ $thread->participantNamesLabel() }}
                </p>
                @if ($latest)
                    <div class="mt-1 flex items-center gap-2">
                        <span class="text-xs text-gray-600 truncate">
                            <span class="font-medium">{{ $latest->senderName() }}:</span>
                            {{ \Illuminate\Support\Str::limit($latest->body, 150) }}
                        </span>
                        <span class="text-[11px] text-gray-400 whitespace-nowrap shrink-0">
                            {{ $latest->created_at?->diffForHumans() }}
                        </span>
                    </div>
                @endif
            </div>
        </div>
    </a>

    {{-- Hover actions --}}
    <div class="absolute right-2 top-1/2 -translate-y-1/2 hidden group-hover:flex items-center gap-1 bg-white/90 pl-2 py-1 shadow-sm rounded-lg z-10">
        @if ($tab !== 'archived')
            <form method="POST" hx-post="{{ route($toggleReadRoute, $thread) }}?tab={{ $tab }}&filter={{ $filter }}&page={{ $currentPage }}"
                  hx-target="#message-inbox-column" hx-swap="innerHTML" class="inline"
                  hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
                @csrf
                @if ($isUnread)
                    <button type="submit" class="p-1.5 text-gray-400 hover:text-emerald-600 transition-colors rounded hover:bg-emerald-50" title="{{ __('Mark as read') }}">
                        <i class="bi bi-envelope-open text-sm"></i>
                    </button>
                @else
                    <button type="submit" class="p-1.5 text-gray-400 hover:text-amber-600 transition-colors rounded hover:bg-amber-50" title="{{ __('Mark as unread') }}">
                        <i class="bi bi-envelope text-sm"></i>
                    </button>
                @endif
            </form>
        @endif

        <form method="POST" hx-post="{{ route($toggleArchiveRoute, $thread) }}?tab={{ $tab }}&filter={{ $filter }}&page={{ $currentPage }}"
              hx-target="#message-inbox-column" hx-swap="innerHTML" class="inline"
              hx-headers='{"X-CSRF-TOKEN": "{{ csrf_token() }}"}'>
            @csrf
            @if ($archivedAt)
                <button type="submit" class="p-1.5 text-gray-400 hover:text-violet-600 transition-colors rounded hover:bg-violet-50" title="{{ __('Unarchive') }}">
                    <i class="bi bi-archive-fill text-sm"></i>
                </button>
            @else
                <button type="submit" class="p-1.5 text-gray-400 hover:text-rose-600 transition-colors rounded hover:bg-rose-50" title="{{ __('Archive') }}">
                    <i class="bi bi-archive text-sm"></i>
                </button>
            @endif
        </form>
    </div>
</div>
