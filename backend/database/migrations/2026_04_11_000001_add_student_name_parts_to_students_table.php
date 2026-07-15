<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (! Schema::hasColumn('students', 'last_name')) {
                $table->string('last_name')->nullable()->after('name');
            }
            if (! Schema::hasColumn('students', 'first_name')) {
                $table->string('first_name')->nullable()->after('last_name');
            }
            if (! Schema::hasColumn('students', 'middle_initial')) {
                $table->string('middle_initial', 5)->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('students', 'name_extension')) {
                $table->string('name_extension', 20)->nullable()->after('middle_initial');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            foreach (['name_extension', 'middle_initial', 'first_name', 'last_name'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
