<?php

namespace App\Models;

use App\Support\HasDeleteProtection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    /** @use HasDeleteProtection<Company> */
    use HasDeleteProtection;

    use HasFactory;

    /**
     * Fields that can be mass-assigned when creating/updating a company.
     */
    protected $fillable = [
        'name',
        'address',
        'street_address',
        'barangay',
        'city_municipality',
        'province_id',
        'city_id',
        'barangay_id',
        'company_industry_id',
        'contact_person',
        'contact_last_name',
        'contact_first_name',
        'contact_middle_initial',
        'contact_name_extension',
        'contact_email',
        'contact_phone',
        'is_active',
        'notes',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'geofencing_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'geofencing_enabled' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geofence_radius_meters' => 'integer',
        ];
    }

    public function deleteBlockers(): array
    {
        $count = $this->deployments()->count();
        if ($count > 0) {
            return [__('Cannot delete: :name has :count deployment(s). Remove deployments first.', [
                'name' => $this->name,
                'count' => $count,
            ])];
        }

        return [];
    }

    protected static function booted(): void
    {
        static::saving(function (Company $company): void {
            $company->address = $company->full_address;
            $company->contact_person = $company->contact_person_name;
        });
    }

    /**
     * Deployments associated with this company.
     */
    public function deployments()
    {
        return $this->hasMany(Deployment::class);
    }

    /**
     * The industry classification for this company.
     */
    public function industry()
    {
        return $this->belongsTo(CompanyIndustry::class, 'company_industry_id');
    }

    public function getFullAddressAttribute(): ?string
    {
        $parts = array_filter([
            trim((string) ($this->street_address ?? '')),
            trim((string) ($this->barangay ?? '')),
            trim((string) ($this->city_municipality ?? '')),
        ]);

        if ($parts !== []) {
            return implode(', ', $parts);
        }

        $legacy = trim((string) ($this->attributes['address'] ?? ''));

        return $legacy !== '' ? $legacy : null;
    }

    public function getContactPersonNameAttribute(): ?string
    {
        $last = trim((string) ($this->contact_last_name ?? ''));
        $first = trim((string) ($this->contact_first_name ?? ''));
        $middle = trim((string) ($this->contact_middle_initial ?? ''));
        $ext = trim((string) ($this->contact_name_extension ?? ''));

        if ($last !== '' || $first !== '') {
            $core = trim($last.($first !== '' ? ', '.$first : ''));
            if ($middle !== '') {
                $core .= ' '.$middle;
            }
            if ($ext !== '') {
                $core .= ' '.$ext;
            }

            return trim($core);
        }

        $legacy = trim((string) ($this->attributes['contact_person'] ?? ''));

        return $legacy !== '' ? $legacy : null;
    }
}
