<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('required_documents', function (Blueprint $table): void {
            $table->dropColumn('submission_mode');
        });
    }

    public function down(): void
    {
        Schema::table('required_documents', function (Blueprint $table): void {
            $table->string('submission_mode', 20)->default('upload')->after('company_id');
        });
    }
};
