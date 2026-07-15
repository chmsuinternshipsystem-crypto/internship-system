<?php

namespace App\Policies;

use App\Models\User;
use App\Support\InternshipRoles;

class RemarkPolicy
{
    public function create(User $user): bool
    {
        return in_array($user->role, InternshipRoles::operationalManagerRoles(), true);
    }
}
