<?php

use App\Models\Setting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->time('attendance_time_in_start')->default('06:30')->after('geofence_review_buffer_meters');
            $table->time('attendance_time_in_end')->default('09:00')->after('attendance_time_in_start');
            $table->time('attendance_time_out_start')->default('16:30')->after('attendance_time_in_end');
            $table->time('attendance_time_out_end')->default('17:30')->after('attendance_time_out_start');
            $table->unsignedSmallInteger('attendance_grace_minutes')->default(60)->after('attendance_time_out_end');
        });

        // Set defaults for existing row
        Setting::query()->update([
            'attendance_time_in_start' => '06:30',
            'attendance_time_in_end' => '09:00',
            'attendance_time_out_start' => '16:30',
            'attendance_time_out_end' => '17:30',
            'attendance_grace_minutes' => 60,
        ]);
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'attendance_time_in_start',
                'attendance_time_in_end',
                'attendance_time_out_start',
                'attendance_time_out_end',
                'attendance_grace_minutes',
            ]);
        });
    }
};
