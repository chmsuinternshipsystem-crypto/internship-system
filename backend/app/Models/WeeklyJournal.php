<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WeeklyJournal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'deployment_id',
        'week_start_date',
        'week_end_date',
        'week_number',
        'activities',
        'files',
        'supervisor_name',
        'status',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
        'remarks',
    ];

    protected $casts = [
        'week_start_date' => 'date',
        'week_end_date' => 'date',
        'activities' => 'json',
        'files' => 'json',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    protected $appends = [
        'is_late',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isEditable(): bool
    {
        return $this->status === 'draft';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isReviewed(): bool
    {
        return $this->status === 'reviewed';
    }

    public function getIsLateAttribute(): bool
    {
        return $this->submitted_at !== null
            && $this->week_end_date !== null
            && $this->submitted_at->gt($this->week_end_date->copy()->endOfDay());
    }
}
