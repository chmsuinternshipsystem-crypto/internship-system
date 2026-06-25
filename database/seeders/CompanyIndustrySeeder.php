<?php

namespace Database\Seeders;

use App\Models\CompanyIndustry;
use Illuminate\Database\Seeder;

class CompanyIndustrySeeder extends Seeder
{
    private const INDUSTRIES = [
        ['name' => 'Information Technology', 'color' => '#2563eb'],
        ['name' => 'BPO / Call Center', 'color' => '#7c3aed'],
        ['name' => 'Banking & Finance', 'color' => '#059669'],
        ['name' => 'Government', 'color' => '#dc2626'],
        ['name' => 'Education', 'color' => '#d97706'],
        ['name' => 'Healthcare', 'color' => '#e11d48'],
        ['name' => 'Retail & E-commerce', 'color' => '#0891b2'],
        ['name' => 'Manufacturing', 'color' => '#4f46e5'],
        ['name' => 'Telecommunications', 'color' => '#0d9488'],
        ['name' => 'Consulting', 'color' => '#9333ea'],
        ['name' => 'Hospitality & Tourism', 'color' => '#ea580c'],
        ['name' => 'Other', 'color' => '#6b7280'],
    ];

    public function run(): void
    {
        foreach (self::INDUSTRIES as $industry) {
            CompanyIndustry::firstOrCreate(
                ['slug' => \Str::slug($industry['name'])],
                [
                    'name' => $industry['name'],
                    'color' => $industry['color'],
                    'is_active' => true,
                ]
            );
        }
    }
}
