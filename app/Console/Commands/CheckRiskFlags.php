<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\Deployment;
use App\Models\RequiredDocument;
use App\Models\Student;
use App\Models\StudentDocument;
use App\Models\StudentRiskFlag;
use App\Models\WeeklyJournal;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckRiskFlags extends Command
{
    protected $signature = 'flags:check';

    protected $description = 'Auto-submit completed draft journals, then check all students for risk criteria';

    public function handle(): int
    {
        $this->info('Checking risk flags...');
        $flagged = [];
        $resolved = [];

        // --- 0. Auto-submit completed draft journals ---
        $this->info('  Auto-submitting completed draft journals...');
        $autoSubmitted = 0;
        $gracePeriodEnd = now()->subDay()->startOfDay();
        WeeklyJournal::where('status', 'draft')
            ->where('week_end_date', '<=', $gracePeriodEnd)
            ->chunk(50, function ($journals) use (&$autoSubmitted) {
                foreach ($journals as $journal) {
                    $activities = $journal->activities ?? [];
                    if (! is_array($activities)) continue;

                    $allFilled = collect($activities)->every(function ($entry) {
                        $tasks = is_array($entry) ? ($entry['tasks'] ?? '') : '';
                        return trim((string) $tasks) !== '';
                    });

                    if ($allFilled) {
                        $journal->update([
                            'status' => 'submitted',
                            'submitted_at' => now(),
                        ]);
                        $autoSubmitted++;
                    }
                }
            });
        $this->info("  Auto-submitted: {$autoSubmitted}");

        // Collect all currently active flags keyed by "student_id:type"
        $activeFlags = StudentRiskFlag::active()->get()->keyBy(fn ($f) => $f->student_id . ':' . $f->type);

        // --- 1. Consecutive absences ---
        $this->info('  Checking consecutive absences...');
        $threeDaysAgo = now()->subDays(3)->startOfDay();
        Student::whereHas('deployments', fn ($q) => $q->whereIn('status', ['active', 'completed']))
            ->chunk(50, function ($students) use ($threeDaysAgo, &$flagged) {
                foreach ($students as $student) {
                    $lastAttendance = Attendance::where('student_id', $student->id)
                        ->whereNotNull('time_out_at')
                        ->orderByDesc('check_in_at')
                        ->first();

                    if (! $lastAttendance || $lastAttendance->check_in_at->lt($threeDaysAgo)) {
                        $flagged[] = [
                            'student_id' => $student->id,
                            'type' => 'consecutive_absences',
                            'severity' => 'critical',
                            'message' => __('No attendance record for 3+ consecutive days.'),
                        ];
                    }
                }
            });

        // --- 2. Late journal submissions ---
        $this->info('  Checking late journal submissions...');
        $lateStudentIds = WeeklyJournal::where('submitted_at', '>', now()->subDays(30))
            ->whereNotNull('submitted_at')
            ->whereNotNull('week_end_date')
            ->get()
            ->filter(fn ($j) => $j->is_late)
            ->groupBy('student_id')
            ->filter(fn ($journals) => $journals->count() >= 2)
            ->keys();

        foreach ($lateStudentIds as $sid) {
            $flagged[] = [
                'student_id' => $sid,
                'type' => 'late_journals',
                'severity' => 'warning',
                'message' => __('Submitted 2+ weekly journals late.'),
            ];
        }

        // --- 3. Missing mandatory documents 7+ days post-deployment ---
        $this->info('  Checking missing documents...');
        $sevenDaysAgo = now()->subDays(7)->startOfDay();
        $mandatoryIds = RequiredDocument::where('is_mandatory', true)->pluck('id');

        if ($mandatoryIds->isNotEmpty()) {
            Deployment::whereIn('status', ['active', 'completed'])
                ->where('start_date', '<=', $sevenDaysAgo)
                ->chunk(50, function ($deployments) use ($mandatoryIds, &$flagged) {
                    foreach ($deployments as $deployment) {
                        $submittedCount = StudentDocument::where('student_id', $deployment->student_id)
                            ->whereIn('required_document_id', $mandatoryIds)
                            ->where('status', 'Submitted')
                            ->count();

                        if ($submittedCount < $mandatoryIds->count()) {
                            $flagged[] = [
                                'student_id' => $deployment->student_id,
                                'type' => 'missing_documents',
                                'severity' => 'warning',
                                'message' => __('Mandatory documents still missing 7+ days after deployment.'),
                            ];
                        }
                    }
                });
        }

        // --- 4. Expired deployment without certificate ---
        $this->info('  Checking expired deployments...');
        Deployment::where('status', 'active')
            ->whereNotNull('end_date')
            ->where('end_date', '<', now())
            ->chunk(50, function ($deployments) use (&$flagged) {
                foreach ($deployments as $deployment) {
                    $hasCertificate = Certificate::where('student_id', $deployment->student_id)
                        ->where('status', 'verified')
                        ->exists();

                    if (! $hasCertificate) {
                        $flagged[] = [
                            'student_id' => $deployment->student_id,
                            'type' => 'expired_deployment',
                            'severity' => 'critical',
                            'message' => __('Deployment ended without a verified certificate.'),
                        ];
                    }
                }
            });

        // --- Upsert new flags ---
        $created = 0;
        $alreadyFlagged = 0;
        foreach ($flagged as $flag) {
            $key = $flag['student_id'] . ':' . $flag['type'];
            if ($activeFlags->has($key)) {
                $alreadyFlagged++;
                continue;
            }

            StudentRiskFlag::create($flag);
            $created++;
        }

        // --- Resolve flags that no longer apply ---
        $resolvedCount = 0;
        foreach ($activeFlags as $key => $flag) {
            [$sid, $type] = explode(':', $key, 2);
            $stillActive = collect($flagged)->first(fn ($f) => (int) $f['student_id'] === (int) $sid && $f['type'] === $type);

            if (! $stillActive) {
                $flag->update(['resolved_at' => now()]);
                $resolvedCount++;
            }
        }

        $this->info("  Created: {$created}, Already flagged: {$alreadyFlagged}, Resolved: {$resolvedCount}");

        return Command::SUCCESS;
    }
}
