<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentRiskFlag;
use App\Support\InternshipRoles;
use App\Support\StudentListSearch;
use Illuminate\Http\Request;

class ComplianceController extends Controller
{
    public function index(Request $request)
    {
        $filterStatus = $request->query('status');
        $search = trim((string) $request->query('search', ''));
        $sectionFilter = trim((string) $request->query('section', ''));
        $riskFilter = $request->boolean('risk');
        $myStudents = $request->has('my_students') ? $request->boolean('my_students') : true;

        $mandatoryDocs = RequiredDocument::where('is_mandatory', true)->get();
        $mandatoryIds = $mandatoryDocs->pluck('id');
        $mandatoryCount = $mandatoryIds->count();

        $studentsQuery = Student::with('activeRiskFlags')->orderBy('student_number');

        if ($myStudents && auth()->user()?->role === 'instructor') {
            $studentsQuery->where('assigned_instructor_id', auth()->id());
        }

        if ($sectionFilter !== '' && in_array($sectionFilter, ['A', 'B', 'C', 'D'], true)) {
            $studentsQuery->where('section', $sectionFilter);
        }

        if ($riskFilter) {
            $atRiskIds = StudentRiskFlag::active()->pluck('student_id')->unique()->values()->all();
            $studentsQuery->whereIn('id', $atRiskIds);
        }

        if ($mandatoryCount > 0) {
            $studentsQuery->withCount([
                'documents as submitted_mandatory_count' => function ($query) use ($mandatoryIds) {
                    $query->whereIn('required_document_id', $mandatoryIds)
                        ->where('status', 'Submitted');
                },
            ]);
        }

        StudentListSearch::apply($studentsQuery, $search);

        $totalStudents = (clone $studentsQuery)->count();

        $summary = [
            'total_students' => $totalStudents,
            'compliant' => 0,
            'partially_compliant' => 0,
            'non_compliant' => 0,
        ];

        $summaryRows = collect();
        if ($mandatoryCount > 0) {
            // Use a dedicated subquery for counts — withCount columns don't survive explicit ->get(['id', ...])
            $summaryRows = (clone $studentsQuery)->select(['id'])->get()->keyBy('id');
            $submittedCounts = \DB::table('student_documents')
                ->whereIn('required_document_id', $mandatoryIds)
                ->where('status', 'Submitted')
                ->whereIn('student_id', $summaryRows->keys())
                ->groupBy('student_id')
                ->selectRaw('student_id, COUNT(*) as cnt')
                ->pluck('cnt', 'student_id');

            foreach ($summaryRows as $row) {
                $row->submitted_mandatory_count = (int) ($submittedCounts[$row->id] ?? 0);
            }

            $summary['compliant'] = $summaryRows->where('submitted_mandatory_count', $mandatoryCount)->count();
            $summary['non_compliant'] = $summaryRows->where('submitted_mandatory_count', 0)->count();
            $summary['partially_compliant'] = max(0, $totalStudents - $summary['compliant'] - $summary['non_compliant']);
        }

        // Apply status filter using the already-loaded $summaryRows
        if ($filterStatus && $mandatoryCount > 0 && $summaryRows->isNotEmpty()) {
            $filteredIds = match ($filterStatus) {
                'compliant' => $summaryRows->where('submitted_mandatory_count', $mandatoryCount)->pluck('id'),
                'non_compliant' => $summaryRows->where('submitted_mandatory_count', 0)->pluck('id'),
                'partially_compliant' => $summaryRows
                    ->filter(fn ($row) => (int) $row->submitted_mandatory_count > 0 && (int) $row->submitted_mandatory_count < $mandatoryCount)
                    ->pluck('id'),
                default => collect(),
            };

            if ($filteredIds->isEmpty()) {
                $studentsQuery->whereRaw('1 = 0');
            } else {
                $studentsQuery->whereIn('id', $filteredIds->all());
            }
        }

        $paginatedStudents = $studentsQuery->paginate(5)->withQueryString();
        $studentIds = $paginatedStudents->getCollection()->pluck('id');
        $anomalyCounts = Attendance::query()
            ->selectRaw('student_id, COUNT(*) as anomalies_count')
            ->whereIn('student_id', $studentIds)
            ->where('check_in_at', '>=', now()->subDays(7))
            ->whereIn('geofence_status', ['outside_flagged', 'near_boundary_review', 'location_unavailable'])
            ->groupBy('student_id')
            ->pluck('anomalies_count', 'student_id');

        $studentsWithCompliance = $paginatedStudents->getCollection()->map(function (Student $student) use ($mandatoryCount, $anomalyCounts) {
            $submittedCount = (int) ($student->submitted_mandatory_count ?? 0);
            $status = $this->complianceStatusForCounts($submittedCount, $mandatoryCount);
            $attendanceAnomalies7d = (int) ($anomalyCounts[$student->id] ?? 0);

            $riskScore = 0;
            if ($status === 'non_compliant') {
                $riskScore += 60;
            } elseif ($status === 'partially_compliant') {
                $riskScore += 30;
            }
            $riskScore += min(40, $attendanceAnomalies7d * 10);

            $riskLevel = 'low';
            if ($riskScore >= 70) {
                $riskLevel = 'critical';
            } elseif ($riskScore >= 40) {
                $riskLevel = 'warning';
            }

            return [
                'student' => $student,
                'status' => $status,
                'submitted_mandatory' => $submittedCount,
                'total_mandatory' => $mandatoryCount,
                'attendance_anomalies_7d' => $attendanceAnomalies7d,
                'risk_score' => $riskScore,
                'risk_level' => $riskLevel,
            ];
        });

        $canManage = in_array((string) (auth()->user()?->role ?? ''), InternshipRoles::operationalManagerRoles(), true);

        $viewData = [
            'studentsWithCompliance' => $studentsWithCompliance,
            'paginatedStudents' => $paginatedStudents,
            'summary' => $summary,
            'mandatoryDocs' => $mandatoryDocs,
            'mandatoryDocsCount' => $mandatoryCount,
            'filterStatus' => $filterStatus,
            'search' => $search,
            'sectionFilter' => $sectionFilter,
            'riskFilter' => $riskFilter,
            'myStudents' => $myStudents,
            'canManage' => $canManage,
        ];

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('compliance.partials.ajax-list', $viewData);
        }

        return view('compliance.index', $viewData);
    }

    private function complianceStatusForCounts(int $submittedCount, int $totalMandatory): string
    {
        if ($totalMandatory === 0) {
            return 'no_required_documents';
        }

        if ($submittedCount === $totalMandatory) {
            return 'compliant';
        }
        if ($submittedCount === 0) {
            return 'non_compliant';
        }

        return 'partially_compliant';
    }
}
