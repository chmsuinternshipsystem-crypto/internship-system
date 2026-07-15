<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            if (! Schema::hasColumn('certificates', 'reference_number')) {
                $table->string('reference_number', 50)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('certificates', 'generated_at')) {
                $table->timestamp('generated_at')->nullable()->after('verified_at');
            }
            if (! Schema::hasColumn('certificates', 'is_auto_generated')) {
                $table->boolean('is_auto_generated')->default(false)->after('generated_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table): void {
            $table->dropColumn(['reference_number', 'generated_at', 'is_auto_generated']);
        });
    }
};
