<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EvaluationCriterion extends Model
{
    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'category_key',
        'category_label',
        'item_key',
        'item_label',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public static function getActiveCriteria(): array
    {
        return Cache::remember('evaluation_criteria_active', 3600, function () {
            $criteria = static::query()
                ->where('is_active', true)
                ->orderBy('category_key')
                ->orderBy('sort_order')
                ->get();

            if ($criteria->isEmpty()) {
                return Evaluation::CRITERIA_CATEGORIES;
            }

            $grouped = [];
            foreach ($criteria as $criterion) {
                $ck = $criterion->category_key;
                if (! isset($grouped[$ck])) {
                    $grouped[$ck] = [
                        'label' => $criterion->category_label,
                        'items' => [],
                    ];
                }
                $grouped[$ck]['items'][$criterion->item_key] = $criterion->item_label;
            }

            return $grouped;
        });
    }

    public static function flushCriteriaCache(): void
    {
        Cache::forget('evaluation_criteria_active');
    }
}
