<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Shared LIKE search across student list columns that appear in tables (staff lists, compliance, deployment joins).
 */
final class StudentListSearch
{
    public static function apply(Builder $query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $like = '%'.$search.'%';

        $query->where(function (Builder $q) use ($like, $search) {
            $q->where('student_number', 'like', $like)
                ->orWhere('name', 'like', $like)
                ->orWhere('last_name', 'like', $like)
                ->orWhere('first_name', 'like', $like)
                ->orWhere('program', 'like', $like)
                ->orWhere('section', 'like', $like)
                ->orWhere('contact_number', 'like', $like)
                ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', $like)
                ->orWhere(DB::raw("CONCAT(last_name, ', ', first_name)"), 'like', $like)
                ->orWhere(DB::raw("CONCAT(first_name, ' ', middle_name, ' ', last_name)"), 'like', $like);

            // year_level is numeric in DB; match string fragments (e.g. "3", "4")
            $q->orWhereRaw('CAST(year_level AS CHAR) LIKE ?', [$like]);
        });
    }
}
