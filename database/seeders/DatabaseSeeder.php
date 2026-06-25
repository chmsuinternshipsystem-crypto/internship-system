<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Order: sample users/students/companies/documents/deployments first, then workflow templates
     * and required-document template links (depends on required document rows existing).
     */
    public function run(): void
    {
        $this->call([
            CompanyIndustrySeeder::class,
            SampleDataSeeder::class,
            DocumentWorkflowSeeder::class,
            InstructorFirstLoginSeeder::class,
        ]);
    }
}
