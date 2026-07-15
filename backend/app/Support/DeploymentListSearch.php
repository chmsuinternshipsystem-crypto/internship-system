<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Search deployments by student text, company text, status, remarks, and dates (including human-readable dates).
 */
final class DeploymentListSearch
{
    public static function apply(Builder $query, string $search): void
    {
        self::applyTextSearch($query, $search);
    }

    public static function applyWithIndustry(Builder $query, string $search, ?int $industryId): void
    {
        self::applyTextSearch($query, $search);

        if ($industryId !== null) {
            $query->whereHas('company', function (Builder $q) use ($industryId) {
                $q->where('company_industry_id', $industryId);
            });
        }
    }

    private static function applyTextSearch(Builder $query, string $search): void
    {
        $search = trim($search);
        if ($search === '') {
            return;
        }

        $like = '%'.$search.'%';

        $parsedDate = null;
        foreach ([$search, str_replace('/', '-', $search)] as $candidate) {
            try {
                $parsedDate = Carbon::parse($candidate);
                break;
            } catch (\Throwable) {
            }
        }

        $driver = DB::connection()->getDriverName();

        $query->where(function (Builder $q) use ($search, $like, $parsedDate, $driver) {
            $q->whereHas('student', function (Builder $q2) use ($search) {
                StudentListSearch::apply($q2, $search);
            })->orWhereHas('company', function (Builder $q2) use ($like) {
                $q2->where('name', 'like', $like)
                    ->orWhere('contact_person', 'like', $like)
                    ->orWhere('contact_email', 'like', $like)
                    ->orWhere('contact_phone', 'like', $like);
            })->orWhere(function (Builder $q2) use ($like, $parsedDate, $driver) {
                $q2->where('status', 'like', $like)
                    ->orWhere('remarks', 'like', $like)
                    ->orWhereRaw('CAST(start_date AS CHAR) LIKE ?', [$like])
                    ->orWhereRaw('CAST(end_date AS CHAR) LIKE ?', [$like]);

                if ($parsedDate !== null) {
                    $d = $parsedDate->toDateString();
                    $q2->orWhereDate('start_date', $d)
                        ->orWhereDate('end_date', $d);
                }

                if ($driver === 'mysql') {
                    $q2->orWhereRaw('DATE_FORMAT(start_date, "%b %Y") LIKE ?', [$like])
                        ->orWhereRaw('DATE_FORMAT(start_date, "%M %Y") LIKE ?', [$like])
                        ->orWhereRaw('DATE_FORMAT(end_date, "%b %Y") LIKE ?', [$like])
                        ->orWhereRaw('DATE_FORMAT(end_date, "%M %Y") LIKE ?', [$like]);
                }
            });
        });
    }
}
