<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateDeploymentRequest;
use App\Models\Company;
use App\Models\CompanyIndustry;
use App\Models\Deployment;
use App\Models\Student;
use App\Models\StudentAccount;
use App\Services\NotificationService;
use App\Support\DeploymentListSearch;
use App\Support\InternshipRoles;

class DeploymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(\Illuminate\Http\Request $request)
    {
        $query = Deployment::with(['student', 'company.industry'])
            ->orderByDesc('start_date');

        $search = trim((string) $request->query('search', ''));
        $industryFilter = $request->query('industry');
        $industryId = $industryFilter !== null && $industryFilter !== '' ? (int) $industryFilter : null;
        $statusFilter = trim((string) $request->query('status', ''));
        $myStudents = $request->has('my_students') ? $request->boolean('my_students') : true;

        if ($search !== '' || $industryId !== null) {
            DeploymentListSearch::applyWithIndustry($query, $search, $industryId);
        }

        if ($statusFilter !== '' && in_array($statusFilter, ['active', 'completed', 'pending', 'withdrawn'], true)) {
            $query->where('status', $statusFilter);
        }

        if ($myStudents && auth()->user()?->role === 'instructor') {
            $query->whereHas('student', fn ($q) => $q->where('assigned_instructor_id', auth()->id()));
        }

        $deployments = $query->paginate(5)->withQueryString();

        $canManage = in_array((string) (auth()->user()?->role ?? ''), InternshipRoles::operationalManagerRoles(), true);
        $industries = CompanyIndustry::active()->ordered()->get();
        $viewData = compact('deployments', 'search', 'canManage', 'industries', 'industryFilter', 'statusFilter', 'myStudents');

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('deployments.partials.ajax-list', $viewData);
        }

        return view('deployments.index', $viewData);
    }

    /**
     * Show the form for creating a new resource.
     */
    /**
     * Display the specified resource.
     */
    public function show(Deployment $deployment)
    {
        $deployment->load(['student', 'company']);

        return view('deployments.show', compact('deployment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Deployment $deployment)
    {
        $students = Student::orderBy('student_number')->get();
        $companies = Company::orderBy('name')->get();

        return view('deployments.edit', compact('deployment', 'students', 'companies'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDeploymentRequest $request, Deployment $deployment)
    {
        $data = $request->validated();

        $deployment->update($data);

        return redirect()
            ->route('deployments.index')
            ->with('status', __('Deployment updated successfully.'))
            ->with('status_type', 'success');
    }

    public function assignCompany(\Illuminate\Http\Request $request, Deployment $deployment)
    {
        $request->validate([
            'company_id' => ['required', 'integer', 'exists:companies,id'],
        ]);

        $deployment->update([
            'company_id' => (int) $request->input('company_id'),
        ]);

        $student = $deployment->student;
        if ($student && $student->account) {
            $company = $deployment->company;
            app(NotificationService::class)->notifyStudentAccount($student->account, [
                'event_type' => 'deployment.company_assigned',
                'title' => __('Company Assigned'),
                'body' => __('You have been assigned to :company for your OJT.', [
                    'company' => $company?->name ?? __('a company'),
                ]),
                'action_url' => route('student.dashboard'),
                'meta' => [
                    'deployment_id' => (int) $deployment->id,
                    'company_id' => (int) $deployment->company_id,
                ],
            ]);
        }

        $student?->autoActivateDeployment();

        $redirectBack = $request->input('return', $request->query('return', 'deployments.index'));

        return redirect()
            ->route($redirectBack === 'student' ? 'students.show' : 'deployments.index', $redirectBack === 'student' ? ['student' => $deployment->student_id] : [])
            ->with('status', __('Company assigned successfully. Student has been notified.'))
            ->with('status_type', 'success');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deployment $deployment)
    {
        $canDelete = $deployment->canDelete();
        if ($canDelete !== true) {
            return redirect()
                ->route('deployments.index')
                ->with('status', $canDelete)
                ->with('status_type', 'error');
        }

        $deployment->delete();

        return redirect()
            ->route('deployments.index')
            ->with('status', __('Deployment deleted successfully.'))
            ->with('status_type', 'success');
    }
}
