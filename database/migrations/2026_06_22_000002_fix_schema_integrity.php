<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix WARN-3: monthly_dttrs.month should be unsigned (1-12, not -128 to 127)
        Schema::table('monthly_dttrs', function (Blueprint $table) {
            $table->unsignedTinyInteger('month')->change();
        });

        // Fix WARN-20: certificates.uploaded_by cascadeOnDelete → nullOnDelete
        // prevents data loss when an instructor account is deleted.
        // Must drop the existing FK first, then re-create with nullOnDelete.
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
        });
        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('uploaded_by')->nullable()->change();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
        });

        // Add missing indexes on commonly filtered columns
        Schema::table('deployments', function (Blueprint $table) {
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->index('resolution_status');
            $table->index('geofence_status');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->index('evaluation_type');
            $table->index('score');
            $table->index('evaluated_at');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->index('issued_at');
            $table->index('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_dttrs', function (Blueprint $table) {
            $table->tinyInteger('month')->change();
        });

        // Revert FK to cascadeOnDelete (column stays nullable to avoid data-loss errors)
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropForeign(['uploaded_by']);
            $table->foreign('uploaded_by')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('deployments', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['start_date', 'end_date']);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropIndex(['resolution_status']);
            $table->dropIndex(['geofence_status']);
        });

        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndex(['evaluation_type']);
            $table->dropIndex(['score']);
            $table->dropIndex(['evaluated_at']);
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropIndex(['issued_at']);
            $table->dropIndex(['verified_at']);
        });
    }
};
