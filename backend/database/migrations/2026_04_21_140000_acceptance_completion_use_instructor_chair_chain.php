<?php

use App\Models\DocumentWorkflowTemplate;
use App\Models\RequiredDocument;
use App\Models\StudentDocument;
use App\Support\InternshipRoles;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const NAMES = ['Acceptance Letter', 'Certificate of Completion'];

    public function up(): void
    {
        $templateId = DocumentWorkflowTemplate::query()
            ->where('code', 'HTE_SIGNATURE_CHAIN')
            ->value('id');

        if (! $templateId) {
            return;
        }

        foreach (self::NAMES as $name) {
            RequiredDocument::query()
                ->where('name', $name)
                ->update(['workflow_template_id' => $templateId]);
        }

        $requiredIds = RequiredDocument::query()
            ->whereIn('name', self::NAMES)
            ->pluck('id');

        if ($requiredIds->isEmpty()) {
            return;
        }

        StudentDocument::query()
            ->whereIn('required_document_id', $requiredIds)
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->update(['workflow_template_id' => $templateId]);

        // Old template pointed "next" at dean; chain now goes to chair next.
        StudentDocument::query()
            ->whereIn('required_document_id', $requiredIds)
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->where('current_step_order', 1)
            ->where('current_holder_role', InternshipRoles::INSTRUCTOR)
            ->where('next_step_role', InternshipRoles::DEAN)
            ->update(['next_step_role' => InternshipRoles::CHAIRPERSON]);
    }

    public function down(): void
    {
        $deanTemplateId = DocumentWorkflowTemplate::query()
            ->where('code', 'HTE_ISSUED_ARCHIVE')
            ->value('id');

        if (! $deanTemplateId) {
            return;
        }

        foreach (self::NAMES as $name) {
            RequiredDocument::query()
                ->where('name', $name)
                ->update(['workflow_template_id' => $deanTemplateId]);
        }

        $requiredIds = RequiredDocument::query()
            ->whereIn('name', self::NAMES)
            ->pluck('id');

        if ($requiredIds->isEmpty()) {
            return;
        }

        StudentDocument::query()
            ->whereIn('required_document_id', $requiredIds)
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->update(['workflow_template_id' => $deanTemplateId]);

        StudentDocument::query()
            ->whereIn('required_document_id', $requiredIds)
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->where('current_step_order', 1)
            ->where('current_holder_role', InternshipRoles::INSTRUCTOR)
            ->where('next_step_role', InternshipRoles::CHAIRPERSON)
            ->update(['next_step_role' => InternshipRoles::DEAN]);
    }
};
