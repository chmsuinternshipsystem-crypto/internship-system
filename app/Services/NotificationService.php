<?php

namespace App\Services;

use App\Mail\NotificationMail;
use App\Models\StudentAccount;
use App\Models\User;
use App\Notifications\InAppNotification;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * @param  array<int, int>  $userIds
     * @param  array<string, mixed>  $payload
     */
    public function notifyUsers(array $userIds, array $payload): void
    {
        $userIds = collect($userIds)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        if ($userIds === []) {
            return;
        }

        foreach ($userIds as $userId) {
            $user = User::find($userId);
            if ($user && $user->email) {
                Mail::to($user->email)->queue(new NotificationMail(
                    recipientName: $user->name,
                    subjectText: (string) ($payload['title'] ?? 'Update'),
                    bodyText: (string) ($payload['body'] ?? ''),
                    actionUrl: $payload['action_url'] ?? null,
                    actionLabel: __('View'),
                ));
            }

            if ($user) {
                $user->notify(new InAppNotification(
                    eventType: (string) ($payload['event_type'] ?? 'generic'),
                    title: (string) ($payload['title'] ?? 'Update'),
                    body: (string) ($payload['body'] ?? ''),
                    actionUrl: $payload['action_url'] ?? null,
                    meta: $payload['meta'] ?? [],
                ));
            }
        }
    }

    public function notifyRole(string $role, array $payload): void
    {
        $users = User::query()
            ->where('role', $role)
            ->get();

        foreach ($users as $user) {
            if ($user->email) {
                Mail::to($user->email)->queue(new NotificationMail(
                    recipientName: $user->name,
                    subjectText: (string) ($payload['title'] ?? 'Update'),
                    bodyText: (string) ($payload['body'] ?? ''),
                    actionUrl: $payload['action_url'] ?? null,
                    actionLabel: __('View'),
                ));
            }

            $user->notify(new InAppNotification(
                eventType: (string) ($payload['event_type'] ?? 'generic'),
                title: (string) ($payload['title'] ?? 'Update'),
                body: (string) ($payload['body'] ?? ''),
                actionUrl: $payload['action_url'] ?? null,
                meta: $payload['meta'] ?? [],
            ));
        }
    }

    public function notifyStudentAccount(StudentAccount $studentAccount, array $payload): void
    {
        if ($studentAccount->email) {
            Mail::to($studentAccount->email)->queue(new NotificationMail(
                recipientName: $studentAccount->student?->name ?? $studentAccount->email,
                subjectText: (string) ($payload['title'] ?? 'Update'),
                bodyText: (string) ($payload['body'] ?? ''),
                actionUrl: $payload['action_url'] ?? null,
                actionLabel: __('View'),
            ));
        }

        $studentAccount->notify(new InAppNotification(
            eventType: (string) ($payload['event_type'] ?? 'generic'),
            title: (string) ($payload['title'] ?? 'Update'),
            body: (string) ($payload['body'] ?? ''),
            actionUrl: $payload['action_url'] ?? null,
            meta: $payload['meta'] ?? [],
        ));
    }
}
