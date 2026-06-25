<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->unsignedBigInteger('province_id')->nullable()->after('city_municipality');
            $table->unsignedBigInteger('city_id')->nullable()->after('province_id');
            $table->unsignedBigInteger('barangay_id')->nullable()->after('city_id');

            $table->foreign('province_id')
                ->references('id')
                ->on('philippine_provinces')
                ->nullOnDelete();

            $table->foreign('city_id')
                ->references('id')
                ->on('philippine_cities')
                ->nullOnDelete();

            $table->foreign('barangay_id')
                ->references('id')
                ->on('philippine_barangays')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign(['province_id']);
            $table->dropForeign(['city_id']);
            $table->dropForeign(['barangay_id']);
            $table->dropColumn(['province_id', 'city_id', 'barangay_id']);
        });
    }
};
