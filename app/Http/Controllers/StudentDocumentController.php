<?php

namespace App\Http\Controllers;

use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Services\DocumentWorkflowEngine;
use App\Services\NotificationService;
use App\Support\InternshipRoles;
use App\Support\StudentListSearch;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentDocumentController extends Controller
{
    public function __construct(
        private readonly DocumentWorkflowEngine $workflowEngine,
        private readonly NotificationService $notificationService
    ) {}

    public function edit(Request $request, Student $student)
    {
        $this->authorize('viewAny', StudentDocument::class);
        $scopedCompanyId = $student->deployments()
            ->orderByDesc('start_date')
            ->value('company_id');

        $requiredDocuments = RequiredDocument::orderBy('order_index')
            ->where(function ($query) use ($scopedCompanyId): void {
                $query->whereNull('company_id');
                if ($scopedCompanyId) {
                    $query->orWhere('company_id', $scopedCompanyId);
                }
            })
            ->orderBy('name')
            ->get();

        $perPage = 100;
        $currentPage = Paginator::resolveCurrentPage('page');
        $total = $requiredDocuments->count();
        $docsForPage = $requiredDocuments->forPage($currentPage, $perPage);
        $documentsPaginator = new LengthAwarePaginator(
            $docsForPage,
            $total,
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        $phaseOrder = ['pre', 'monitoring', 'all'];
        $groupedDocs = $docsForPage
            ->groupBy(fn ($doc) => $doc->phase ?? 'all')
            ->sortBy(fn ($group, $phase) => array_search($phase, $phaseOrder, true) ?: 999);

        $existing = $student->documents()
            ->with(['workflowTemplate.steps', 'actions.actor'])
            ->get()
            ->keyBy('required_document_id');

        foreach ($existing as $studentDocument) {
            $this->workflowEngine->initialize($studentDocument);
        }

        $existing = $student->documents()
            ->with(['workflowTemplate.steps', 'actions.actor'])
            ->get()
            ->keyBy('required_document_id');

        $return = (string) request()->query('return', '');

        if ($return === 'queue') {
            $backUrl = route('student-documents.queue');
            $backLabel = __('Back to Document Queue');
        } elseif ($return === 'compliance') {
            $backUrl = route('compliance.index');
            $backLabel = __('Back to Compliance');
        } elseif (in_array((string) (auth()->user()?->role ?? ''), InternshipRoles::studentRegistryRoles(), true)) {
            $backUrl = route('students.index');
            $backLabel = __('Back to Students');
        } else {
            $backUrl = route('student-documents.queue');
            $backLabel = __('Back to Document Queue');
        }

        if ($request->header('HX-Request')) {
            return view('student-documents.partials.checklist-table', compact(
                'student',
                'groupedDocs',
                'existing',
                'documentsPaginator',
            ));
        }

        return view('student-documents.edit', compact(
            'student',
            'groupedDocs',
            'existing',
            'backUrl',
            'backLabel',
            'documentsPaginator',
        ));
    }

    public function queue(Request $request)
    {
        $this->authorize('viewAny', StudentDocument::class);

        session(['last_queue_view_at' => now()->toDateTimeString()]);

        if (! $this->workflowColumnsAvailable()) {
            return redirect()
                ->route('dashboard')
                ->withErrors([
                    'workflow' => __('Workflow queue is unavailable: please run database migrations first.'),
                ]);
        }

        $role = (string) (auth()->user()?->role ?? '');
        $statusFilter = trim((string) request('status', ''));
        $requiredDocumentFilter = (int) request('required_document_id', 0);
        $studentQ = trim((string) request('search', ''));

        $documentsQuery = StudentDocument::query()
            ->with([
                'student:id,student_number,name',
                'requiredDocument:id,name,submission_deadline_at',
                'workflowTemplate.steps',
                'actions.actor:id,name',
            ])
            ->where(function ($q) use ($role) {
                $q->where('current_holder_role', $role)
                  ->orWhereNull('current_holder_role');
            })
            ->where(function ($q) {
                $q->whereNotIn('workflow_status', ['completed', 'rejected'])
                  ->orWhereNull('workflow_status');
            })
            ->whereNotNull('file_path')
            ->orderByDesc('last_action_at');

        if ($statusFilter !== '') {
            $documentsQuery->where('workflow_status', $statusFilter);
        }
        if ($requiredDocumentFilter > 0) {
            $documentsQuery->where('required_document_id', $requiredDocumentFilter);
        }
        if ($studentQ !== '') {
            $documentsQuery->whereHas('student', function ($q) use ($studentQ): void {
                StudentListSearch::apply($q, $studentQ);
            });
        }

        $sectionFilter = trim((string) request('section', ''));
        if ($sectionFilter !== '' && in_array($sectionFilter, ['A', 'B', 'C', 'D'], true)) {
            $documentsQuery->whereHas('student', fn ($q) => $q->where('section', $sectionFilter));
        }

        $myStudents = $request->has('my_students') ? $request->boolean('my_students') : true;
        if ($myStudents) {
            $documentsQuery->whereHas('student', fn ($q) => $q->where('assigned_instructor_id', auth()->id()));
        }

        $perPage = 15;
        $focusQueueStudentDocumentId = (int) request('focus_sd', 0);

        if ($focusQueueStudentDocumentId > 0) {
            $focusVisible = (clone $documentsQuery)->whereKey($focusQueueStudentDocumentId)->exists();
            if ($focusVisible) {
                $orderedIds = (clone $documentsQuery)->pluck('id')->values();
                $idx = $orderedIds->search(fn ($id) => (int) $id === $focusQueueStudentDocumentId);
                if ($idx !== false) {
                    $targetPage = (int) (floor((int) $idx / $perPage) + 1);
                    $currentPage = max(1, (int) request()->query('page', 1));
                    if ($targetPage !== $currentPage) {
                        $query = array_merge(
                            request()->query(),
                            ['page' => $targetPage, 'focus_sd' => $focusQueueStudentDocumentId]
                        );

                        return redirect()->route('student-documents.queue', $query);
                    }
                }
            } else {
                $focusDoc = StudentDocument::query()->find($focusQueueStudentDocumentId);
                if ($focusDoc && (int) $focusDoc->student_id > 0) {
                    return redirect()
                        ->route('student-documents.edit', [
                            'student' => (int) $focusDoc->student_id,
                            'return' => 'queue',
                            'focus_req' => (int) $focusDoc->required_document_id,
                        ])
                        ->with(
                            'status',
                            __('This item is not in your document queue right now (another reviewer may hold it, filters may be hiding it, or it has no active workflow step for your role). The student checklist was opened instead.')
                        );
                }
            }
        }

        $documents = $documentsQuery->paginate($perPage)->withQueryString();

        $allowedActionsByDocumentId = [];
        $returnTargetsByDocumentId = [];
        foreach ($documents as $studentDocument) {
            $docId = (int) $studentDocument->id;
            $allowedActionsByDocumentId[$docId] = $this->workflowEngine
                ->allowedActions($studentDocument, $role);
            $returnTargetsByDocumentId[$docId] = $this->workflowEngine
                ->allowedReturnTargets($studentDocument, $role);
        }

        // Group documents by student
        $grouped = $documents->groupBy(fn ($d) => (int) $d->student_id);
        $studentGroups = $grouped->map(function ($groupDocs, $studentId) {
            $first = $groupDocs->first();
            return [
                'student_id' => $studentId,
                'student_number' => $first->student?->student_number,
                'student_name' => $first->student?->name,
                'doc_count' => $groupDocs->count(),
                'documents' => $groupDocs,
            ];
        })->values();

        $requiredDocuments = RequiredDocument::query()
            ->orderBy('order_index')
            ->orderBy('name')
            ->get(['id', 'name']);
        $statusOptions = StudentDocument::query()
            ->where('current_holder_role', $role)
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->distinct()
            ->orderBy('workflow_status')
            ->pluck('workflow_status')
            ->filter()
            ->values();

        $sectionOptions = ['A', 'B', 'C', 'D'];
        $myStudents = $request->has('my_students') ? $request->boolean('my_students') : true;

        if ($request->header('HX-Request')) {
            return view('student-documents.partials.queue-table', compact(
                'documents',
                'studentGroups',
                'allowedActionsByDocumentId',
                'returnTargetsByDocumentId',
                'requiredDocuments',
                'statusFilter',
                'requiredDocumentFilter',
                'statusOptions',
                'studentQ',
                'sectionFilter',
                'sectionOptions',
                'myStudents',
            ));
        }

        return view('student-documents.queue', compact(
            'documents',
            'studentGroups',
            'allowedActionsByDocumentId',
            'returnTargetsByDocumentId',
            'requiredDocuments',
            'statusFilter',
            'requiredDocumentFilter',
            'statusOptions',
            'studentQ',
            'sectionFilter',
            'sectionOptions',
            'myStudents',
        ));
    }

    public function update(Request $request, Student $student)
    {
        $this->authorize('manageCompliance', StudentDocument::class);
        $actorRole = (string) (auth()->user()?->role ?? '');

        $validated = $request->validate([
            'documents' => ['sometimes', 'array'],
            'documents.*.status' => ['nullable', 'in:Submitted,Pending,Missing'],
            'documents.*.submitted_at' => ['nullable', 'date'],
            'documents.*.file' => [
                'nullable',
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

        if (! isset($validated['documents']) || ! is_array($validated['documents']) || $validated['documents'] === []) {
            return back()->with('status', __('No changes to save.'));
        }

        $scopedCompanyId = $student->deployments()
            ->orderByDesc('start_date')
            ->value('company_id');
        $allowedIds = RequiredDocument::query()
            ->where(function ($query) use ($scopedCompanyId): void {
                $query->whereNull('company_id');
                if ($scopedCompanyId) {
                    $query->orWhere('company_id', $scopedCompanyId);
                }
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        foreach (array_keys($validated['documents']) as $key) {
            $id = filter_var($key, FILTER_VALIDATE_INT);
            if ($id === false || ! in_array($id, $allowedIds, true)) {
                throw ValidationException::withMessages([
                    'documents' => [__('Invalid document selection.')],
                ]);
            }
        }

        $workflowTemplateByRequiredId = RequiredDocument::query()
            ->whereIn('id', array_map('intval', array_keys($validated['documents'])))
            ->pluck('workflow_template_id', 'id')
            ->mapWithKeys(fn ($workflowTemplateId, $id) => [(int) $id => $workflowTemplateId ? (int) $workflowTemplateId : null])
            ->all();

        $existingDocs = StudentDocument::query()
            ->where('student_id', $student->id)
            ->get()
            ->keyBy(fn (StudentDocument $doc) => (int) $doc->required_document_id);

        foreach ($validated['documents'] as $requiredDocumentId => $docData) {
            $requiredDocumentId = (int) $requiredDocumentId;
            $existingDoc = $existingDocs->get($requiredDocumentId);
            $workflowTemplateId = (int) (
                $existingDoc?->workflow_template_id
                ?? ($workflowTemplateByRequiredId[$requiredDocumentId] ?? 0)
            );

            if ($workflowTemplateId > 0) {
                if ($existingDoc && $existingDoc->current_holder_role) {
                    if ((string) $existingDoc->current_holder_role !== $actorRole) {
                        continue;
                    }
                } else {
                    $firstStep = \App\Models\DocumentWorkflowStep::query()
                        ->where('workflow_template_id', $workflowTemplateId)
                        ->orderBy('step_order')
                        ->first();
                    if (! $firstStep || (string) $firstStep->role !== $actorRole) {
                        continue;
                    }
                }
            }

            $status = $docData['status'] ?? ($existingDoc?->status ?? 'Pending');
            $submittedAt = $docData['submitted_at'] ?? null;

            if ($status !== 'Submitted') {
                $submittedAt = null;
            } elseif (empty($submittedAt)) {
                $submittedAt = now()->toDateString();
            }

            $updateData = [
                'workflow_template_id' => $workflowTemplateId > 0 ? $workflowTemplateId : null,
                'status' => $status,
                'submitted_at' => $submittedAt,
                'verified_by' => auth()->id(),
            ];

            if ($workflowTemplateId > 0 && $existingDoc) {
                // Workflow docs use automated lifecycle fields.
                $updateData['status'] = (string) ($existingDoc->status ?? 'Pending');
                $updateData['submitted_at'] = $existingDoc->submitted_at;
            }

            if ($request->hasFile("documents.{$requiredDocumentId}.file")) {
                $file = $request->file("documents.{$requiredDocumentId}.file");
                $path = $file->store(
                    "student-documents/{$student->id}",
                    'public'
                );

                if ($existingDoc && $existingDoc->file_path && $existingDoc->file_path !== $path) {
                    Storage::disk('public')->delete($existingDoc->file_path);
                }

                $updateData['file_path'] = $path;
                $updateData['uploaded_by'] = auth()->id();
            }

            $studentDocument = StudentDocument::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'required_document_id' => $requiredDocumentId,
                ],
                $updateData
            );

            $this->workflowEngine->initialize(
                $studentDocument,
                auth()->user(),
                __('Workflow initialized during compliance update.')
            );
        }

        \App\Models\Deployment::checkAndActivateForStudent($student);

        // Auto-deploy if all Pre docs are now approved
        $student->autoActivateDeployment();

        if ($request->header('HX-Request')) {
            $updatedDoc = StudentDocument::query()
                ->where('student_id', $student->id)
                ->where('required_document_id', array_key_last($validated['documents'] ?? []))
                ->with(['requiredDocument:id,name,submission_deadline_at'])
                ->first();

            return view('student-documents.partials.upload-panel', compact(
                'student',
                'updatedDoc',
            ));
        }

        return redirect()
            ->route('student-documents.edit', array_filter([
                'student' => $student,
                'return' => $request->input('return') ?? $request->query('return'),
            ]))
            ->with('status', 'Document compliance updated successfully.');
    }

    public function uploadPanel(Request $request, Student $student, RequiredDocument $requiredDocument)
    {
        $this->authorize('viewAny', StudentDocument::class);

        $studentDoc = $student->documents()
            ->where('required_document_id', $requiredDocument->id)
            ->first();

        return view('student-documents.partials.upload-panel', compact(
            'student',
            'requiredDocument',
            'studentDoc',
        ));
    }

    public function workflowAction(Request $request, Student $student, StudentDocument $studentDocument)
    {
        if (! $this->workflowColumnsAvailable()) {
            return back()->withErrors([
                'workflow' => __('Workflow actions are unavailable: please run database migrations first.'),
            ]);
        }

        if ((int) $studentDocument->student_id !== (int) $student->id) {
            abort(404);
        }

        $this->authorize('actWorkflow', $studentDocument);

        $validated = $request->validate([
            'action' => ['required', 'string', 'in:review,approve,return_for_revision,forward,sign'],
            'note' => ['nullable', 'string', 'max:50'],
            'return_step_order' => ['nullable', 'integer', 'min:1'],
        ]);

        $validated['note'] = isset($validated['note']) ? trim(strip_tags($validated['note'])) : null;

        $beforeHolderRole = (string) ($studentDocument->current_holder_role ?? '');
        try {
            $this->workflowEngine->applyAction(
                $studentDocument,
                auth()->user(),
                $validated['action'],
                $validated['note'] ?? null,
                isset($validated['return_step_order']) ? (int) $validated['return_step_order'] : null
            );
        } catch (\InvalidArgumentException $e) {
            if ($request->header('HX-Request')) {
                return response(__($e->getMessage()), 422);
            }

            return back()->withErrors([
                'workflow' => __($e->getMessage()),
            ]);
        }

        $student->autoActivateDeployment();

        $studentAccount = $student->account;
        // Internal faculty steps (chairperson sign/review, etc.) should not ping the student;
        // students are notified when the workflow produces a student-facing outcome (handled below for non-chair actors).
        $notifyStudent = (bool) $studentAccount;
        if ($notifyStudent) {
            $this->notificationService->notifyStudentAccount($studentAccount, [
                'event_type' => 'document.workflow.updated',
                'title' => __('Requirement update'),
                'body' => __('Your :document was marked as :status by :role.', [
                    'document' => $studentDocument->requiredDocument?->name ?? __('document'),
                    'status' => str((string) $studentDocument->workflow_status)->replace('_', ' ')->title(),
                    'role' => str((string) auth()->user()->role)->replace('_', ' ')->title(),
                ]),
                'action_url' => route('student.documents', [
                    'focus' => (int) $studentDocument->required_document_id,
                ]),
                'meta' => [
                    'student_document_id' => (int) $studentDocument->id,
                    'required_document_id' => (int) $studentDocument->required_document_id,
                    'action' => $validated['action'],
                    'status' => (string) $studentDocument->workflow_status,
                ],
            ]);
        }

        $nextHolderRole = (string) ($studentDocument->current_holder_role ?? '');
        if ($nextHolderRole !== '' && $nextHolderRole !== $beforeHolderRole && $nextHolderRole !== (string) auth()->user()->role) {
            $this->notificationService->notifyRole($nextHolderRole, [
                'event_type' => 'document.workflow.assigned',
                'title' => __('Document assigned to your queue'),
                'body' => __(':document for :student is now waiting for your action.', [
                    'document' => $studentDocument->requiredDocument?->name ?? __('Document'),
                    'student' => $student->name,
                ]),
                'action_url' => route('student-documents.edit', [
                    'student' => $student,
                    'return' => 'queue',
                    'focus_req' => (int) $studentDocument->required_document_id,
                ]),
                'meta' => [
                    'student_document_id' => (int) $studentDocument->id,
                    'student_id' => (int) $student->id,
                    'required_document_id' => (int) $studentDocument->required_document_id,
                    'holder_role' => $nextHolderRole,
                ],
            ]);
        }

        \App\Models\Deployment::checkAndActivateForStudent($student);

        if ($request->header('HX-Request')) {
            $studentDocument->refresh();
            $studentDocument->load([
                'requiredDocument:id,name,submission_deadline_at',
                'workflowTemplate.steps',
                'actions.actor:id,name',
            ]);
            $actorRole = (string) (auth()->user()?->role ?? '');
            $allowedActions = $this->workflowEngine->allowedActions($studentDocument, $actorRole);
            $returnTargets = $this->workflowEngine->allowedReturnTargets($studentDocument, $actorRole);
            $isWorkflowManaged = ($studentDocument->workflow_template_id ?? 0) > 0;

            $actionLabels = [
                'review' => __('Document marked as reviewed.'),
                'approve' => __('Document approved successfully.'),
                'return_for_revision' => __('Document returned for revision.'),
                'forward' => __('Document forwarded.'),
                'sign' => __('Document signed.'),
            ];
            $toastMsg = $actionLabels[$validated['action']] ?? __('Workflow action recorded successfully.');

            return response(
                view('student-documents.partials.review-panel', compact(
                    'student', 'studentDocument', 'allowedActions', 'returnTargets', 'isWorkflowManaged',
                )),
                200,
                [
                    'HX-Trigger' => json_encode(['close-review-panel' => true]),
                    'X-Toast-Message' => $toastMsg,
                ]
            );
        }

        return back()->with('status', __('Workflow action recorded successfully.'));
    }

    public function review(Request $request, Student $student, StudentDocument $studentDocument)
    {
        if (! $this->workflowColumnsAvailable()) {
            return response()->json(['error' => __('Workflow actions are unavailable.')], 422);
        }

        if ((int) $studentDocument->student_id !== (int) $student->id) {
            abort(404);
        }

        $isWorkflowManaged = ($studentDocument->workflow_template_id ?? 0) > 0;

        $actorRole = (string) (auth()->user()?->role ?? '');
        $canAct = auth()->user()->can('actWorkflow', $studentDocument);

        if (! $canAct) {
            $this->authorize('viewAny', StudentDocument::class);
        }

        if ($canAct && $isWorkflowManaged) {
            $studentDocument->load([
                'workflowTemplate.steps',
            ]);
        }

        $studentDocument->load([
            'requiredDocument:id,name,submission_deadline_at',
            'workflowTemplate.steps',
            'actions.actor:id,name',
        ]);

        $allowedActions = $this->workflowEngine->allowedActions($studentDocument, $actorRole);
        $returnTargets = $this->workflowEngine->allowedReturnTargets($studentDocument, $actorRole);

        if ($request->header('HX-Request')) {
            return view('student-documents.partials.review-panel', compact(
                'student',
                'studentDocument',
                'allowedActions',
                'returnTargets',
                'isWorkflowManaged',
            ));
        }

        return back()->with('status', __('Review panel opened.'));
    }

    public function preview(Request $request, Student $student, StudentDocument $studentDocument)
    {
        if ((int) $studentDocument->student_id !== (int) $student->id) {
            abort(404);
        }

        $this->authorize('viewAny', StudentDocument::class);

        $studentDocument->load([
            'requiredDocument:id,name,submission_deadline_at',
            'workflowTemplate.steps',
            'actions.actor:id,name',
        ]);

        $isWorkflowManaged = ($studentDocument->workflow_template_id ?? 0) > 0;
        $actorRole = (string) (auth()->user()?->role ?? '');
        $allowedActions = $isWorkflowManaged
            ? $this->workflowEngine->allowedActions($studentDocument, $actorRole)
            : [];
        $returnTargets = $isWorkflowManaged
            ? $this->workflowEngine->allowedReturnTargets($studentDocument, $actorRole)
            : [];

        return view('student-documents.partials.checklist-panel', compact(
            'student',
            'studentDocument',
            'isWorkflowManaged',
            'allowedActions',
            'returnTargets',
        ));
    }

    public function download(Student $student, StudentDocument $studentDocument): StreamedResponse
    {
        if ((int) $studentDocument->student_id !== (int) $student->id) {
            abort(404);
        }

        if (! $studentDocument->file_path || ! Storage::disk('public')->exists($studentDocument->file_path)) {
            abort(404);
        }

        $this->authorize('download', $studentDocument);

        return Storage::disk('public')->response(
            $studentDocument->file_path,
            basename($studentDocument->file_path),
            ['Content-Disposition' => 'inline']
        );
    }

    private function workflowColumnsAvailable(): bool
    {
        return Schema::hasColumns('student_documents', [
            'current_holder_role',
            'next_step_role',
            'workflow_status',
            'last_action_at',
        ]);
    }
}
