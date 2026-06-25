<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $missingStudents = DB::table('students')
            ->leftJoin('student_accounts', 'student_accounts.student_id', '=', 'students.id')
            ->whereNull('student_accounts.id')
            ->select('students.id', 'students.student_number')
            ->get();

        foreach ($missingStudents as $student) {
            DB::table('student_accounts')->insert([
                'student_id' => (int) $student->id,
                'email' => null,
                'password' => Hash::make((string) $student->student_number),
                'is_active' => true,
                'last_login_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Keep existing student_accounts intact on rollback.
    }
};
