@php
    $isStudentPortal = $isStudentPortal ?? false;
    $replyRoute = $isStudentPortal ? 'student.messages.reply' : 'messages.reply';
    $indexRoute = $isStudentPortal ? 'student.messages.index' : 'messages.index';
    $actorUserId = auth()->id();
    $actorStudentAccountId = session('student_account_id');
    $isPartial = request()->query('partial') === '1';
@endphp

<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden flex flex-col">
    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between shrink-0"
         x-data="{ showInfo: false }">
        <div class="flex items-center gap-2 min-w-0 flex-1">
            @if ($isPartial)
                <button type="button"
                        hx-get="{{ route($indexRoute) }}?empty=1"
                        hx-target="#message-conversation-panel"
                        hx-swap="innerHTML"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors shrink-0"
                        title="{{ __('Back to Inbox') }}">
                    <i class="bi bi-arrow-left text-base"></i>
                </button>
            @else
                <a href="{{ route($indexRoute) }}"
                   class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors shrink-0"
                   title="{{ __('Back to Inbox') }}">
                    <i class="bi bi-arrow-left text-base"></i>
                </a>
            @endif
            <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $thread->subject }}</h3>
        </div>
        <div class="flex items-center gap-1">
            <div class="relative">
                <button type="button" @click="showInfo = !showInfo"
                        class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                        title="{{ __('Participants') }}">
                    <i class="bi bi-info-circle text-base"></i>
                </button>
                <div x-show="showInfo" x-cloak @click.away="showInfo = false"
                     class="absolute right-0 top-full mt-1 z-50 w-64 bg-white rounded-xl border border-gray-200 shadow-lg py-2">
                    <p class="px-4 pb-1.5 text-[11px] font-semibold uppercase tracking-wider text-gray-500 border-b border-gray-100 mb-1">
                        {{ __('Participants') }} ({{ $thread->participants->count() }})
                    </p>
                    @foreach($thread->participants as $participant)
                        @php
                            if ($participant->user) {
                                $pName = $participant->user->name;
                                $pRole = $participant->user->role;
                                $pEmail = $participant->user->email;
                            } elseif ($participant->studentAccount) {
                                $pName = $participant->studentAccount->student?->name ?? __('Student');
                                $pRole = 'student';
                                $pEmail = $participant->studentAccount->email ?? '';
                            } else { continue; }
                            $roleLabel = match($pRole) {
                                'instructor' => __('Instructor'),
                                'chairperson' => __('Chairperson'),
                                'dean' => __('Dean'),
                                'student' => __('Student'),
                                default => $pRole,
                            };
                            $roleColor = match($pRole) {
                                'instructor' => 'bg-emerald-100 text-emerald-700',
                                'chairperson' => 'bg-blue-100 text-blue-700',
                                'dean' => 'bg-purple-100 text-purple-700',
                                'student' => 'bg-amber-100 text-amber-700',
                                default => 'bg-gray-100 text-gray-600',
                            };
                        @endphp
                        <div class="px-4 py-1.5 flex items-center gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600 uppercase">
                                {{ substr($pName, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $pName }}</p>
                                <p class="text-[11px] text-gray-500 truncate">{{ $pEmail }}</p>
                            </div>
                            <span class="shrink-0 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold {{ $roleColor }}">
                                {{ $roleLabel }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Messages viewport --}}
    <div id="threadMessageViewport"
         class="flex-1 overflow-y-auto bg-gray-50/70 p-4 sm:p-6 min-h-0 max-h-[calc(100vh-320px)]"
         x-init="setTimeout(() => $el.scrollTop = $el.scrollHeight, 50)">
        <div class="min-h-full flex flex-col justify-end space-y-5">
            @forelse ($thread->messages as $message)
                @php
                    $mine = $actorUserId
                        ? (int) $message->sender_id === (int) $actorUserId
                        : ($actorStudentAccountId && (int) ($message->sender_student_account_id ?? 0) === (int) $actorStudentAccountId);

                    $senderColor = $mine
                        ? 'bg-emerald-600 text-white rounded-2xl rounded-br-sm'
                        : 'bg-white text-gray-900 rounded-2xl rounded-bl-sm border border-gray-200 shadow-sm';
                @endphp
                <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] sm:max-w-[70%]">
                        <div class="flex items-center gap-2 mb-1.5 {{ $mine ? 'justify-end' : 'justify-start' }}">
                            <span class="text-xs font-semibold {{ $mine ? 'text-emerald-700' : 'text-gray-700' }}">
                                {{ $message->senderName() }}
                            </span>
                            <span class="text-[11px] text-gray-400">{{ $message->created_at?->diffForHumans() }}</span>
                        </div>
                        <div class="{{ $senderColor }} px-4 py-3 text-sm leading-relaxed">
                            <p class="whitespace-pre-line break-words">{{ $message->body }}</p>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-0.5 {{ $mine ? 'text-right' : 'text-left' }}">
                            {{ $message->created_at?->format('M d, Y h:i A') }}
                        </p>
                    </div>
                </div>
            @empty
                <div class="flex items-center justify-center">
                    <p class="text-sm text-gray-500">{{ __('No messages yet.') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Reply form --}}
    <form method="POST" action="{{ route($replyRoute, $thread) }}"
          hx-post="{{ route($replyRoute, $thread) }}"
          hx-target="#message-conversation-panel"
          hx-swap="innerHTML"
          class="border-t border-gray-200 p-4 sm:p-6 bg-white shrink-0">
        @csrf
        <div class="flex items-start gap-3">
            <div class="flex-1">
                <textarea id="body" name="body" rows="3" maxlength="3000" required
                    class="block w-full h-[76px] rounded-xl border-2 border-gray-200 bg-white px-4 py-3 text-sm text-gray-900 resize-none overflow-y-auto focus:border-emerald-400 focus:ring-emerald-400/20 transition-colors"
                    placeholder="{{ __('Type your reply...') }}">{{ old('body') }}</textarea>
                <div class="flex items-center justify-between mt-1.5">
                    <x-input-error class="mt-1" :messages="$errors->get('body')" />
                    <p class="text-xs text-gray-400 ml-auto">{{ __('Max 3000 characters') }}</p>
                </div>
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition-colors shrink-0 mt-1">
                <i class="bi bi-send-fill"></i>
                <span class="hidden sm:inline">{{ __('Send') }}</span>
            </button>
        </div>
    </form>

    <script>
        (function () {
            const viewport = document.getElementById('threadMessageViewport');
            if (viewport) {
                viewport.scrollTop = viewport.scrollHeight;
            }
        })();
    </script>
</div>
