<?php

namespace App\Models;

use App\Models\Concerns\Archivable;
use App\Models\DailyTimeRecord;
use App\Models\MonthlyDttr;
use App\Models\WeeklyJournal;
use App\Support\HasDeleteProtection;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Used by deleteBlockers()

class Deployment extends Model
{
    /** @use HasDeleteProtection<Deployment> */
    use HasDeleteProtection;
    use Archivable;

    use HasFactory;

    /**
     * Fields that can be mass-assigned when creating/updating a deployment.
     */
    protected $fillable = [
        'student_id',
        'company_id',
        'start_date',
        'end_date',
        'status',
        'remarks',
    ];

    public function deleteBlockers(): array
    {
        $messages = [];

        $journalCount = WeeklyJournal::where('deployment_id', $this->id)->count();
        if ($journalCount > 0) {
            $messages[] = __('Cannot delete: deployment has :count weekly journal(s).', ['count' => $journalCount]);
        }

        $certCount = Certificate::where('deployment_id', $this->id)->count();
        if ($certCount > 0) {
            $messages[] = __('Cannot delete: deployment has :count certificate(s).', ['count' => $certCount]);
        }

        $dtrCount = DailyTimeRecord::where('deployment_id', $this->id)->count();
        if ($dtrCount > 0) {
            $messages[] = __('Cannot delete: deployment has :count daily time record(s).', ['count' => $dtrCount]);
        }

        $monthlyDttrCount = MonthlyDttr::where('deployment_id', $this->id)->count();
        if ($monthlyDttrCount > 0) {
            $messages[] = __('Cannot delete: deployment has :count monthly DTTR (Daily Time and Task Record) record(s).', ['count' => $monthlyDttrCount]);
        }

        return $messages;
    }

    /**
     * Casts for date fields.
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_hours' => 'integer',
            'archived_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Deployment $deployment): void {
            // Auto-derive school_year from start_date
            if ($deployment->start_date && ! $deployment->school_year) {
                $year = (int) $deployment->start_date->format('Y');
                $month = (int) $deployment->start_date->format('n');
                $nextYear = $month >= 6 ? $year + 1 : $year;
                $deployment->school_year = ($month >= 6 ? $year : $year - 1) . '-' . $nextYear;
            }

            $status = self::computeStatus(
                (string) ($deployment->start_date?->format('Y-m-d') ?? ''),
                $deployment->end_date?->format('Y-m-d'),
            );

            $student = $deployment->student;

            // Auto-deploy to active only when all Pre-requirement documents are approved
            if ($status === 'active') {
                if (! $student || ! $student->areAllPreDocsApproved()) {
                    $status = 'pending';
                }
            }

            // Mark completed only when ALL mandatory docs are submitted
            if ($status === 'completed') {
                if (! $student || ! $student->areAllMandatoryDocsSubmitted()) {
                    $status = 'active';
                }
            }

            $deployment->status = $status;
        });

        static::saved(function (Deployment $deployment): void {
            $student = $deployment->student;
            if (! $student) {
                return;
            }

            // Auto-generate certificate when deployment completes
            if ($deployment->status === 'completed' && $student->areAllMandatoryDocsSubmitted()) {
                $hasCert = Certificate::where('deployment_id', $deployment->id)->exists();
                if (! $hasCert) {
                    try {
                        $cert = Certificate::firstOrCreate(
                            ['deployment_id' => $deployment->id],
                            [
                                'student_id' => $student->id,
                                'type' => 'completion',
                                'title' => __('Certificate of Completion'),
                                'status' => 'pending',
                                'is_auto_generated' => true,
                                'generated_at' => now(),
                            ]
                        );
                        if ($cert->wasRecentlyCreated) {
                            $service = app(\App\Services\CertificateExportService::class);
                            $path = $service->generate($student, $cert);
                            $cert->update(['file_path' => $path]);

                            // Notify instructors
                            $instructors = \App\Models\User::where('role', 'instructor')->get();
                            \App\Services\NotificationService::notifyUsers(
                                $instructors->all(),
                                __('Certificate Ready for Review'),
                                __('A certificate has been auto-generated for :name. Please review and approve it.', ['name' => $student->name])
                            );
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                }
                return;
            }

            if ($deployment->status !== 'active' || ! $deployment->start_date) {
                return;
            }
            if (! $student) {
                return;
            }

            // Generate attendance passcode on activation
            $account = $student->account;
            if ($account && empty($account->attendance_passcode)) {
                $account->ensureAttendancePasscode();
            }

            // Create weekly journal weeks on activation
            $existingWeeks = WeeklyJournal::where('student_id', $student->id)->count();
            if ($existingWeeks > 0) {
                return;
            }

            $cursor = $deployment->start_date->copy()->startOfWeek();
            $end = $deployment->end_date
                ? $deployment->end_date->copy()->endOfWeek()
                : now()->endOfWeek();

            $weekNumber = 1;
            while ($cursor->lte($end)) {
                WeeklyJournal::withoutEvents(function () use ($student, $cursor, $weekNumber): void {
                    WeeklyJournal::firstOrCreate(
                        [
                            'student_id' => $student->id,
                            'week_number' => $weekNumber,
                        ],
                        [
                            'week_start_date' => $cursor->copy(),
                            'week_end_date' => $cursor->copy()->next(Carbon::SATURDAY),
                            'status' => 'draft',
                        ]
                    );
                });
                $cursor->addWeek();
                $weekNumber++;
            }
        });
    }

    public static function checkAndActivateForStudent(\App\Models\Student $student): void
    {
        foreach ($student->deployments()->where('status', 'pending')->get() as $deployment) {
            if ($deployment->start_date && ! $deployment->start_date->isFuture()) {
                $deployment->save();
            }
        }
    }

    public static function computeStatus(string $startDate, ?string $endDate): string
    {
        if ($endDate !== null && $endDate < now()->format('Y-m-d')) {
            return 'completed';
        }

        if ($startDate <= now()->format('Y-m-d')) {
            return 'active';
        }

        return 'pending';
    }

    /**
     * The student assigned to this deployment.
     */
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * The company where the student is deployed.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Check if this deployment's section has active deployments for other students,
     * which may indicate multiple instructors handling the same section.
     */
    public function hasSectionConflict(): bool
    {
        $section = $this->student?->section;
        if (! $section) {
            return false;
        }

        return self::query()
            ->whereHas('student', fn ($q) => $q->where('section', $section))
            ->where('id', '!=', $this->id)
            ->where('status', 'active')
            ->exists();
    }
}
