<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use App\Models\EvaluationCriterion;
use Illuminate\Database\Seeder;

class EvaluationCriterionSeeder extends Seeder
{
    public function run(): void
    {
        EvaluationCriterion::truncate();

        $sort = 0;
        foreach (Evaluation::CRITERIA_CATEGORIES as $categoryKey => $category) {
            foreach ($category['items'] as $itemKey => $itemLabel) {
                EvaluationCriterion::create([
                    'category_key' => $categoryKey,
                    'category_label' => $category['label'],
                    'item_key' => $itemKey,
                    'item_label' => $itemLabel,
                    'sort_order' => $sort++,
                    'is_active' => true,
                ]);
            }
        }

        EvaluationCriterion::flushCriteriaCache();
    }
}
