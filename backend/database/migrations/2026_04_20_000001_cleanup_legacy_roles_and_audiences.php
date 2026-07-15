<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')
                ->whereIn('role', ['employer', 'vpaa', 'cier', 'legal'])
                ->delete();
        }

        if (Schema::hasTable('announcements') && Schema::hasColumn('announcements', 'visible_to_role')) {
            DB::table('announcements')
                ->whereNotNull('visible_to_role')
                ->whereNotIn('visible_to_role', ['all', 'student', 'instructor', 'chairperson', 'dean'])
                ->update([
                    'visible_to_role' => null,
                    'updated_at' => now(),
                ]);
        }

        if (Schema::hasTable('document_workflow_steps') && Schema::hasColumn('document_workflow_steps', 'role')) {
            DB::table('document_workflow_steps')
                ->whereNotIn('role', ['instructor', 'chairperson', 'dean'])
                ->update(['role' => 'instructor']);
        }
    }

    public function down(): void
    {
        // Cleanup migration is intentionally non-reversible.
    }
};
