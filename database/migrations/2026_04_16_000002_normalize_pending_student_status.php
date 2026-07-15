<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('students')
            ->where('status', 'not_deployed')
            ->update(['status' => 'pending']);

        // MySQL/MariaDB only: column default (SQLite has no equivalent ALTER).
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE students ALTER status SET DEFAULT 'pending'");
        }
    }

    public function down(): void
    {
        DB::table('students')
            ->where('status', 'pending')
            ->update(['status' => 'not_deployed']);

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE students ALTER status SET DEFAULT 'not_deployed'");
        }
    }
};
