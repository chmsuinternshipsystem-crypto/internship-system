<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEvaluationRequest extends FormRequest
{
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
            'student_id' => ['required', 'exists:students,id'],
            'evaluation_type' => ['required', Rule::in(['industry', 'school', 'student_feedback'])],
            'score' => ['required', 'integer', 'between:1,100'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'student_id.required' => __('Please select a student.'),
            'student_id.exists' => __('Selected student does not exist.'),
            'evaluation_type.required' => __('Please select an evaluation type.'),
            'evaluation_type.in' => __('Invalid evaluation type selected.'),
            'score.required' => __('Please enter a score.'),
            'score.integer' => __('Score must be a whole number.'),
            'score.between' => __('Score must be between 1 and 100.'),
            'comments.max' => __('Comments must not exceed 1000 characters.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'comments' => $this->comments !== null ? trim(strip_tags((string) $this->comments)) : null,
        ]);
    }
}
