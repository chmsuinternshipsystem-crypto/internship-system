<?php

namespace App\Console\Commands;

use App\Models\PhilippineBarangay;
use App\Models\PhilippineCity;
use App\Models\PhilippineProvince;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SeedPhilippineAddresses extends Command
{
    protected $signature = 'psgc:seed';

    protected $description = 'Seed Philippine provinces, cities, and barangays from the PSGC API';

    public function handle(): int
    {
        $this->info('Fetching provinces...');
        $provinces = Http::get('https://psgc.gitlab.io/api/provinces/')->json();
        $bar = $this->output->createProgressBar(count($provinces));
        $bar->start();

        foreach ($provinces as $prov) {
            $province = PhilippineProvince::firstOrCreate(
                ['code' => $prov['code']],
                [
                    'name' => $prov['name'],
                    'region' => $prov['region'] ?? '',
                ]
            );

            $this->fetchCities($province, $prov['code']);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Done!');
        $this->info('Provinces: '.PhilippineProvince::count());
        $this->info('Cities/Municipalities: '.PhilippineCity::count());
        $this->info('Barangays: '.PhilippineBarangay::count());

        return Command::SUCCESS;
    }

    private function fetchCities(PhilippineProvince $province, string $provinceCode): void
    {
        // Fetch both cities and municipalities — both go into philippine_cities table
        $endpoints = [
            "https://psgc.gitlab.io/api/provinces/{$provinceCode}/cities/",
            "https://psgc.gitlab.io/api/provinces/{$provinceCode}/municipalities/",
        ];

        foreach ($endpoints as $url) {
            try {
                $response = Http::timeout(30)->get($url);
                if (! $response->successful()) continue;
                $items = $response->json();
            } catch (\Throwable $e) {
                $this->warn("  Skipping {$url}: {$e->getMessage()}");
                continue;
            }

            foreach ($items as $item) {
                $cityModel = PhilippineCity::firstOrCreate(
                    ['code' => $item['code']],
                    [
                        'name' => $item['name'],
                        'province_id' => $province->id,
                        'type' => $item['type'] ?? null,
                    ]
                );

                $this->fetchBarangays($cityModel, $item['code']);
            }
        }
    }

    private function fetchBarangays(PhilippineCity $city, string $cityCode): void
    {
        $barangays = null;
        $endpoints = [
            "https://psgc.gitlab.io/api/cities/{$cityCode}/barangays/",
            "https://psgc.gitlab.io/api/municipalities/{$cityCode}/barangays/",
        ];

        foreach ($endpoints as $url) {
            try {
                $response = Http::timeout(15)->get($url);
                if ($response->successful()) {
                    $barangays = $response->json();
                    break;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        if ($barangays === null) return;

        foreach ($barangays as $brgy) {
            PhilippineBarangay::firstOrCreate(
                ['code' => $brgy['code']],
                [
                    'name' => $brgy['name'],
                    'city_id' => $city->id,
                ]
            );
        }
    }
}
