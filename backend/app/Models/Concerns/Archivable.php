<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Archivable
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull($this->getTable().'.archived_at');
    }

    public function scopeArchivedOnly(Builder $query): Builder
    {
        return $query->whereNotNull($this->getTable().'.archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function archive(string $reason = null): bool
    {
        return $this->update([
            'archived_at' => now(),
            'archive_reason' => $reason,
        ]);
    }

    public function restore(): bool
    {
        return $this->update([
            'archived_at' => null,
            'archive_reason' => null,
        ]);
    }
}
