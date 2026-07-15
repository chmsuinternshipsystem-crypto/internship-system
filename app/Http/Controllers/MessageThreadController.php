<?php

namespace App\Http\Controllers;

use App\Mail\NotificationMail;
use App\Models\Message;
use App\Models\MessageThread;
use App\Models\MessageThreadParticipant;
use App\Models\StudentAccount;
use App\Models\User;
use App\Services\NotificationService;
use App\Support\InternshipRoles;
use App\Support\MessageActor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MessageThreadController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}
    public function index(Request $request)
    {
        if ($request->query('empty') === '1' && $request->header('HX-Request') !== null) {
            return view('messages.partials.empty-state');
        }

        $data = $this->buildInboxColumnData($request);

        if ($request->header('HX-Request') !== null) {
            return view('messages.partials.inbox-column', $data);
        }

        return view('messages.index', $data);
    }

    public function create(Request $request)
    {
        $actor = MessageActor::fromRequest($request);

        $allowedRoles = $actor['student_account_id']
            ? ['instructor', 'chairperson']
            : InternshipRoles::messageParticipantRolesForStaffSender();

        $recipients = User::query()
            ->whereIn('role', $allowedRoles)
            ->when($actor['user_id'], fn ($q) => $q->where('id', '!=', $actor['user_id']))
            ->orderBy('role')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role'])
            ->groupBy('role');

        $preSelectedStudentIds = [];
        if ($actor['user_id']) {
            $ids = $request->query('student_account_ids', $request->query('student_account_id', []));
            $preSelectedStudentIds = array_map('intval', (array) $ids);
        }

        $studentAccountsForMessaging = collect();
        if ($actor['user_id']) {
            $studentAccountsForMessaging = StudentAccount::query()
                ->where('is_active', true)
                ->whereHas('student')
                ->join('students', 'students.id', '=', 'student_accounts.student_id')
                ->select('student_accounts.id', 'students.student_number', 'students.name', 'students.section')
                ->selectRaw('EXISTS (SELECT 1 FROM deployments WHERE deployments.student_id = students.id AND deployments.status IN ("active","completed")) as has_deployment')
                ->selectRaw('EXISTS (SELECT 1 FROM student_documents WHERE student_documents.student_id = students.id AND student_documents.status IN ("pending","returned")) as has_pending_documents')
                ->orderBy('students.section')
                ->orderBy('students.student_number')
                ->get()
                ->groupBy(fn ($sa) => $sa->section ?: __('No Section'));
        }

        $viewData = [
            'recipients' => $recipients,
            'studentAccountsForMessaging' => $studentAccountsForMessaging,
            'isStudentPortal' => MessageActor::isStudentPortal($request),
            'preSelectedStudentIds' => $preSelectedStudentIds,
        ];

        if ($request->header('HX-Request')) {
            return view('messages.partials.create-form', array_merge($viewData, ['isHtmxPartial' => true]));
        }

        return view('messages.create', $viewData);
    }

    public function store(Request $request)
    {
        $actor = MessageActor::fromRequest($request);

        if ($actor['student_account_id']) {
            $data = $request->validate([
                'subject' => ['required', 'string', 'max:255'],
                'participant_ids' => ['required', 'array', 'min:1'],
                'participant_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id'],
                'body' => ['required', 'string', 'max:3000'],
            ]);
        } else {
            $data = $request->validate([
                'subject' => ['required', 'string', 'max:255'],
                'participant_ids' => ['nullable', 'array'],
                'participant_ids.*' => ['integer', 'distinct', 'exists:users,id'],
                'student_account_ids' => ['nullable', 'array'],
                'student_account_ids.*' => [
                    'integer',
                    Rule::exists('student_accounts', 'id')->where(function ($q): void {
                        // Rule::exists uses query builder (not Eloquent), so use column checks/subquery instead of whereHas.
                        $q->where('is_active', true)
                            ->whereNotNull('student_id');
                    }),
                ],
                'body' => ['required', 'string', 'max:3000'],
            ]);
        }

        $data['subject'] = trim(strip_tags($data['subject']));
        $data['body'] = trim(strip_tags($data['body']));

        $allowedRoles = $actor['student_account_id']
            ? ['instructor', 'chairperson']
            : InternshipRoles::messageParticipantRolesForStaffSender();

        $participantIds = array_values(array_filter(array_map('intval', $data['participant_ids'] ?? [])));

        $recipientIds = $participantIds === []
            ? []
            : User::query()
                ->whereIn('id', $participantIds)
                ->whereIn('role', $allowedRoles)
                ->when($actor['user_id'], fn ($q) => $q->where('id', '!=', $actor['user_id']))
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

        $studentAccountIds = array_values(array_filter(array_map('intval', $data['student_account_ids'] ?? [])));
        $studentAccounts = collect();
        if (! empty($studentAccountIds) && $actor['user_id']) {
            $studentAccounts = StudentAccount::query()
                ->whereIn('id', $studentAccountIds)
                ->where('is_active', true)
                ->whereHas('student')
                ->with('student:id,name')
                ->get()
                ->keyBy('id');
        }

        if (count($recipientIds) === 0 && $studentAccounts->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['participant_ids' => __('Choose at least one staff recipient and/or a student to include.')]);
        }

        // Prevent self-messaging
        if ($actor['user_id'] && in_array($actor['user_id'], $participantIds, true)) {
            return back()
                ->withInput()
                ->withErrors(['participant_ids' => __('You cannot send a message to yourself.')]);
        }

        // Check for existing thread with the exact same participant set
        $existingThread = $this->findExistingThread($recipientIds, $studentAccounts, $actor['user_id'], $actor['student_account_id']);
        if ($existingThread) {
            $existingThread->messages()->create([
                'sender_id' => $actor['user_id'],
                'sender_student_account_id' => $actor['student_account_id'],
                'body' => $data['body'],
            ]);

            $existingThread->touch();

            $showRoute = MessageActor::isStudentPortal($request) ? 'student.messages.show' : 'messages.show';
            $redirectUrl = route($showRoute, $existingThread);
            if ($request->header('HX-Request')) {
                $redirectUrl .= '?partial=1';
            }

            return redirect($redirectUrl)
                ->with('status', __('Message added to existing conversation.'));
        }

        $thread = DB::transaction(function () use ($data, $recipientIds, $actor, $studentAccounts) {
            $thread = MessageThread::create([
                'subject' => $data['subject'],
                'created_by' => $actor['user_id'],
                'created_by_student_account_id' => $actor['student_account_id'],
            ]);

            if ($actor['user_id']) {
                MessageThreadParticipant::create([
                    'thread_id' => $thread->id,
                    'user_id' => $actor['user_id'],
                    'student_account_id' => null,
                    'last_read_at' => now(),
                ]);
            } else {
                MessageThreadParticipant::create([
                    'thread_id' => $thread->id,
                    'user_id' => null,
                    'student_account_id' => $actor['student_account_id'],
                    'last_read_at' => now(),
                ]);
            }

            foreach ($recipientIds as $rid) {
                MessageThreadParticipant::create([
                    'thread_id' => $thread->id,
                    'user_id' => $rid,
                    'student_account_id' => null,
                    'last_read_at' => null,
                ]);
            }

            foreach ($studentAccounts as $saRecord) {
                MessageThreadParticipant::create([
                    'thread_id' => $thread->id,
                    'user_id' => null,
                    'student_account_id' => (int) $saRecord->id,
                    'last_read_at' => null,
                ]);
            }

            if ($actor['user_id']) {
                $thread->messages()->create([
                    'sender_id' => $actor['user_id'],
                    'sender_student_account_id' => null,
                    'body' => $data['body'],
                ]);
            } else {
                $thread->messages()->create([
                    'sender_id' => null,
                    'sender_student_account_id' => $actor['student_account_id'],
                    'body' => $data['body'],
                ]);
            }

            return $thread;
        });

        $showRoute = MessageActor::isStudentPortal($request) ? 'student.messages.show' : 'messages.show';

        // Notify all staff participants (except sender) via email
        $staffRecipients = User::query()
            ->whereIn('id', $recipientIds)
            ->get();
        $actorName = $actor['user_id']
            ? (auth()->user()?->name ?? __('A staff member'))
            : ($actor['student_account_id'] ? __('A student') : __('Someone'));
        foreach ($staffRecipients as $recipient) {
            if ($recipient->email) {
                Mail::to($recipient->email)->queue(new NotificationMail(
                    recipientName: $recipient->name,
                    subjectText: __('New message: :subject', ['subject' => $data['subject']]),
                    bodyText: __(':actor sent you a new message in ":subject".', [
                        'actor' => $actorName,
                        'subject' => $data['subject'],
                    ]),
                    actionUrl: route($showRoute, $thread, absolute: false),
                    actionLabel: __('View Thread'),
                ));
            }
        }

        if (! empty($recipientIds)) {
            $this->notificationService->notifyUsers($recipientIds, [
                'event_type' => 'message.new',
                'title' => __('New message: :subject', ['subject' => $data['subject']]),
                'body' => __(':actor sent you a new message in ":subject".', [
                    'actor' => $actorName,
                    'subject' => $data['subject'],
                ]),
                'action_url' => route($showRoute, $thread, absolute: false),
                'meta' => [
                    'thread_id' => (int) $thread->id,
                    'subject' => $data['subject'],
                ],
            ]);
        }

        // Notify student participants (if any) via email
        foreach ($studentAccounts as $saRecord) {
            if ($saRecord->email) {
                $studentName = $saRecord->student?->name ?? __('Student');
                Mail::to($saRecord->email)->queue(new NotificationMail(
                    recipientName: $studentName,
                    subjectText: __('New message: :subject', ['subject' => $data['subject']]),
                    bodyText: __(':actor sent you a new message in ":subject".', [
                        'actor' => $actorName,
                        'subject' => $data['subject'],
                    ]),
                    actionUrl: route('student.messages.show', $thread, absolute: false),
                    actionLabel: __('View Thread'),
                ));
            }
        }

        foreach ($studentAccounts as $saRecord) {
            $this->notificationService->notifyStudentAccount($saRecord, [
                'event_type' => 'message.new',
                'title' => __('New message: :subject', ['subject' => $data['subject']]),
                'body' => __(':actor sent you a new message in ":subject".', [
                    'actor' => $actorName,
                    'subject' => $data['subject'],
                ]),
                'action_url' => route('student.messages.show', $thread, absolute: false),
                'meta' => [
                    'thread_id' => (int) $thread->id,
                    'subject' => $data['subject'],
                ],
            ]);
        }

        $redirectUrl = route($showRoute, $thread);
        if ($request->header('HX-Request')) {
            $redirectUrl .= '?partial=1&toast=' . urlencode(__('Message sent.'));
        }

        return redirect($redirectUrl)
            ->with('status', __('Message thread created.'));
    }

    public function show(Request $request, MessageThread $message)
    {
        $actor = MessageActor::fromRequest($request);
        $this->ensureParticipant($message, $actor);

        $isStudentPortal = MessageActor::isStudentPortal($request);

        if ($request->query('partial') && $request->header('HX-Request') !== null) {
            $view = $this->renderConversationPartial($request, $message, $actor, $isStudentPortal);
            $headers = ['HX-Trigger' => json_encode(['refresh-inbox' => time()])];
            $toast = $request->query('toast');
            if ($toast) {
                $headers['X-Toast-Message'] = urldecode($toast);
            }
            return response($view, 200, $headers);
        }

        $message->load([
            'participants.user:id,name,email,role',
            'participants.studentAccount.student:id,name,student_number',
            'messages' => fn ($q) => $q->with([
                'senderUser:id,name,role',
                'senderStudentAccount.student:id,name',
            ])->orderBy('created_at'),
        ]);

        $this->touchReadReceipt($message, $actor);

        return view('messages.show', [
            'thread' => $message,
            'isStudentPortal' => $isStudentPortal,
        ]);
    }

    public function reply(Request $request, MessageThread $message)
    {
        $actor = MessageActor::fromRequest($request);
        $this->ensureParticipant($message, $actor);

        $isHtmx = $request->header('HX-Request') !== null;
        $isStudentPortal = MessageActor::isStudentPortal($request);

        // Rate-limit replies to 10 per minute
        $throttleKey = 'message-reply:'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $msg = __('Too many replies. Please wait :seconds seconds before sending another.', ['seconds' => $seconds]);
            if ($isHtmx) {
                return $this->renderConversationPartial($request, $message, $actor, $isStudentPortal)
                    ->withErrors(['body' => $msg]);
            }
            return back()->withErrors(['body' => $msg]);
        }
        RateLimiter::hit($throttleKey, 60);

        // Manual validation for HTMX compatibility
        $validator = Validator::make($request->all(), [
            'body' => ['required', 'string', 'max:3000'],
        ]);

        if ($validator->fails()) {
            if ($isHtmx) {
                $request->flash();
                return $this->renderConversationPartial($request, $message, $actor, $isStudentPortal)
                    ->withErrors($validator);
            }
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();
        $data['body'] = trim(strip_tags($data['body']));

        if ($actor['user_id']) {
            $message->messages()->create([
                'sender_id' => $actor['user_id'],
                'sender_student_account_id' => null,
                'body' => $data['body'],
            ]);
        } else {
            $message->messages()->create([
                'sender_id' => null,
                'sender_student_account_id' => $actor['student_account_id'],
                'body' => $data['body'],
            ]);
        }

        $this->touchReadReceipt($message, $actor);

        $showRoute = $isStudentPortal ? 'student.messages.show' : 'messages.show';

        // Notify all other participants via email
        $participants = MessageThreadParticipant::query()
            ->where('thread_id', $message->id)
            ->where(function ($q) use ($actor) {
                if ($actor['user_id']) {
                    $q->where('user_id', '!=', $actor['user_id']);
                } else {
                    $q->where('student_account_id', '!=', $actor['student_account_id']);
                }
            })
            ->with(['user', 'studentAccount.student'])
            ->get();

        $actorName = $actor['user_id']
            ? (auth()->user()?->name ?? __('A staff member'))
            : ($actor['student_account_id'] ? __('A student') : __('Someone'));

        foreach ($participants as $participant) {
            if ($participant->user && $participant->user->email) {
                Mail::to($participant->user->email)->queue(new NotificationMail(
                    recipientName: $participant->user->name,
                    subjectText: __('New reply in ":subject"', ['subject' => $message->subject]),
                    bodyText: __(':actor replied to a conversation you are part of.', ['actor' => $actorName]),
                    actionUrl: route($showRoute, $message, absolute: false),
                    actionLabel: __('View Thread'),
                ));
            } elseif ($participant->studentAccount && $participant->studentAccount->email) {
                $studentName = $participant->studentAccount->student?->name ?? __('Student');
                Mail::to($participant->studentAccount->email)->queue(new NotificationMail(
                    recipientName: $studentName,
                    subjectText: __('New reply in ":subject"', ['subject' => $message->subject]),
                    bodyText: __(':actor replied to a conversation you are part of.', ['actor' => $actorName]),
                    actionUrl: route('student.messages.show', $message, absolute: false),
                    actionLabel: __('View Thread'),
                ));
            }
        }

        $staffParticipantIds = $participants->pluck('user_id')->filter()->values()->all();
        if (! empty($staffParticipantIds)) {
            $this->notificationService->notifyUsers($staffParticipantIds, [
                'event_type' => 'message.reply',
                'title' => __('New reply in ":subject"', ['subject' => $message->subject]),
                'body' => __(':actor replied to a conversation you are part of.', ['actor' => $actorName]),
                'action_url' => route($showRoute, $message, absolute: false),
                'meta' => [
                    'thread_id' => (int) $message->id,
                    'subject' => $message->subject,
                ],
            ]);
        }

        foreach ($participants as $participant) {
            if ($participant->studentAccount) {
                $this->notificationService->notifyStudentAccount($participant->studentAccount, [
                    'event_type' => 'message.reply',
                    'title' => __('New reply in ":subject"', ['subject' => $message->subject]),
                    'body' => __(':actor replied to a conversation you are part of.', ['actor' => $actorName]),
                    'action_url' => route('student.messages.show', $message, absolute: false),
                    'meta' => [
                        'thread_id' => (int) $message->id,
                        'subject' => $message->subject,
                    ],
                ]);
            }
        }

        if ($isHtmx) {
            $view = $this->renderConversationPartial($request, $message, $actor, $isStudentPortal);
            return response($view, 200, [
                'HX-Trigger' => json_encode(['refresh-inbox' => time()]),
                'X-Toast-Message' => __('Reply sent.'),
            ]);
        }

        return redirect()
            ->route($showRoute, $message)
            ->with('status', __('Reply sent.'));
    }

    public function toggleRead(Request $request, MessageThread $message)
    {
        $actor = MessageActor::fromRequest($request);
        $this->ensureParticipant($message, $actor);

        $participant = MessageThreadParticipant::query()
            ->where('thread_id', $message->id)
            ->where(function ($q) use ($actor) {
                if ($actor['user_id']) {
                    $q->where('user_id', $actor['user_id']);
                } else {
                    $q->where('student_account_id', $actor['student_account_id']);
                }
            })
            ->firstOrFail();

        $participant->update([
            'last_read_at' => $participant->last_read_at ? null : now(),
        ]);

        $data = $this->buildInboxColumnData($request);

        return response(view('messages.partials.inbox-column', $data))
            ->header('X-Toast-Message', $participant->last_read_at ? __('Marked as read.') : __('Marked as unread.'));
    }

    public function toggleArchive(Request $request, MessageThread $message)
    {
        $actor = MessageActor::fromRequest($request);
        $this->ensureParticipant($message, $actor);

        $participant = MessageThreadParticipant::query()
            ->where('thread_id', $message->id)
            ->where(function ($q) use ($actor) {
                if ($actor['user_id']) {
                    $q->where('user_id', $actor['user_id']);
                } else {
                    $q->where('student_account_id', $actor['student_account_id']);
                }
            })
            ->firstOrFail();

        $participant->update([
            'archived_at' => $participant->archived_at ? null : now(),
        ]);

        $data = $this->buildInboxColumnData($request);

        return response(view('messages.partials.inbox-column', $data))
            ->header('X-Toast-Message', $participant->archived_at ? __('Archived.') : __('Unarchived.'));
    }

    /**
     * @param  array{user_id: int|null, student_account_id: int|null}  $actor
     */
    private function renderConversationPartial(Request $request, MessageThread $message, array $actor, bool $isStudentPortal): \Illuminate\Contracts\View\View
    {
        $message->load([
            'participants.user:id,name,email,role',
            'participants.studentAccount.student:id,name,student_number',
            'messages' => fn ($q) => $q->with([
                'senderUser:id,name,role',
                'senderStudentAccount.student:id,name',
            ])->orderBy('created_at'),
        ]);

        $this->touchReadReceipt($message, $actor);

        return view('messages.partials.conversation', [
            'thread' => $message,
            'isStudentPortal' => $isStudentPortal,
        ]);
    }

    private function buildInboxColumnData(Request $request): array
    {
        $actor = MessageActor::fromRequest($request);
        $tab = $request->query('tab', 'inbox');
        $filter = $request->query('filter', 'all');
        $showArchived = $tab === 'archived';
        $showSent = $tab === 'sent';

        $threads = MessageThread::query()
            ->whereHas('participants', function ($q) use ($actor, $showArchived, $filter) {
                if ($actor['user_id']) {
                    $q->where('user_id', $actor['user_id']);
                } else {
                    $q->where('student_account_id', $actor['student_account_id']);
                }
                if ($showArchived) {
                    $q->whereNotNull('archived_at');
                } else {
                    $q->whereNull('archived_at');
                }
                if ($filter === 'unread') {
                    $q->where(function ($q) {
                        $q->whereNull('last_read_at')
                          ->orWhereRaw('last_read_at < (SELECT MAX(created_at) FROM messages WHERE messages.thread_id = message_thread_participants.thread_id)');
                    });
                } elseif ($filter === 'read') {
                    $q->whereNotNull('last_read_at')
                      ->whereRaw('last_read_at >= (SELECT MAX(created_at) FROM messages WHERE messages.thread_id = message_thread_participants.thread_id)');
                }
            })
            ->with([
                'participants.user:id,name,role',
                'participants.studentAccount.student:id,name',
                'latestMessage.senderUser:id,name',
                'latestMessage.senderStudentAccount.student:id,name',
            ])
            ->when($showSent, function ($q) use ($actor) {
                $q->whereHas('latestMessage', function ($q) use ($actor) {
                    if ($actor['user_id']) {
                        $q->where('sender_id', $actor['user_id']);
                    } else {
                        $q->where('sender_student_account_id', $actor['student_account_id']);
                    }
                });
            })
            ->orderByDesc(
                Message::select('created_at')
                    ->whereColumn('messages.thread_id', 'message_threads.id')
                    ->latest()
                    ->limit(1)
            )
            ->paginate(5);

        $unreadMessageCount = MessageThreadParticipant::query()
            ->when($actor['user_id'], fn ($q) => $q->where('user_id', $actor['user_id']))
            ->when($actor['student_account_id'], fn ($q) => $q->where('student_account_id', $actor['student_account_id']))
            ->whereNull('archived_at')
            ->where(function ($q) {
                $q->whereNull('last_read_at')
                  ->orWhereRaw('last_read_at < (SELECT MAX(created_at) FROM messages WHERE messages.thread_id = message_thread_participants.thread_id)');
            })
            ->count();
        // The unread count excludes threads where the actor sent the latest message,
        // but for the sidebar badge this approximation is close enough. The inbox-column
        // partial handles per-thread unread state on its own.

        return [
            'threads' => $threads,
            'isStudentPortal' => MessageActor::isStudentPortal($request),
            'tab' => $tab,
            'filter' => $filter,
            'unreadMessageCount' => $unreadMessageCount,
        ];
    }

    /**
     * @param  array{user_id: int|null, student_account_id: int|null}  $actor
     */
    private function renderThreadItem(Request $request, MessageThread $thread, array $actor, bool $isStudentPortal, string $tab): \Illuminate\View\View
    {
        $thread->load([
            'participants.user:id,name,role',
            'participants.studentAccount.student:id,name',
            'latestMessage.senderUser:id,name',
            'latestMessage.senderStudentAccount.student:id,name',
        ]);

        $actorUserId = $actor['user_id'];
        $actorStudentAccountId = $actor['student_account_id'];

        return view('messages.partials.thread-item', compact(
            'thread',
            'actorUserId',
            'actorStudentAccountId',
            'isStudentPortal',
            'tab',
        ));
    }

    /**
     * @param  array{user_id: int|null, student_account_id: int|null}  $actor
     */
    private function ensureParticipant(MessageThread $thread, array $actor): void
    {
        $q = MessageThreadParticipant::query()->where('thread_id', $thread->id);
        if ($actor['user_id']) {
            $q->where('user_id', $actor['user_id']);
        } elseif ($actor['student_account_id']) {
            $q->where('student_account_id', $actor['student_account_id']);
        } else {
            abort(403);
        }

        abort_unless($q->exists(), 403);
    }

    /**
     * @param  array{user_id: int|null, student_account_id: int|null}  $actor
     */
    private function touchReadReceipt(MessageThread $thread, array $actor): void
    {
        $q = MessageThreadParticipant::query()->where('thread_id', $thread->id);
        if ($actor['user_id']) {
            $q->where('user_id', $actor['user_id']);
        } else {
            $q->where('student_account_id', $actor['student_account_id']);
        }
        $q->update([
            'last_read_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Find an existing thread with the exact same set of participants (excluding the sender).
     *
     * @param  array<int, int>  $recipientUserIds
     * @param  \Illuminate\Support\Collection  $studentAccounts
     * @return \App\Models\MessageThread|null
     */
    private function findExistingThread(array $recipientUserIds, \Illuminate\Support\Collection $studentAccounts, ?int $actorUserId, ?int $actorStudentAccountId): ?MessageThread
    {
        $expectedCount = count($recipientUserIds) + $studentAccounts->count() + 1;

        $threadIds = MessageThreadParticipant::query()
            ->when($actorUserId, fn ($q) => $q->where('user_id', $actorUserId), fn ($q) => $q->where('student_account_id', $actorStudentAccountId))
            ->pluck('thread_id');

        foreach ($threadIds as $threadId) {
            $participants = MessageThreadParticipant::query()->where('thread_id', $threadId)->get();

            if ($participants->count() !== $expectedCount) {
                continue;
            }

            $matched = collect($recipientUserIds)->every(fn ($id) => $participants->contains('user_id', $id))
                && $studentAccounts->keys()->every(fn ($id) => $participants->contains('student_account_id', $id))
                && $participants->contains(function ($p) use ($actorUserId, $actorStudentAccountId) {
                    return $actorUserId ? (int) $p->user_id === $actorUserId : (int) $p->student_account_id === $actorStudentAccountId;
                });

            if ($matched) {
                return MessageThread::find($threadId);
            }
        }

        return null;
    }
}
