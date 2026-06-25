<?php

namespace App\Support;

trait HasDeleteProtection
{
    /**
     * @return array<int, string> Empty if safe to delete, or error messages if blocked.
     */
    public function deleteBlockers(): array
    {
        return [];
    }

    public function canDelete(): true|string
    {
        $blockers = $this->deleteBlockers();

        if ($blockers === []) {
            return true;
        }

        return implode(' ', $blockers);
    }
}
