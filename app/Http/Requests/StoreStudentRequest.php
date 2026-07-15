<?php

namespace App\Http\Requests;

use App\Support\PhoneHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    private const NAME_PATTERN = "/^(?!.*\\d)\\p{L}+(?:[ .'-]\\p{L}+)*$/u";

    private const MIDDLE_NAME_PATTERN = "/^(?!.*\\d)\\p{L}+(?:[ .'-]\\p{L}+)*$/u";

    private const SECTION_PATTERN = '/^[A-Z][A-Z0-9-]{0,9}$/';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'last_name' => [
                'required',
                'string',
                'max:120',
                'regex:'.self::NAME_PATTERN,
            ],
            'first_name' => [
                'required',
                'string',
                'max:120',
                'regex:'.self::NAME_PATTERN,
            ],
            'middle_name' => [
                'nullable',
                'string',
                'max:120',
                'regex:'.self::MIDDLE_NAME_PATTERN,
            ],
            'name_extension' => [
                'nullable',
                'string',
                Rule::in(['Jr.', 'Sr.', 'II', 'III', 'IV', 'V']),
            ],
            'account_password' => [
                'nullable',
                'string',
                'confirmed',
                'min:8',
            ],
            'student_number' => [
                'required',
                'digits:8',
                'unique:students,student_number',
            ],
            'program' => [
                'required',
                'string',
                Rule::in(['BSIS']),
            ],
            'year_level' => [
                'required',
                'integer',
                Rule::in([4]),
            ],
            'section' => [
                'required',
                'string',
                'max:10',
                'regex:'.self::SECTION_PATTERN,
            ],
            'contact_number' => [
                'required',
                'regex:/^09[0-9]{9}$/',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('student_accounts', 'email'),
            ],
            'ojt_type' => [
                'required',
                'string',
                Rule::in(['unplaced', 'internal', 'external']),
            ],
            'assigned_instructor_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],
            'company_id' => [
                'nullable',
                'integer',
                'exists:companies,id',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'student_number.unique' => __('This Student ID is already registered.'),
            'last_name.regex' => __('Last name must contain letters only.'),
            'first_name.regex' => __('First name must contain letters only.'),
            'middle_name.regex' => __('Middle name must contain letters only.'),
            'section.regex' => __('Section must start with a letter and may only contain letters, numbers, or hyphens.'),
            'contact_number.required' => __('Contact number is required.'),
            'contact_number.regex' => __('Contact number must be a valid Philippine mobile number (09XXXXXXXXX).'),
            'email.unique' => __('This email address is already in use by another student account.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'last_name' => $this->last_name ? trim((string) $this->last_name) : null,
            'first_name' => $this->first_name ? trim((string) $this->first_name) : null,
            'middle_name' => $this->middle_name ? trim((string) $this->middle_name) : null,
            'name_extension' => $this->name_extension ? trim((string) $this->name_extension) : null,
            'student_number' => $this->student_number ? preg_replace('/\D+/', '', (string) $this->student_number) : null,
            'program' => 'BSIS',
            'section' => $this->section ? strtoupper(trim((string) $this->section)) : null,
            'contact_number' => $this->contact_number ? PhoneHelper::normalizeMobile((string) $this->contact_number) : null,
            'email' => $this->email ? strtolower(trim((string) $this->email)) : null,
            'year_level' => 4,
            'ojt_type' => $this->ojt_type ?: 'unplaced',
            'assigned_instructor_id' => $this->assigned_instructor_id !== null && $this->assigned_instructor_id !== '' ? (int) $this->assigned_instructor_id : null,
        ]);
    }
}
