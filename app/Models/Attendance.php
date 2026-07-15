<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Attendance extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'check_in_at',
        'time_out_at',
        'am_check_in',
        'am_check_out',
        'pm_check_in',
        'pm_check_out',
        'total_minutes',
        'latitude',
        'longitude',
        'time_out_latitude',
        'time_out_longitude',
        'accuracy_meters',
        'time_out_accuracy_meters',
        'distance_meters',
        'time_out_distance_meters',
        'geofence_status',
        'time_out_geofence_status',
        'review_required',
        'resolution_status',
        'resolved_by',
        'resolved_at',
        'resolution_note',
        'is_within_campus',
        'location_unavailable',
        'time_outside_window',
        'accuracy_suspicious',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'check_in_at' => 'datetime',
            'time_out_at' => 'datetime',
            'am_check_in' => 'datetime',
            'am_check_out' => 'datetime',
            'pm_check_in' => 'datetime',
            'pm_check_out' => 'datetime',
            'total_minutes' => 'integer',
            'review_required' => 'boolean',
            'is_within_campus' => 'boolean',
            'location_unavailable' => 'boolean',
            'resolved_at' => 'datetime',
        ];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function resolver()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }
}
