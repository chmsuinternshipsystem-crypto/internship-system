<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->unique('student_id');
        });

        // Backfill student accounts from legacy linked student users (role=student)
        DB::statement("
            INSERT INTO student_accounts (student_id, email, password, is_active, created_at, updated_at)
            SELECT s.id, u.email, u.password, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
            FROM students s
            INNER JOIN users u ON u.id = s.user_id
            WHERE u.role = 'student'
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('student_accounts');
    }
};
