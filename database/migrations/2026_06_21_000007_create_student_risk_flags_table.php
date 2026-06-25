<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_risk_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('severity');
            $table->text('message');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_risk_flags');
    }
};
