<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamp('check_in_at');
            $table->decimal('latitude', 15, 12)->nullable();
            $table->decimal('longitude', 15, 12)->nullable();
            $table->unsignedInteger('distance_meters')->nullable();
            $table->boolean('is_within_campus')->nullable();
            $table->boolean('location_unavailable')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
