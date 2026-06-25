<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        // Fix legacy rows where check_in_at was overwritten during time-out updates.
        DB::statement('
            UPDATE attendances
            SET check_in_at = DATE_SUB(time_out_at, INTERVAL total_minutes MINUTE)
            WHERE time_out_at IS NOT NULL
              AND total_minutes IS NOT NULL
              AND total_minutes > 0
              AND check_in_at >= time_out_at
        ');

        // Prevent automatic check_in_at mutation on row updates (MySQL TIMESTAMP behavior).
        DB::statement('ALTER TABLE attendances MODIFY check_in_at DATETIME NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        DB::statement('ALTER TABLE attendances MODIFY check_in_at TIMESTAMP NOT NULL');
    }
};
