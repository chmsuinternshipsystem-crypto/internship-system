<?php

namespace App\Http\Controllers;

use App\Models\StudentAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $staffUser = auth()->user();
        $notifiable = null;
        $isStaff = true;

        if ($staffUser) {
            $notifiable = $staffUser;
        } elseif (session()->has('student_account_id')) {
            $notifiable = StudentAccount::find((int) session('student_account_id'));
            $isStaff = false;
        }

        if (! $notifiable) {
            abort(403);
        }

        $notifications = $notifiable->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.index', [
            'notifications' => $notifications,
            'isStaff' => $isStaff,
            'unreadCount' => $notifiable->unreadNotifications()->count(),
        ]);
    }

    public function unreadCount(): JsonResponse
    {
        $notifiable = $this->resolveNotifiable();
        if (! $notifiable) {
            return response()->json(['count' => 0]);
        }

        return response()->json([
            'count' => $notifiable->unreadNotifications()->count(),
        ]);
    }

    public function recent(): JsonResponse
    {
        $notifiable = $this->resolveNotifiable();
        if (! $notifiable) {
            return response()->json(['notifications' => []]);
        }

        $notifications = $notifiable->notifications()
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn (DatabaseNotification $n) => [
                'id' => $n->id,
                'type' => $n->data['event_type'] ?? 'generic',
                'title' => $n->data['title'] ?? '',
                'body' => $n->data['body'] ?? '',
                'action_url' => $n->data['action_url'] ?? null,
                'read' => $n->read(),
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $notifiable->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(string $id): JsonResponse
    {
        $notifiable = $this->resolveNotifiable();
        if (! $notifiable) {
            abort(403);
        }

        $notification = $notifiable->notifications()->where('id', $id)->first();
        if (! $notification) {
            abort(404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $notifiable = $this->resolveNotifiable();
        if (! $notifiable) {
            abort(403);
        }

        $notifiable->unreadNotifications()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('status', __('All notifications marked as read.'));
    }

    private function resolveNotifiable(): User|StudentAccount|null
    {
        $staffUser = auth()->user();
        if ($staffUser) {
            return $staffUser;
        }

        if (session()->has('student_account_id')) {
            return StudentAccount::find((int) session('student_account_id'));
        }

        return null;
    }
}
