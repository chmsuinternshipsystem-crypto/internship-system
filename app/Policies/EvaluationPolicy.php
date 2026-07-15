<?php

namespace App\Policies;

use App\Models\Evaluation;
use App\Models\User;
use App\Support\InternshipRoles;

class EvaluationPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, InternshipRoles::institutionalMonitoringRoles(), true);
    }

    public function view(User $user, Evaluation $evaluation): bool
    {
        return $this->viewAny($user);
    }

    public function manage(User $user): bool
    {
        return in_array($user->role, InternshipRoles::operationalManagerRoles(), true);
    }
}
