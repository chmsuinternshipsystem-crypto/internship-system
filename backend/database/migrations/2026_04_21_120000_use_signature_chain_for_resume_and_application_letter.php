<?php

use App\Models\DocumentWorkflowTemplate;
use App\Models\RequiredDocument;
use App\Models\StudentDocument;
use App\Support\InternshipRoles;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private const NAMES = ['Resume', 'Application Letter'];

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

        // Re-point next reviewer for in-progress rows that were on single-step Open Archive.
        StudentDocument::query()
            ->whereIn('required_document_id', $requiredIds)
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->where('current_step_order', 1)
            ->where('current_holder_role', InternshipRoles::INSTRUCTOR)
            ->update([
                'next_step_role' => InternshipRoles::CHAIRPERSON,
            ]);
    }

    public function down(): void
    {
        $openId = DocumentWorkflowTemplate::query()
            ->where('code', 'OPEN_ARCHIVE')
            ->value('id');

        if (! $openId) {
            return;
        }

        foreach (self::NAMES as $name) {
            RequiredDocument::query()
                ->where('name', $name)
                ->update(['workflow_template_id' => $openId]);
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
            ->update(['workflow_template_id' => $openId]);

        StudentDocument::query()
            ->whereIn('required_document_id', $requiredIds)
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->where('current_step_order', 1)
            ->where('current_holder_role', InternshipRoles::INSTRUCTOR)
            ->update(['next_step_role' => null]);
    }
};
