<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('time_outside_window')->default(false)->after('total_minutes');
            $table->boolean('accuracy_suspicious')->default(false)->after('time_outside_window');
            $table->string('ip_address', 45)->nullable()->after('accuracy_suspicious');
            $table->string('user_agent', 500)->nullable()->after('ip_address');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['time_outside_window', 'accuracy_suspicious', 'ip_address', 'user_agent']);
        });
    }
};
