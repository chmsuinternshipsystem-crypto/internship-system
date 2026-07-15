<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_documents', function (Blueprint $table): void {
            if (! Schema::hasColumn('student_documents', 'workflow_template_id')) {
                $table->foreignId('workflow_template_id')
                    ->nullable()
                    ->after('required_document_id')
                    ->constrained('document_workflow_templates')
                    ->nullOnDelete();
            }
            if (! Schema::hasColumn('student_documents', 'current_step_order')) {
                $table->unsignedInteger('current_step_order')->nullable()->after('workflow_template_id');
            }
            if (! Schema::hasColumn('student_documents', 'current_holder_role')) {
                $table->string('current_holder_role')->nullable()->after('current_step_order');
            }
            if (! Schema::hasColumn('student_documents', 'next_step_role')) {
                $table->string('next_step_role')->nullable()->after('current_holder_role');
            }
            if (! Schema::hasColumn('student_documents', 'workflow_status')) {
                $table->string('workflow_status')->nullable()->after('next_step_role');
            }
            if (! Schema::hasColumn('student_documents', 'last_action_at')) {
                $table->timestamp('last_action_at')->nullable()->after('workflow_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table): void {
            foreach ([
                'last_action_at',
                'workflow_status',
                'next_step_role',
                'current_holder_role',
                'current_step_order',
            ] as $col) {
                if (Schema::hasColumn('student_documents', $col)) {
                    $table->dropColumn($col);
                }
            }
            if (Schema::hasColumn('student_documents', 'workflow_template_id')) {
                $table->dropConstrainedForeignId('workflow_template_id');
            }
        });
    }
};
