<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeocodingService
{
    private const NOMINATIM_URL = 'https://nominatim.openstreetmap.org/search';

    private static ?float $lastRequestTime = null;

    public function geocode(string $address): ?array
    {
        $this->rateLimit();

        $response = Http::withHeaders([
            'User-Agent' => config('app.geocoding_user_agent', 'InternshipSystem/1.0'),
            'Accept-Language' => 'en',
        ])->timeout(15)->retry(2, 1000)->get(self::NOMINATIM_URL, [
            'q' => $address,
            'format' => 'json',
            'limit' => 1,
        ]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();
        if (empty($data) || ! isset($data[0]['lat'], $data[0]['lon'])) {
            return null;
        }

        return [
            'lat' => (float) $data[0]['lat'],
            'lng' => (float) $data[0]['lon'],
        ];
    }

    public function geocodeFromParts(?string $street, ?string $barangay, ?string $city): ?array
    {
        $parts = array_filter([
            trim((string) $street),
            trim((string) $barangay),
            trim((string) $city),
        ]);

        if ($parts === []) {
            return null;
        }

        $address = implode(', ', $parts);

        return $this->geocode($address);
    }

    private function rateLimit(): void
    {
        if (self::$lastRequestTime !== null) {
            $elapsed = microtime(true) - self::$lastRequestTime;
            if ($elapsed < 1.0) {
                usleep((int) ((1.0 - $elapsed) * 1_000_000));
            }
        }
        self::$lastRequestTime = microtime(true);
    }
}
