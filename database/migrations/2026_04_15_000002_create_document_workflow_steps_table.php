<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_workflow_steps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workflow_template_id')->constrained('document_workflow_templates')->cascadeOnDelete();
            $table->unsignedInteger('step_order');
            $table->string('role');
            $table->boolean('can_return')->default(true);
            $table->boolean('requires_signature')->default(false);
            $table->timestamps();

            $table->unique(['workflow_template_id', 'step_order'], 'workflow_steps_unique_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_workflow_steps');
    }
};
