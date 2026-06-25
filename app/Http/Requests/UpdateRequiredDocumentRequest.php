<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRequiredDocumentRequest extends FormRequest
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
        $document = $this->route('required_document');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('required_documents', 'name')->ignore($document?->id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'order_slot' => [
                'nullable',
                'string',
                'regex:/^(?:[1-9]|[1-8][0-9]|99)$/',
            ],
            'company_id' => [
                'nullable',
                'integer',
                'exists:companies,id',
            ],
            'phase' => [
                'nullable',
                'in:pre,monitoring,all',
            ],
            'submission_deadline_at' => [
                'nullable',
                'date',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('Please enter a document name.'),
            'name.unique' => __('A required document with this name already exists.'),
            'name.max' => __('Document name must not exceed 255 characters.'),
            'description.max' => __('Description must not exceed 1000 characters.'),
            'order_slot.regex' => __('Order slot must be a number between 1 and 99.'),
            'company_id.exists' => __('Selected company does not exist.'),
            'phase.in' => __('Invalid phase selected.'),
            'submission_deadline_at.date' => __('Please provide a valid date.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $slot = $this->order_slot;
        if ($slot !== null && trim((string) $slot) === '') {
            $slot = null;
        }

        $this->merge([
            'name' => $this->name !== null ? trim(strip_tags((string) $this->name)) : null,
            'description' => $this->description !== null ? trim(strip_tags((string) $this->description)) : null,
            'order_slot' => $slot,
            'phase' => $this->phase !== null ? trim((string) $this->phase) : 'all',
        ]);
    }
}
