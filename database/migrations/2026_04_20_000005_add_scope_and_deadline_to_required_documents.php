<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('required_documents', function (Blueprint $table): void {
            $table->foreignId('company_id')
                ->nullable()
                ->after('workflow_template_id')
                ->constrained()
                ->nullOnDelete();
            $table->string('submission_mode', 20)
                ->default('upload')
                ->after('company_id');
            $table->timestamp('submission_deadline_at')
                ->nullable()
                ->after('submission_mode');
        });
    }

    public function down(): void
    {
        Schema::table('required_documents', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn([
                'submission_mode',
                'submission_deadline_at',
            ]);
        });
    }
};
