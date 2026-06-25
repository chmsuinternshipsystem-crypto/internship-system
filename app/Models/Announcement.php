<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
        'visible_to_role',
        'created_by',
    ];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Staff inbox: general posts plus audience targeted to this user's role.
     */
    public function scopeVisibleToStaffUser(Builder $query, User $user): Builder
    {
        $role = strtolower((string) ($user->role ?? ''));

        return $query->where(function (Builder $q) use ($role) {
            $q->whereNull('visible_to_role')
                ->orWhere('visible_to_role', '')
                ->orWhereRaw('LOWER(visible_to_role) = ?', ['all'])
                ->orWhereRaw('LOWER(visible_to_role) = ?', [$role]);
        });
    }
}
