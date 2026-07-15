<?php

use App\Models\DocumentWorkflowTemplate;
use App\Models\RequiredDocument;
use App\Models\StudentDocument;
use App\Support\InternshipRoles;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('required_documents') || ! Schema::hasTable('student_documents')) {
            return;
        }

        $chainTemplateId = (int) DocumentWorkflowTemplate::query()
            ->where('code', 'HTE_SIGNATURE_CHAIN')
            ->value('id');

        if ($chainTemplateId <= 0) {
            return;
        }

        // Restore the intended default flow for all student-submitted requirements.
        RequiredDocument::query()->update(['workflow_template_id' => $chainTemplateId]);

        StudentDocument::query()
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->update(['workflow_template_id' => $chainTemplateId]);

        // Normalize active rows to proper routing in the 3-step chain.
        StudentDocument::query()
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->where('current_step_order', 1)
            ->where('current_holder_role', InternshipRoles::INSTRUCTOR)
            ->update(['next_step_role' => InternshipRoles::CHAIRPERSON]);

        StudentDocument::query()
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->where('current_step_order', 2)
            ->whereIn('current_holder_role', [InternshipRoles::DEAN, InternshipRoles::CHAIRPERSON])
            ->update([
                'current_holder_role' => InternshipRoles::CHAIRPERSON,
                'next_step_role' => InternshipRoles::INSTRUCTOR,
            ]);

        StudentDocument::query()
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->where('current_step_order', 3)
            ->where('current_holder_role', InternshipRoles::INSTRUCTOR)
            ->update(['next_step_role' => null]);
    }

    public function down(): void
    {
        // No rollback: this migration restores the product-approved routing policy.
    }
};
