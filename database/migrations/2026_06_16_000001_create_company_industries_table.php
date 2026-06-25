<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_industries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('color', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->foreignId('company_industry_id')
                ->nullable()
                ->after('city_municipality')
                ->constrained()
                ->nullOnDelete();

            $table->text('notes')
                ->nullable()
                ->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['company_industry_id']);
            $table->dropColumn(['company_industry_id', 'notes']);
        });

        Schema::dropIfExists('company_industries');
    }
};
