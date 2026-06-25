<?php

/**
 * Extends messaging for student portal accounts. Same batch date as add_unique_indexes migration; runs second (filename order).
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow student portal accounts (student_accounts) to create threads, participate, and send messages.
     */
    public function up(): void
    {
        Schema::table('message_threads', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('message_threads', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreignId('created_by_student_account_id')->nullable()->after('created_by')->constrained('student_accounts')->nullOnDelete();
        });

        Schema::table('message_thread_participants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('message_thread_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('student_account_id')->nullable()->after('user_id')->constrained('student_accounts')->cascadeOnDelete();
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('sender_id')->nullable()->change();
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('sender_student_account_id')->nullable()->after('sender_id')->constrained('student_accounts')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_student_account_id']);
            $table->dropColumn('sender_student_account_id');
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['sender_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('sender_id')->nullable(false)->change();
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('message_thread_participants', function (Blueprint $table) {
            $table->dropForeign(['student_account_id']);
            $table->dropColumn('student_account_id');
        });

        Schema::table('message_thread_participants', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('message_thread_participants', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::table('message_threads', function (Blueprint $table) {
            $table->dropForeign(['created_by_student_account_id']);
            $table->dropColumn('created_by_student_account_id');
        });

        Schema::table('message_threads', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
        });

        Schema::table('message_threads', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
