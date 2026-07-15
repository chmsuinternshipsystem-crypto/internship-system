<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Attendance;
use App\Models\Evaluation;
use App\Http\Requests\UpdateStudentProfileRequest;
use App\Models\RequiredDocument;
use App\Models\StudentDocument;
use App\Services\DocumentWorkflowEngine;
use App\Services\EvaluationExportService;
use App\Services\NotificationService;
use App\Support\OjtGradeCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class StudentPortalController extends Controller
{
    public function __construct(
        private readonly DocumentWorkflowEngine $workflowEngine,
        private readonly NotificationService $notificationService
    ) {}

    private function currentStudent(Request $request)
    {
        $student = $request->attributes->get('student');
        abort_unless($student, 403, 'Student profile is not linked to this account.');

        return $student;
    }

    public function exportEvaluation(Evaluation $evaluation)
    {
        $student = $this->currentStudent(request());
        abort_unless($evaluation->student_id === $student->id, 403);
        abort_unless($evaluation->evaluation_type === 'industry', 403);

        $service = new EvaluationExportService();
        $tempPath = $service->generate($evaluation);

        $filename = "OJT-Evaluation-{$student->student_number}.docx";

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function portalAccessStatus(Request $request)
    {
        $student = $this->currentStudent($request);
        $student->autoActivateDeployment();
        \App\Models\Deployment::checkAndActivateForStudent($student);

        return response()->json([
            'has_full_access' => $student->hasFullStudentPortalAccess(),
            'are_pre_docs_approved' => $student->areAllPreDocsApproved(),
        ]);
    }

    public function dashboard()
    {
        $student = $this->currentStudent(request());
        /** @var \App\Models\StudentAccount|null $studentAccount */
        $studentAccount = request()->attributes->get('studentAccount');

        $student->load(['deployments.company']);

        $latestDeployment = $student->deployments()
            ->with('company')
            ->orderByDesc('start_date')
            ->first();

        $mandatoryIds = RequiredDocument::query()
            ->where('is_mandatory', true)
            ->pluck('id');

        $documents = $student->documents()
            ->whereIn('required_document_id', $mandatoryIds)
            ->get()
            ->keyBy('required_document_id');

        $submittedCount = 0;
        foreach ($mandatoryIds as $docId) {
            if (($documents->get($docId)?->status ?? 'Missing') === 'Submitted') {
                $submittedCount++;
            }
        }

        $totalMandatory = $mandatoryIds->count();
        $missingCount = max(0, $totalMandatory - $submittedCount);

        $announcements = Announcement::query()
            ->with('author')
            ->where(function ($query): void {
                $query->whereNull('visible_to_role')
                    ->orWhere('visible_to_role', '')
                    ->orWhereRaw('LOWER(visible_to_role) = ?', ['all'])
                    ->orWhereRaw('LOWER(visible_to_role) = ?', ['student']);
            })
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $alerts = collect();
        if ($latestDeployment && $latestDeployment->status === 'active') {
            $company = $latestDeployment->company?->name ?? 'your assigned company';
            $alerts->push("You are accepted for deployment at {$company}.");
        } elseif ($latestDeployment && $latestDeployment->status === 'completed') {
            $alerts->push('Your deployment has been marked as completed.');
        } else {
            $alerts->push('Your deployment is not active yet. Wait for coordinator updates.');
        }

        if ($missingCount > 0) {
            $alerts->push("You still have {$missingCount} required document(s) to comply.");
        }

        $latestAttendance = Attendance::query()
            ->where('student_id', $student->id)
            ->latest('check_in_at')
            ->first();

        $todayAttendance = Attendance::query()
            ->where('student_id', $student->id)
            ->whereDate('check_in_at', today())
            ->latest('check_in_at')
            ->first();

        $attendancePasscode = null;
        $attendancePasscodeGeneratedAt = null;
        if ($student->isDeploymentEligibleForPortal() && $studentAccount) {
            $attendancePasscode = $studentAccount->ensureAttendancePasscode();
            $attendancePasscodeGeneratedAt = $studentAccount->attendance_passcode_generated_at;
        }

        $ojtGrade = OjtGradeCalculator::summary($student);
        $hteEvaluation = OjtGradeCalculator::latestIndustryEvaluation($student);

        $showGettingStarted = $submittedCount === 0 && ! $latestDeployment;
        $showCompletion = $totalMandatory > 0 && $submittedCount === $totalMandatory && $latestDeployment?->status === 'completed';

        return view('student.dashboard', compact(
            'student',
            'latestDeployment',
            'submittedCount',
            'totalMandatory',
            'missingCount',
            'announcements',
            'alerts',
            'latestAttendance',
            'todayAttendance',
            'attendancePasscode',
            'attendancePasscodeGeneratedAt',
            'ojtGrade',
            'hteEvaluation',
            'showGettingStarted',
            'showCompletion',
        ));
    }

    public function documents()
    {
        $student = $this->currentStudent(request());
        $activeCompanyId = $student->deployments()
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->value('company_id');

        $hasActiveDeployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->exists();

        $hasCompletedDeployment = $student->deployments()
            ->where('status', 'completed')
            ->exists();

        $requiredDocuments = RequiredDocument::query()
            ->where(function ($query) use ($activeCompanyId): void {
                $query->whereNull('company_id');
                if ($activeCompanyId) {
                    $query->orWhere('company_id', $activeCompanyId);
                }
            })
            ->when(! $hasActiveDeployment, fn ($q) => $q->whereNotIn('phase', ['monitoring', 'post']))
            ->when($hasActiveDeployment && ! $hasCompletedDeployment, fn ($q) => $q->where('phase', '!=', 'post'))
            ->orderBy('order_index')
            ->orderBy('name')
            ->get();

        $existing = $student->documents()
            ->with(['requiredDocument', 'actions.actor'])
            ->get()
            ->keyBy('required_document_id');

        $reloaded = false;
        foreach ($existing as $sd) {
            $dirty = false;
            if (($sd->workflow_status ?? '') === 'completed' && $sd->status !== 'Submitted') {
                $sd->status = 'Submitted';
                $dirty = true;
            }
            if ($sd->file_path && ! $sd->submitted_at) {
                $sd->submitted_at = $sd->updated_at ?? now();
                $dirty = true;
            }
            if ($dirty) {
                $sd->saveQuietly();
                $reloaded = true;
            }
        }
        if ($reloaded) {
            $existing = $student->documents()
                ->with(['requiredDocument', 'actions.actor'])
                ->get()
                ->keyBy('required_document_id');
        }

        $latestDeployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->first();
        $submittedCount = $existing->filter(fn ($sd) => $sd->status === 'Submitted')->count();
        $showGettingStarted = $submittedCount === 0 && ! $latestDeployment;

        $mandatoryIds = $requiredDocuments
            ->where('is_mandatory', true)
            ->pluck('id');
        $mandatoryTotal = $mandatoryIds->count();
        $mandatorySubmitted = $mandatoryTotal > 0
            ? StudentDocument::query()
                ->where('student_id', $student->id)
                ->whereIn('required_document_id', $mandatoryIds)
                ->where('status', 'Submitted')
                ->count()
            : 0;
        $deploymentEligible = $student->isDeploymentEligibleForPortal();
        $mandatoryChecklistComplete = $mandatoryTotal === 0 || $mandatorySubmitted >= $mandatoryTotal;

        $studentPortalLimited = ! $student->hasFullStudentPortalAccess();
        $focusRequiredDocumentId = max(0, (int) request()->query('focus', 0));

        return response()
            ->view('student.documents', compact(
                'student',
                'requiredDocuments',
                'existing',
                'activeCompanyId',
                'studentPortalLimited',
                'focusRequiredDocumentId',
                'deploymentEligible',
                'mandatoryChecklistComplete',
                'mandatorySubmitted',
                'mandatoryTotal',
                'showGettingStarted',
                'submittedCount',
            ))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function uploadDocument(Request $request, RequiredDocument $requiredDocument)
    {
        $student = $this->currentStudent($request);
        $activeCompanyId = $student->deployments()
            ->where('status', 'active')
            ->orderByDesc('start_date')
            ->value('company_id');

        $isHtmx = $request->header('HX-Request') === 'true';

        if ($requiredDocument->company_id !== null && (int) $requiredDocument->company_id !== (int) ($activeCompanyId ?? 0)) {
            if ($isHtmx) {
                $existing = $student->documents()
                    ->with(['requiredDocument', 'actions.actor'])
                    ->get()
                    ->keyBy('required_document_id');

                return response()
                    ->view('student.partials.document-card', [
                        'doc' => $requiredDocument,
                        'current' => $existing->get($requiredDocument->id),
                        'uploadError' => __('This requirement is not assigned to your current company.'),
                    ], 403);
            }

            abort(403, __('This requirement is not assigned to your current company.'));
        }

        // Late submissions are allowed by policy; timing indicators are shown in the UI.

        try {
            $data = $request->validate([
                'file' => [
                    'required',
                    'file',
                    'max:2048',
                    'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    function ($attribute, $value, $fail) {
                        $allowedMimes = [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ];
                        if (! in_array($value->getMimeType(), $allowedMimes, true)) {
                            $fail(__('File must be a valid PDF, DOC, or DOCX document.'));
                        }
                        $extension = strtolower($value->getClientOriginalExtension());
                        if (! in_array($extension, ['pdf', 'doc', 'docx'], true)) {
                            $fail(__('Invalid file extension. Only .pdf, .doc, .docx are allowed.'));
                        }
                    },
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if (! $isHtmx) {
                throw $e;
            }

            $existing = $student->documents()
                ->with(['requiredDocument', 'actions.actor'])
                ->get()
                ->keyBy('required_document_id');

            return response()
                ->view('student.partials.document-card', [
                    'doc' => $requiredDocument,
                    'current' => $existing->get($requiredDocument->id),
                    'uploadError' => $e->validator->errors()->first('file') ?: __('Upload failed.'),
                ], 422);
        }

        $path = $data['file']->store("student-documents/{$student->id}", 'public');

        $existingDoc = StudentDocument::query()
            ->where('student_id', $student->id)
            ->where('required_document_id', $requiredDocument->id)
            ->first();

        if ($existingDoc && $existingDoc->file_path && $existingDoc->file_path !== $path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($existingDoc->file_path);
        }

        $studentDocument = StudentDocument::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'required_document_id' => $requiredDocument->id,
            ],
            [
                'workflow_template_id' => $requiredDocument->workflow_template_id,
                'status' => 'Pending',
                'submitted_at' => now(),
                'file_path' => $path,
                'uploaded_by' => null,
                'verified_by' => null,
            ]
        );

        // Student resubmission after rejection should restart the workflow from step 1.
        if ((string) ($studentDocument->workflow_status ?? '') === 'rejected') {
            $studentDocument->current_step_order = null;
            $studentDocument->current_holder_role = null;
            $studentDocument->next_step_role = null;
            $studentDocument->workflow_status = null;
            $studentDocument->last_action_at = null;
            $studentDocument->save();
        }

        $this->workflowEngine->initialize(
            $studentDocument,
            null,
            __('Document submitted by student.')
        );

        $studentDocument->refresh();

        if (! $studentDocument->current_holder_role) {
            $studentDocument->current_holder_role = 'instructor';
            $studentDocument->next_step_role = 'chairperson';
            $studentDocument->workflow_status = 'received';
            $studentDocument->last_action_at = now();
            $studentDocument->save();
        }

        $holderRole = (string) ($studentDocument->current_holder_role ?: 'instructor');
        $submissionActionUrl = $studentDocument->workflow_template_id && $studentDocument->current_holder_role
            ? route('student-documents.queue', ['focus_sd' => (int) $studentDocument->id])
            : route('student-documents.edit', [
                'student' => $student->id,
                'return' => 'queue',
                'focus_req' => (int) $requiredDocument->id,
            ]);

        $this->notificationService->notifyRole($holderRole, [
            'event_type' => 'document.submitted',
            'title' => __('New document submission'),
            'body' => __(':student submitted :document and it is waiting for review.', [
                'student' => $student->name,
                'document' => $requiredDocument->name,
            ]),
            'action_url' => $submissionActionUrl,
            'meta' => [
                'student_id' => (int) $student->id,
                'student_document_id' => (int) $studentDocument->id,
                'required_document_id' => (int) $requiredDocument->id,
            ],
        ]);

        \App\Models\Deployment::checkAndActivateForStudent($student);

        if ($isHtmx) {
            $current = StudentDocument::query()
                ->with(['actions.actor'])
                ->where('student_id', $student->id)
                ->where('required_document_id', $requiredDocument->id)
                ->first();

            return view('student.partials.document-card', [
                'doc' => $requiredDocument,
                'current' => $current,
                'uploadError' => null,
            ]);
        }

        return redirect()
            ->route('student.documents')
            ->with('status', 'Document uploaded successfully. Waiting for verification.');
    }

    public function announcements()
    {
        $student = $this->currentStudent(request());
        $studentPortalLimited = ! $student->hasFullStudentPortalAccess();

        $announcements = Announcement::query()
            ->with('author')
            ->where(function ($query): void {
                $query->whereNull('visible_to_role')
                    ->orWhere('visible_to_role', '')
                    ->orWhereRaw('LOWER(visible_to_role) = ?', ['all'])
                    ->orWhereRaw('LOWER(visible_to_role) = ?', ['student']);
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('student.announcements', compact('announcements', 'studentPortalLimited'));
    }

    public function downloadDocument(StudentDocument $studentDocument)
    {
        $student = $this->currentStudent(request());
        abort_unless((int) $studentDocument->student_id === (int) $student->id, 403);

        if (! $studentDocument->file_path || ! Storage::disk('public')->exists($studentDocument->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->response(
            $studentDocument->file_path,
            basename($studentDocument->file_path)
        );
    }

    /**
     * Show the first-time password change form.
     */
    public function showPasswordChange()
    {
        $student = $this->currentStudent(request());
        $studentAccount = request()->attributes->get('studentAccount');

        return view('student.auth.password-change', compact('student', 'studentAccount'));
    }

    /**
     * Update password on first login.
     */
    public function updatePassword(Request $request)
    {
        $student = $this->currentStudent($request);
        $studentAccount = $request->attributes->get('studentAccount');

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $studentAccount->password = bcrypt($request->string('password'));
        $studentAccount->markFirstLoginComplete();
        $studentAccount->save();

        $home = $student->hasFullStudentPortalAccess() ? 'student.dashboard' : 'student.documents';

        return redirect()->route($home)->with('status', 'Password updated successfully. Welcome!');
    }

    public function profile()
    {
        $student = $this->currentStudent(request());
        $studentAccount = request()->attributes->get('studentAccount');

        return view('student.profile', compact('student', 'studentAccount'));
    }

    public function updateProfile(UpdateStudentProfileRequest $request)
    {
        $student = $this->currentStudent($request);
        $studentAccount = $request->attributes->get('studentAccount');
        $data = $request->validated();

        if (! empty($data['email'])) {
            $studentAccount->email = $data['email'];
        }
        if (! empty($data['contact_number'])) {
            $student->contact_number = $data['contact_number'];
            $student->save();
        }
        if (! empty($data['password'])) {
            $studentAccount->password = bcrypt($data['password']);
        }
        $studentAccount->save();

        return redirect()->route('student.profile')
            ->with('status', __('Profile updated successfully.'))
            ->with('status_type', 'success');
    }

    /**
     * Student logout — clears student session without requiring web guard auth.
     */
    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->forget([
            'student_account_id',
            'student_otp_pending_id',
            'student_otp_code',
            'student_otp_expires_at',
            'student_otp_email',
            'student_last_activity',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
