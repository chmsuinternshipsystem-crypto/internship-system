<?php

/**
 * Idempotent schema repair for databases that missed or partially ran earlier migrations.
 * Safe to run multiple times (uses Schema::hasColumn / hasTable checks).
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->ensureAttendancesColumns();
        $this->ensureStudentDocumentsColumns();
        $this->ensureRequiredDocumentsIndexes();
    }

    public function down(): void
    {
        // Non-destructive migration: no automatic down (avoids dropping columns on repair installs).
    }

    private function ensureAttendancesColumns(): void
    {
        if (! Schema::hasTable('attendances')) {
            return;
        }

        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'accuracy_meters')) {
                $table->unsignedInteger('accuracy_meters')->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('attendances', 'geofence_status')) {
                $table->string('geofence_status', 40)->default('location_unavailable')->after('distance_meters');
            }
            if (! Schema::hasColumn('attendances', 'review_required')) {
                $table->boolean('review_required')->default(false)->after('geofence_status');
            }
            if (! Schema::hasColumn('attendances', 'resolution_status')) {
                $table->string('resolution_status', 30)->default('pending')->after('review_required');
            }
            if (! Schema::hasColumn('attendances', 'resolved_by')) {
                $table->foreignId('resolved_by')->nullable()->after('resolution_status')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('attendances', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            }
            if (! Schema::hasColumn('attendances', 'resolution_note')) {
                $table->string('resolution_note', 255)->nullable()->after('resolved_at');
            }
            if (! Schema::hasColumn('attendances', 'time_out_at')) {
                $table->timestamp('time_out_at')->nullable()->after('check_in_at');
            }
            if (! Schema::hasColumn('attendances', 'total_minutes')) {
                $table->unsignedInteger('total_minutes')->nullable()->after('time_out_at');
            }
            if (! Schema::hasColumn('attendances', 'time_out_latitude')) {
                $table->decimal('time_out_latitude', 15, 12)->nullable()->after('longitude');
            }
            if (! Schema::hasColumn('attendances', 'time_out_longitude')) {
                $table->decimal('time_out_longitude', 15, 12)->nullable()->after('time_out_latitude');
            }
            if (! Schema::hasColumn('attendances', 'time_out_accuracy_meters')) {
                $table->unsignedInteger('time_out_accuracy_meters')->nullable()->after('accuracy_meters');
            }
            if (! Schema::hasColumn('attendances', 'time_out_distance_meters')) {
                $table->unsignedInteger('time_out_distance_meters')->nullable()->after('distance_meters');
            }
            if (! Schema::hasColumn('attendances', 'time_out_geofence_status')) {
                $table->string('time_out_geofence_status', 40)->nullable()->after('geofence_status');
            }
        });
    }

    private function ensureStudentDocumentsColumns(): void
    {
        if (! Schema::hasTable('student_documents') || ! Schema::hasTable('document_workflow_templates')) {
            return;
        }

        Schema::table('student_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('student_documents', 'uploaded_by')) {
                $table->foreignId('uploaded_by')
                    ->nullable()
                    ->after('file_path')
                    ->constrained('users')
                    ->nullOnDelete();
            }
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

    private function ensureRequiredDocumentsIndexes(): void
    {
        if (! Schema::hasTable('required_documents')) {
            return;
        }

        if (Schema::hasIndex('required_documents', ['order_index'])) {
            return;
        }

        Schema::table('required_documents', function (Blueprint $table) {
            $table->index('order_index');
        });
    }
};
