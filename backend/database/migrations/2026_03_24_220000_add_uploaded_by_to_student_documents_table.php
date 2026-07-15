<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->foreignId('uploaded_by')
                ->nullable()
                ->after('file_path')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('student_documents')
            ->whereNull('uploaded_by')
            ->whereNotNull('file_path')
            ->update([
                'uploaded_by' => DB::raw('verified_by'),
            ]);
    }

    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('uploaded_by');
        });
    }
};
