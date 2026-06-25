<?php

use App\Models\StudentDocument;
use App\Services\DocumentWorkflowEngine;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_documents') || ! Schema::hasTable('required_documents')) {
            return;
        }

        if (! Schema::hasColumn('student_documents', 'workflow_template_id')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        // Backfill missing template links from required_documents.
        if ($driver === 'mysql') {
            DB::statement(
                'UPDATE student_documents sd
                 INNER JOIN required_documents rd ON rd.id = sd.required_document_id
                 SET sd.workflow_template_id = rd.workflow_template_id
                 WHERE sd.workflow_template_id IS NULL
                   AND rd.workflow_template_id IS NOT NULL'
            );
        } else {
            StudentDocument::query()
                ->whereNull('workflow_template_id')
                ->with('requiredDocument:id,workflow_template_id')
                ->chunkById(200, function ($documents): void {
                    foreach ($documents as $document) {
                        $templateId = (int) ($document->requiredDocument?->workflow_template_id ?? 0);
                        if ($templateId > 0) {
                            $document->workflow_template_id = $templateId;
                            $document->save();
                        }
                    }
                });
        }

        if (
            ! Schema::hasColumn('student_documents', 'current_step_order')
            || ! Schema::hasColumn('student_documents', 'current_holder_role')
            || ! Schema::hasColumn('student_documents', 'workflow_status')
        ) {
            return;
        }

        $workflowEngine = app(DocumentWorkflowEngine::class);

        // Initialize workflow routing state for existing routed documents.
        StudentDocument::query()
            ->whereNotNull('workflow_template_id')
            ->where(function ($query): void {
                $query->whereNull('current_step_order')
                    ->orWhereNull('current_holder_role')
                    ->orWhereNull('workflow_status');
            })
            ->orderBy('id')
            ->chunkById(200, function ($documents) use ($workflowEngine): void {
                foreach ($documents as $document) {
                    $workflowEngine->initialize($document);
                }
            });
    }

    public function down(): void
    {
        // Backfill migration; no destructive rollback needed.
    }
};
