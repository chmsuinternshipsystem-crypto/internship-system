<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyIndustryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('company_industries')->where(function ($query) {
                    $normalized = strtolower(trim((string) $this->input('name')));
                    return $query->whereRaw('LOWER(TRIM(name)) = ?', [$normalized]);
                }),
            ],
            'color' => ['nullable', 'string', 'max:20'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim(strip_tags((string) $this->name));
        $this->merge([
            'name' => $name,
            'slug' => \Str::slug($name),
            'color' => $this->color !== null ? trim(strip_tags((string) $this->color)) : null,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.unique' => __('An industry with this name already exists.'),
        ];
    }
}
