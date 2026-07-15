<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('required_documents', function (Blueprint $table): void {
            if (! Schema::hasColumn('required_documents', 'workflow_template_id')) {
                $table->foreignId('workflow_template_id')
                    ->nullable()
                    ->after('order_index')
                    ->constrained('document_workflow_templates')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('required_documents', function (Blueprint $table): void {
            if (Schema::hasColumn('required_documents', 'workflow_template_id')) {
                $table->dropConstrainedForeignId('workflow_template_id');
            }
        });
    }
};
