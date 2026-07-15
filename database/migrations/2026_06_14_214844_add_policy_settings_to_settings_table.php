<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'maintenance_mode')) {
                $table->boolean('maintenance_mode')->default(false)->after('geofence_review_buffer_meters');
            }
            if (! Schema::hasColumn('settings', 'policy_review_notes')) {
                $table->text('policy_review_notes')->nullable()->after('maintenance_mode');
            }
            if (! Schema::hasColumn('settings', 'semester')) {
                $table->string('semester', 50)->nullable()->after('policy_review_notes');
            }
            if (! Schema::hasColumn('settings', 'academic_year')) {
                $table->string('academic_year', 20)->nullable()->after('semester');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'maintenance_mode',
                'policy_review_notes',
                'semester',
                'academic_year',
            ]);
        });
    }
};
