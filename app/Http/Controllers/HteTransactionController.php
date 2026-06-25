<?php

namespace App\Http\Controllers;

use App\Models\Evaluation;
use App\Models\HteTransactionLink;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use App\Services\DocumentWorkflowEngine;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HteTransactionController extends Controller
{
    public function __construct(
        private readonly DocumentWorkflowEngine $workflowEngine,
        private readonly NotificationService $notificationService,
    ) {}

    public function show(string $token)
    {
        $link = $this->findLink($token);
        if (! $link) {
            return view('hte-transactions.invalid');
        }

        return view('hte-transactions.show', compact('link'));
    }

    public function submitEvaluation(Request $request, string $token)
    {
        $link = $this->findLink($token);
        if (! $link) {
            return view('hte-transactions.invalid');
        }

        $rules = [
            'comments' => ['nullable', 'string', 'max:1000'],
        ];

        foreach (Evaluation::CRITERIA_CATEGORIES as $catKey => $category) {
            foreach (array_keys($category['items']) as $itemKey) {
                $field = "criteria_scores.{$catKey}.{$itemKey}";
                $rules[$field] = ['required', 'integer', 'between:1,5'];
            }
        }

        $data = $request->validateWithBag('evaluate', $rules);

        $criteriaScores = [];
        $raw = $data['criteria_scores'] ?? [];
        if (is_array($raw)) {
            foreach (Evaluation::CRITERIA_CATEGORIES as $catKey => $category) {
                $catRaw = $raw[$catKey] ?? [];
                if (! is_array($catRaw)) {
                    continue;
                }
                foreach (array_keys($category['items']) as $itemKey) {
                    $v = $catRaw[$itemKey] ?? null;
                    if ($v !== null && $v !== '') {
                        $criteriaScores[$catKey][$itemKey] = (int) $v;
                    }
                }
            }
        }

        $computedScore = Evaluation::computeOverallScore($criteriaScores);

        $comment = trim(strip_tags((string) ($data['comments'] ?? '')));
        $name = trim(strip_tags((string) ($link->supervisor_name ?? '')));
        $email = trim(strip_tags((string) ($link->supervisor_email ?? '')));

        if ($name !== '') {
            $comment = ($comment !== '' ? $comment."\n\n" : '').'Supervisor: '.$name;
        }
        if ($email !== '') {
            $comment = ($comment !== '' ? $comment."\n" : '').'Supervisor Email: '.$email;
        }

        try {
            DB::transaction(function () use ($link, $criteriaScores, $computedScore, $comment): void {
                Evaluation::query()->create([
                    'student_id' => $link->student_id,
                    'company_id' => $link->company_id,
                    'evaluator_id' => null,
                    'evaluation_type' => 'industry',
                    'score' => $computedScore ?? 50,
                    'criteria_scores' => $criteriaScores,
                    'comments' => $comment !== '' ? $comment : null,
                    'evaluated_at' => now(),
                ]);

                $link->forceFill([
                    'used_at' => now(),
                    'used_for' => 'evaluation',
                ])->save();
            });
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->withErrors([
                    'form' => __('We could not submit the evaluation right now. Please try again.'),
                ], 'evaluate');
        }

        // Notify student
        $student = $link->student()->with('account')->first();
        if ($student && $student->account) {
            $this->notificationService->notifyStudentAccount($student->account, [
                'event_type' => 'hte.evaluation_submitted',
                'title' => __('HTE Evaluation Completed'),
                'body' => __('Your HTE supervisor has submitted your evaluation. Check your grades on the dashboard.'),
                'action_url' => route('student.dashboard'),
                'meta' => ['score' => $computedScore],
            ]);
        }

        // Notify instructor(s)
        $assignedInstructor = Student::find($link->student_id)?->assignedInstructor;
        if ($assignedInstructor) {
            $this->notificationService->notifyUsers([$assignedInstructor->id], [
                'event_type' => 'hte.evaluation_submitted',
                'title' => __('HTE Evaluation Received'),
                'body' => __('The HTE supervisor of :student has submitted the evaluation. Review it in the Document Queue.', ['student' => $student?->name ?? '']),
                'action_url' => route('student-documents.queue'),
                'meta' => ['student_id' => $link->student_id],
            ]);
        }

        return view('hte-transactions.success', [
            'title' => __('Evaluation Submitted'),
            'message' => __('Thank you. The evaluation for :student has been recorded and sent to the instructor for review.', ['student' => $student?->name ?? '']),
        ]);
    }

    public function submitUpload(Request $request, string $token)
    {
        $link = $this->findLink($token);
        if (! $link) {
            return view('hte-transactions.invalid');
        }

        $data = $request->validateWithBag('upload', [
            'required_document_id' => ['required', 'exists:required_documents,id'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx', 'mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'max:2048'],
        ]);

        $requiredDocument = RequiredDocument::query()->findOrFail((int) $data['required_document_id']);
        $path = $data['file']->store("student-documents/{$link->student_id}", 'public');

        try {
            DB::transaction(function () use ($link, $requiredDocument, $path): void {
                $existing = StudentDocument::query()
                    ->where('student_id', $link->student_id)
                    ->where('required_document_id', $requiredDocument->id)
                    ->first();
                $oldPath = $existing?->file_path;

                $studentDocument = StudentDocument::query()->updateOrCreate(
                    [
                        'student_id' => $link->student_id,
                        'required_document_id' => $requiredDocument->id,
                    ],
                    [
                        'workflow_template_id' => $requiredDocument->workflow_template_id,
                        'status' => 'Submitted',
                        'submitted_at' => now(),
                        'file_path' => $path,
                        'uploaded_by' => null,
                        'verified_by' => null,
                    ]
                );

                $this->workflowEngine->initialize(
                    $studentDocument,
                    null,
                    __('Document uploaded by HTE supervisor via transaction link.')
                );

                if ($oldPath && $oldPath !== $path) {
                    Storage::disk('public')->delete($oldPath);
                }

                $link->forceFill([
                    'used_at' => now(),
                    'used_for' => 'document_upload',
                ])->save();
            });
        } catch (Throwable $e) {
            report($e);
            Storage::disk('public')->delete($path);

            return back()
                ->withInput()
                ->withErrors([
                    'form' => __('We could not upload the document right now. Please try again.'),
                ], 'upload');
        }

        return view('hte-transactions.success', [
            'title' => __('Document uploaded'),
            'message' => __('Thank you. Your document upload has been recorded.'),
        ]);
    }

    private function findLink(string $token): ?HteTransactionLink
    {
        $link = HteTransactionLink::query()
            ->with(['student', 'company'])
            ->where('token', $token)
            ->first();

        if (! $link || ! $link->isUsable()) {
            return null;
        }

        return $link;
    }
}
