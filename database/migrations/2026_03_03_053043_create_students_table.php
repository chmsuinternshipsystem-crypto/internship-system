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
        Schema::create('students', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Link to the user account that owns this student profile
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Core student identity fields
            // 8-digit student number (e.g. 20230001)
            $table->string('student_number', 8)->unique();
            $table->string('program', 50);              // e.g. BSIS
            $table->unsignedTinyInteger('year_level');  // e.g. 1, 2, 3, 4
            $table->string('section', 20);              // e.g. A, B

            // Contact and internship status information
            // Philippine mobile number (11 digits, e.g. 09XXXXXXXXX)
            $table->string('contact_number', 11)->nullable();
            $table->string('status', 30)->default('not_deployed');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
