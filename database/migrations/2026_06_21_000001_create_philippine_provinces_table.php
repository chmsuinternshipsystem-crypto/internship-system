<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('philippine_provinces', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 120);
            $table->string('region', 10);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('philippine_provinces');
    }
};
