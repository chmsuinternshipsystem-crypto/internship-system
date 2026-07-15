<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL-safe FK alteration using raw SQL to avoid requiring doctrine/dbal.
        DB::statement('ALTER TABLE evaluations DROP FOREIGN KEY evaluations_evaluator_id_foreign');
        DB::statement('ALTER TABLE evaluations MODIFY evaluator_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE evaluations ADD CONSTRAINT evaluations_evaluator_id_foreign FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE SET NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE evaluations DROP FOREIGN KEY evaluations_evaluator_id_foreign');
        DB::statement('ALTER TABLE evaluations MODIFY evaluator_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE evaluations ADD CONSTRAINT evaluations_evaluator_id_foreign FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE');
    }
};
