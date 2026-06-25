<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_forwarding_batches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('release_at')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('document_forwarding_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('document_forwarding_batches')->cascadeOnDelete();
            $table->foreignId('student_document_id')->constrained('student_documents')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('required_document_id')->constrained('required_documents')->cascadeOnDelete();
            $table->timestamp('released_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->unique(['batch_id', 'student_document_id']);
        });

        Schema::create('transmittal_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('batch_id')->constrained('document_forwarding_batches')->cascadeOnDelete();
            $table->foreignId('item_id')->nullable()->constrained('document_forwarding_items')->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('required_document_id')->constrained('required_documents')->cascadeOnDelete();
            $table->string('action_type', 30);
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('acted_at');
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transmittal_logs');
        Schema::dropIfExists('document_forwarding_items');
        Schema::dropIfExists('document_forwarding_batches');
    }
};
