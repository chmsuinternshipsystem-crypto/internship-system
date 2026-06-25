<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_accounts', function (Blueprint $table): void {
            if (! Schema::hasColumn('student_accounts', 'first_login')) {
                $table->boolean('first_login')->default(true)->after('attendance_passcode_generated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_accounts', function (Blueprint $table): void {
            if (Schema::hasColumn('student_accounts', 'first_login')) {
                $table->dropColumn('first_login');
            }
        });
    }
};
