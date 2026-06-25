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
        Schema::create('remarks', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Student the remark is about
            $table->foreignId('student_id')
                ->constrained()
                ->cascadeOnDelete();

            // Author of the remark (e.g. coordinator)
            $table->foreignId('author_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Remark text
            $table->text('content');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remarks');
    }
};
