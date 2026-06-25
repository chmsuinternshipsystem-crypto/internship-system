<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Deployment;
use App\Models\Student;
use App\Models\User;
use App\Models\WeeklyJournal;
use App\Support\StudentListSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    public function journals(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:weekly_journals,id'],
            'action' => ['required', 'in:review'],
        ]);

        $count = 0;
        $action = $validated['action'];

        if ($action === 'review') {
            $journals = WeeklyJournal::whereIn('id', $validated['ids'])
                ->where('status', 'submitted')
                ->get();

            foreach ($journals as $journal) {
                $journal->update([
                    'status' => 'reviewed',
                    'reviewed_by' => auth()->id(),
                    'reviewed_at' => now(),
                ]);
                $count++;
            }
        }

        return back()->with('status', __(':count journal(s) processed.', ['count' => $count]))
            ->with('status_type', 'success');
    }

    public function students(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:students,id'],
            'action' => ['required', 'in:deploy,assign-instructor'],
            'select_all_matching' => ['nullable', 'boolean'],
            'filter_section' => ['nullable', 'string', 'max:10'],
            'filter_search' => ['nullable', 'string', 'max:100'],
            'filter_my_students' => ['nullable', 'string', 'max:1'],
            'exclude_ids' => ['nullable', 'array'],
            'exclude_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $selectAll = (bool) ($validated['select_all_matching'] ?? false);
        $excludeIds = $validated['exclude_ids'] ?? [];

        $count = 0;
        $action = $validated['action'];

        // Build query based on filters when select-all-matching is active
        $query = Student::query();
        if ($selectAll) {
            $section = trim((string) ($validated['filter_section'] ?? ''));
            if ($section !== '' && in_array($section, ['A', 'B', 'C', 'D'], true)) {
                $query->where('section', $section);
            }
            $search = trim((string) ($validated['filter_search'] ?? ''));
            StudentListSearch::apply($query, $search);
            if (! empty($validated['filter_my_students'])) {
                $query->where('assigned_instructor_id', auth()->id());
            }
        } else {
            $query->whereIn('id', $validated['ids'] ?? []);
        }

        if ($selectAll && !empty($excludeIds)) {
            $query->whereNotIn('id', $excludeIds);
        }

        if ($action === 'deploy') {
            $companyId = (int) $request->input('company_id');
            $company = Company::findOrFail($companyId);

            $students = $query->get();

            DB::transaction(function () use ($students, $company, &$count): void {
                foreach ($students as $student) {
                    $alreadyDeployed = Deployment::where('student_id', $student->id)
                        ->whereIn('status', ['active', 'completed'])
                        ->exists();

                    if (! $alreadyDeployed) {
                        Deployment::create([
                            'student_id' => $student->id,
                            'company_id' => $company->id,
                            'start_date' => today(),
                        ]);
                        $count++;
                    }
                }
            });

            return back()->with('status', __(':count student(s) deployed to :company.', [
                'count' => $count,
                'company' => $company->name,
            ]))->with('status_type', 'success')->with('batch_cleared', true);
        }

        if ($action === 'assign-instructor') {
            $instructorId = (int) $request->input('instructor_id');
            $instructor = User::findOrFail($instructorId);

            $query->update(['assigned_instructor_id' => $instructorId]);
            $count = $selectAll ? $query->count() : count($validated['ids'] ?? []);

            return back()->with('status', __(':count student(s) assigned to :instructor.', [
                'count' => $count,
                'instructor' => $instructor->name,
            ]))->with('status_type', 'success')->with('batch_cleared', true);
        }

        return back();
    }

    public function attendance(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:attendances,id'],
            'action' => ['required', 'in:resolve'],
            'resolution_note' => ['nullable', 'string', 'max:255'],
        ]);

        $updateData = [
            'resolution_status' => 'resolved',
            'resolved_by' => auth()->id(),
            'resolved_at' => now(),
        ];

        if (isset($validated['resolution_note'])) {
            $updateData['resolution_note'] = trim(strip_tags($validated['resolution_note']));
        }

        $count = Attendance::whereIn('id', $validated['ids'])
            ->where('resolution_status', '!=', 'resolved')
            ->update($updateData);

        if ($request->header('HX-Request')) {
            return response()
                ->back()
                ->with('status', __(':count attendance record(s) resolved.', ['count' => $count]))
                ->with('status_type', 'success')
                ->header('HX-Trigger', 'refresh-attendance');
        }

        return back()->with('status', __(':count attendance record(s) resolved.', ['count' => $count]))
            ->with('status_type', 'success');
    }

    public function certificates(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:certificates,id'],
            'action' => ['required', 'in:verify'],
        ]);

        $count = Certificate::whereIn('id', $validated['ids'])
            ->where('status', 'pending')
            ->update([
                'status' => 'verified',
                'verified_by' => auth()->id(),
                'verified_at' => now(),
            ]);

        return back()->with('status', __(':count certificate(s) verified.', ['count' => $count]))
            ->with('status_type', 'success');
    }
}
