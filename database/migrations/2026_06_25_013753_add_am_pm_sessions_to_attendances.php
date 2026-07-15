<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dateTime('am_check_in')->nullable()->after('time_out_at');
            $table->dateTime('am_check_out')->nullable()->after('am_check_in');
            $table->dateTime('pm_check_in')->nullable()->after('am_check_out');
            $table->dateTime('pm_check_out')->nullable()->after('pm_check_in');
        });

        DB::statement('UPDATE attendances SET am_check_in = check_in_at, pm_check_out = time_out_at');
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn(['am_check_in', 'am_check_out', 'pm_check_in', 'pm_check_out']);
        });
    }
};
