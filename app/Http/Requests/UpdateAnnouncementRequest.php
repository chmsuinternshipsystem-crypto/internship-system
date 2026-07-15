<?php

namespace App\Http\Requests;

use App\Support\InternshipRoles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnouncementRequest extends FormRequest
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
        $announcement = $this->route('announcement');

        return [
            'title' => ['required', 'string', 'max:255', Rule::unique('announcements', 'title')->ignore($announcement?->id)],
            'body' => ['required', 'string', 'max:3000'],
            'visible_to_role' => ['nullable', 'string', Rule::in(InternshipRoles::announcementVisibleToRuleValues())],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => __('Please enter an announcement title.'),
            'title.unique' => __('An announcement with this title already exists.'),
            'title.max' => __('Title must not exceed 255 characters.'),
            'body.required' => __('Please enter the announcement body.'),
            'body.max' => __('Body must not exceed 3000 characters.'),
            'visible_to_role.in' => __('Invalid audience selected.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => $this->title !== null ? trim(strip_tags((string) $this->title)) : null,
            'body' => $this->body !== null ? trim(strip_tags((string) $this->body)) : null,
            'visible_to_role' => $this->visible_to_role !== null ? strtolower(trim((string) $this->visible_to_role)) : null,
        ]);
    }
}
