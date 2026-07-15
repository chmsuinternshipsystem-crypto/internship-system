<?php

namespace App\Http\Requests;

use App\Models\StudentAccount;
use App\Support\PhoneHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStudentProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentAccount = $this->attributes->get('studentAccount');

        return [
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('student_accounts', 'email')
                    ->ignore($studentAccount?->id),
            ],
            'contact_number' => [
                'nullable',
                'regex:/^09[0-9]{9}$/',
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'email.email' => __('Please enter a valid email address.'),
            'email.max' => __('Email address must not exceed 255 characters.'),
            'email.unique' => __('This email address is already in use by another account.'),
            'contact_number.regex' => __('Contact number must be a valid Philippine mobile number (09XXXXXXXXX or +639XXXXXXXXX).'),
            'password.min' => __('Password must be at least 8 characters.'),
            'password.confirmed' => __('Password confirmation does not match.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email
                ? strtolower(trim((string) $this->email))
                : null,
            'contact_number' => $this->contact_number
                ? PhoneHelper::normalizeMobile((string) $this->contact_number)
                : null,
        ]);
    }
}
