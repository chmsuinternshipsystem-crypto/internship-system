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

    protected $description = 'Seed Philippine provinces, cities, and barangays from the PSGC API (4 bulk calls)';

    public function handle(): int
    {
        // ── 1. Provinces ──────────────────────────────────────────
        $this->info('Fetching provinces (bulk)...');
        $provinces = Http::timeout(60)->get('https://psgc.gitlab.io/api/provinces/');
        if (! $provinces->successful()) {
            $this->error('Failed to fetch provinces');
            return Command::FAILURE;
        }
        $provinces = $provinces->json();
        $this->info('  -> '.count($provinces).' provinces received');

        $bar = $this->output->createProgressBar(count($provinces));
        $bar->start();
        foreach ($provinces as $prov) {
            PhilippineProvince::firstOrCreate(
                ['code' => $prov['code']],
                ['name' => $prov['name'], 'region' => $prov['region'] ?? '']
            );
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        $provinceMap = PhilippineProvince::pluck('id', 'code');

        // ── 2. Cities ────────────────────────────────────────────
        $this->info('Fetching cities (bulk)...');
        $cities = [];
        $resp = Http::timeout(60)->get('https://psgc.gitlab.io/api/cities/');
        if ($resp->successful()) {
            $cities = $resp->json();
            $this->info('  -> '.count($cities).' cities received');
        } else {
            $this->warn('  Failed to fetch cities');
        }

        // ── 3. Municipalities ────────────────────────────────────
        $this->info('Fetching municipalities (bulk)...');
        $municipalities = [];
        $resp = Http::timeout(60)->get('https://psgc.gitlab.io/api/municipalities/');
        if ($resp->successful()) {
            $municipalities = $resp->json();
            $this->info('  -> '.count($municipalities).' municipalities received');
        } else {
            $this->warn('  Failed to fetch municipalities');
        }

        $allPlaces = array_merge($cities, $municipalities);
        $this->info('  -> total: '.count($allPlaces).' places to insert');

        $placeBar = $this->output->createProgressBar(count($allPlaces));
        $placeBar->start();
        foreach ($allPlaces as $item) {
            $data = [
                'name' => $item['name'],
                'province_id' => ($item['provinceCode'] ?? false)
                    ? ($provinceMap[$item['provinceCode']] ?? null)
                    : null,
                'type' => $item['type'] ?? null,
            ];
            if (! $data['province_id'] && ! $item['provinceCode']) {
                $data['province_id'] = null;
            }

            PhilippineCity::firstOrCreate(
                ['code' => $item['code']],
                $data
            );
            $placeBar->advance();
        }
        $placeBar->finish();
        $this->newLine();

        $cityMap = PhilippineCity::pluck('id', 'code');

        // ── 4. Barangays ─────────────────────────────────────────
        $this->info('Fetching barangays (bulk) — this may take a moment...');
        $barangays = Http::timeout(120)->get('https://psgc.gitlab.io/api/barangays/');
        if (! $barangays->successful()) {
            $this->warn('  Failed to fetch barangays');
            return Command::FAILURE;
        }
        $barangays = $barangays->json();
        $this->info('  -> '.count($barangays).' barangays received');

        $brgyBar = $this->output->createProgressBar(count($barangays));
        $brgyBar->start();
        foreach ($barangays as $brgy) {
            $cityCode = $brgy['cityCode'] ?: ($brgy['municipalityCode'] ?? null);
            if ($cityCode === null || ! isset($cityMap[$cityCode])) {
                $brgyBar->advance();
                continue;
            }

            PhilippineBarangay::firstOrCreate(
                ['code' => $brgy['code']],
                ['name' => $brgy['name'], 'city_id' => $cityMap[$cityCode]]
            );
            $brgyBar->advance();
        }
        $brgyBar->finish();
        $this->newLine();

        $this->info('Done!');
        $this->info('Provinces: '.PhilippineProvince::count());
        $this->info('Cities/Municipalities: '.PhilippineCity::count());
        $this->info('Barangays: '.PhilippineBarangay::count());

        return Command::SUCCESS;
    }
}
