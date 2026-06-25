<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->time('am_arrival')->nullable()->after('date');
            $table->time('am_departure')->nullable()->after('am_arrival');
            $table->time('pm_arrival')->nullable()->after('am_departure');
            $table->time('pm_departure')->nullable()->after('pm_arrival');
            $table->text('tasks')->nullable()->after('remarks');
        });
    }

    public function down(): void
    {
        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->dropColumn(['am_arrival', 'am_departure', 'pm_arrival', 'pm_departure', 'tasks']);
        });
    }
};
