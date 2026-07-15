<?php

use App\Models\RequiredDocument;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        RequiredDocument::updateOrCreate(
            ['name' => 'Signed DTTR'],
            [
                'description' => 'Signed Daily Time & Tasks Record for the entire internship period',
                'is_mandatory' => true,
                'order_index' => 15,
                'phase' => 'post',
            ]
        );

        RequiredDocument::updateOrCreate(
            ['name' => 'Signed Weekly Journal'],
            [
                'description' => 'Signed and compiled weekly journals for the entire internship period',
                'is_mandatory' => true,
                'order_index' => 16,
                'phase' => 'post',
            ]
        );
    }

    public function down(): void
    {
        RequiredDocument::whereIn('name', ['Signed DTTR', 'Signed Weekly Journal'])->delete();
    }
};
