<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_thread_participants', function (Blueprint $table) {
            $table->index('student_account_id');
            $table->index('user_id');
            $table->index(['thread_id', 'student_account_id']);
            $table->index(['thread_id', 'user_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->index('thread_id');
        });
    }

    public function down(): void
    {
        Schema::table('message_thread_participants', function (Blueprint $table) {
            $table->dropIndex(['student_account_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['thread_id', 'student_account_id']);
            $table->dropIndex(['thread_id', 'user_id']);
        });

        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['thread_id']);
        });
    }
};
