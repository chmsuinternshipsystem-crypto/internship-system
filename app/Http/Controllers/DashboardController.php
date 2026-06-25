<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\Deployment;
use App\Models\Evaluation;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentRiskFlag;
use App\Models\WeeklyJournal;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $role = auth()->user()?->role;

        $stats = $this->computeBaseStats();
        $complianceSummary = $this->getComplianceSummary($stats['totalStudents']);
        $pendingQueueCount = $role === 'chairperson'
            ? $this->getPendingQueueCount()
            : 0;
        $sectionCompliance = in_array($role, ['instructor', 'chairperson'], true)
            ? $this->getSectionCompliance()
            : collect();
        $evalSummary = $role === 'dean'
            ? $this->getEvalSummary()
            : null;
        $kpiCards = $this->getKpiCards($role, $stats, $complianceSummary, $pendingQueueCount);

        $atRiskCount = StudentRiskFlag::active()
            ->when($role === 'instructor', fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('assigned_instructor_id', auth()->id())))
            ->count();

        return view('dashboard', compact(
            'kpiCards',
            'sectionCompliance',
            'evalSummary',
            'complianceSummary',
            'atRiskCount',
        ));
    }

    private function computeBaseStats(): array
    {
        $totalStudents = Student::count();

        $deployedStudents = Student::whereHas('deployments', function ($q) {
            $q->whereIn('status', ['active', 'completed']);
        })->count();

        $completedStudents = Student::whereHas('deployments', function ($q) {
            $q->where('status', 'completed');
        })->count();

        return [
            'totalStudents' => $totalStudents,
            'deployedStudents' => $deployedStudents,
            'completedStudents' => $completedStudents,
            'notDeployedStudents' => max(0, $totalStudents - $deployedStudents),
            'totalCompanies' => Company::count(),
            'activeCompanies' => Company::where('is_active', true)->count(),
            'totalDeployments' => Deployment::count(),
        ];
    }

    private function getComplianceSummary(int $totalStudents): array
    {
        $mandatoryCount = RequiredDocument::where('is_mandatory', true)->count();

        if ($mandatoryCount === 0 || $totalStudents === 0) {
            return [
                'compliant' => 0,
                'partial' => 0,
                'nonCompliant' => $totalStudents,
            ];
        }

        $students = Student::query()->withCount([
            'documents as submitted_mandatory_count' => function ($q) {
                $q->whereHas('requiredDocument', function ($r) {
                    $r->where('is_mandatory', true);
                })->where('status', 'Submitted');
            },
        ])->get(['id']);

        $compliant = $students->where('submitted_mandatory_count', $mandatoryCount)->count();
        $nonCompliant = $students->where('submitted_mandatory_count', 0)->count();
        $partial = max(0, $totalStudents - $compliant - $nonCompliant);

        return [
            'compliant' => $compliant,
            'partial' => $partial,
            'nonCompliant' => $nonCompliant,
        ];
    }

    private function getSectionCompliance(): Collection
    {
        $mandatoryCount = RequiredDocument::where('is_mandatory', true)->count();

        if ($mandatoryCount === 0) {
            return collect();
        }

        return Student::whereNotNull('section')
            ->select('section')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(
                CASE WHEN (
                    SELECT COUNT(*) FROM student_documents
                    WHERE student_id = students.id
                    AND required_document_id IN (
                        SELECT id FROM required_documents WHERE is_mandatory = true
                    )
                    AND status = 'Submitted'
                ) >= {$mandatoryCount} THEN 1 ELSE 0 END
            ) as compliant_count")
            ->groupBy('section')
            ->orderBy('section')
            ->get();
    }

    private function getPendingQueueCount(): int
    {
        return StudentDocument::query()
            ->where('current_holder_role', 'chairperson')
            ->whereNotNull('workflow_status')
            ->whereNotIn('workflow_status', ['completed', 'rejected'])
            ->count();
    }

    private function getEvalSummary(): object
    {
        return cache()->remember('dashboard:eval_summary', 300, function () {
            $row = Evaluation::selectRaw("
                    COUNT(*) as total,
                    AVG(CASE WHEN evaluation_type = 'industry' THEN score END) as industry_avg,
                    AVG(CASE WHEN evaluation_type = 'school' THEN score END) as school_avg,
                    AVG(CASE WHEN evaluation_type = 'student_feedback' THEN score END) as student_feedback_avg
                ")->first();

            return (object) [
                'total' => (int) ($row->total ?? 0),
                'industryAvg' => isset($row->industry_avg) ? round((float) $row->industry_avg, 1) : null,
                'schoolAvg' => isset($row->school_avg) ? round((float) $row->school_avg, 1) : null,
                'studentFeedbackAvg' => isset($row->student_feedback_avg) ? round((float) $row->student_feedback_avg, 1) : null,
            ];
        });
    }

    private function getKpiCards(string $role, array $stats, array $compliance, int $pendingQueueCount): array
    {
        return match ($role) {
            'chairperson' => $this->chairpersonCards($compliance, $stats, $pendingQueueCount),
            'dean' => $this->deanCards($compliance, $stats),
            default => $this->instructorCards($stats),
        };
    }

    private function instructorCards(array $stats): array
    {
        $userId = auth()->id();
        $supervisedCount = Student::where('assigned_instructor_id', $userId)->count();
        $pendingReviews = WeeklyJournal::whereHas('student', fn ($q) => $q->where('assigned_instructor_id', $userId))
            ->where('status', 'submitted')
            ->count();
        $absentToday = Attendance::whereDate('check_in_at', today())
            ->whereHas('student', fn ($q) => $q->where('assigned_instructor_id', $userId))
            ->whereNull('time_out_at')
            ->count();

        return [
            [
                'label' => __('MY STUDENTS'),
                'value' => $supervisedCount,
                'sub' => __('Pending reviews: :count', ['count' => $pendingReviews]),
                'color' => '#059669',
                'icon' => 'bi-people-fill',
                'link' => route('students.index', ['my_students' => 1]),
            ],
            [
                'label' => __('DEPLOYED'),
                'value' => $stats['deployedStudents'],
                'sub' => __('Currently assigned to partner companies'),
                'color' => '#1a6b3c',
                'icon' => 'bi-briefcase-fill',
            ],
            [
                'label' => __('COMPLETED'),
                'value' => $stats['completedStudents'],
                'sub' => __('Marked as internship completed'),
                'color' => '#14b8a6',
                'icon' => 'bi-check-circle-fill',
            ],
            [
                'label' => __('NOT DEPLOYED'),
                'value' => $stats['notDeployedStudents'],
                'sub' => __('Students still awaiting deployment'),
                'color' => '#f97316',
                'icon' => 'bi-clock-fill',
                'link' => route('students.index', ['deployment_status' => 'pending']),
            ],
            [
                'label' => __('PARTNER COMPANIES'),
                'value' => $stats['totalCompanies'],
                'sub' => __('Active: :count', ['count' => $stats['activeCompanies']]),
                'color' => '#8b5cf6',
                'icon' => 'bi-building-fill',
            ],
            [
                'label' => __('DEPLOYMENTS RECORDED'),
                'value' => $stats['totalDeployments'],
                'sub' => __('Total student–company assignments in the system.'),
                'color' => '#6b7280',
                'icon' => 'bi-list-ul',
                'link' => route('deployments.index'),
            ],
        ];
    }

    private function chairpersonCards(array $compliance, array $stats, int $pendingQueueCount): array
    {
        return [
            [
                'label' => __('TOTAL STUDENTS'),
                'value' => $stats['totalStudents'],
                'sub' => __('All registered students in the system'),
                'color' => '#6366f1',
                'icon' => 'bi-people-fill',
                'link' => route('students.index'),
            ],
            [
                'label' => __('PENDING YOUR REVIEW'),
                'value' => $pendingQueueCount,
                'sub' => __('Documents awaiting your action'),
                'color' => '#7c3aed',
                'icon' => 'bi-diagram-3-fill',
                'link' => route('student-documents.queue'),
            ],
            [
                'label' => __('COMPLETE'),
                'value' => $compliance['compliant'],
                'sub' => __('Out of :total total students', ['total' => $stats['totalStudents']]),
                'color' => '#059669',
                'icon' => 'bi-check-circle-fill',
                'link' => route('compliance.index'),
            ],
            [
                'label' => __('IN PROGRESS'),
                'value' => $compliance['partial'],
                'sub' => __('Some mandatory documents submitted'),
                'color' => '#2563eb',
                'icon' => 'bi-exclamation-circle-fill',
                'link' => route('compliance.index'),
            ],
            [
                'label' => __('NEEDS ATTENTION'),
                'value' => $compliance['nonCompliant'],
                'sub' => __('No mandatory documents submitted'),
                'color' => '#d97706',
                'icon' => 'bi-x-circle-fill',
                'link' => route('compliance.index'),
            ],
        ];
    }

    private function deanCards(array $compliance, array $stats): array
    {
        return [
            [
                'label' => __('COMPLETE'),
                'value' => $compliance['compliant'],
                'sub' => __('Out of :total total students', ['total' => $stats['totalStudents']]),
                'color' => '#059669',
                'icon' => 'bi-check-circle-fill',
                'link' => route('compliance.index'),
            ],
            [
                'label' => __('IN PROGRESS'),
                'value' => $compliance['partial'],
                'sub' => __('Some mandatory documents submitted'),
                'color' => '#2563eb',
                'icon' => 'bi-exclamation-circle-fill',
                'link' => route('compliance.index'),
            ],
            [
                'label' => __('NEEDS ATTENTION'),
                'value' => $compliance['nonCompliant'],
                'sub' => __('No mandatory documents submitted'),
                'color' => '#d97706',
                'icon' => 'bi-x-circle-fill',
                'link' => route('compliance.index'),
            ],
        ];
    }
}
