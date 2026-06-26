<?php

namespace App\Console\Commands;

use App\Models\Deployment;
use App\Models\Student;
use App\Services\NotificationService;
use Illuminate\Console\Command;

class AutoCompleteDeployments extends Command
{
    protected $signature = 'deployments:auto-complete';

    protected $description = 'Auto-complete active deployments whose end_date has passed, and notify affected parties.';

    public function handle(NotificationService $notifier): int
    {
        $completed = Deployment::where('status', 'active')
            ->where('end_date', '<', now())
            ->get();

        if ($completed->isEmpty()) {
            $this->info('No deployments to auto-complete.');

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($completed as $deployment) {
            $deployment->update(['status' => 'completed']);

            $student = $deployment->student;
            if ($student && $student->account) {
                $notifier->notifyStudentAccount($student->account, [
                    'event_type' => 'deployment.completed',
                    'title' => __('Deployment Completed'),
                    'body' => __('Your OJT deployment at :company has been marked as completed.', [
                        'company' => $deployment->company?->name ?? __('your company'),
                    ]),
                    'action_url' => route('student.dashboard'),
                ]);
            }

            $instructor = $student?->assignedInstructor;
            if ($instructor) {
                $notifier->notifyUser($instructor, [
                    'event_type' => 'deployment.completed',
                    'title' => __('Deployment Completed'),
                    'body' => __(':name\'s OJT deployment has been marked as completed.', [
                        'name' => $student?->name ?? __('A student'),
                    ]),
                    'action_url' => route('students.show', $student),
                ]);
            }

            $this->line("  Completed deployment #{$deployment->id} for student {$deployment->student_id}");
            $count++;
        }

        $this->info("Auto-completed {$count} deployment(s). Notifications sent.");

        return self::SUCCESS;
    }
}
