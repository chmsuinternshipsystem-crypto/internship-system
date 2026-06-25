<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->string('geofence_status', 40)->default('location_unavailable')->after('distance_meters');
            $table->boolean('review_required')->default(false)->after('geofence_status');
            $table->string('resolution_status', 30)->default('pending')->after('review_required');
            $table->foreignId('resolved_by')->nullable()->after('resolution_status')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            $table->string('resolution_note', 255)->nullable()->after('resolved_at');
            $table->unsignedInteger('accuracy_meters')->nullable()->after('longitude');
        });

        DB::table('attendances')
            ->where('location_unavailable', true)
            ->update([
                'geofence_status' => 'location_unavailable',
                'review_required' => true,
                'resolution_status' => 'pending',
            ]);

        DB::table('attendances')
            ->where('location_unavailable', false)
            ->where('is_within_campus', true)
            ->update([
                'geofence_status' => 'inside_pass',
                'review_required' => false,
                'resolution_status' => 'not_needed',
            ]);

        DB::table('attendances')
            ->where('location_unavailable', false)
            ->where('is_within_campus', false)
            ->update([
                'geofence_status' => 'outside_flagged',
                'review_required' => true,
                'resolution_status' => 'pending',
            ]);
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropColumn([
                'geofence_status',
                'review_required',
                'resolution_status',
                'resolved_at',
                'resolution_note',
                'accuracy_meters',
            ]);
        });
    }
};
