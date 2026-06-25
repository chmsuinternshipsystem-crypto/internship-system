<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->unsignedInteger('student_geofence_radius_meters')->default(100)->after('campus_radius_meters');
            $table->unsignedInteger('geofence_review_buffer_meters')->default(20)->after('student_geofence_radius_meters');
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'student_geofence_radius_meters',
                'geofence_review_buffer_meters',
            ]);
        });
    }
};
