<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->index(['current_holder_role', 'workflow_status', 'last_action_at'], 'idx_queue_performance');
            $table->index('workflow_status', 'idx_workflow_status');
        });

        Schema::table('student_document_actions', function (Blueprint $table) {
            $table->index(['student_document_id', 'acted_at'], 'idx_doc_actions_lookup');
        });
    }

    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropIndex('idx_queue_performance');
            $table->dropIndex('idx_workflow_status');
        });

        Schema::table('student_document_actions', function (Blueprint $table) {
            $table->dropIndex('idx_doc_actions_lookup');
        });
    }
};
