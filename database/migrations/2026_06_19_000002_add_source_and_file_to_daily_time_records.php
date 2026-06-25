<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->enum('source', ['attendance', 'manual', 'upload'])->default('manual')->after('deployment_id');
            $table->string('file_path')->nullable()->after('remarks');
            $table->string('file_name')->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('daily_time_records', function (Blueprint $table) {
            $table->dropColumn(['source', 'file_path', 'file_name']);
        });
    }
};
