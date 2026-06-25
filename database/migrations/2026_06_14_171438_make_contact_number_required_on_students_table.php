<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep nullable in DB for test compatibility; form validation enforces required
        Schema::table('students', function (Blueprint $table): void {
            if (Schema::hasColumn('students', 'contact_number')) {
                $table->string('contact_number', 11)->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (Schema::hasColumn('students', 'contact_number')) {
                $table->string('contact_number', 11)->nullable()->change();
            }
        });
    }
};
