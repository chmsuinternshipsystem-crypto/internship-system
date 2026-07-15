<?php

namespace App\Http\Controllers;

use App\Imports\StudentsImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentImportController extends Controller
{
    public function create()
    {
        return view('students.import');
    }

    public function store(Request $request)
    {
        set_time_limit(0);

        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],
        ]);

        $import = new StudentsImport;
        Excel::import($import, $request->file('file'));

        if ($import->allRowsAlreadyExisting()) {
            $error = __('This file has already been imported. All student records already exist in the system.');

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $error,
                    'status_type' => 'error',
                    'redirect' => route('students.index'),
                ]);
            }

            return redirect()
                ->route('students.index')
                ->with('status', $error)
                ->with('status_type', 'error');
        }

        $message = __('Students imported. :created created, :updated updated, :skipped skipped.', [
            'created' => $import->getCreatedCount(),
            'updated' => $import->getUpdatedCount(),
            'skipped' => $import->getSkippedCount(),
        ]);

        $warnings = $import->getDuplicateNameWarnings();
        $statusType = 'success';
        if ($warnings > 0) {
            $message .= ' ' . __(':count student(s) share a name with existing records — please verify.', ['count' => $warnings]);
            $statusType = 'warning';
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => $statusType === 'success',
                'message' => $message,
                'status_type' => $statusType,
                'redirect' => route('students.index'),
            ]);
        }

        return redirect()
            ->route('students.index')
            ->with('status', $message)
            ->with('status_type', $statusType);
    }
}
