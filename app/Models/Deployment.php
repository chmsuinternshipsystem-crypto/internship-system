<?php

namespace App\Models;

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
            $messages[] = __('Cannot delete: deployment has :count monthly DTTR(s).', ['count' => $monthlyDttrCount]);
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
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Deployment $deployment): void {
            $status = self::computeStatus(
                (string) ($deployment->start_date?->format('Y-m-d') ?? ''),
                $deployment->end_date?->format('Y-m-d'),
            );

            // Auto-deploy to active only when all Pre-requirement documents are approved
            if ($status === 'active') {
                $student = $deployment->student;
                if (! $student || ! $student->areAllPreDocsApproved()) {
                    $status = 'pending';
                }
            }

            $deployment->status = $status;
        });

        static::saved(function (Deployment $deployment): void {
            if ($deployment->status !== 'active' || ! $deployment->start_date) {
                return;
            }

            $student = $deployment->student;
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
