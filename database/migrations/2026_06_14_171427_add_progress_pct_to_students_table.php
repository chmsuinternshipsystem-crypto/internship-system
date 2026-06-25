<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (! Schema::hasColumn('students', 'progress_pct')) {
                $table->unsignedTinyInteger('progress_pct')->default(0)->after('contact_number');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (Schema::hasColumn('students', 'progress_pct')) {
                $table->dropColumn('progress_pct');
            }
        });
    }
};
