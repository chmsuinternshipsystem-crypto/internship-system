<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $users = [
            ['name' => 'Dean User', 'email' => 'dean@chmsu.edu.ph', 'role' => 'dean'],
        ];

        foreach ($users as $user) {
            $exists = DB::table('users')->where('email', $user['email'])->exists();
            if ($exists) {
                continue;
            }

            DB::table('users')->insert([
                'name' => $user['name'],
                'email' => $user['email'],
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role' => $user['role'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('email', [
                'dean@chmsu.edu.ph',
            ])
            ->delete();
    }
};
