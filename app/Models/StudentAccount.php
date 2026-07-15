<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class StudentAccount extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'student_id',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'attendance_passcode',
        'attendance_passcode_generated_at',
        'first_login',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'first_login' => 'boolean',
            'last_login_at' => 'datetime',
            'attendance_passcode_generated_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function ensureAttendancePasscode(): string
    {
        if (filled($this->attendance_passcode)) {
            return (string) $this->attendance_passcode;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->forceFill([
            'attendance_passcode' => $code,
            'attendance_passcode_generated_at' => now(),
        ])->save();

        return $code;
    }

    /**
     * Check if this is the first login and clear the flag.
     */
    public function isFirstLogin(): bool
    {
        return (bool) $this->first_login;
    }

    /**
     * Mark first login as complete.
     */
    public function markFirstLoginComplete(): void
    {
        if ($this->first_login) {
            $this->first_login = false;
            $this->saveQuietly();
        }
    }
}
