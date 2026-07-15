<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Company;
use App\Models\Deployment;
use App\Models\Evaluation;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentRiskFlag;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $tab = $request->query('tab', 'deployed');

        $kpis = [
            'totalStudents' => Student::count(),
            'deployed' => Deployment::whereIn('status', ['active', 'completed'])->distinct('student_id')->count('student_id'),
            'completed' => Deployment::where('status', 'completed')->distinct('student_id')->count('student_id'),
            'atRisk' => StudentRiskFlag::whereNull('resolved_at')->distinct('student_id')->count('student_id'),
        ];

        if ($request->header('HX-Request') && $tab) {
            $request->merge(['partial' => true]);
            return match ($tab) {
                'deployed' => $this->deployedPerCompany($request),
                'missing' => $this->missingDocuments($request),
                'compliance' => $this->complianceSummary($request),
                'attendance' => $this->attendanceExport($request),
                default => $this->deployedPerCompany($request),
            };
        }

        return view('reports.index', compact('kpis'));
    }

    private function applyReportFilters(Request $request, $query): void
    {
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search): void {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('section')) {
            $query->where('section', $request->query('section'));
        }
        if ($request->boolean('my_students')) {
            $query->where('assigned_instructor_id', auth()->id());
        }
    }

    /**
     * Deployed students grouped by company.
     */
    public function deployedPerCompany(Request $request)
    {
        $studentQuery = Student::query();
        $this->applyReportFilters($request, $studentQuery);

        // Build subquery once — avoids plucking IDs into memory
        $studentSub = $studentQuery->select('id');

        $companies = Company::with(['deployments' => fn ($q) => $q
            ->whereIn('status', ['active', 'completed'])
            ->whereIn('student_id', $studentSub),
            'deployments.student',
        ])
            ->whereHas('deployments', fn ($q) => $q
                ->whereIn('status', ['active', 'completed'])
                ->whereIn('student_id', $studentSub)
            )
            ->orderBy('name')
            ->paginate(20)
            ->through(fn (Company $company) => [
                'company' => $company,
                'deployments' => $company->deployments,
            ]);

        if ($request->query('export') === 'pdf') {
            return $this->downloadPdf(
                'reports.pdf.deployed-per-company',
                compact('companies'),
                'deployed-per-company.pdf',
                'landscape'
            );
        }

        if ($request->boolean('partial')) {
            return view('reports.partials.deployed-table', compact('companies'));
        }

        return view('reports.deployed-per-company', compact('companies'));
    }

    /**
     * Students with missing or pending documents.
     */
    public function missingDocuments(Request $request)
    {
        $requiredDocs = RequiredDocument::where('is_mandatory', true)
            ->orderBy('order_index')
            ->orderBy('name')
            ->get();

        $requiredIds = $requiredDocs->pluck('id');

        // Filter students who have missing or pending documents via SQL (avoids loading all students)
        $studentsQuery = Student::with('documents')
            ->orderBy('student_number');

        $this->applyReportFilters($request, $studentsQuery);

        if ($requiredIds->isNotEmpty()) {
            $studentsQuery->where(function ($q) use ($requiredIds) {
                foreach ($requiredIds as $docId) {
                    $q->orWhereDoesntHave('documents', fn ($sq) => $sq
                        ->where('required_document_id', $docId)
                        ->where('status', 'Submitted')
                    );
                }
            });
        }

        $paginated = $studentsQuery->paginate(50);

        $students = $paginated->through(function (Student $student) use ($requiredIds, $requiredDocs) {
            $docsByRequired = $student->documents
                ->whereIn('required_document_id', $requiredIds)
                ->keyBy('required_document_id');

            $missing = [];
            $pending = [];

            foreach ($requiredDocs as $doc) {
                $studentDoc = $docsByRequired->get($doc->id);
                $status = $studentDoc?->status ?? 'Missing';

                if ($status === 'Missing') {
                    $missing[] = $doc->name;
                } elseif ($status === 'Pending') {
                    $pending[] = $doc->name;
                }
            }

            return [
                'student' => $student,
                'missing' => $missing,
                'pending' => $pending,
            ];
        });

        if ($request->query('export') === 'pdf') {
            return $this->downloadPdf(
                'reports.pdf.missing-documents',
                compact('students', 'requiredDocs'),
                'missing-pending-documents.pdf',
                'portrait'
            );
        }

        if ($request->boolean('partial')) {
            return view('reports.partials.missing-table', compact('students', 'requiredDocs'));
        }

        return view('reports.missing-documents', compact('students', 'requiredDocs'));
    }

    /**
     * Overall compliance summary.
     */
    public function complianceSummary(Request $request)
    {
        $mandatoryDocs = RequiredDocument::where('is_mandatory', true)->get();
        $mandatoryIds = $mandatoryDocs->pluck('id');

        $studentQuery = Student::with('documents')->orderBy('student_number');
        $this->applyReportFilters($request, $studentQuery);

        $paginated = $studentQuery->paginate(50);

        $rows = $paginated->through(function (Student $student) use ($mandatoryIds) {
            if ($mandatoryIds->isEmpty()) {
                return [
                    'student' => $student,
                    'submitted' => 0,
                    'total' => 0,
                    'status' => 'no_mandatory_docs',
                ];
            }

            $docsByRequired = $student->documents
                ->whereIn('required_document_id', $mandatoryIds)
                ->keyBy('required_document_id');

            $submitted = 0;
            foreach ($mandatoryIds as $docId) {
                if (($docsByRequired->get($docId)?->status ?? 'Missing') === 'Submitted') {
                    $submitted++;
                }
            }

            $total = $mandatoryIds->count();

            if ($submitted === $total) {
                $status = 'compliant';
            } elseif ($submitted === 0) {
                $status = 'non_compliant';
            } else {
                $status = 'partially_compliant';
            }

            return [
                'student' => $student,
                'submitted' => $submitted,
                'total' => $total,
                'status' => $status,
            ];
        });

        $summary = [
            'total' => $rows->count(),
            'compliant' => $rows->where('status', 'compliant')->count(),
            'partially_compliant' => $rows->where('status', 'partially_compliant')->count(),
            'non_compliant' => $rows->where('status', 'non_compliant')->count(),
        ];

        if ($request->query('export') === 'pdf') {
            return $this->downloadPdf(
                'reports.pdf.compliance-summary',
                compact('rows', 'summary'),
                'compliance-summary.pdf',
                'landscape'
            );
        }

        if ($request->boolean('partial')) {
            return view('reports.partials.compliance-table', compact('rows', 'summary'));
        }

        return view('reports.compliance-summary', compact('rows', 'summary'));
    }

    /**
     * Deployment locations grouped by company with student section and address.
     */
    public function deploymentLocations(Request $request)
    {
        $companies = Company::with(['deployments.student'])
            ->orderBy('name')
            ->get()
            ->map(function (Company $company) {
                return [
                    'company' => $company,
                    'deployments' => $company->deployments,
                ];
            });

        if ($request->query('export') === 'pdf') {
            return $this->downloadPdf(
                'reports.pdf.deployment-locations',
                compact('companies'),
                'deployment-locations.pdf',
                'landscape'
            );
        }

        return view('reports.deployment-locations', compact('companies'));
    }

    /**
     * Attendance records with date filter and export.
     */
    public function attendanceExport(Request $request)
    {
        $query = Attendance::with('student')
            ->orderBy('check_in_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('student', fn ($q) => $q->where('student_number', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('check_in_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('check_in_at', '<=', $request->date_to);
        }

        if ($request->filled('section')) {
            $query->whereHas('student', fn ($q) => $q->where('section', $request->section));
        }

        if ($request->boolean('my_students')) {
            $query->whereHas('student', fn ($q) => $q->where('assigned_instructor_id', auth()->id()));
        }

        if (! $request->filled('date_from') && ! $request->filled('date_to')) {
            $query->whereMonth('check_in_at', now()->month)
                  ->whereYear('check_in_at', now()->year);
        }

        $sections = Student::select('section')->distinct()->orderBy('section')->pluck('section');

        if ($request->query('export') === 'pdf') {
            $records = $query->get();
            return $this->downloadPdf(
                'reports.pdf.attendance-export',
                compact('records'),
                'attendance-export.pdf',
                'landscape'
            );
        }

        if ($request->query('export') === 'csv') {
            $records = $query->get();
            $filename = 'attendance-export-' . now()->format('Y-m-d-His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename={$filename}",
            ];

            $callback = function () use ($records): void {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Date', 'Student No.', 'Name', 'Section', 'Check In', 'Time Out', 'Total (min)', 'Status']);

                foreach ($records as $att) {
                    $statusLabel = match (true) {
                        $att->location_unavailable => 'No Location',
                        $att->review_required && $att->resolution_status !== 'resolved' => 'Pending Review',
                        $att->review_required && $att->resolution_status === 'resolved' => 'Resolved',
                        default => 'Normal',
                    };

                    fputcsv($handle, [
                        $att->check_in_at?->format('Y-m-d'),
                        $att->student?->student_number,
                        $att->student?->name,
                        $att->student?->section ?? '-',
                        $att->check_in_at?->format('H:i:s'),
                        $att->time_out_at?->format('H:i:s') ?? '-',
                        $att->total_minutes ?? '-',
                        $statusLabel,
                    ]);
                }

                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        $records = $query->paginate(50);

        if ($request->header('HX-Request')) {
            return view('reports.partials.attendance-export-table', compact('records'));
        }

        return view('reports.attendance-export', compact('records', 'sections'));
    }

    /**
     * Executive Summary — dean only.
     * All KPIs aggregated, trends, company satisfaction, evaluator feedback.
     */
    public function executiveSummary()
    {
        $total = Student::count();
        $deployed = Student::whereHas('deployments', fn ($q) => $q->whereIn('status', ['active', 'completed']))->count();
        $completed = Student::whereHas('deployments', fn ($q) => $q->where('status', 'completed'))->count();
        $certified = Certificate::where('status', 'verified')->distinct('student_id')->count('student_id');
        $atRisk = StudentRiskFlag::whereNull('resolved_at')->distinct('student_id')->count('student_id');

        $deploymentPct = $total > 0 ? round(($deployed / $total) * 100, 1) : 0;
        $completionPct = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        $companySatisfaction = Evaluation::where('evaluation_type', 'industry')
            ->select('company_id', DB::raw('AVG(score) as avg_score'), DB::raw('COUNT(*) as count'))
            ->whereHas('company')
            ->groupBy('company_id')
            ->with('company')
            ->get()
            ->sortByDesc('avg_score');

        $avgSatisfaction = $companySatisfaction->count() > 0
            ? round($companySatisfaction->avg('avg_score'), 1) : 0;

        $mandatoryDocs = RequiredDocument::where('is_mandatory', true)->count();
        $compliantStudents = 0;
        if ($mandatoryDocs > 0) {
            $compliantStudents = Student::whereDoesntHave('documents', fn ($q) => $q
                ->whereIn('required_document_id', RequiredDocument::where('is_mandatory', true)->pluck('id'))
                ->where('status', '!=', 'Submitted')
            )->count();
        }

        $recentDeployments = Deployment::whereIn('status', ['active', 'completed'])
            ->where('start_date', '>=', now()->subMonths(6))
            ->count();

        $feedbackCount = Evaluation::where('evaluation_type', 'industry')->count();

        return view('reports.executive-summary', compact(
            'total', 'deployed', 'completed', 'certified', 'atRisk',
            'deploymentPct', 'completionPct', 'companySatisfaction',
            'avgSatisfaction', 'mandatoryDocs', 'compliantStudents',
            'recentDeployments', 'feedbackCount',
        ));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function downloadPdf(string $view, array $data, string $filename, string $orientation)
    {
        $pdf = Pdf::loadView($view, $data)
            ->setPaper('a4', $orientation);

        return $pdf->download($filename);
    }
}
