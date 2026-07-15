<?php

/**
 * Unique (student_id, company_id, start_date). Same date prefix as message_threads migration; runs first alphabetically.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->unique(['student_id', 'company_id', 'start_date'], 'deployments_student_company_start_unique');
        });
    }

    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table) {
            $table->dropUnique('deployments_student_company_start_unique');
        });
    }
};
