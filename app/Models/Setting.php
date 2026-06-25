<?php

namespace App\Models;

use App\Services\GeofencingService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'campus_lat',
        'campus_lng',
        'campus_radius_meters',
        'student_geofence_radius_meters',
        'geofence_review_buffer_meters',
        'campus_boundary_buffer_meters',
        'maintenance_mode',
        'policy_review_notes',
        'semester',
        'academic_year',
        'attendance_time_in_start',
        'attendance_time_in_end',
        'attendance_time_out_start',
        'attendance_time_out_end',
        'attendance_grace_minutes',
        'attendance_am_time_in_start',
        'attendance_am_time_in_end',
        'attendance_am_time_out_start',
        'attendance_am_time_out_end',
        'attendance_pm_time_in_start',
        'attendance_pm_time_in_end',
        'attendance_pm_time_out_start',
        'attendance_pm_time_out_end',
    ];

    protected function casts(): array
    {
        return [
            'maintenance_mode' => 'boolean',
        ];
    }

    public static function campus(): self
    {
        return static::firstOrCreate([], [
            'campus_lat' => 10.742584903620171,
            'campus_lng' => 122.96932879047095,
            'campus_radius_meters' => 200,
            'student_geofence_radius_meters' => 100,
            'geofence_review_buffer_meters' => 20,
            'campus_boundary_buffer_meters' => 20,
            'maintenance_mode' => false,
            'attendance_time_in_start' => '06:30',
            'attendance_time_in_end' => '09:00',
            'attendance_time_out_start' => '16:30',
            'attendance_time_out_end' => '17:30',
            'attendance_grace_minutes' => 60,
        ]);
    }

    /** Returns campus settings safe for cache serialization (excludes binary geometry column). */
    public static function campusCached(): self
    {
        $key = 'chmsu-talisay-internship-system-cache-campus_settings';

        return cache()->remember($key, 300, function () {
            $setting = static::campus();
            unset($setting->attributes['campus_boundary'], $setting->original['campus_boundary']);

            return $setting;
        });
    }

    /** Student portal "on campus" pass distance (meters), clamped for safety. */
    public function studentGeofencePassMeters(): int
    {
        $r = (int) ($this->student_geofence_radius_meters ?? 100);

        return max(25, min(1000, $r));
    }

    /** Extra meters beyond pass radius treated as "near boundary" for GPS drift. */
    public function studentGeofenceBufferMeters(): int
    {
        $b = (int) ($this->geofence_review_buffer_meters ?? 20);

        return max(0, min(200, $b));
    }

    public function getCampusBoundaryVerticesAttribute(): array
    {
        if ($this->id === null) {
            return [];
        }

        $result = DB::selectOne("SELECT ST_AsText(campus_boundary) AS wkt FROM settings WHERE id = ?", [$this->id]);

        return GeofencingService::polygonWktToVertices($result->wkt ?? null);
    }
}
