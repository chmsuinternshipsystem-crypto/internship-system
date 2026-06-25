<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\CompanyIndustry;
use App\Models\Deployment;
use App\Models\Student;
use App\Services\GeocodingService;
use App\Support\InternshipRoles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::with('industry')->orderBy('name');

        $industryFilter = $request->query('industry');
        if ($industryFilter !== null && $industryFilter !== '') {
            $query->where('company_industry_id', $industryFilter);
        }

        if ($request->boolean('geofenced')) {
            $query->whereNotNull('latitude')->whereNotNull('longitude');
        }

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $needle = strtolower($search);
            $query->where(function ($q) use ($like, $needle) {
                $q->where('name', 'like', $like)
                    ->orWhere('contact_last_name', 'like', $like)
                    ->orWhere('contact_first_name', 'like', $like)
                    ->orWhere('contact_middle_initial', 'like', $like)
                    ->orWhere('contact_name_extension', 'like', $like)
                    ->orWhere('contact_person', 'like', $like)
                    ->orWhere('street_address', 'like', $like)
                    ->orWhere('barangay', 'like', $like)
                    ->orWhere('city_municipality', 'like', $like)
                    ->orWhere('address', 'like', $like)
                    ->orWhere('contact_email', 'like', $like)
                    ->orWhere('contact_phone', 'like', $like);

                if (in_array($needle, ['active', 'inactive'], true)) {
                    $q->orWhere('is_active', $needle === 'active');
                }
            });
        }

        $companies = $query->paginate(5)->withQueryString();

        $canManage = in_array((string) (auth()->user()?->role ?? ''), InternshipRoles::operationalManagerRoles(), true);
        $industries = CompanyIndustry::active()->ordered()->get();
        $geofenced = $request->boolean('geofenced');
        $viewData = compact('companies', 'search', 'canManage', 'industries', 'industryFilter', 'geofenced');

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('companies.partials.ajax-list', $viewData);
        }

        return view('companies.index', $viewData);
    }

    public function create()
    {
        $industries = CompanyIndustry::active()->ordered()->get();

        return view('companies.create', compact('industries'));
    }

    public function store(StoreCompanyRequest $request)
    {
        $data = $request->validated();

        $data['is_active'] = $data['is_active'] ?? true;
        $data['geofencing_enabled'] = $request->has('geofencing_enabled');
        $data['geofence_radius_meters'] = isset($data['geofence_radius_meters']) && $data['geofence_radius_meters'] !== '' && $data['geofence_radius_meters'] !== null
            ? (int) $data['geofence_radius_meters']
            : 100;

        Company::create($data);

        return redirect()
            ->route('companies.index')
            ->with('status', __('Company created successfully.'))
            ->with('status_type', 'success');
    }

    // ============ Show Page — Unified Tabbed Student List ============

    public function show(Company $company, Request $request)
    {
        $company->load(['industry']);

        $tab = $request->query('tab', 'all');
        $deployments = $this->companyDeploymentsQuery($company, $tab)->paginate(5)->withQueryString();

        $canManage = in_array((string) (auth()->user()?->role ?? ''), InternshipRoles::operationalManagerRoles(), true);

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('companies.partials.students-list', compact('company', 'deployments', 'tab', 'canManage'));
        }

        return view('companies.show', compact('company', 'deployments', 'tab', 'canManage'));
    }

    private function companyDeploymentsQuery(Company $company, string $tab)
    {
        $q = $company->deployments()->with('student:id,student_number,name,section')->orderByDesc('start_date');

        if ($tab === 'pending') {
            $q->where('status', 'pending');
        } elseif ($tab === 'deployed') {
            $q->whereIn('status', ['active', 'completed']);
        }

        return $q;
    }

    // ============ Edit Page — Assignment Hub ============

    public function edit(Company $company, Request $request)
    {
        $industries = CompanyIndustry::active()->ordered()->get();
        $canManage = in_array((string) (auth()->user()?->role ?? ''), InternshipRoles::operationalManagerRoles(), true);

        $assignedDeployments = $company->deployments()
            ->with('student:id,student_number,name,section')
            ->orderByDesc('start_date')
            ->paginate(5)
            ->withQueryString();

        // If HTMX request for assigned list, return only the partial
        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('companies.partials.assigned-list', compact('company', 'assignedDeployments'));
        }

        // Load initial assignable students for the Add Students panel
        $students = Student::with('assignedInstructor')
            ->whereDoesntHave('deployments', function ($q) {
                $q->whereIn('status', ['active', 'completed'])
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'pending')->whereNotNull('company_id');
                  });
            })
            ->orderBy('student_number')
            ->paginate(10);
        $search = '';
        $myStudents = false;

        return view('companies.edit', compact('company', 'industries', 'canManage', 'assignedDeployments', 'students', 'search', 'myStudents'));
    }

    /**
     * HTMX partial: paginated list of assignable (pending, no company) students.
     */
    public function assignableStudents(Company $company, Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $myStudents = $request->boolean('my_students');

        $query = Student::with('assignedInstructor')
            ->whereDoesntHave('deployments', function ($q) {
                $q->whereIn('status', ['active', 'completed'])
                  ->orWhere(function ($sub) {
                      $sub->where('status', 'pending')->whereNotNull('company_id');
                  });
            })
            ->orderBy('student_number');

        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($like) {
                $q->where('student_number', 'like', $like)
                  ->orWhere('last_name', 'like', $like)
                  ->orWhere('first_name', 'like', $like);
            });
        }

        if ($myStudents) {
            $query->where('assigned_instructor_id', Auth::id());
        }

        $students = $query->paginate(10)->withQueryString();

        return view('companies.partials.assignable-list', compact('company', 'students', 'search', 'myStudents'));
    }

    /**
     * Batch assign multiple students to this company.
     */
    public function attachStudents(Company $company, Request $request)
    {
        $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ]);

        $attached = 0;
        foreach ($request->student_ids as $studentId) {
            $student = Student::find($studentId);
            if (! $student) continue;

            $deployment = $student->deployments()
                ->where('status', 'pending')
                ->whereNull('company_id')
                ->first();

            if ($deployment) {
                $deployment->update(['company_id' => $company->id]);
                $attached++;
            }
        }

        return redirect()
            ->route('companies.edit', $company)
            ->with('status', __(':count student(s) assigned to :company.', ['count' => $attached, 'company' => $company->name]))
            ->with('status_type', $attached > 0 ? 'success' : 'error');
    }

    /**
     * Detach a student from this company (sets company_id back to null).
     */
    public function detachStudent(Company $company, Request $request)
    {
        $request->validate([
            'deployment_id' => ['required', 'integer', 'exists:deployments,id'],
        ]);

        $deployment = Deployment::where('id', $request->deployment_id)
            ->where('company_id', $company->id)
            ->firstOrFail();

        if ($deployment->status !== 'pending') {
            return back()->with('status', __('Only pending deployments can be removed from a company.'))->with('status_type', 'error');
        }

        $deployment->update(['company_id' => null]);

        return back()->with('status', __('Student removed from :company.', ['company' => $company->name]))->with('status_type', 'success');
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $data = $request->validated();

        $data['is_active'] = $request->has('is_active');
        $data['geofencing_enabled'] = $request->has('geofencing_enabled');
        $data['geofence_radius_meters'] = isset($data['geofence_radius_meters']) && $data['geofence_radius_meters'] !== '' && $data['geofence_radius_meters'] !== null
            ? (int) $data['geofence_radius_meters']
            : ($company->geofence_radius_meters ?? 100);

        $company->update($data);

        return redirect()
            ->route('companies.index')
            ->with('status', __('Company updated successfully.'))
            ->with('status_type', 'success');
    }

    public function geocode(Request $request)
    {
        $address = trim((string) $request->query('address', ''));
        if ($address === '') {
            return response()->json(['error' => __('Address is required.')], 422);
        }

        $result = (new GeocodingService)->geocode($address);

        if ($result === null) {
            return response()->json(['error' => __('Address not found.')], 404);
        }

        return response()->json($result);
    }

    public function destroy(Company $company)
    {
        $canDelete = $company->canDelete();
        if ($canDelete !== true) {
            return redirect()
                ->route('companies.index')
                ->with('status', $canDelete)
                ->with('status_type', 'error');
        }

        $company->delete();

        return redirect()
            ->route('companies.index')
            ->with('status', __('Company deleted successfully.'))
            ->with('status_type', 'success');
    }
}
