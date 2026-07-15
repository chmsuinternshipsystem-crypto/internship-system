<?php

namespace App\Http\Requests;

use App\Support\PhilippineContactNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    private const NAME_PATTERN = "/^(?!.*\\d)\\p{L}+(?:[ .'-]\\p{L}+)*$/u";

    private const ADDRESS_PATTERN = "/^[\\p{L}\\p{N}][\\p{L}\\p{N} .,#'\\/&()@!:;-]*$/u";

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Role checks are enforced by route middleware (role:employer,instructor,chairperson).
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
                'regex:/^(?=.*\\p{L})[\\p{L}\\p{N} .,&()\\/@#!\':;"-]+$/u',
                Rule::unique('companies')
                    ->ignore($this->route('company'))
                    ->where(function ($query) {
                        $normalized = strtolower(trim((string) $this->input('name')));

                        return $query->whereRaw('LOWER(TRIM(name)) = ?', [$normalized]);
                    }),
            ],
            'street_address' => ['required', 'string', 'max:100', 'regex:'.self::ADDRESS_PATTERN],
            'barangay' => ['required', 'string', 'max:120', "regex:/^(?=.*\\p{L})[\\p{L}\\p{N} .'()-]+$/u"],
            'city_municipality' => ['required', 'string', 'max:120', "regex:/^(?=.*\\p{L})[\\p{L}\\p{N} .'()-]+$/u"],
            'contact_last_name' => ['required', 'string', 'max:60', 'regex:'.self::NAME_PATTERN],
            'contact_first_name' => ['required', 'string', 'max:60', 'regex:'.self::NAME_PATTERN],
            'contact_middle_initial' => ['nullable', 'string', 'max:4', 'regex:/^(?!.*\\d)\\p{L}(?:\\.?\\p{L})?\\.?$/u'],
            'contact_name_extension' => ['nullable', 'string', 'in:Jr.,Sr.,II,III,IV,V'],
            'contact_email' => ['nullable', 'email', 'max:120'],
            'contact_phone' => [
                'nullable',
                'string',
                'max:30',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if ($value === null || trim((string) $value) === '') {
                        return;
                    }
                    $normalized = PhilippineContactNumber::normalize((string) $value);
                    if ($normalized === null || ! preg_match('/^0\d{9,11}$/', $normalized)) {
                        $fail(__('Use a valid Philippine number: mobile (e.g. 09XXXXXXXXX) or landline (e.g. 034XXXXXXX). Only digits, spaces, and + are accepted—letters are not allowed.'));
                    }
                },
            ],
            'company_industry_id' => ['nullable', 'integer', 'exists:company_industries,id'],
            'province_id' => ['nullable', 'integer', 'exists:philippine_provinces,id'],
            'city_id' => ['nullable', 'integer', 'exists:philippine_cities,id'],
            'barangay_id' => ['nullable', 'integer', 'exists:philippine_barangays,id'],
            'notes' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius_meters' => ['nullable', 'integer', 'min:10', 'max:5000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $email = $this->contact_email !== null ? trim((string) $this->contact_email) : '';
        $this->merge([
            'name' => $this->name !== null ? trim(strip_tags((string) $this->name)) : null,
            'street_address' => $this->street_address !== null ? trim(strip_tags((string) $this->street_address)) : null,
            'barangay' => $this->barangay !== null ? trim(strip_tags((string) $this->barangay)) : null,
            'city_municipality' => $this->city_municipality !== null ? trim(strip_tags((string) $this->city_municipality)) : null,
            'company_industry_id' => $this->company_industry_id !== null && $this->company_industry_id !== '' ? (int) $this->company_industry_id : null,
            'province_id' => $this->province_id !== null && $this->province_id !== '' ? (int) $this->province_id : null,
            'city_id' => $this->city_id !== null && $this->city_id !== '' ? (int) $this->city_id : null,
            'barangay_id' => $this->barangay_id !== null && $this->barangay_id !== '' ? (int) $this->barangay_id : null,
            'notes' => $this->notes !== null ? trim(strip_tags((string) $this->notes)) : null,
            'address' => collect([
                $this->street_address !== null ? trim((string) $this->street_address) : null,
                $this->barangay !== null ? trim((string) $this->barangay) : null,
                $this->city_municipality !== null ? trim((string) $this->city_municipality) : null,
            ])->filter()->implode(', '),
            'contact_last_name' => $this->contact_last_name !== null ? trim(strip_tags((string) $this->contact_last_name)) : null,
            'contact_first_name' => $this->contact_first_name !== null ? trim(strip_tags((string) $this->contact_first_name)) : null,
            'contact_middle_initial' => $this->contact_middle_initial !== null ? strtoupper(trim(strip_tags((string) $this->contact_middle_initial))) : null,
            'contact_name_extension' => $this->contact_name_extension !== null ? trim(strip_tags((string) $this->contact_name_extension)) : null,
            'contact_person' => trim(implode(' ', array_filter([
                ($this->contact_last_name !== null && trim((string) $this->contact_last_name) !== '') ? trim((string) $this->contact_last_name).',' : null,
                $this->contact_first_name !== null ? trim((string) $this->contact_first_name) : null,
                $this->contact_middle_initial !== null ? strtoupper(trim((string) $this->contact_middle_initial)) : null,
                $this->contact_name_extension !== null ? trim((string) $this->contact_name_extension) : null,
            ]))),
            'contact_email' => $email !== '' ? strtolower($email) : null,
            'contact_phone' => $this->contact_phone !== null ? preg_replace('/[^\d+]/', '', (string) $this->contact_phone) : null,
            'is_active' => $this->has('is_active'),
            'latitude' => $this->latitude !== null && $this->latitude !== '' ? (float) $this->latitude : null,
            'longitude' => $this->longitude !== null && $this->longitude !== '' ? (float) $this->longitude : null,
            'geofence_radius_meters' => $this->geofence_radius_meters !== null && $this->geofence_radius_meters !== '' ? (int) $this->geofence_radius_meters : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.regex' => __('Company name must use readable letters/words only.'),
            'street_address.regex' => __('Street address contains invalid characters.'),
            'barangay.regex' => __('Barangay must use readable words only.'),
            'city_municipality.regex' => __('City / Municipality must use readable words only.'),
            'contact_last_name.regex' => __('Contact last name must contain letters only.'),
            'contact_first_name.regex' => __('Contact first name must contain letters only.'),
            'contact_middle_initial.regex' => __('Contact middle initial must use letters only.'),
            'name.unique' => __('Another company with this name already exists. Choose a different name.'),
            'is_active.boolean' => __('Active status must be valid.'),
        ];
    }
}
