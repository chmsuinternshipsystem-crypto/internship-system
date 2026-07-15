<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Deployment;
use App\Models\Student;
use App\Support\InternshipRoles;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $role = (string) ($user->role ?? '');
        $canManage = in_array($role, InternshipRoles::operationalManagerRoles(), true);

        $type = $request->query('type', 'all');
        $schoolYear = $request->query('school_year', '');

        $students = collect();
        $companies = collect();
        $deployments = collect();

        if (in_array($type, ['all', 'students'], true)) {
            $q = Student::archivedOnly()->with('deployments');
            if ($schoolYear) {
                $q->whereHas('deployments', fn ($dq) => $dq->where('school_year', $schoolYear));
            }
            $students = $q->orderBy('archived_at', 'desc')->paginate(10, ['*'], 'students_page');
        }

        if (in_array($type, ['all', 'companies'], true)) {
            $q = Company::archivedOnly();
            if ($schoolYear) {
                $q->whereHas('deployments', fn ($dq) => $dq->where('school_year', $schoolYear));
            }
            $companies = $q->orderBy('archived_at', 'desc')->paginate(10, ['*'], 'companies_page');
        }

        if (in_array($type, ['all', 'deployments'], true)) {
            $q = Deployment::archivedOnly()->with('student');
            if ($schoolYear) {
                $q->where('school_year', $schoolYear);
            }
            $deployments = $q->orderBy('archived_at', 'desc')->paginate(10, ['*'], 'deployments_page');
        }

        $schoolYears = Deployment::whereNotNull('school_year')
            ->distinct()
            ->orderBy('school_year', 'desc')
            ->pluck('school_year');

        return view('archive.index', compact('students', 'companies', 'deployments', 'type', 'schoolYear', 'schoolYears', 'canManage'));
    }

    public function archiveStudent(Request $request, Student $student)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:255']);
        $student->archive($data['reason'] ?? null);

        return redirect()->route('archive.index')
            ->with('status', __('Student archived successfully.'))
            ->with('status_type', 'success');
    }

    public function restoreStudent(Student $student)
    {
        $student->restore();

        return redirect()->route('archive.index')
            ->with('status', __('Student restored successfully.'))
            ->with('status_type', 'success');
    }

    public function archiveCompany(Request $request, Company $company)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:255']);
        $company->archive($data['reason'] ?? null);

        return redirect()->route('archive.index')
            ->with('status', __('Company archived successfully.'))
            ->with('status_type', 'success');
    }

    public function restoreCompany(Company $company)
    {
        $company->restore();

        return redirect()->route('archive.index')
            ->with('status', __('Company restored successfully.'))
            ->with('status_type', 'success');
    }

    public function archiveDeployment(Request $request, Deployment $deployment)
    {
        $data = $request->validate(['reason' => 'nullable|string|max:255']);
        $deployment->archive($data['reason'] ?? null);

        return redirect()->route('archive.index')
            ->with('status', __('Deployment archived successfully.'))
            ->with('status_type', 'success');
    }

    public function restoreDeployment(Deployment $deployment)
    {
        $deployment->restore();

        return redirect()->route('archive.index')
            ->with('status', __('Deployment restored successfully.'))
            ->with('status_type', 'success');
    }
}
