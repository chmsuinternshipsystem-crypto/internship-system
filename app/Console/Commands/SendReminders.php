<?php

namespace App\Console\Commands;

use App\Mail\NotificationMail;
use App\Models\Attendance;
use App\Models\Deployment;
use App\Models\Evaluation;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\User;
use App\Models\WeeklyJournal;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Send automated email reminders to students (journals, attendance, documents, deployments)';

    public function handle(): int
    {
        $this->info('Sending reminders...');
        $sent = 0;

        // --- 1. Journal due tomorrow ---
        $this->info('  Checking journals due tomorrow...');
        $tomorrow = now()->addDay()->startOfDay();
        $journalsDueTomorrow = WeeklyJournal::where('week_end_date', $tomorrow)
            ->where('status', 'draft')
            ->with('student.account')
            ->get();

        foreach ($journalsDueTomorrow as $journal) {
            if ($journal->student?->account?->email) {
                Mail::to($journal->student->account->email)->queue(new NotificationMail(
                    recipientName: $journal->student->name,
                    subjectText: __('Weekly Journal Due Tomorrow'),
                    bodyText: __('Your weekly journal for Week :week is due tomorrow (:date). Please submit it on time.', [
                        'week' => $journal->week_number,
                        'date' => $journal->week_end_date->format('M d, Y'),
                    ]),
                    actionUrl: route('student.weekly-journals.show', $journal),
                    actionLabel: __('Open Journal'),
                ));
                app(NotificationService::class)->notifyStudentAccount($journal->student->account, [
                    'event_type' => 'reminder.journal_due',
                    'title' => __('Journal Due Tomorrow'),
                    'body' => __('Week :week journal is due tomorrow (:date).', [
                        'week' => $journal->week_number,
                        'date' => $journal->week_end_date->format('M d, Y'),
                    ]),
                    'action_url' => route('student.weekly-journals.show', $journal),
                ]);
                $sent++;
            }
        }

        // --- 2. Journal overdue ---
        $this->info('  Checking overdue journals...');
        $yesterday = now()->subDay()->endOfDay();
        $overdueJournals = WeeklyJournal::where('week_end_date', '<=', $yesterday)
            ->where('status', 'draft')
            ->with('student.account')
            ->get();

        foreach ($overdueJournals as $journal) {
            if ($journal->student?->account?->email) {
                Mail::to($journal->student->account->email)->queue(new NotificationMail(
                    recipientName: $journal->student->name,
                    subjectText: __('Weekly Journal Overdue'),
                    bodyText: __('Your weekly journal for Week :week (due :date) is overdue. Please submit it as soon as possible.', [
                        'week' => $journal->week_number,
                        'date' => $journal->week_end_date->format('M d, Y'),
                    ]),
                    actionUrl: route('student.weekly-journals.show', $journal),
                    actionLabel: __('Submit Now'),
                ));
                app(NotificationService::class)->notifyStudentAccount($journal->student->account, [
                    'event_type' => 'reminder.journal_overdue',
                    'title' => __('Journal Overdue'),
                    'body' => __('Week :week journal (due :date) is overdue.', [
                        'week' => $journal->week_number,
                        'date' => $journal->week_end_date->format('M d, Y'),
                    ]),
                    'action_url' => route('student.weekly-journals.show', $journal),
                ]);
                $sent++;
            }
        }

        // --- 3. No check-in today (after 4pm) ---
        $this->info('  Checking no check-in today...');
        $now = now();
        if ($now->hour >= 16) { // Only send after 4pm
            $deployedToday = Deployment::whereIn('status', ['active', 'completed'])
                ->with('student.account')
                ->get()
                ->filter(fn ($d) => $d->student?->account?->email)
                ->pluck('student');

            foreach ($deployedToday as $student) {
                $checkedIn = Attendance::where('student_id', $student->id)
                    ->whereDate('check_in_at', today())
                    ->exists();

                if (! $checkedIn) {
                    Mail::to($student->account->email)->queue(new NotificationMail(
                        recipientName: $student->name,
                        subjectText: __('Attendance Reminder'),
                        bodyText: __('You haven\'t checked in today. Please remember to clock in for your internship.'),
                        actionUrl: route('attendance.check-in'),
                        actionLabel: __('Check In'),
                    ));
                    app(NotificationService::class)->notifyStudentAccount($student->account, [
                        'event_type' => 'reminder.attendance',
                        'title' => __('Attendance Reminder'),
                        'body' => __('You haven\'t checked in today. Please clock in for your internship.'),
                        'action_url' => route('attendance.check-in'),
                    ]);
                    $sent++;
                }
            }
        }

        // --- 4. Documents pending 7+ days ---
        $this->info('  Checking pending documents...');
        $sevenDaysAgo = now()->subDays(7);
        $pendingDocs = StudentDocument::whereIn('status', ['pending', 'returned'])
            ->where('created_at', '<=', $sevenDaysAgo)
            ->with(['student.account', 'requiredDocument'])
            ->get()
            ->groupBy('student_id');

        foreach ($pendingDocs as $studentId => $docs) {
            $student = $docs->first()->student;
            if (! $student?->account?->email) {
                continue;
            }

            $docNames = $docs->take(3)->pluck('requiredDocument.name')->filter()->implode(', ');
            $count = $docs->count();

            Mail::to($student->account->email)->queue(new NotificationMail(
                recipientName: $student->name,
                subjectText: __('Documents Pending Review'),
                bodyText: $count > 3
                    ? __('You have :count documents that have been pending for 7+ days: :names and more.', ['count' => $count, 'names' => $docNames])
                    : __('These documents have been pending for 7+ days: :names.', ['names' => $docNames]),
                actionUrl: route('student.documents'),
                actionLabel: __('View Documents'),
            ));
            app(NotificationService::class)->notifyStudentAccount($student->account, [
                'event_type' => 'reminder.documents_pending',
                'title' => __('Documents Pending'),
                'body' => $count > 3
                    ? __('You have :count documents pending for 7+ days.', ['count' => $count])
                    : __('Pending: :names.', ['names' => $docNames]),
                'action_url' => route('student.documents'),
            ]);
            $sent++;
        }

        // --- 5. Deployment starting tomorrow ---
        $this->info('  Checking deployments starting tomorrow...');
        $tomorrowDate = now()->addDay()->startOfDay();
        $startingDeployments = Deployment::whereDate('start_date', $tomorrowDate)
            ->with('student.account')
            ->get();

        foreach ($startingDeployments as $deployment) {
            if ($deployment->student?->account?->email) {
                Mail::to($deployment->student->account->email)->queue(new NotificationMail(
                    recipientName: $deployment->student->name,
                    subjectText: __('Internship Starts Tomorrow'),
                    bodyText: __('Your internship at :company starts tomorrow (:date). Please prepare your requirements and check in on time.', [
                        'company' => $deployment->company?->name ?? __('your assigned company'),
                        'date' => $deployment->start_date->format('M d, Y'),
                    ]),
                    actionUrl: route('student.dashboard'),
                    actionLabel: __('Go to Dashboard'),
                ));
                $sent++;
            }
        }

        // --- 6. Deployment ending within 7 days, no HTE evaluation submitted ---
        $this->info('  Checking deployments ending soon without HTE evaluation...');
        $weekFromNow = now()->addDays(7)->endOfDay();
        $endingDeployments = Deployment::where('status', 'active')
            ->whereDate('end_date', '<=', $weekFromNow)
            ->whereDate('end_date', '>=', now())
            ->with('student.assignedInstructor')
            ->get();

        foreach ($endingDeployments as $deployment) {
            $student = $deployment->student;
            if (! $student) {
                continue;
            }

            $hasEvaluation = Evaluation::where('student_id', $student->id)
                ->where('evaluation_type', 'industry')
                ->exists();

            if ($hasEvaluation) {
                continue;
            }

            $instructor = $student->assignedInstructor;
            if (! $instructor) {
                $instructor = User::where('role', 'instructor')->first();
            }
            if (! $instructor || ! $instructor->email) {
                continue;
            }

            Mail::to($instructor->email)->queue(new NotificationMail(
                recipientName: $instructor->name,
                subjectText: __('HTE Evaluation Needed'),
                bodyText: __(
                    ':student\'s deployment ends on :date and no HTE evaluation has been submitted yet. Send the evaluation link to their supervisor now.',
                    [
                        'student' => $student->name,
                        'date' => $deployment->end_date->format('M d, Y'),
                    ]
                ),
                actionUrl: route('evaluations.hte-links.create'),
                actionLabel: __('Send HTE Link'),
            ));
            $sent++;
        }

        // --- 7. Pre-docs complete but no company assigned ---
        $this->info('  Checking students ready for deployment without company...');
        $preMandatoryIds = RequiredDocument::where('is_mandatory', true)->where('phase', 'pre')->pluck('id');
        if ($preMandatoryIds->isNotEmpty()) {
            $readyStudents = Student::whereDoesntHave('deployments', function ($q) {
                $q->whereIn('status', ['active', 'completed']);
            })->whereHas('deployments', function ($q) {
                $q->where('status', 'pending')->whereNull('company_id');
            })->get();

            foreach ($readyStudents as $student) {
                $submittedCount = StudentDocument::where('student_id', $student->id)
                    ->whereIn('required_document_id', $preMandatoryIds)
                    ->where('status', 'Submitted')
                    ->where(function ($q) {
                        $q->where('workflow_status', 'completed')
                          ->orWhereNull('workflow_template_id');
                    })
                    ->count();

                if ($submittedCount < $preMandatoryIds->count()) {
                    continue;
                }

                $instructor = $student->assignedInstructor;
                if (! $instructor || ! $instructor->email) {
                    continue;
                }

                Mail::to($instructor->email)->queue(new NotificationMail(
                    recipientName: $instructor->name,
                    subjectText: __('Student Ready for Deployment — No Company'),
                    bodyText: __(
                        ':name (:number) has completed all Pre-requirement documents but does not have a company assigned. Please assign a company from the company page.',
                        ['name' => $student->name, 'number' => $student->student_number]
                    ),
                    actionUrl: route('students.show', $student),
                    actionLabel: __('View Student'),
                ));
                $sent++;
            }
        }

        $this->info("  Sent: {$sent} reminder(s)");

        return Command::SUCCESS;
    }
}
