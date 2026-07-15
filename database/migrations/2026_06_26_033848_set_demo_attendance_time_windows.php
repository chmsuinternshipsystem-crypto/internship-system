<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $setting = Setting::first();
        if ($setting) {
            $setting->forceFill([
                'attendance_am_time_in_start' => '00:00',
                'attendance_am_time_in_end' => '23:59',
                'attendance_am_time_out_start' => '00:00',
                'attendance_am_time_out_end' => '23:59',
                'attendance_pm_time_in_start' => '00:00',
                'attendance_pm_time_in_end' => '23:59',
                'attendance_pm_time_out_start' => '00:00',
                'attendance_pm_time_out_end' => '23:59',
                'attendance_grace_minutes' => 0,
            ])->save();
        }
    }

    public function down(): void
    {
        // Revert defaults — no-op; user can reconfigure via settings UI.
    }
};
