<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->timestamp('time_out_at')->nullable()->after('check_in_at');
            $table->unsignedInteger('total_minutes')->nullable()->after('time_out_at');
            $table->decimal('time_out_latitude', 15, 12)->nullable()->after('longitude');
            $table->decimal('time_out_longitude', 15, 12)->nullable()->after('time_out_latitude');
            $table->unsignedInteger('time_out_accuracy_meters')->nullable()->after('accuracy_meters');
            $table->unsignedInteger('time_out_distance_meters')->nullable()->after('distance_meters');
            $table->string('time_out_geofence_status', 40)->nullable()->after('geofence_status');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'time_out_at',
                'total_minutes',
                'time_out_latitude',
                'time_out_longitude',
                'time_out_accuracy_meters',
                'time_out_distance_meters',
                'time_out_geofence_status',
            ]);
        });
    }
};
