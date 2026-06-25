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
        Schema::create('student_documents', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Links to student and required document
            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('required_document_id')
                ->constrained()
                ->cascadeOnDelete();

            // Status for this document for this student
            $table->string('status', 20)->default('Pending'); // Submitted / Pending / Missing

            // Optional file path if we later support uploads
            $table->string('file_path')->nullable();

            // When the document was submitted/recorded
            $table->timestamp('submitted_at')->nullable();

            // Who verified the document (null until verified)
            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Ensure one row per student per required document
            $table->unique(['student_id', 'required_document_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_documents');
    }
};
