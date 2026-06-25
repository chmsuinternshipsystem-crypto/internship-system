<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStudentRequest;
use App\Http\Requests\UpdateStudentRequest;
use App\Models\Deployment;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\Certificate;
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

        // Pre-compute progress values in 2 queries instead of per-student N+1
        $mandatoryIds = RequiredDocument::where('is_mandatory', true)->pluck('id');
        $totalMandatory = $mandatoryIds->count();

        $studentIds = $students->pluck('id');

        $submittedCounts = StudentDocument::whereIn('student_id', $studentIds)
            ->whereIn('required_document_id', $mandatoryIds)
            ->where('status', 'Submitted')
            ->groupBy('student_id')
            ->selectRaw('student_id, count(*) as count')
            ->pluck('count', 'student_id');

        $journalCounts = WeeklyJournal::whereIn('student_id', $studentIds)
            ->where('week_end_date', '<=', now())
            ->selectRaw('student_id, count(*) as total, sum(case when status = ? then 1 else 0 end) as reviewed', ['reviewed'])
            ->groupBy('student_id')
            ->get()
            ->keyBy('student_id');

        $progressData = [];
        foreach ($studentIds as $id) {
            $submitted = (int) ($submittedCounts[$id] ?? 0);
            $journal = $journalCounts[$id] ?? null;
            $progressData[$id] = [
                'doc_pct' => $totalMandatory > 0 ? (int) round(($submitted / $totalMandatory) * 100) : 0,
                'journal_pct' => $journal && $journal->total > 0 ? (int) round(($journal->reviewed / $journal->total) * 100) : 0,
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

            Deployment::create([
                'student_id' => $student->id,
                'company_id' => null,
                'start_date' => today(),
                'status' => 'pending',
            ]);
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

        return view('students.edit', compact('student', 'instructors', 'ojtTypes'));
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $data = $request->validated();

        DB::transaction(function () use ($student, $data): void {
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

            $student->fill([
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'] ?? null,
                'name_extension' => $data['name_extension'] ?? null,
                'student_number' => $data['student_number'],
                'program' => $data['program'],
                'year_level' => $data['year_level'],
                'section' => $data['section'],
                'contact_number' => $data['contact_number'],
                'ojt_type' => $data['ojt_type'] ?? 'unplaced',
                'assigned_instructor_id' => $data['assigned_instructor_id'] ?? null,
            ]);

            $student->save();
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
        $dtrRecords = \App\Models\DailyTimeRecord::where('student_id', $student->id)
            ->orderByDesc('date')
            ->take(30)
            ->get();

        return view('students.partials.tab-dtr', compact('dtrRecords', 'student'));
    }

    public function tabAttendance(Student $student)
    {
        $attendances = \App\Models\Attendance::where('student_id', $student->id)
            ->latest('id')
            ->take(30)
            ->get();

        \Illuminate\Support\Facades\Log::debug('TABATT', [
            'sid' => $student->id,
            'count' => $attendances->count(),
        ]);

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
