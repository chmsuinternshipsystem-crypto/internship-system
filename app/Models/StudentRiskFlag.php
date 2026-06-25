<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRiskFlag extends Model
{
    protected $fillable = [
        'student_id',
        'type',
        'severity',
        'message',
        'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function scopeActive($query)
    {
        return $query->whereNull('resolved_at');
    }
}
