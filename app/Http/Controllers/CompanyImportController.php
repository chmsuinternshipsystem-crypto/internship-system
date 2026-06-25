<?php

namespace App\Http\Controllers;

use App\Imports\CompaniesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CompanyImportController extends Controller
{
    public function create()
    {
        return view('companies.import');
    }

    public function store(Request $request)
    {
        set_time_limit(0);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new CompaniesImport;
        Excel::import($import, $request->file('file'));

        $message = __('Companies imported. :created created, :updated updated, :skipped skipped.', [
            'created' => $import->getCreatedCount(),
            'updated' => $import->getUpdatedCount(),
            'skipped' => $import->getSkippedCount(),
        ]);

        $unmatched = $import->getUnmatchedIndustries();
        if ($unmatched !== []) {
            $message .= ' ' . __(':count industry name(s) were not found: :names.', [
                'count' => count($unmatched),
                'names' => implode(', ', $unmatched),
            ]);
        }

        $statusType = 'success';
        if ($import->getGeocodeFailures() > 0 || $unmatched !== []) {
            if ($import->getGeocodeFailures() > 0) {
                $message .= ' ' . __(':count address(es) could not be geocoded — you can set coordinates on the edit form.', [
                    'count' => $import->getGeocodeFailures(),
                ]);
            }
            $statusType = 'warning';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => $statusType === 'success',
                'message' => $message,
                'status_type' => $statusType,
                'redirect' => route('companies.index'),
            ]);
        }

        return redirect()
            ->route('companies.index')
            ->with('status', $message)
            ->with('status_type', $statusType);
    }
}
