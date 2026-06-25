<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\WeeklyJournal;
use App\Services\NotificationService;
use App\Services\WeeklyJournalExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class WeeklyJournalController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    // ============ Staff Views ============

    public function index(Request $request)
    {
        $query = WeeklyJournal::with(['student', 'deployment.company', 'reviewer'])
            ->orderByDesc('week_start_date');

        $search = $request->query('search');
        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $status = $request->query('status');
        if ($status && in_array($status, ['draft', 'submitted', 'reviewed'])) {
            $query->where('status', $status);
        }

        $section = $request->query('section');
        if ($section && in_array($section, ['A', 'B', 'C', 'D'])) {
            $query->whereHas('student', fn ($q) => $q->where('section', $section));
        }

        $myStudents = $request->has('my_students') ? $request->boolean('my_students') : true;
        if ($myStudents) {
            $query->whereHas('student', fn ($q) => $q->where('assigned_instructor_id', auth()->id()));
        }

        $weeklyJournals = $query->paginate(10)->withQueryString();

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('weekly-journals.partials.ajax-list', compact('weeklyJournals', 'search', 'status', 'section', 'myStudents'));
        }

        return view('weekly-journals.index', compact('weeklyJournals', 'search', 'status', 'section', 'myStudents'));
    }

    public function show(WeeklyJournal $weeklyJournal)
    {
        $weeklyJournal->load(['student', 'deployment.company', 'reviewer']);

        return view('weekly-journals.show', compact('weeklyJournal'));
    }

    public function studentProgress(Student $student)
    {
        $journals = WeeklyJournal::where('student_id', $student->id)
            ->with(['deployment.company', 'reviewer'])
            ->orderBy('week_number')
            ->get();

        return view('weekly-journals.student', compact('student', 'journals'));
    }

    public function review(Request $request, WeeklyJournal $weeklyJournal)
    {
        $validated = $request->validate([
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $weeklyJournal->update([
            'status' => 'reviewed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'remarks' => $validated['remarks'],
        ]);

        if ($weeklyJournal->student->account) {
            $this->notificationService->notifyStudentAccount($weeklyJournal->student->account, [
                'event_type' => 'journal.reviewed',
                'title' => __('Weekly Journal Reviewed'),
                'body' => __('Your weekly journal for Week :week has been reviewed.', [
                    'week' => $weeklyJournal->week_number,
                ]),
                'action_url' => route('student.weekly-journals.show', $weeklyJournal),
                'meta' => [
                    'journal_id' => (int) $weeklyJournal->id,
                    'week_number' => (int) $weeklyJournal->week_number,
                ],
            ]);
        }

        return back()->with('status', __('Weekly journal marked as reviewed.'))
            ->with('status_type', 'success');
    }

    // ============ Student Portal ============

    public function studentIndex()
    {
        $student = request()->attributes->get('student');
        $this->ensureWeeksExist($student);

        $weeklyJournals = WeeklyJournal::where('student_id', $student->id)
            ->with(['deployment.company'])
            ->orderBy('week_number')
            ->get();

        $deployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->first();

        return view('student.weekly-journals.index', compact('weeklyJournals', 'deployment'));
    }

    public function studentShow(WeeklyJournal $weeklyJournal)
    {
        $student = request()->attributes->get('student');
        abort_unless($weeklyJournal->student_id === $student->id, 403);

        $weeklyJournal->load(['deployment.company']);

        if (! $weeklyJournal->supervisor_name) {
            $company = $weeklyJournal->deployment?->company;
            if ($company && ($company->contact_first_name || $company->contact_last_name)) {
                $weeklyJournal->supervisor_name = trim($company->contact_first_name . ' ' . $company->contact_last_name);
            }
        }

        return view('student.weekly-journals.show', compact('weeklyJournal'));
    }

    public function updateActivities(Request $request, WeeklyJournal $weeklyJournal)
    {
        $student = request()->attributes->get('student');
        abort_unless($weeklyJournal->student_id === $student->id, 403);
        abort_unless($weeklyJournal->isEditable(), 403);

        $validated = $request->validate([
            'activities' => ['nullable', 'array'],
            'activities.*.day' => ['required', 'string', 'max:20'],
            'activities.*.date' => ['nullable', 'date'],
            'activities.*.tasks' => ['nullable', 'string', 'max:2000'],
        ]);

        $weeklyJournal->update(['activities' => $validated['activities'] ?? []]);

        return response()->json(['saved' => true]);
    }

    public function uploadFile(Request $request, WeeklyJournal $weeklyJournal)
    {
        $student = request()->attributes->get('student');
        abort_unless($weeklyJournal->student_id === $student->id, 403);
        abort_unless($weeklyJournal->isEditable(), 403);

        $validated = $request->validate([
            'day' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,jpg,jpeg,png'],
        ]);

        $file = $request->file('file');
        $path = $file->store("student-documents/{$student->id}/weekly-journals", 'public');

        $files = $weeklyJournal->files ?? [];
        $files[] = [
            'day' => $validated['day'],
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
        ];

        $weeklyJournal->update(['files' => $files]);

        return response()->json([
            'saved' => true,
            'file' => [
                'day' => $validated['day'],
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'url' => route('student.weekly-journals.file', ['weeklyJournal' => $weeklyJournal, 'day' => $validated['day']]),
            ],
        ]);
    }

    public function deleteFile(Request $request, WeeklyJournal $weeklyJournal)
    {
        $student = request()->attributes->get('student');
        abort_unless($weeklyJournal->student_id === $student->id, 403);
        abort_unless($weeklyJournal->isEditable(), 403);

        $validated = $request->validate([
            'day' => ['required', 'string', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday'],
            'file_path' => ['required', 'string'],
        ]);

        $files = collect($weeklyJournal->files ?? [])->filter(function ($f) use ($validated) {
            return ! ($f['day'] === $validated['day'] && $f['file_path'] === $validated['file_path']);
        })->values()->toArray();

        Storage::disk('public')->delete($validated['file_path']);

        $weeklyJournal->update(['files' => $files]);

        return response()->json(['saved' => true]);
    }

    public function studentSubmit(WeeklyJournal $weeklyJournal)
    {
        $student = request()->attributes->get('student');
        abort_unless($weeklyJournal->student_id === $student->id, 403);
        abort_unless($weeklyJournal->isEditable(), 403);

        $previous = [
            'status' => $weeklyJournal->status,
            'submitted_at' => $weeklyJournal->submitted_at,
        ];

        $weeklyJournal->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $this->notificationService->notifyRole('instructor', [
            'event_type' => 'journal.submitted',
            'title' => __('Weekly Journal Submitted'),
            'body' => __(':student submitted their Week :week journal for review.', [
                'student' => $student->name,
                'week' => $weeklyJournal->week_number,
            ]),
            'action_url' => route('weekly-journals.show', $weeklyJournal),
            'meta' => [
                'journal_id' => (int) $weeklyJournal->id,
                'student_id' => (int) $student->id,
                'week_number' => (int) $weeklyJournal->week_number,
            ],
        ]);

        $undoKey = 'undo_' . $weeklyJournal->id . '_' . now()->timestamp;
        Cache::put($undoKey, [
            'model' => WeeklyJournal::class,
            'id' => $weeklyJournal->id,
            'action' => 'submit',
            'previous' => $previous,
        ], 30);

        return back()->with('status', __('Weekly journal submitted for review.'))
            ->with('status_type', 'success')
            ->with('undo_key', $undoKey);
    }

    // ============ Export ============

    public function exportDocx(WeeklyJournal $weeklyJournal)
    {
        $student = request()->attributes->get('student');
        abort_unless($weeklyJournal->student_id === $student->id, 403);

        $service = new WeeklyJournalExportService();
        $tempPath = $service->generate($weeklyJournal);

        $filename = "WeeklyJournal-Week{$weeklyJournal->week_number}-{$student->student_number}.docx";

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }

    public function downloadFile(WeeklyJournal $weeklyJournal, string $day)
    {
        $user = auth()->user();
        $student = request()->attributes->get('student');

        $files = $weeklyJournal->files ?? [];
        $file = collect($files)->firstWhere('day', $day);

        if (! $file || ! isset($file['file_path'])) {
            abort(404, __('File not found.'));
        }

        if ($student) {
            abort_unless($weeklyJournal->student_id === $student->id, 403);
        } elseif (! $user || ! in_array($user->role, ['instructor', 'chairperson', 'dean'])) {
            abort(403);
        }

        $path = $file['file_path'];
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            abort(404, __('File not found.'));
        }

        $fileName = $file['file_name'] ?? "week-{$weeklyJournal->week_number}-{$day}.pdf";

        return $disk->download($path, $fileName);
    }

    // ============ Week Generation ============

    private function ensureWeeksExist($student): void
    {
        $deployment = $student->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->first();

        if (! $deployment || ! $deployment->start_date) {
            return;
        }

        $existingWeeks = WeeklyJournal::where('student_id', $student->id)
            ->orderBy('week_start_date')
            ->get();

        $latestWeekNumber = $existingWeeks->max('week_number') ?? 0;

        if ($existingWeeks->isNotEmpty()) {
            $lastWeekEnd = $existingWeeks->last()->week_end_date;
            $cursor = $lastWeekEnd->copy()->addDay()->startOfWeek();
        } else {
            $cursor = $deployment->start_date->copy()->startOfWeek();
        }

        $end = $deployment->end_date
            ? $deployment->end_date->copy()->endOfWeek()
            : now()->endOfWeek();

        $weekNumber = $latestWeekNumber;

        while ($cursor->lte($end)) {
            $weekNumber++;
            WeeklyJournal::create([
                'student_id' => $student->id,
                'deployment_id' => $deployment->id,
                'week_start_date' => $cursor->format('Y-m-d'),
                'week_end_date' => $cursor->copy()->endOfWeek()->format('Y-m-d'),
                'week_number' => $weekNumber,
                'status' => 'draft',
            ]);
            $cursor->addWeek();
        }
    }
}
