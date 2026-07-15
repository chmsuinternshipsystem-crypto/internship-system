<?php

namespace App\Http\Controllers;

use App\Models\WeeklyJournal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UndoController extends Controller
{
    public function restore(Request $request, string $key)
    {
        $data = Cache::get($key);

        if (! $data || ! is_array($data)) {
            return back()->with('status', __('Undo window has expired.'))
                ->with('status_type', 'error');
        }

        $modelClass = $data['model'] ?? '';
        $modelId = (int) ($data['id'] ?? 0);
        $action = $data['action'] ?? '';
        $previous = $data['previous'] ?? [];

        if ($modelClass === WeeklyJournal::class && $modelId > 0) {
            $journal = WeeklyJournal::withTrashed()->find($modelId);
            if (! $journal) {
                return back()->with('status', __('Record not found.'))
                    ->with('status_type', 'error');
            }

            if ($action === 'submit') {
                $journal->update([
                    'status' => $previous['status'] ?? 'draft',
                    'submitted_at' => $previous['submitted_at'] ?? null,
                ]);

                Cache::forget($key);

                return back()->with('status', __('Journal submission undone.'))
                    ->with('status_type', 'success');
            }
        }

        return back()->with('status', __('Cannot undo this action.'))
            ->with('status_type', 'error');
    }
}
