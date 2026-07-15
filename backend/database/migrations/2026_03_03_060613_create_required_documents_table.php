<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('required_documents', function (Blueprint $table) {
            // Primary key
            $table->id();

            // Document name (e.g. Endorsement Letter, MOA, DTR, Final Report)
            $table->string('name')->unique();

            // Optional description to clarify usage
            $table->text('description')->nullable();

            // Whether this document is mandatory
            $table->boolean('is_mandatory')->default(true);

            // Display order in lists
            $table->unsignedInteger('order_index')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('required_documents');
    }
};
