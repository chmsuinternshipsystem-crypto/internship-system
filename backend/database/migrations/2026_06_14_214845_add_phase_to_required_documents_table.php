<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('required_documents', function (Blueprint $table) {
            if (! Schema::hasColumn('required_documents', 'phase')) {
                $table->enum('phase', ['pre', 'monitoring', 'post', 'all'])->default('all')->after('submission_deadline_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('required_documents', function (Blueprint $table) {
            if (Schema::hasColumn('required_documents', 'phase')) {
                $table->dropColumn('phase');
            }
        });
    }
};
