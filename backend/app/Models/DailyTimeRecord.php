<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyTimeRecord extends Model
{
    protected $fillable = [
        'student_id',
        'deployment_id',
        'source',
        'date',
        'am_arrival',
        'am_departure',
        'pm_arrival',
        'pm_departure',
        'time_in',
        'time_out',
        'total_minutes',
        'remarks',
        'tasks',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'am_arrival' => 'datetime:H:i:s',
            'am_departure' => 'datetime:H:i:s',
            'pm_arrival' => 'datetime:H:i:s',
            'pm_departure' => 'datetime:H:i:s',
            'time_in' => 'datetime:H:i:s',
            'time_out' => 'datetime:H:i:s',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function deployment(): BelongsTo
    {
        return $this->belongsTo(Deployment::class);
    }

    public function getTotalHoursAttribute(): ?string
    {
        if ($this->total_minutes === null) {
            return null;
        }

        $hours = intdiv($this->total_minutes, 60);
        $mins = $this->total_minutes % 60;

        return sprintf('%dh %02dm', $hours, $mins);
    }

    public function scopeSchoolBased($query)
    {
        return $query->where('source', 'attendance');
    }

    public function scopeCompanyBased($query)
    {
        return $query->where('source', 'manual');
    }
}