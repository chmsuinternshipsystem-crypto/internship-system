<?php

use App\Models\RequiredDocument;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('required_documents')
            ->where('name', 'NBI Clearance')
            ->update(['is_mandatory' => false]);

        RequiredDocument::flushCache();
    }

    public function down(): void
    {
        DB::table('required_documents')
            ->where('name', 'NBI Clearance')
            ->update(['is_mandatory' => true]);

        RequiredDocument::flushCache();
    }
};
