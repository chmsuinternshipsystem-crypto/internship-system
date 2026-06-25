<?php

use App\Models\DocumentWorkflowStep;
use App\Models\DocumentWorkflowTemplate;
use App\Models\RequiredDocument;
use App\Models\StudentDocument;
use App\Support\InternshipRoles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $instructor = InternshipRoles::INSTRUCTOR;
        $chairperson = InternshipRoles::CHAIRPERSON;
        $dean = InternshipRoles::DEAN;

        // ── 1. Replace HTE_SIGNATURE_CHAIN steps with new 2-step template ──
        $hteTemplate = DocumentWorkflowTemplate::query()->where('code', 'HTE_SIGNATURE_CHAIN')->first();
        if ($hteTemplate) {
            $hteTemplate->update([
                'name' => 'Document Review Chain',
                'description' => 'Instructor (approve final) → optionally forward to Chairperson (approve final or return)',
            ]);

            DocumentWorkflowStep::query()->where('workflow_template_id', $hteTemplate->id)->delete();

            DocumentWorkflowStep::query()->create([
                'workflow_template_id' => $hteTemplate->id,
                'step_order' => 1,
                'role' => $instructor,
                'can_return' => true,
                'requires_signature' => false,
            ]);
            DocumentWorkflowStep::query()->create([
                'workflow_template_id' => $hteTemplate->id,
                'step_order' => 2,
                'role' => $chairperson,
                'can_return' => true,
                'requires_signature' => false,
            ]);
        }

        // ── 2. Update PLEDGE_CHAIN and ENDORSEMENT_CHAIN to match ──
        foreach (['PLEDGE_CHAIN', 'ENDORSEMENT_CHAIN'] as $code) {
            $template = DocumentWorkflowTemplate::query()->where('code', $code)->first();
            if ($template) {
                DocumentWorkflowStep::query()->where('workflow_template_id', $template->id)->delete();

                DocumentWorkflowStep::query()->create([
                    'workflow_template_id' => $template->id,
                    'step_order' => 1,
                    'role' => $instructor,
                    'can_return' => true,
                    'requires_signature' => false,
                ]);
                DocumentWorkflowStep::query()->create([
                    'workflow_template_id' => $template->id,
                    'step_order' => 2,
                    'role' => $chairperson,
                    'can_return' => true,
                    'requires_signature' => false,
                ]);
            }
        }

        // ── 3. Update HTE_ISSUED_ARCHIVE to remove dean step ──
        $archiveTemplate = DocumentWorkflowTemplate::query()->where('code', 'HTE_ISSUED_ARCHIVE')->first();
        if ($archiveTemplate) {
            DocumentWorkflowStep::query()->where('workflow_template_id', $archiveTemplate->id)->delete();

            DocumentWorkflowStep::query()->create([
                'workflow_template_id' => $archiveTemplate->id,
                'step_order' => 1,
                'role' => $instructor,
                'can_return' => true,
                'requires_signature' => false,
            ]);
        }

        // ── 4. Ensure all RequiredDocuments use HTE_SIGNATURE_CHAIN (or OPEN_ARCHIVE if they already are) ──
        $openArchiveId = DocumentWorkflowTemplate::query()->where('code', 'OPEN_ARCHIVE')->value('id');

        RequiredDocument::query()
            ->whereNotNull('workflow_template_id')
            ->where('workflow_template_id', '!=', $openArchiveId)
            ->update(['workflow_template_id' => $hteTemplate?->id]);

        // ── 5. Migrate existing StudentDocuments ──
        // Documents at step_order 3 (old instructor final step in HTE_SIGNATURE_CHAIN):
        //   → If status is pending_review: complete them
        //   → Otherwise: move back to step 2 (chairperson)
        if ($hteTemplate) {
            $oldStep3Docs = StudentDocument::query()
                ->where('workflow_template_id', $hteTemplate->id)
                ->where('current_step_order', 3)
                ->get();

            foreach ($oldStep3Docs as $doc) {
                if ($doc->workflow_status === 'pending_review') {
                    // About to be final-approved — complete it
                    $doc->current_step_order = 2;
                    $doc->current_holder_role = $chairperson;
                    $doc->next_step_role = null;
                    $doc->workflow_status = 'completed';
                    $doc->status = 'Submitted';
                    $doc->submitted_at = $doc->submitted_at ?: now();
                    $doc->save();
                } else {
                    // Move back to chairperson
                    $doc->current_step_order = 2;
                    $doc->current_holder_role = $chairperson;
                    $doc->next_step_role = null;
                    $doc->save();
                }
            }

            // Documents at step_order 2 (chairperson):
            //   → Keep at step 2, ensure next_step_role is null (chairperson can now approve final)
            StudentDocument::query()
                ->where('workflow_template_id', $hteTemplate->id)
                ->where('current_step_order', 2)
                ->update(['next_step_role' => null]);
        }

        // ── 6. Remove dean from workflow ──
        // Documents with dean as holder: move back to instructor
        StudentDocument::query()
            ->where('current_holder_role', $dean)
            ->update([
                'current_holder_role' => $instructor,
                'current_step_order' => 1,
                'next_step_role' => null,
                'workflow_status' => DB::raw('COALESCE(workflow_status, \'received\')'),
            ]);
    }

    public function down(): void
    {
        // Cannot reliably reverse — old 3-step chain data is lost.
    }
};
