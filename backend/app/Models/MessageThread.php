<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageThread extends Model
{
    use HasFactory;

    protected $fillable = [
        'subject',
        'context_type',
        'context_id',
        'created_by',
        'created_by_student_account_id',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creatorStudentAccount(): BelongsTo
    {
        return $this->belongsTo(StudentAccount::class, 'created_by_student_account_id');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(MessageThreadParticipant::class, 'thread_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'thread_id');
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class, 'thread_id')->latestOfMany();
    }

    /**
     * Comma-separated participant names for list headers.
     */
    public function participantNamesLabel(): string
    {
        if (! $this->relationLoaded('participants')) {
            $this->load(['participants.user:id,name', 'participants.studentAccount.student:id,name']);
        }

        return $this->participants->map(fn (MessageThreadParticipant $p) => $p->displayName())->implode(', ');
    }
}
