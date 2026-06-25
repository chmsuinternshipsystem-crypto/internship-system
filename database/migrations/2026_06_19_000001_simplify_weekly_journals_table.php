<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('weekly_journals', function (Blueprint $table) {
            $table->json('activities')->nullable()->after('week_number');
            $table->json('files')->nullable()->after('activities');
            $table->string('supervisor_name')->nullable()->after('files');
            $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft')->after('supervisor_name');
            $table->foreignId('reviewed_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->text('remarks')->nullable()->after('reviewed_at');
        });

        Schema::table('weekly_journals', function (Blueprint $table) {
            $table->dropForeign(['pre_reviewed_by']);
            $table->dropForeign(['monitoring_reviewed_by']);
            $table->dropForeign(['post_reviewed_by']);
        });

        Schema::table('weekly_journals', function (Blueprint $table) {
            $table->dropColumn([
                'pre_requirements', 'pre_status', 'pre_remarks', 'pre_reviewed_by', 'pre_reviewed_at',
                'monitoring_requirements', 'monitoring_status', 'monitoring_remarks', 'monitoring_reviewed_by', 'monitoring_reviewed_at',
                'post_requirements', 'post_status', 'post_remarks', 'post_reviewed_by', 'post_reviewed_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('weekly_journals', function (Blueprint $table) {
            $table->json('pre_requirements')->nullable();
            $table->enum('pre_status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->text('pre_remarks')->nullable();
            $table->foreignId('pre_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('pre_reviewed_at')->nullable();
            $table->json('monitoring_requirements')->nullable();
            $table->enum('monitoring_status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->text('monitoring_remarks')->nullable();
            $table->foreignId('monitoring_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('monitoring_reviewed_at')->nullable();
            $table->json('post_requirements')->nullable();
            $table->enum('post_status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->text('post_remarks')->nullable();
            $table->foreignId('post_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('post_reviewed_at')->nullable();
        });

        Schema::table('weekly_journals', function (Blueprint $table) {
            $table->dropForeign(['reviewed_by']);
        });

        Schema::table('weekly_journals', function (Blueprint $table) {
            $table->dropColumn(['activities', 'files', 'supervisor_name', 'status', 'reviewed_by', 'reviewed_at', 'remarks']);
        });
    }
};
