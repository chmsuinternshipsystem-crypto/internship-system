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

        $openArchiveTemplateId = (int) DocumentWorkflowTemplate::query()
            ->where('code', 'OPEN_ARCHIVE')
            ->value('id');

        if ($openArchiveTemplateId <= 0) {
            return;
        }

        // Instructor-only routing for all student-submitted requirements.
        RequiredDocument::query()->update(['workflow_template_id' => $openArchiveTemplateId]);

        StudentDocument::query()
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->update([
                'workflow_template_id' => $openArchiveTemplateId,
                'current_step_order' => 1,
                'current_holder_role' => InternshipRoles::INSTRUCTOR,
                'next_step_role' => null,
            ]);
    }

    public function down(): void
    {
        // No rollback: this migration enforces the current agreed workflow policy.
    }
};
