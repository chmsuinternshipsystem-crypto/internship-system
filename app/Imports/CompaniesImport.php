<?php

namespace App\Imports;

use App\Models\Company;
use App\Models\CompanyIndustry;
use App\Services\GeocodingService;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class CompaniesImport implements SkipsOnFailure, ToModel, WithChunkReading, WithHeadingRow, WithValidation
{
    use SkipsErrors;

    private int $createdCount = 0;
    private int $updatedCount = 0;
    private int $skippedCount = 0;
    private int $geocodeFailures = 0;
    private array $unmatchedIndustries = [];

    public function chunkSize(): int
    {
        return 100;
    }

    public function model(array $row)
    {
        $name = trim((string) ($row['name'] ?? ''));
        if ($name === '') {
            $this->skippedCount++;
            return null;
        }

        $existing = Company::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])->first();

        $street = trim((string) ($row['street_address'] ?? $row['street address'] ?? ''));
        $barangay = trim((string) ($row['barangay'] ?? ''));
        $city = trim((string) ($row['city_municipality'] ?? $row['city municipality'] ?? ''));

        $geocoded = (new GeocodingService())->geocodeFromParts($street, $barangay, $city);
        if ($geocoded === null) {
            $this->geocodeFailures++;
        }

        $industryId = null;
        $industryName = trim((string) ($row['company_industry'] ?? $row['industry'] ?? ''));
        if ($industryName !== '') {
            $industry = CompanyIndustry::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($industryName)])->first();
            if (! $industry) {
                $industry = CompanyIndustry::whereRaw('LOWER(TRIM(name)) LIKE ?', ['%'.strtolower($industryName).'%'])->first();
            }
            if ($industry) {
                $industryId = $industry->id;
            } else {
                $this->unmatchedIndustries[$industryName] = $industryName;
            }
        }

        $data = [
            'name' => $name,
            'street_address' => $street,
            'barangay' => $barangay,
            'city_municipality' => $city,
            'contact_last_name' => trim((string) ($row['contact_last_name'] ?? $row['contact last name'] ?? '')),
            'contact_first_name' => trim((string) ($row['contact_first_name'] ?? $row['contact first name'] ?? '')),
            'contact_middle_initial' => trim((string) ($row['contact_middle_initial'] ?? $row['contact middle initial'] ?? '')),
            'contact_name_extension' => trim((string) ($row['contact_name_extension'] ?? $row['contact name extension'] ?? '')),
            'contact_email' => trim((string) ($row['contact_email'] ?? $row['contact email'] ?? '')),
            'contact_phone' => preg_replace('/[^\d+]/', '', (string) ($row['contact_phone'] ?? $row['contact phone'] ?? '')),
            'company_industry_id' => $industryId,
            'notes' => trim((string) ($row['notes'] ?? '')),
            'is_active' => true,
            'latitude' => $geocoded['lat'] ?? null,
            'longitude' => $geocoded['lng'] ?? null,
            'geofence_radius_meters' => ! empty($row['geofence_radius_meters'] ?? $row['geofence radius meters'] ?? '')
                ? (int) ($row['geofence_radius_meters'] ?? $row['geofence radius meters'])
                : 100,
        ];

        if ($existing) {
            $existing->fill($data);
            $existing->save();
            $this->updatedCount++;
            return $existing;
        }

        Company::create($data);
        $this->createdCount++;

        return null;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150',
            'street_address' => 'required|string|max:100',
            'barangay' => 'required|string|max:120',
            'city_municipality' => 'required|string|max:120',
            'contact_phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:120',
            'notes' => 'nullable|string|max:100',
            'geofence_radius_meters' => 'nullable|integer|min:10|max:5000',
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $this->skippedCount += count($failures);
    }

    public function getCreatedCount(): int { return $this->createdCount; }
    public function getUpdatedCount(): int { return $this->updatedCount; }
    public function getSkippedCount(): int { return $this->skippedCount; }
    public function getGeocodeFailures(): int { return $this->geocodeFailures; }
    public function getUnmatchedIndustries(): array { return $this->unmatchedIndustries; }
}
