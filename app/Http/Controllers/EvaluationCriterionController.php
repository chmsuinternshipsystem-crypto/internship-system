<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\EvaluationCriterion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvaluationCriterionController extends Controller
{
    public function index()
    {
        $this->authorize('manage', Evaluation::class);

        $categories = Evaluation::CRITERIA_CATEGORIES;
        $criteria = EvaluationCriterion::where('is_active', true)
            ->orderBy('category_key')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category_key');

        return view('evaluations.criteria', compact('categories', 'criteria'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage', Evaluation::class);

        $validated = $request->validate([
            'category_key' => ['required', 'string', Rule::in(array_keys(Evaluation::CRITERIA_CATEGORIES))],
            'item_key' => ['required', 'string', 'max:100',
                Rule::unique('evaluation_criteria', 'item_key')->where(fn ($q) => $q->where('category_key', $request->category_key)),
            ],
            'item_label' => ['required', 'string', 'max:255'],
        ]);

        $categoryLabel = Evaluation::CRITERIA_CATEGORIES[$validated['category_key']]['label'];
        $maxSort = EvaluationCriterion::where('category_key', $validated['category_key'])->max('sort_order') ?? 0;

        EvaluationCriterion::create([
            'category_key' => $validated['category_key'],
            'category_label' => $categoryLabel,
            'item_key' => $validated['item_key'],
            'item_label' => $validated['item_label'],
            'sort_order' => $maxSort + 1,
            'is_active' => true,
        ]);

        EvaluationCriterion::flushCriteriaCache();

        return redirect()->route('evaluations.criteria.index')
            ->with('status', __('Criterion added.'))
            ->with('status_type', 'success');
    }

    public function destroy(EvaluationCriterion $criterion)
    {
        $this->authorize('manage', Evaluation::class);

        $criterion->update(['is_active' => false]);
        EvaluationCriterion::flushCriteriaCache();

        return redirect()->route('evaluations.criteria.index')
            ->with('status', __('Criterion removed.'))
            ->with('status_type', 'success');
    }
}
