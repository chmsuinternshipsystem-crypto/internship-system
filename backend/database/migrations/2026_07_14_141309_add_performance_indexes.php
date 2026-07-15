<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_documents', function (Blueprint $table): void {
            if (! Schema::hasIndex('student_documents', 'sd_req_status_student')) {
                $table->index(['required_document_id', 'status', 'student_id'], 'sd_req_status_student');
            }
        });

        Schema::table('attendances', function (Blueprint $table): void {
            if (! Schema::hasIndex('attendances', 'att_student_checked')) {
                $table->index(['student_id', 'check_in_at'], 'att_student_checked');
            }
        });

        Schema::table('weekly_journals', function (Blueprint $table): void {
            if (! Schema::hasIndex('weekly_journals', 'wj_student_week_status')) {
                $table->index(['student_id', 'week_end_date', 'status'], 'wj_student_week_status');
            }
        });

        Schema::table('daily_time_records', function (Blueprint $table): void {
            if (! Schema::hasIndex('daily_time_records', 'dtr_student_date')) {
                $table->index(['student_id', 'date'], 'dtr_student_date');
            }
        });

        Schema::table('deployments', function (Blueprint $table): void {
            if (! Schema::hasIndex('deployments', 'dep_student_status_start')) {
                $table->index(['student_id', 'status', 'start_date'], 'dep_student_status_start');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table): void {
            $table->dropIndex('sd_req_status_student');
        });
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropIndex('att_student_checked');
        });
        Schema::table('weekly_journals', function (Blueprint $table): void {
            $table->dropIndex('wj_student_week_status');
        });
        Schema::table('daily_time_records', function (Blueprint $table): void {
            $table->dropIndex('dtr_student_date');
        });
        Schema::table('deployments', function (Blueprint $table): void {
            $table->dropIndex('dep_student_status_start');
        });
    }
};
