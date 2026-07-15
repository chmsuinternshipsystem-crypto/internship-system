<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deployments', function (Blueprint $table): void {
            if (! Schema::hasColumn('deployments', 'school_year')) {
                $table->string('school_year', 9)->nullable()->after('end_date');
                $table->index('school_year');
            }
        });
    }

    public function down(): void
    {
        Schema::table('deployments', function (Blueprint $table): void {
            $table->dropIndex(['school_year']);
            $table->dropColumn('school_year');
        });
    }
};
