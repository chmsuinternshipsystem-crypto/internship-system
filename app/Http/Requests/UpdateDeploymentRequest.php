<?php

namespace App\Http\Requests;

use App\Models\Deployment;
use App\Models\Student;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDeploymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $deployment = $this->route('deployment');

        return [
            'student_id' => ['required', 'exists:students,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'start_date' => [
                'required',
                'date',
                Rule::unique('deployments', 'start_date')
                    ->ignore($deployment?->id)
                    ->where(function ($q) {
                        return $q->where('student_id', (int) $this->input('student_id'))
                            ->where('company_id', (int) $this->input('company_id'));
                    }),
            ],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.required' => __('The start date is required.'),
            'start_date.date' => __('Please provide a valid start date.'),
            'start_date.unique' => __('This student already has a deployment with this start date at this company.'),
            'end_date.date' => __('Please provide a valid end date.'),
            'end_date.after_or_equal' => __('The end date must be on or after the start date.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'remarks' => $this->remarks !== null ? trim(strip_tags((string) $this->remarks)) : null,
        ]);
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $studentId = (int) $this->input('student_id');
            $startDate = $this->input('start_date');
            $endDate = $this->input('end_date');
            $deployment = $this->route('deployment');
            $deploymentId = $deployment instanceof Deployment ? (int) $deployment->id : null;

            if ($studentId <= 0) {
                return;
            }

            // Date overlap with existing deployments (exclude self)
            if (filled($startDate) && $this->hasDateOverlap($studentId, $deploymentId, $startDate, $endDate)) {
                $validator->errors()->add('start_date', __('This student already has a deployment with overlapping dates.'));
            }

            // Auto-computed status validations
            $status = Deployment::computeStatus($startDate, $endDate);

            // Completed — end date required
            if ($status === 'completed' && blank($endDate)) {
                $validator->errors()->add('end_date', __('End date is required when the deployment is completed.'));
            }

            // Duplicate active deployment check
            if ($status === 'active') {
                $exists = Deployment::query()
                    ->where('student_id', $studentId)
                    ->where('status', 'active')
                    ->when($deploymentId, fn ($q) => $q->where('id', '!=', $deploymentId))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('start_date', __('This student already has a different active deployment. Complete or change it before activating this one.'));
                }
            }

            // Student changed — check overlap with new student's other deployments
            if ($deployment instanceof Deployment && (int) $deployment->student_id !== $studentId && filled($startDate)) {
                if ($this->hasDateOverlap($studentId, null, $startDate, $endDate)) {
                    $validator->errors()->add('student_id', __('The selected student already has a deployment with overlapping dates.'));
                }
            }

            // Flag section conflict
            $student = Student::find($studentId);
            if ($student && $student->section) {
                $sameSectionOthers = Deployment::query()
                    ->whereHas('student', fn ($q) => $q->where('section', $student->section))
                    ->where('student_id', '!=', $studentId)
                    ->where('status', 'active')
                    ->when($deploymentId, fn ($q) => $q->where('id', '!=', $deploymentId))
                    ->exists();

                if ($sameSectionOthers) {
                    $validator->errors()->add('section_warning', __('Warning: Section :section already has active deployments for other students.', ['section' => $student->section]));
                }
            }
        });
    }

    private function hasDateOverlap(int $studentId, ?int $excludeId, string $startDate, ?string $endDate): bool
    {
        return Deployment::query()
            ->where('student_id', $studentId)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->where(function ($q) use ($startDate, $endDate) {
                $q->where('start_date', '<=', $endDate ?? $startDate);
            })
            ->where(function ($q) use ($startDate) {
                $q->whereNull('end_date')->orWhere('end_date', '>=', $startDate);
            })
            ->exists();
    }
}
