<?php

use App\Models\DocumentWorkflowStep;
use App\Models\DocumentWorkflowTemplate;
use App\Models\StudentDocument;
use App\Support\InternshipRoles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** Templates that were Instructor → Chairperson → Dean; step 3 becomes Instructor (final). */
    private const TEMPLATE_CODES = [
        'HTE_SIGNATURE_CHAIN',
        'PLEDGE_CHAIN',
        'ENDORSEMENT_CHAIN',
    ];

    public function up(): void
    {
        foreach (self::TEMPLATE_CODES as $code) {
            $template = DocumentWorkflowTemplate::query()->where('code', $code)->first();
            if (! $template) {
                continue;
            }

            DB::transaction(function () use ($template): void {
                DocumentWorkflowStep::query()
                    ->where('workflow_template_id', $template->id)
                    ->delete();

                $steps = [
                    ['role' => InternshipRoles::INSTRUCTOR, 'requires_signature' => false],
                    ['role' => InternshipRoles::CHAIRPERSON, 'requires_signature' => true],
                    ['role' => InternshipRoles::INSTRUCTOR, 'requires_signature' => false],
                ];

                foreach ($steps as $index => $step) {
                    DocumentWorkflowStep::query()->create([
                        'workflow_template_id' => $template->id,
                        'step_order' => $index + 1,
                        'role' => $step['role'],
                        'can_return' => true,
                        'requires_signature' => (bool) $step['requires_signature'],
                    ]);
                }

                $template->update([
                    'description' => 'Instructor → Chairperson → Instructor (final approval)',
                ]);
            });
        }

        $templateIds = DocumentWorkflowTemplate::query()
            ->whereIn('code', self::TEMPLATE_CODES)
            ->pluck('id');

        if ($templateIds->isEmpty()) {
            return;
        }

        // Queue rows still assigned to Dean at step 3 should land on Instructor.
        StudentDocument::query()
            ->whereIn('workflow_template_id', $templateIds)
            ->where('current_step_order', 3)
            ->where('current_holder_role', InternshipRoles::DEAN)
            ->update(['current_holder_role' => InternshipRoles::INSTRUCTOR]);

        // After Chairperson signs, the next holder is step 3 Instructor (not Dean).
        StudentDocument::query()
            ->whereIn('workflow_template_id', $templateIds)
            ->where('current_step_order', 2)
            ->where('current_holder_role', InternshipRoles::CHAIRPERSON)
            ->where('next_step_role', InternshipRoles::DEAN)
            ->update(['next_step_role' => InternshipRoles::INSTRUCTOR]);
    }

    public function down(): void
    {
        foreach (self::TEMPLATE_CODES as $code) {
            $template = DocumentWorkflowTemplate::query()->where('code', $code)->first();
            if (! $template) {
                continue;
            }

            DB::transaction(function () use ($template): void {
                DocumentWorkflowStep::query()
                    ->where('workflow_template_id', $template->id)
                    ->delete();

                $steps = [
                    ['role' => InternshipRoles::INSTRUCTOR, 'requires_signature' => false],
                    ['role' => InternshipRoles::CHAIRPERSON, 'requires_signature' => true],
                    ['role' => InternshipRoles::DEAN, 'requires_signature' => true],
                ];

                foreach ($steps as $index => $step) {
                    DocumentWorkflowStep::query()->create([
                        'workflow_template_id' => $template->id,
                        'step_order' => $index + 1,
                        'role' => $step['role'],
                        'can_return' => true,
                        'requires_signature' => (bool) $step['requires_signature'],
                    ]);
                }

                $template->update([
                    'description' => 'Instructor -> Chairperson -> Dean',
                ]);
            });
        }

        $templateIds = DocumentWorkflowTemplate::query()
            ->whereIn('code', self::TEMPLATE_CODES)
            ->pluck('id');

        if ($templateIds->isEmpty()) {
            return;
        }

        StudentDocument::query()
            ->whereIn('workflow_template_id', $templateIds)
            ->where('current_step_order', 3)
            ->where('current_holder_role', InternshipRoles::INSTRUCTOR)
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->update(['current_holder_role' => InternshipRoles::DEAN]);

        StudentDocument::query()
            ->whereIn('workflow_template_id', $templateIds)
            ->where('current_step_order', 2)
            ->where('current_holder_role', InternshipRoles::CHAIRPERSON)
            ->where('next_step_role', InternshipRoles::INSTRUCTOR)
            ->update(['next_step_role' => InternshipRoles::DEAN]);
    }
};
