<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CertificateController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function index(Request $request)
    {
        $query = Certificate::with(['student', 'deployment.company', 'uploader', 'verifier'])
            ->orderByDesc('issued_at');

        $search = $request->query('search');
        if ($search) {
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('student_number', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $type = $request->query('type');
        if ($type) {
            $query->where('type', $type);
        }

        $status = $request->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $certificates = $query->paginate(10)->withQueryString();

        if (filter_var($request->header('HX-Request'), FILTER_VALIDATE_BOOLEAN)) {
            return view('certificates.partials.ajax-list', compact('certificates'));
        }

        return view('certificates.index', compact('certificates', 'search', 'type', 'status'));
    }

    public function create()
    {
        $students = Student::whereHas('deployments', fn ($q) => $q->whereIn('status', ['active', 'completed']))
            ->with('deployments.company')
            ->orderBy('student_number')
            ->get();

        return view('certificates.create', compact('students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'deployment_id' => ['nullable', 'exists:deployments,id'],
            'type' => ['required', Rule::in(['completion', 'merit', 'attendance', 'special', 'other'])],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'file' => ['required', 'file', 'max:2048', 'mimes:pdf,jpg,jpeg,png'],
            'issued_at' => ['nullable', 'date'],
        ]);

        $validated['title'] = trim(strip_tags($validated['title']));
        $validated['description'] = isset($validated['description']) ? trim(strip_tags($validated['description'])) : null;

        $path = $request->file('file')->store('certificates', 'public');

        $certificate = Certificate::create([
            'student_id' => $validated['student_id'],
            'deployment_id' => $validated['deployment_id'],
            'uploaded_by' => Auth::id(),
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'file_path' => $path,
            'issued_at' => $validated['issued_at'] ?? now(),
            'status' => 'pending',
        ]);

        // Notify student
        $student = Student::find($validated['student_id']);
        if ($student->account) {
            $this->notificationService->notifyStudentAccount($student->account, [
                'event_type' => 'certificate.uploaded',
                'title' => __('New certificate uploaded'),
                'body' => __('A new certificate ":title" has been uploaded and is pending verification.', ['title' => $validated['title']]),
                'action_url' => route('student.certificates.show', $certificate),
                'meta' => [
                    'certificate_id' => (int) $certificate->id,
                ],
            ]);
        }

        return redirect()->route('certificates.index')
            ->with('status', __('Certificate uploaded successfully. Pending verification.'))
            ->with('status_type', 'success');
    }

    public function show(Certificate $certificate)
    {
        $certificate->load(['student', 'deployment.company', 'uploader', 'verifier']);

        return view('certificates.show', compact('certificate'));
    }

    public function verify(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['approve', 'reject'])],
            'verification_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $certificate->update([
            'status' => $validated['action'] === 'approve' ? 'verified' : 'rejected',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $validated['verification_notes'],
        ]);

        // Notify student
        if ($certificate->student->account) {
            $actionLabel = $validated['action'] === 'approve' ? 'verified' : 'rejected';
            $this->notificationService->notifyStudentAccount($certificate->student->account, [
                'event_type' => 'certificate.'.$validated['action'],
                'title' => __('Certificate :action', ['action' => $actionLabel]),
                'body' => __('Your certificate ":title" has been :action.', [
                    'title' => $certificate->title,
                    'action' => $actionLabel,
                ]),
                'action_url' => route('student.certificates.show', $certificate),
                'meta' => [
                    'certificate_id' => (int) $certificate->id,
                    'action' => $validated['action'],
                ],
            ]);
        }

        return back()->with('status', __('Certificate :action successfully.', ['action' => $validated['action'].'d']))
            ->with('status_type', 'success');
    }

    public function download(Certificate $certificate, Request $request)
    {
        $student = $request->attributes->get('student');
        if ($student && $certificate->student_id !== $student->id) {
            abort(403);
        }

        if (! $certificate->file_path || ! Storage::disk('public')->exists($certificate->file_path)) {
            abort(404);
        }

        return Storage::disk('public')->response(
            $certificate->file_path,
            basename($certificate->file_path),
            ['Content-Disposition' => 'inline']
        );
    }

    // Student Portal
    public function studentIndex()
    {
        $student = request()->attributes->get('student');
        $certificates = Certificate::with('deployment.company')
            ->where('student_id', $student->id)
            ->orderByDesc('issued_at')
            ->paginate(10);

        return view('student.certificates.index', compact('certificates'));
    }

    public function studentShow(Certificate $certificate)
    {
        $student = request()->attributes->get('student');
        abort_unless($certificate->student_id === $student->id, 403);

        $certificate->load(['deployment.company']);

        return view('student.certificates.show', compact('certificate'));
    }
}
