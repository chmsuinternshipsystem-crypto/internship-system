<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->time('attendance_am_time_in_start')->default('06:30')->after('attendance_grace_minutes');
            $table->time('attendance_am_time_in_end')->default('08:30')->after('attendance_am_time_in_start');
            $table->time('attendance_am_time_out_start')->default('11:30')->after('attendance_am_time_in_end');
            $table->time('attendance_am_time_out_end')->default('12:30')->after('attendance_am_time_out_start');
            $table->time('attendance_pm_time_in_start')->default('13:00')->after('attendance_am_time_out_end');
            $table->time('attendance_pm_time_in_end')->default('13:30')->after('attendance_pm_time_in_start');
            $table->time('attendance_pm_time_out_start')->default('16:30')->after('attendance_pm_time_in_end');
            $table->time('attendance_pm_time_out_end')->default('17:30')->after('attendance_pm_time_out_start');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table): void {
            $table->dropColumn([
                'attendance_am_time_in_start',
                'attendance_am_time_in_end',
                'attendance_am_time_out_start',
                'attendance_am_time_out_end',
                'attendance_pm_time_in_start',
                'attendance_pm_time_in_end',
                'attendance_pm_time_out_start',
                'attendance_pm_time_out_end',
            ]);
        });
    }
};
