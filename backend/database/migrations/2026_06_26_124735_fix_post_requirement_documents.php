<?php

use App\Models\RequiredDocument;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Remove duplicates created by our previous migration (order >= 15)
        RequiredDocument::whereIn('name', ['Signed DTTR', 'Signed Weekly Journal'])
            ->where('order_index', '>=', 15)
            ->delete();

        // Remove any stale records with old names (seeder recreates them)
        RequiredDocument::where('name', 'Final DTR')->delete();
        RequiredDocument::where('name', 'Final Weekly Journal')->delete();

        // Rename existing post docs to "Signed" naming
        RequiredDocument::where('name', 'Signed DTTR')->update([
            'description' => 'Signed Daily Time & Tasks Record for the entire internship period',
        ]);

        RequiredDocument::where('name', 'Signed Weekly Journal')->update([
            'description' => 'Signed and compiled weekly journals for the entire internship period',
        ]);
    }

    public function down(): void
    {
        // No safe reversal — state is cleaned up to match current seeder.
    }
};
