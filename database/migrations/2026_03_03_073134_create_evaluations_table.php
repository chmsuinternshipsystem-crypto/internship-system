<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('evaluations', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Student being evaluated
            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            // Evaluator (typically coordinator or company supervisor)
            $table->foreignId('evaluator_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Overall numeric score (e.g., 1–100 or 1–5 scale)
            $table->unsignedTinyInteger('score');

            // Free-text comments about performance
            $table->text('comments')->nullable();

            // When the evaluation was made
            $table->date('evaluated_at');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
