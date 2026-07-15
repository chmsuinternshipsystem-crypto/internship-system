<?php

use App\Models\RequiredDocument;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Delete stale records with old names
        RequiredDocument::where('name', 'Final DTR')->delete();
        RequiredDocument::where('name', 'Final Weekly Journal')->delete();

        // Delete any Signed DTTR/WJ that have a higher order_index than expected (duplicates)
        RequiredDocument::where('name', 'Signed DTTR')->where('order_index', '>', 13)->delete();
        RequiredDocument::where('name', 'Signed Weekly Journal')->where('order_index', '>', 14)->delete();
    }

    public function down(): void
    {
        // No reversal — this cleans up duplicate state.
    }
};
