<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE settings ADD campus_boundary GEOMETRY NULL AFTER geofence_review_buffer_meters');
        DB::statement('ALTER TABLE settings ADD campus_boundary_buffer_meters INT UNSIGNED DEFAULT 20 AFTER campus_boundary');
    }

    public function down(): void
    {
        Schema::table('settings', function ($table) {
            $table->dropColumn(['campus_boundary', 'campus_boundary_buffer_meters']);
        });
    }
};
