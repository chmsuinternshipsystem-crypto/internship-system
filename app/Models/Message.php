<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id',
        'sender_id',
        'sender_student_account_id',
        'body',
    ];

    public function thread()
    {
        return $this->belongsTo(MessageThread::class, 'thread_id');
    }

    public function senderUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function senderStudentAccount(): BelongsTo
    {
        return $this->belongsTo(StudentAccount::class, 'sender_student_account_id');
    }

    public function senderName(): string
    {
        if ($this->sender_id) {
            return (string) ($this->senderUser?->name ?? '?');
        }

        return (string) ($this->senderStudentAccount?->student?->name ?? __('Student'));
    }
}
