<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Deployment;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\Certificate;
use App\Models\DailyTimeRecord;
use App\Models\StudentAccount;
use App\Models\StudentDocument;
use App\Models\User;
use App\Models\WeeklyJournal;
use App\Support\InternshipRoles;
use App\Support\OjtGradeCalculator;
use App\Support\StudentListSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('assignedInstructor')->orderBy('student_number');

        $search = trim((string) $request->query('search', ''));
        $section = trim((string) $request->query('section', ''));
        if ($section !== '' && in_array($section, ['A', 'B', 'C', 'D'], true)) {
            $query->where('section', $section);
        }
        StudentListSearch::apply($query, $search);

        $myStudents = $request->boolean('my_students');
        $user = Auth::user();
        if ($myStudents && $user) {
            $query->where('assigned_instructor_id', $user->id);
        }

        $deploymentStatus = trim((string) $request->query('deployment_status', ''));
        if ($deploymentStatus === 'pending') {
            $query->whereDoesntHave('deployments', fn ($q) => $q->whereIn('status', ['active', 'completed']));
        } elseif ($deploymentStatus === 'deployed') {
            $query->whereHas('deployments', fn ($q) => $q->whereIn('status', ['active', 'completed']));
        }

        $noCompany = $request->boolean('no_company');
        if ($noCompany) {
            $query->whereHas('deployments', fn ($q) => $q->whereNull('company_id')->whereIn('status', ['pending', 'active']));
        }

        $students = $query->paginate(5)->withQueryString();

        // Pre-compute progress values matching the model's phase-aware logic
        $mandatoryIds = RequiredDocument::where('is_mandatory', true)->pluck('id');
        $phaseDocIds = RequiredDocument::where('is_mandatory', true)->get()->groupBy('phase')->map(fn ($docs) => $docs->pluck('id'));
        $preMandatoryIds = $phaseDocIds->get('pre', collect());
        $monitoringMandatoryIds = $phaseDocIds->get('monitoring', collect());
        $postMandatoryIds = $phaseDocIds->get('post', collect());
        $allMandatoryIds = $preMandatoryIds->merge($monitoringMandatoryIds)->merge($postMandatoryIds);

        $studentIds = $students->pluck('id');

        $submittedCounts = StudentDocument::whereIn('student_id', $studentIds)
            ->whereIn('required_document_id', $allMandatoryIds)
            ->where('status', 'Submitted')
            ->groupBy('student_id')
            ->selectRaw('student_id, count(*) as count')
            ->pluck('count', 'student_id');

        // Pre-docs with workflow completion (stricter check for phase unlocking)
        $preApprovedCounts = StudentDocument::whereIn('student_id', $studentIds)
            ->whereIn('required_document_id', $preMandatoryIds)
            ->where('status', 'Submitted')
            ->where(function ($q) {
                $q->where('workflow_status', 'completed')
                  ->orWhereNull('workflow_template_id');
            })
            ->groupBy('student_id')
            ->selectRaw('student_id, count(*) as count')
            ->pluck('count', 'student_id');

        // Monitoring submission check
        $monitoringSubmittedCounts = StudentDocument::whereIn('student_id', $studentIds)
            ->whereIn('required_document_id', $monitoringMandatoryIds)
            ->where('status', 'Submitted')
            ->groupBy('student_id')
            ->selectRaw('student_id, count(*) as count')
            ->pluck('count', 'student_id');

        $preTotal = $preMandatoryIds->count();
        $monitoringTotal = $monitoringMandatoryIds->count();
        $allTotal = $allMandatoryIds->count();

        $progressData = [];
        foreach ($studentIds as $id) {
            $submitted = (int) ($submittedCounts[$id] ?? 0);
            $preApproved = $preTotal > 0 && ($preApprovedCounts[$id] ?? 0) >= $preTotal;
            $monitoringAllSubmitted = $monitoringTotal > 0 && ($monitoringSubmittedCounts[$id] ?? 0) >= $monitoringTotal;

            if (! $preApproved) {
                $denominator = $preTotal;
            } elseif (! $monitoringAllSubmitted) {
                $denominator = $preTotal + $monitoringTotal;
            } else {
                $denominator = $allTotal;
            }

            $progressData[$id] = [
                'doc_pct' => $denominator > 0 ? (int) round(($submitted / $denominator) * 100) : 0,
            ];
        }

        $instructors = User::where('role', 'instructor')->orderBy('name')->get(['id', 'name']);

        $hasActiveFilters = $search !== '' || $section !== '' || $myStudents || $deploymentStatus !== '' || $noCompany;
        $canManage = in_array((string) ($user->role ?? ''), InternshipRoles::operationalManagerRoles(), true);
        $totalMatching = $query->toBase()->getCountForPagination();

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('students.partials.ajax-list', compact('students', 'canManage', 'search', 'section', 'myStudents', 'totalMatching', 'progressData', 'instructors', 'deploymentStatus', 'noCompany'));
        }

        return view('students.index', compact('students', 'search', 'section', 'hasActiveFilters', 'canManage', 'myStudents', 'totalMatching', 'progressData', 'instructors', 'deploymentStatus', 'noCompany'));
    }

    public function create()
    {
        $instructors = User::where('role', 'instructor')->orderBy('name')->get();
        $ojtTypes = ['unplaced' => 'No Placement Yet', 'internal' => 'Internal OJT (School-based)', 'external' => 'External OJT (With Company)'];

        return view('students.create', compact('instructors', 'ojtTypes'));
    }

    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();

        $duplicateName = Student::where('first_name', $data['first_name'])
            ->where('last_name', $data['last_name'])
            ->where('middle_name', $data['middle_name'] ?? null)
            ->where('name_extension', $data['name_extension'] ?? null)
            ->where('student_number', '!=', $data['student_number'])
            ->exists();

        DB::transaction(function () use ($data): void {
            $student = Student::create([
                'user_id' => null,
                'assigned_instructor_id' => $data['assigned_instructor_id'] ?? auth()->id(),
                'ojt_type' => $data['ojt_type'] ?? 'unplaced',
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'name_extension' => $data['name_extension'] ?? null,
                'student_number' => $data['student_number'],
                'program' => $data['program'],
                'year_level' => $data['year_level'],
                'section' => $data['section'],
                'contact_number' => $data['contact_number'],
            ]);

            StudentAccount::create([
                'student_id' => $student->id,
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['account_password'] ?? $data['student_number']),
                'is_active' => true,
            ]);

            $deployment = Deployment::create([
                'student_id' => $student->id,
                'company_id' => null,
                'start_date' => today(),
                'status' => 'pending',
            ]);

            if (($data['ojt_type'] ?? '') === 'external' && ! empty($data['company_id'])) {
                $deployment->update(['company_id' => (int) $data['company_id']]);
            }
        });

        $message = __('Student profile created successfully.');
        if ($duplicateName) {
            $message .= ' ' . __('Note: Another student with the same name already exists in the system.');
        }

        return redirect()
            ->route('students.index')
            ->with('status', $message)
            ->with('status_type', $duplicateName ? 'warning' : 'success');
    }

    public function checkDuplicateName(\Illuminate\Http\Request $request)
    {
        $firstName = trim((string) $request->query('first_name', ''));
        $lastName = trim((string) $request->query('last_name', ''));
        $middleName = trim((string) $request->query('middle_name', ''));
        $nameExtension = trim((string) $request->query('name_extension', ''));
        $excludeId = (int) $request->query('exclude', 0);

        if ($firstName === '' || $lastName === '') {
            return response()->json(['duplicate' => false]);
        }

        $exists = Student::where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->where('middle_name', $middleName ?: null)
            ->where('name_extension', $nameExtension ?: null)
            ->when($excludeId > 0, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        return response()->json(['duplicate' => $exists]);
    }

    public function show(Student $student)
    {
        $student->load([
            'deployments.company',
            'documents.requiredDocument',
            'assignedInstructor',
        ]);

        $latestDeployment = $student->deployments()
            ->orderByDesc('start_date')
            ->first();

        $mandatoryIds = RequiredDocument::where('is_mandatory', true)->pluck('id');

        $complianceLabel = __('No mandatory documents');
        $complianceVariant = 'inactive';
        $submittedMandatory = 0;
        $totalMandatory = $mandatoryIds->count();

        if ($mandatoryIds->isNotEmpty()) {
            $docsByRequiredId = $student->documents
                ->whereIn('required_document_id', $mandatoryIds)
                ->keyBy('required_document_id');

            foreach ($mandatoryIds as $docId) {
                $studentDoc = $docsByRequiredId->get($docId);
                if (($studentDoc?->status ?? 'Missing') === 'Submitted') {
                    $submittedMandatory++;
                }
            }

            if ($submittedMandatory === $totalMandatory) {
                $complianceLabel = __('Complete');
                $complianceVariant = 'compliant';
            } elseif ($submittedMandatory === 0) {
                $complianceLabel = __('Needs Submission');
                $complianceVariant = 'non_compliant';
            } else {
                $complianceLabel = __('In Progress');
                $complianceVariant = 'partial';
            }
        }

        $ojtGrade = OjtGradeCalculator::summary($student);

        // Only load profile + documents data on initial page load.
        // Journals, DTR, attendance, certificates are lazy-loaded via HTMX tab clicks.

        return view('students.show', compact(
            'student',
            'latestDeployment',
            'complianceLabel',
            'complianceVariant',
            'submittedMandatory',
            'totalMandatory',
            'ojtGrade',
        ));
    }

    public function edit(Student $student)
    {
        $student->load(['user', 'account', 'assignedInstructor', 'deployments.company']);
        $instructors = User::where('role', 'instructor')->orderBy('name')->get();
        $ojtTypes = ['unplaced' => 'No Placement Yet', 'internal' => 'Internal OJT (School-based)', 'external' => 'External OJT (With Company)'];

        $latestDeployment = $student->deployments()->latest()->first();
        $hasActiveDeployment = $latestDeployment && in_array($latestDeployment->status, ['active', 'completed']);
        $isPendingDeployment = $latestDeployment && $latestDeployment->status === 'pending';
        $selectedCompanyId = $latestDeployment?->company_id;

        return view('students.edit', compact('student', 'instructors', 'ojtTypes', 'hasActiveDeployment', 'isPendingDeployment', 'selectedCompanyId'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $data = $request->validated();

        $latestDeployment = $student->deployments()->latest()->first();
        $companyLocked = $latestDeployment && in_array($latestDeployment->status, ['active', 'completed']);

        DB::transaction(function () use ($student, $data, $companyLocked, $latestDeployment): void {
            $account = $student->account;
            if (! $account) {
                $account = StudentAccount::create([
                    'student_id' => $student->id,
                    'email' => $data['email'] ?? null,
                    'password' => Hash::make($data['account_password'] ?? $data['student_number']),
                    'is_active' => true,
                ]);
            } else {
                if (! empty($data['account_password'])) {
                    $account->password = Hash::make($data['account_password']);
                }
                if (array_key_exists('email', $data)) {
                    $account->email = $data['email'];
                }
                $account->save();
            }

            $fillData = [
                'student_number' => $data['student_number'],
                'program' => $data['program'],
                'year_level' => $data['year_level'],
                'section' => $data['section'],
                'contact_number' => $data['contact_number'],
                'ojt_type' => $data['ojt_type'] ?? 'unplaced',
                'assigned_instructor_id' => $data['assigned_instructor_id'] ?? null,
            ];

            $nameLocked = $companyLocked || ($latestDeployment && $latestDeployment->status === 'pending');
            if (! $nameLocked) {
                $fillData['last_name'] = $data['last_name'];
                $fillData['first_name'] = $data['first_name'];
                $fillData['middle_name'] = $data['middle_name'] ?? null;
                $fillData['name_extension'] = $data['name_extension'] ?? null;
            }

            $student->fill($fillData);
            $student->save();

            if (! $companyLocked && ($data['ojt_type'] ?? '') === 'external' && ! empty($data['company_id'])) {
                $pending = $student->deployments()->where('status', 'pending')->latest()->first();
                if ($pending) {
                    $pending->update(['company_id' => (int) $data['company_id']]);
                }
            }
        });

        return redirect()
            ->route('students.index')
            ->with('status', __('Student profile updated successfully.'))
            ->with('status_type', 'success');
    }

    public function destroy(Student $student)
    {
        $canDelete = $student->canDelete();
        if ($canDelete !== true) {
            return redirect()
                ->route('students.index')
                ->with('status', $canDelete)
                ->with('status_type', 'error');
        }

        $student->delete();

        return redirect()
            ->route('students.index')
            ->with('status', __('Student profile deleted successfully.'))
            ->with('status_type', 'success');
    }

    // ============ Lazy-loaded Tab Partials ============

    public function tabProfile(Student $student)
    {
        $canManage = in_array(auth()->user()?->role ?? null, InternshipRoles::operationalManagerRoles(), true);

        $latestDeployment = $student->deployments()
            ->orderByDesc('start_date')
            ->first();

        $mandatoryIds = RequiredDocument::where('is_mandatory', true)->pluck('id');
        $complianceLabel = __('No mandatory documents');
        $complianceVariant = 'inactive';
        $submittedMandatory = 0;
        $totalMandatory = $mandatoryIds->count();

        if ($mandatoryIds->isNotEmpty()) {
            $docsByRequiredId = $student->documents
                ->whereIn('required_document_id', $mandatoryIds)
                ->keyBy('required_document_id');

            foreach ($mandatoryIds as $docId) {
                $studentDoc = $docsByRequiredId->get($docId);
                if (($studentDoc?->status ?? 'Missing') === 'Submitted') {
                    $submittedMandatory++;
                }
            }

            if ($submittedMandatory === $totalMandatory) {
                $complianceLabel = __('Complete');
                $complianceVariant = 'compliant';
            } elseif ($submittedMandatory === 0) {
                $complianceLabel = __('Needs Submission');
                $complianceVariant = 'non_compliant';
            } else {
                $complianceLabel = __('In Progress');
                $complianceVariant = 'partial';
            }
        }

        $ojtGrade = OjtGradeCalculator::summary($student);

        return view('students.partials.tab-profile', compact(
            'student', 'canManage', 'latestDeployment',
            'complianceLabel', 'complianceVariant',
            'submittedMandatory', 'totalMandatory', 'ojtGrade',
        ));
    }

    public function tabDocuments(Student $student)
    {
        return view('students.partials.tab-documents', compact('student'));
    }

    public function tabJournals(Student $student)
    {
        $journals = WeeklyJournal::where('student_id', $student->id)
            ->with(['deployment.company', 'reviewer'])
            ->orderBy('week_number')
            ->get();

        return view('students.partials.tab-journals', compact('journals', 'student'));
    }

    public function tabDtr(Student $student)
    {
        $deployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->first();

        if ($deployment) {
            $startDate = $deployment->start_date ? Carbon::parse($deployment->start_date) : now()->subMonth();
            $attendanceRecords = Attendance::where('student_id', $student->id)
                ->where('check_in_at', '>=', $startDate->startOfDay())
                ->get()
                ->keyBy(fn ($a) => $a->check_in_at->format('Y-m-d'));

            $existingDtrs = DailyTimeRecord::where('student_id', $student->id)
                ->get()
                ->keyBy(fn ($r) => $r->date->format('Y-m-d'));

            foreach ($attendanceRecords as $dateStr => $att) {
                $dtrData = [
                    'student_id' => $student->id,
                    'deployment_id' => $deployment->id,
                    'source' => 'attendance',
                    'date' => $dateStr,
                    'am_arrival' => $att->am_check_in?->format('H:i:s') ?? $att->check_in_at?->format('H:i:s'),
                    'am_departure' => $att->am_check_out?->format('H:i:s'),
                    'pm_arrival' => $att->pm_check_in?->format('H:i:s'),
                    'pm_departure' => $att->pm_check_out?->format('H:i:s') ?? $att->time_out_at?->format('H:i:s'),
                    'total_minutes' => $att->total_minutes,
                ];

                if ($existingDtrs->has($dateStr)) {
                    $existing = $existingDtrs->get($dateStr);
                    if ($existing->am_departure !== $dtrData['am_departure']
                        || $existing->pm_arrival !== $dtrData['pm_arrival']
                        || $existing->pm_departure !== $dtrData['pm_departure']
                        || $existing->total_minutes !== $dtrData['total_minutes']) {
                        $existing->update($dtrData);
                    }
                } else {
                    DailyTimeRecord::create($dtrData);
                }
            }
        }

        $dtrRecords = DailyTimeRecord::where('student_id', $student->id)
            ->orderByDesc('date')
            ->paginate(15)
            ->withQueryString();

        return view('students.partials.tab-dtr', compact('dtrRecords', 'student'));
    }

    public function tabAttendance(Student $student)
    {
        $attendances = \App\Models\Attendance::where('student_id', $student->id)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('students.partials.tab-attendance', compact('attendances', 'student'));
    }

    public function tabCertificates(Student $student)
    {
        $canManage = in_array(auth()->user()?->role ?? null, \App\Support\InternshipRoles::operationalManagerRoles(), true);
        $certificates = Certificate::where('student_id', $student->id)
            ->with(['uploader', 'verifier'])
            ->orderByDesc('created_at')
            ->get();

        return view('students.partials.tab-certificates', compact('certificates', 'canManage', 'student'));
    }
}
