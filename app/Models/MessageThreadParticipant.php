<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageThreadParticipant extends Model
{
    use HasFactory;

    protected $table = 'message_thread_participants';

    protected $fillable = [
        'thread_id',
        'user_id',
        'student_account_id',
        'last_read_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'last_read_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function studentAccount(): BelongsTo
    {
        return $this->belongsTo(StudentAccount::class, 'student_account_id');
    }

    public function displayName(): string
    {
        if ($this->user_id) {
            return (string) ($this->user?->name ?? '?');
        }

        return (string) ($this->studentAccount?->student?->name ?? __('Student'));
    }
}
