<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEvaluationRequest;
use App\Http\Requests\UpdateEvaluationRequest;
use App\Mail\HteEvaluationLinkMail;
use App\Models\Evaluation;
use App\Models\HteTransactionLink;
use App\Models\Student;
use App\Models\User;
use App\Services\EvaluationExportService;
use App\Services\NotificationService;
use App\Support\InternshipRoles;
use App\Support\StudentListSearch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EvaluationController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Evaluation::class);

        $query = Evaluation::with(['student', 'evaluator', 'company'])
            ->orderByDesc('evaluated_at');

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $like = '%'.$search.'%';
            $query->where(function ($q) use ($search, $like) {
                $q->whereHas('student', function ($q2) use ($search) {
                    StudentListSearch::apply($q2, $search);
                })
                    ->orWhere('evaluation_type', 'like', $like)
                    ->orWhereRaw('CAST(score AS CHAR) LIKE ?', [$like])
                    ->orWhere('comments', 'like', $like)
                    ->orWhereHas('evaluator', function ($q2) use ($like) {
                        $q2->where('name', 'like', $like);
                    })
                    ->orWhereRaw('CAST(evaluated_at AS CHAR) LIKE ?', [$like])
                    ->orWhereRaw("DATE_FORMAT(evaluated_at, '%b %d, %Y') LIKE ?", [$like]);
            });
        }

        $type = trim((string) $request->query('type', ''));
        if (in_array($type, ['industry', 'school', 'student_feedback'], true)) {
            $query->where('evaluation_type', $type);
        } else {
            $type = '';
        }

        $evaluator = $request->query('evaluator');
        if ($evaluator === 'none') {
            $query->whereNull('evaluator_id');
        } elseif ($evaluator !== null && $evaluator !== '') {
            $query->where('evaluator_id', (int) $evaluator);
        } else {
            $evaluator = '';
        }

        $evalYear = trim((string) $request->query('eval_year', ''));
        if ($evalYear !== '' && ctype_digit($evalYear)) {
            $query->whereYear('evaluated_at', (int) $evalYear);
        } else {
            $evalYear = '';
        }

        $evalMonth = trim((string) $request->query('eval_month', ''));
        if ($evalMonth !== '' && ctype_digit($evalMonth)) {
            $m = (int) $evalMonth;
            if ($m >= 1 && $m <= 12) {
                $query->whereMonth('evaluated_at', $m);
            } else {
                $evalMonth = '';
            }
        } else {
            $evalMonth = '';
        }

        $section = trim((string) $request->query('section', ''));
        if ($section !== '' && in_array($section, ['A', 'B', 'C', 'D'], true)) {
            $query->whereHas('student', fn ($q) => $q->where('section', $section));
        } else {
            $section = '';
        }

        $evaluations = $query->paginate(5)->withQueryString();

        $facetBase = Evaluation::query();

        $evalYears = $facetBase->clone()
            ->whereNotNull('evaluated_at')
            ->orderByDesc('evaluated_at')
            ->pluck('evaluated_at')
            ->map(fn ($d) => $d->year)
            ->unique()
            ->sort()
            ->values();

        $evaluatorIds = $facetBase->clone()
            ->whereNotNull('evaluator_id')
            ->distinct()
            ->pluck('evaluator_id');

        $hasNullEvaluator = $facetBase->clone()->whereNull('evaluator_id')->exists();

        $evaluatorUsers = User::query()
            ->whereIn('id', $evaluatorIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $monthOptions = collect(range(1, 12))->mapWithKeys(fn ($m) => [
            (string) $m => Carbon::createFromDate(2020, (int) $m, 1)->translatedFormat('F'),
        ]);

        $hasActiveFilters = $search !== ''
            || $type !== ''
            || $evaluator !== ''
            || $evalYear !== ''
            || $evalMonth !== ''
            || $section !== '';

        $canManage = in_array((string) (Auth::user()?->role ?? ''), InternshipRoles::operationalManagerRoles(), true);

        $viewData = compact(
            'evaluations',
            'search',
            'type',
            'evaluator',
            'evalYear',
            'evalMonth',
            'section',
            'evalYears',
            'evaluatorUsers',
            'hasNullEvaluator',
            'monthOptions',
            'hasActiveFilters',
            'canManage',
        );

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('evaluations.partials.ajax-list', [
                'evaluations' => $evaluations,
                'canManage' => $canManage,
            ]);
        }

        return view('evaluations.index', $viewData);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('manage', Evaluation::class);

        $students = Student::orderBy('student_number')->get();

        return view('evaluations.create', compact('students'));
    }

    /**
     * Show the form used by instructors to email a one-time HTE evaluation link.
     */
    public function createHteLink(Request $request)
    {
        $this->authorize('manage', Evaluation::class);

        $students = Student::query()
            ->whereHas('deployments', fn ($d) => $d->where('status', 'active'))
            ->with([
                'deployments' => fn ($q) => $q->with('company')->where('status', 'active')->orderByDesc('start_date'),
            ])
            ->orderBy('student_number')
            ->get();

        $recentLinks = HteTransactionLink::query()
            ->with(['student:id,student_number,name', 'company:id,name', 'sender:id,name'])
            ->orderByDesc('created_at')
            ->limit(25)
            ->get();

        return view('evaluations.send-hte-link', compact('students', 'recentLinks'));
    }

    /**
     * Generate and email one-time HTE evaluation link.
     */
    public function storeHteLink(Request $request)
    {
        $this->authorize('manage', Evaluation::class);

        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'supervisor_name' => ['nullable', 'string', 'max:255'],
            'supervisor_email' => ['required', 'email', 'max:255'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:30'],
        ]);

        $data['supervisor_name'] = isset($data['supervisor_name']) ? trim(strip_tags($data['supervisor_name'])) : null;

        $student = Student::query()
            ->with(['deployments' => fn ($q) => $q->with('company')->orderByDesc('start_date')])
            ->findOrFail((int) $data['student_id']);

        $activeDeployment = $student->deployments->firstWhere('status', 'active');
        if (! $activeDeployment) {
            return back()
                ->withInput()
                ->withErrors(['student_id' => __('Selected student has no active deployment.')]);
        }

        $days = (int) ($data['expires_in_days'] ?? 7);
        $link = HteTransactionLink::query()->create([
            'token' => Str::random(72),
            'student_id' => $student->id,
            'company_id' => $activeDeployment->company_id,
            'created_by' => Auth::id(),
            'supervisor_name' => isset($data['supervisor_name']) ? trim(strip_tags((string) $data['supervisor_name'])) : null,
            'supervisor_email' => strtolower(trim((string) $data['supervisor_email'])),
            'expires_at' => now()->addDays($days),
        ]);

        $transactionUrl = route('hte.transaction.show', $link->token);
        try {
            Mail::to($link->supervisor_email)->send(new HteEvaluationLinkMail(
                studentName: $student->name ?? $student->student_number,
                companyName: $activeDeployment->company?->name,
                transactionUrl: $transactionUrl,
                expiresAt: $link->expires_at,
            ));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('hte_link_mail_failed', [
                'link_id' => $link->id,
                'supervisor_email' => $link->supervisor_email,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('evaluations.hte-links.create')
                ->with('status', __('Failed to send HTE evaluation link email. Please check the supervisor email address and try again.'))
                ->with('status_type', 'error');
        }

        if ($student->account) {
            $this->notificationService->notifyStudentAccount($student->account, [
                'event_type' => 'hte.evaluation_sent',
                'title' => __('Evaluation form sent'),
                'body' => __('Your evaluation form has been sent to :supervisor at :company.', [
                    'supervisor' => $link->supervisor_name ?? __('the HTE supervisor'),
                    'company' => $activeDeployment->company?->name ?? __('your company'),
                ]),
                'action_url' => route('student.dashboard'),
                'meta' => ['link_id' => $link->id],
            ]);
        }

        return redirect()
            ->route('evaluations.hte-links.create')
            ->with('status', __('HTE evaluation link sent successfully.'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEvaluationRequest $request)
    {
        $this->authorize('manage', Evaluation::class);

        $data = $request->validated();
        $data['evaluator_id'] = Auth::id();
        // Staff evaluation timestamp is system-generated to prevent manual/future date entry.
        $data['evaluated_at'] = now();

        Evaluation::create($data);

        return redirect()
            ->route('evaluations.index')
            ->with('status', __('Evaluation created successfully.'))
            ->with('status_type', 'success');
    }

    /**
     * Display the specified resource.
     */
    public function show(Evaluation $evaluation)
    {
        $this->authorize('view', $evaluation);

        $evaluation->load(['student', 'evaluator', 'company']);

        return view('evaluations.show', compact('evaluation'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Evaluation $evaluation)
    {
        $this->authorize('manage', Evaluation::class);

        $students = Student::orderBy('student_number')->get();

        return view('evaluations.edit', compact('evaluation', 'students'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEvaluationRequest $request, Evaluation $evaluation)
    {
        $this->authorize('manage', Evaluation::class);

        $data = $request->validated();
        // Keep original evaluator_id and evaluated_at; do not change on edit.
        unset($data['evaluated_at']);
        $evaluation->update($data);

        return redirect()
            ->route('evaluations.index')
            ->with('status', __('Evaluation updated successfully.'))
            ->with('status_type', 'success');
    }

    /**
     * Export evaluation as DOCX.
     */
    public function export(Evaluation $evaluation)
    {
        $this->authorize('view', $evaluation);

        $service = new EvaluationExportService();
        $tempPath = $service->generate($evaluation);

        $filename = 'OJT-Evaluation-'.($evaluation->student?->student_number ?? $evaluation->id).'.docx';

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Evaluation $evaluation)
    {
        $this->authorize('manage', Evaluation::class);

        $evaluation->delete();

        return redirect()
            ->route('evaluations.index')
            ->with('status', __('Evaluation deleted successfully.'))
            ->with('status_type', 'success');
    }
}
