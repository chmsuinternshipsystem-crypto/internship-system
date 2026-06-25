<?php

use App\Models\Student;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('name', 255)->nullable()->after('user_id');
        });

        // Backfill name from linked user for existing students
        Student::with('user')->whereNotNull('user_id')->each(function (Student $student) {
            $student->update(['name' => $student->user?->name ?? '']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->string('name', 255)->nullable(false)->change();
        });

        // Make user_id nullable: drop the FK, alter the column, re-add FK with nullOnDelete
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};
