<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weekly_journals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deployment_id')->nullable()->constrained()->nullOnDelete();
            $table->date('week_start_date');
            $table->date('week_end_date');
            $table->unsignedTinyInteger('week_number');

            // Pre-Requirements section
            $table->json('pre_requirements')->nullable();
            $table->enum('pre_status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->text('pre_remarks')->nullable();
            $table->foreignId('pre_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('pre_reviewed_at')->nullable();

            // Monitoring Requirements section
            $table->json('monitoring_requirements')->nullable();
            $table->enum('monitoring_status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->text('monitoring_remarks')->nullable();
            $table->foreignId('monitoring_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('monitoring_reviewed_at')->nullable();

            // Post-Requirements section
            $table->json('post_requirements')->nullable();
            $table->enum('post_status', ['pending', 'submitted', 'approved', 'rejected'])->default('pending');
            $table->text('post_remarks')->nullable();
            $table->foreignId('post_reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('post_reviewed_at')->nullable();

            $table->timestamps();

            $table->unique(['student_id', 'week_number']);
            $table->index(['student_id', 'week_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weekly_journals');
    }
};
