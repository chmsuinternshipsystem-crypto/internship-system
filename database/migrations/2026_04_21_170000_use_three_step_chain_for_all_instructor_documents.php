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

        // Apply Instructor -> Chairperson -> Instructor chain to all requirements routed to staff.
        RequiredDocument::query()->update(['workflow_template_id' => $chainTemplateId]);

        StudentDocument::query()
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->update(['workflow_template_id' => $chainTemplateId]);

        // Step 1 rows should point to chairperson as next reviewer.
        StudentDocument::query()
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->where('current_step_order', 1)
            ->where('current_holder_role', InternshipRoles::INSTRUCTOR)
            ->update(['next_step_role' => InternshipRoles::CHAIRPERSON]);

        // Any legacy dean-at-step2 rows become chairperson step2 rows in the new chain.
        StudentDocument::query()
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->where('current_step_order', 2)
            ->where('current_holder_role', InternshipRoles::DEAN)
            ->update([
                'current_holder_role' => InternshipRoles::CHAIRPERSON,
                'next_step_role' => InternshipRoles::INSTRUCTOR,
            ]);
    }

    public function down(): void
    {
        // Intentional no-op: this migration enforces the current product workflow policy.
    }
};
