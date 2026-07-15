<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['students', 'companies', 'deployments'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table): void {
                if (! Schema::hasColumn($table, 'archived_at')) {
                    $t->timestamp('archived_at')->nullable();
                }
                if (! Schema::hasColumn($table, 'archive_reason')) {
                    $t->string('archive_reason', 255)->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $tables = ['students', 'companies', 'deployments'];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t): void {
                $t->dropColumn(['archived_at', 'archive_reason']);
            });
        }
    }
};
