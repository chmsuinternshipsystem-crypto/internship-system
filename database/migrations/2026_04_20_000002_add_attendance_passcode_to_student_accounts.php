<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_accounts', function (Blueprint $table): void {
            if (! Schema::hasColumn('student_accounts', 'attendance_passcode')) {
                $table->string('attendance_passcode', 20)->nullable()->after('password');
            }
            if (! Schema::hasColumn('student_accounts', 'attendance_passcode_generated_at')) {
                $table->timestamp('attendance_passcode_generated_at')->nullable()->after('attendance_passcode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_accounts', function (Blueprint $table): void {
            if (Schema::hasColumn('student_accounts', 'attendance_passcode_generated_at')) {
                $table->dropColumn('attendance_passcode_generated_at');
            }
            if (Schema::hasColumn('student_accounts', 'attendance_passcode')) {
                $table->dropColumn('attendance_passcode');
            }
        });
    }
};
