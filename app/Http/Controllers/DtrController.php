<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\DailyTimeRecord;
use App\Models\Deployment;
use App\Models\MonthlyDttr;
use App\Models\Student;
use App\Services\DttrExportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DtrController extends Controller
{
    // ============ Staff Views ============

    public function index(Request $request)
    {
        $year = (int) $request->query('year', now()->year);
        $month = (int) $request->query('month', now()->month);
        $year = max(2020, min(2100, $year));
        $month = max(1, min(12, $month));
        $section = trim((string) $request->query('section', ''));
        $search = trim((string) $request->query('search', ''));
        $selectedStudentId = (int) $request->query('student', 0);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();
        $prevMonth = $startOfMonth->copy()->subMonth();
        $nextMonth = $startOfMonth->copy()->addMonth();

        $sections = Student::whereNotNull('section')
            ->distinct()->orderBy('section')->pluck('section');

        $students = Student::whereHas('deployments', fn ($q) => $q->whereIn('status', ['active', 'completed']))
            ->when($section, fn ($q) => $q->where('section', $section))
            ->when($search, fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            }))
            ->orderBy('last_name')
            ->paginate(20)
            ->withQueryString();

        $selectedStudent = null;
        $studentRecords = collect();
        $totalHours = 0;
        $presentDays = 0;
        if ($selectedStudentId) {
            $selectedStudent = Student::find($selectedStudentId);
            if ($selectedStudent) {
                $studentRecords = DailyTimeRecord::where('student_id', $selectedStudentId)
                    ->whereBetween('date', [$startOfMonth, $endOfMonth])
                    ->orderBy('date')
                    ->get();
                $totalMinutes = $studentRecords->sum('total_minutes');
                $totalHours = $totalMinutes > 0 ? sprintf('%dh %02dm', intdiv($totalMinutes, 60), $totalMinutes % 60) : '0h 00m';
                $presentDays = $studentRecords->count();
            }
        }

        if ($request->header('HX-Request')) {
            return view('dtr.partials.student-list', compact(
                'students', 'section', 'search', 'year', 'month',
                'startOfMonth', 'endOfMonth', 'prevMonth', 'nextMonth',
                'sections', 'selectedStudent', 'selectedStudentId',
                'studentRecords', 'totalHours', 'presentDays'
            ));
        }

        return view('dtr.index', compact(
            'students', 'section', 'search', 'year', 'month',
            'startOfMonth', 'endOfMonth', 'prevMonth', 'nextMonth',
            'sections', 'selectedStudent', 'selectedStudentId',
            'studentRecords', 'totalHours', 'presentDays'
        ));
    }

    public function show(DailyTimeRecord $dtr)
    {
        $dtr->load(['student', 'deployment.company']);

        return view('dtr.show', compact('dtr'));
    }

    // ============ Student Portal Views ============

    public function studentIndex()
    {
        $student = request()->attributes->get('student');
        $deployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->first();

        abort_unless($deployment, 403, 'No active deployment found.');

        $deployment->load('company');

        $isSchoolBased = is_null($deployment->company_id);

        $year = request()->query('year', now()->year);
        $month = request()->query('month', now()->month);

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $dtrRecords = DailyTimeRecord::where('student_id', $student->id)
            ->where('source', 'attendance')
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(fn ($r) => $r->date->format('Y-m-d'));

        $attendanceRecords = Attendance::where('student_id', $student->id)
            ->whereBetween('check_in_at', [$startOfMonth, $endOfMonth->endOfDay()])
            ->get()
            ->keyBy(fn ($a) => $a->check_in_at->format('Y-m-d'));

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

            if ($dtrRecords->has($dateStr)) {
                $existing = $dtrRecords->get($dateStr);
                if ($existing->am_departure !== $dtrData['am_departure'] || $existing->pm_arrival !== $dtrData['pm_arrival'] || $existing->pm_departure !== $dtrData['pm_departure'] || $existing->total_minutes !== $dtrData['total_minutes']) {
                    $existing->update($dtrData);
                    $dtrRecords->put($dateStr, $existing);
                }
            } else {
                $dtr = DailyTimeRecord::create($dtrData);
                $dtrRecords->put($dateStr, $dtr);
            }
        }

        $prevMonth = Carbon::create($year, $month, 1)->subMonth();
        $nextMonth = Carbon::create($year, $month, 1)->addMonth();

        $signedDttr = MonthlyDttr::where('student_id', $student->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        return view('student.dtr.index', compact(
            'student', 'deployment', 'isSchoolBased', 'dtrRecords',
            'year', 'month', 'startOfMonth', 'endOfMonth',
            'prevMonth', 'nextMonth', 'signedDttr'
        ));
    }

    /**
     * Update tasks for a specific DTR date.
     * Creates a DTR record if none exists for that date (covers absent days).
     */
    public function updateTasks(Request $request)
    {
        $student = request()->attributes->get('student');

        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'tasks' => ['nullable', 'string', 'max:100'],
        ]);

        $tasks = trim((string) $validated['tasks']);

        $deployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->first();

        $dtr = DailyTimeRecord::firstOrNew([
            'student_id' => $student->id,
            'date' => $validated['date'],
        ]);

        if (! $dtr->exists) {
            $dtr->deployment_id = $deployment?->id;
            $dtr->source = 'manual';
        }

        $dtr->tasks = $tasks;
        $dtr->save();

        return response()->json(['success' => true]);
    }

    public function studentShow(DailyTimeRecord $dtr)
    {
        $student = request()->attributes->get('student');
        abort_unless($dtr->student_id === $student->id, 403);

        $dtr->load(['deployment.company']);

        return view('student.dtr.show', compact('dtr'));
    }

    // ============ Monthly DTTR Download / Upload ============

    public function exportDttrData()
    {
        $student = request()->attributes->get('student');

        $year = request()->query('year', now()->year);
        $month = request()->query('month', now()->month);

        $deployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->first();

        abort_unless($deployment, 403, 'No active deployment found.');

        $companyName = $deployment->company?->name ?? 'School-Based';

        $service = new DttrExportService();
        $tempPath = $service->generate(
            student: $student,
            companyName: $companyName,
            year: $year,
            month: $month,
        );

        $filename = "DTTR-{$student->student_number}-{$year}-{$month}.docx";

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function uploadSignedDttr(Request $request)
    {
        $student = request()->attributes->get('student');
        $deployment = $student->deployments()->whereIn('status', ['active', 'completed'])->latest()->first();
        abort_unless($deployment, 403, 'No active deployment found.');

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
            'signed_file' => ['required', 'file', 'mimes:pdf', 'max:5120'],
        ]);

        $path = $request->file('signed_file')->store("student-documents/{$student->id}/signed-dttr", 'public');

        MonthlyDttr::updateOrCreate(
            [
                'student_id' => $student->id,
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'deployment_id' => $deployment->id,
                'file_path' => $path,
                'file_name' => $request->file('signed_file')->getClientOriginalName(),
            ]
        );

        return back()->with('status', __('Signed DTTR submitted successfully.'))
            ->with('status_type', 'success');
    }

}
