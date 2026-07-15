<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Certificate extends Model
{
    protected $fillable = [
        'student_id',
        'deployment_id',
        'uploaded_by',
        'type',
        'title',
        'description',
        'file_path',
        'issued_at',
        'status',
        'verified_by',
        'verified_at',
        'verification_notes',
        'reference_number',
        'generated_at',
        'is_auto_generated',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'verified_at' => 'datetime',
        'generated_at' => 'datetime',
        'is_auto_generated' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    protected static function booted(): void
    {
        static::deleting(function (Certificate $cert): void {
            if ($cert->file_path) {
                Storage::disk('public')->delete($cert->file_path);
            }
        });
    }
}
