<?php

use App\Models\RequiredDocument;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        RequiredDocument::query()
            ->where('name', 'OJT Supervisor Evaluation Form')
            ->update(['order_index' => 13]);
    }

    public function down(): void
    {
        RequiredDocument::query()
            ->where('name', 'OJT Supervisor Evaluation Form')
            ->update(['order_index' => 15]);
    }
};
