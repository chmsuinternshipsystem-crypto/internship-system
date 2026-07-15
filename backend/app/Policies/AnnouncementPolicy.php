<?php

namespace App\Policies;

use App\Models\Announcement;
use App\Models\User;
use App\Support\InternshipRoles;

class AnnouncementPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, array_merge(['student'], InternshipRoles::staffEmailRoles()), true);
    }

    public function view(User $user, Announcement $announcement): bool
    {
        if (! $this->viewAny($user)) {
            return false;
        }

        $target = strtolower((string) ($announcement->visible_to_role ?? ''));
        if ($target === '' || $target === 'all') {
            return true;
        }

        return $target === strtolower((string) $user->role);
    }

    public function manage(User $user): bool
    {
        return in_array($user->role, InternshipRoles::announcementManagerRoles(), true);
    }
}
