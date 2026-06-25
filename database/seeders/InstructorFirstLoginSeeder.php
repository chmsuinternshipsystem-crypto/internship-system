<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class InstructorFirstLoginSeeder extends Seeder
{
    public function run(): void
    {
        $updated = User::where('role', 'instructor')->update(['first_login' => true]);

        $this->command->info("Marked {$updated} instructor(s) with first_login = true.");
    }
}
