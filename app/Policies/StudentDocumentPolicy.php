<?php

namespace App\Policies;

use App\Models\StudentDocument;
use App\Models\User;
use App\Support\InternshipRoles;

class StudentDocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, InternshipRoles::staffPortalReadRoles(), true);
    }

    public function manageCompliance(User $user): bool
    {
        return in_array($user->role, InternshipRoles::operationalManagerRoles(), true);
    }

    public function actWorkflow(User $user, StudentDocument $studentDocument): bool
    {
        if (! in_array($user->role, InternshipRoles::staffPortalReadRoles(), true)) {
            return false;
        }

        if (! $studentDocument->current_holder_role) {
            return false;
        }

        return $studentDocument->current_holder_role === $user->role;
    }

    public function download(User $user, StudentDocument $studentDocument): bool
    {
        return in_array($user->role, InternshipRoles::staffPortalReadRoles(), true);
    }
}
