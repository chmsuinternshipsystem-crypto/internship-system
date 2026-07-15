<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'last_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\'\-\s]+$/u'],
            'first_name' => ['required', 'string', 'max:255', 'regex:/^[\pL\'\-\s]+$/u'],
            'middle_name' => ['nullable', 'string', 'max:255', 'regex:/^[\pL\'\-\s]*$/u'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'last_name.required' => __('The last name is required.'),
            'last_name.regex' => __('The last name may only contain letters, spaces, apostrophes, and hyphens.'),
            'first_name.required' => __('The first name is required.'),
            'first_name.regex' => __('The first name may only contain letters, spaces, apostrophes, and hyphens.'),
            'middle_name.regex' => __('The middle name may only contain letters, spaces, apostrophes, and hyphens.'),
            'email.required' => __('The email address is required.'),
            'email.email' => __('Please enter a valid email address.'),
            'email.unique' => __('This email address is already in use by another account.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'last_name' => $this->last_name !== null ? trim(strip_tags((string) $this->last_name)) : null,
            'first_name' => $this->first_name !== null ? trim(strip_tags((string) $this->first_name)) : null,
            'middle_name' => $this->middle_name !== null ? trim(strip_tags((string) $this->middle_name)) : null,
            'email' => $this->email !== null ? strtolower(trim((string) $this->email)) : null,
        ]);
    }
}
