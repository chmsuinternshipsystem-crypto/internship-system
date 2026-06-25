<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\HasDeleteProtection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasDeleteProtection, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'last_name',
        'first_name',
        'middle_name',
        'name_extension',
        'email',
        'password',
        'role',
        'first_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'first_login' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            if (filled($user->last_name) && filled($user->first_name)) {
                $user->name = self::composeDisplayName(
                    (string) $user->last_name,
                    (string) $user->first_name,
                    $user->middle_name,
                    $user->name_extension,
                );
            }
        });
    }

    public static function composeDisplayName(
        string $lastName,
        string $firstName,
        ?string $middleName,
        ?string $extension,
    ): string {
        $last = trim($lastName);
        $first = trim($firstName);
        $middle = trim((string) ($middleName ?? ''));
        $ext = trim((string) ($extension ?? ''));
        $core = $last.', '.$first.($middle !== '' ? ' '.$middle : '');

        return $ext !== '' ? $core.' '.$ext : $core;
    }

    public function getNameAttribute(?string $value): ?string
    {
        if (filled($this->last_name) && filled($this->first_name)) {
            return self::composeDisplayName(
                (string) $this->last_name,
                (string) $this->first_name,
                $this->middle_name,
                $this->name_extension,
            );
        }

        return $value;
    }

    /**
     * Student profile associated with this user (if the user is a student).
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    public function supervisedStudents()
    {
        return $this->hasMany(Student::class, 'assigned_instructor_id');
    }

    public function deleteBlockers(): array
    {
        $messages = [];

        $supervisedCount = $this->supervisedStudents()->count();
        if ($supervisedCount > 0) {
            $messages[] = __('Cannot delete: you have :count supervised student(s). Reassign them first.', ['count' => $supervisedCount]);
        }

        return $messages;
    }

    public function messageThreadParticipants()
    {
        return $this->hasMany(MessageThreadParticipant::class, 'user_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function isFirstLogin(): bool
    {
        return (bool) $this->first_login;
    }

    public function markFirstLoginComplete(): void
    {
        if ($this->first_login) {
            $this->first_login = false;
            $this->save();
        }
    }
}
