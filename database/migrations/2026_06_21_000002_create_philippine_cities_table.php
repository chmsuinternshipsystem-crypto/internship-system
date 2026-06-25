<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philippine_cities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 120);
            $table->unsignedBigInteger('province_id');
            $table->string('type', 20)->nullable();
            $table->timestamps();

            $table->foreign('province_id')
                  ->references('id')
                  ->on('philippine_provinces')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philippine_cities');
    }
};
