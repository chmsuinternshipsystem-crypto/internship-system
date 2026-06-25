<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philippine_barangays', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 120);
            $table->unsignedBigInteger('city_id');
            $table->timestamps();

            $table->foreign('city_id')
                  ->references('id')
                  ->on('philippine_cities')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philippine_barangays');
    }
};
