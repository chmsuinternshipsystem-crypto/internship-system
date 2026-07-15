<?php

namespace App\Models;

use App\Models\Attendance;
use App\Models\Concerns\Archivable;
use App\Support\HasDeleteProtection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// These are used by deleteBlockers() — cannot rely on Relation because
// the Student model does not define these as relationship methods.

class Student extends Model
{
    /** @use HasDeleteProtection<Student> */
    use HasDeleteProtection;
    use Archivable;

    use HasFactory;

    /**
     * Fields that can be mass-assigned when creating/updating a student.
     */
    protected function casts(): array
    {
        return array_merge(parent::casts() ?? [], [
            'archived_at' => 'datetime',
        ]);
    }

    protected $fillable = [
        'user_id',
        'assigned_instructor_id',
        'ojt_type',
        'name',
        'last_name',
        'first_name',
        'middle_name',
        'name_extension',
        'student_number',
        'program',
        'year_level',
        'section',
        'contact_number',
        'progress_pct',
    ];

    protected $appends = ['progress_pct_computed', 'journal_progress_pct'];

    protected static function booted(): void
    {
        static::saving(function (Student $student): void {
            if (filled($student->last_name) && filled($student->first_name)) {
                $student->name = self::composeDisplayName(
                    (string) $student->last_name,
                    (string) $student->first_name,
                    $student->middle_name,
                    $student->name_extension,
                );
            }
        });
    }

    public function deleteBlockers(): array
    {
        $messages = [];

        if ($this->deployments()->whereIn('status', ['active', 'completed'])->exists()) {
            $messages[] = __('Cannot delete: :name has :count active deployment(s).', [
                'name' => $this->name,
                'count' => $this->deployments()->count(),
            ]);
        }

        if ($this->documents()->exists()) {
            $messages[] = __('Cannot delete: :name has :count document submission(s).', [
                'name' => $this->name,
                'count' => $this->documents()->count(),
            ]);
        }

        if ($this->evaluations()->exists()) {
            $messages[] = __('Cannot delete: :name has :count evaluation record(s).', [
                'name' => $this->name,
                'count' => $this->evaluations()->count(),
            ]);
        }

        if ($this->remarks()->exists()) {
            $messages[] = __('Cannot delete: :name has :count remark(s).', [
                'name' => $this->name,
                'count' => $this->remarks()->count(),
            ]);
        }

        if (WeeklyJournal::where('student_id', $this->id)->exists()) {
            $count = WeeklyJournal::where('student_id', $this->id)->count();
            $messages[] = __('Cannot delete: :name has :count weekly journal(s).', [
                'name' => $this->name,
                'count' => $count,
            ]);
        }

        if (Certificate::where('student_id', $this->id)->exists()) {
            $count = Certificate::where('student_id', $this->id)->count();
            $messages[] = __('Cannot delete: :name has :count certificate(s).', [
                'name' => $this->name,
                'count' => $count,
            ]);
        }

        if (Attendance::where('student_id', $this->id)->exists()) {
            $count = Attendance::where('student_id', $this->id)->count();
            $messages[] = __('Cannot delete: :name has :count attendance record(s).', [
                'name' => $this->name,
                'count' => $count,
            ]);
        }

        return $messages;
    }

    /**
     * Canonical display name: "Surname, First Middle Extension"
     */
    public static function composeDisplayName(
        string $lastName,
        string $firstName,
        ?string $middleName,
        ?string $extension,
    ): string {
        $last = trim($lastName);
        $first = trim($firstName);
        $middle = trim((string) ($middleName ?? ''));
        $ext = trim((string) ($extension ?? ''));
        $core = $last.', '.$first.($middle !== '' ? ' '.$middle : '');

        return $ext !== '' ? $core.' '.$ext : $core;
    }

    /**
     * The user account that owns this student profile.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignedInstructor()
    {
        return $this->belongsTo(User::class, 'assigned_instructor_id');
    }

    public function riskFlags()
    {
        return $this->hasMany(StudentRiskFlag::class);
    }

    public function activeRiskFlags()
    {
        return $this->hasMany(StudentRiskFlag::class)->whereNull('resolved_at');
    }

    /**
     * Deployments associated with this student.
     */
    public function deployments()
    {
        return $this->hasMany(Deployment::class);
    }

    public function documents()
    {
        return $this->hasMany(StudentDocument::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function remarks()
    {
        return $this->hasMany(Remark::class);
    }

    public function account()
    {
        return $this->hasOne(StudentAccount::class);
    }

    public function isDeploymentEligibleForPortal(): bool
    {
        return $this->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->where(function ($q) {
                $q->whereNotNull('company_id')
                  ->orWhereHas('student', fn ($sq) =>
                      $sq->where('ojt_type', 'internal'));
            })
            ->exists();
    }

    public function hasSubmittedAllDocuments(?int $companyId = null): bool
    {
        $targetCompanyId = $companyId ?? $this->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->orderByDesc('start_date')
            ->value('company_id');

        $requiredIds = RequiredDocument::cachedOrdered()
            ->filter(fn ($doc) => is_null($doc->company_id) || ($targetCompanyId && $doc->company_id === $targetCompanyId))
            ->pluck('id');

        if ($requiredIds->isEmpty()) {
            return true;
        }

        $submitted = StudentDocument::query()
            ->where('student_id', $this->id)
            ->whereIn('required_document_id', $requiredIds)
            ->whereNotNull('file_path')
            ->count();

        return $submitted >= $requiredIds->count();
    }

    public function hasCompletedMandatoryChecklist(): bool
    {
        $mandatoryIds = RequiredDocument::cachedMandatoryIds();

        if ($mandatoryIds->isEmpty()) {
            return true;
        }

        $submittedCount = StudentDocument::query()
            ->where('student_id', $this->id)
            ->whereIn('required_document_id', $mandatoryIds)
            ->where('status', 'Submitted')
            ->count();

        return $submittedCount >= $mandatoryIds->count();
    }

    /**
     * Full portal: deployment (or completed) plus all Pre-requisite documents approved.
     * Until then, students may still sign in for documents + announcements only.
     * Monitoring-phase documents are only required after deployment activation.
     */
    public function hasFullStudentPortalAccess(): bool
    {
        return $this->isDeploymentEligibleForPortal() && $this->areAllPreDocsApproved();
    }

    /** @deprecated Use {@see hasFullStudentPortalAccess()} */
    public function canAccessStudentPortal(): bool
    {
        return $this->hasFullStudentPortalAccess();
    }

    /**
     * Compute progress percentage based on mandatory document completion.
     */
    public function getProgressPctComputedAttribute(): int
    {
        $preApproved = $this->areAllPreDocsApproved();
        $monitoringSubmitted = $this->areAllMonitoringDocsSubmitted();

        $mandatoryIds = RequiredDocument::cachedMandatoryIds();

        if (! $preApproved) {
            $mandatoryQuery = RequiredDocument::query()->whereIn('id', $mandatoryIds)->whereNotIn('phase', ['monitoring', 'post']);
            $mandatoryIds = $mandatoryQuery->pluck('id');
        } elseif (! $monitoringSubmitted) {
            $mandatoryQuery = RequiredDocument::query()->whereIn('id', $mandatoryIds)->where('phase', '!=', 'post');
            $mandatoryIds = $mandatoryQuery->pluck('id');
        }

        if ($mandatoryIds->isEmpty()) {
            return 0;
        }

        $submittedCount = StudentDocument::query()
            ->where('student_id', $this->id)
            ->whereIn('required_document_id', $mandatoryIds)
            ->where('status', 'Submitted')
            ->count();

        return (int) round(($submittedCount / $mandatoryIds->count()) * 100);
    }

    /**
     * Compute document progress: fraction of mandatory documents submitted.
     * Phase-aware: Pre phase docs only before deployment, Monitoring added after.
     */
    public function getDocProgressPctAttribute(): int
    {
        return $this->progress_pct_computed;
    }

    /**
     * Compute DTR progress: percentage of 600 hours (36,000 minutes) accumulated.
     */
    public function getDtrProgressPctAttribute(): int
    {
        $totalMinutes = (int) \App\Models\Attendance::where('student_id', $this->id)
            ->whereNotNull('time_out_at')
            ->sum('total_minutes');

        return min(100, (int) round(($totalMinutes / 36000) * 100));
    }

    /**
     * Compute weekly journal progress: fraction of past weeks reviewed.
     * Future weeks are excluded from the denominator to avoid inflating it.
     */
    public function getJournalProgressPctAttribute(): int
    {
        $deployment = $this->deployments()
            ->whereIn('status', ['active', 'completed'])
            ->latest()
            ->first();

        if (! $deployment || ! $deployment->start_date) {
            return 0;
        }

        // Total expected weeks from deployment period
        $startDate = $deployment->start_date;
        $endDate = $deployment->end_date ?: $startDate->copy()->addWeeks(15);
        $totalWeeks = max(1, (int) ceil($startDate->diffInDays($endDate) / 7));

        // (reviewed / total weeks) * 100
        $reviewedCount = WeeklyJournal::where('student_id', $this->id)
            ->where('status', 'reviewed')
            ->count();

        return min(100, (int) round(($reviewedCount / $totalWeeks) * 100));
    }

    /**
     * Check if all 8 Pre-requirement mandatory documents are submitted with completed workflow.
     */
    public function areAllPreDocsApproved(): bool
    {
        $preMandatoryIds = RequiredDocument::cachedOrdered()
            ->where('is_mandatory', true)
            ->where('phase', 'pre')
            ->pluck('id');

        if ($preMandatoryIds->isEmpty()) {
            return false;
        }

        $submittedCount = StudentDocument::query()
            ->where('student_id', $this->id)
            ->whereIn('required_document_id', $preMandatoryIds)
            ->where('status', 'Submitted')
            ->where(function ($q) {
                $q->where('workflow_status', 'completed')
                  ->orWhereNull('workflow_template_id');
            })
            ->count();

        return $submittedCount >= $preMandatoryIds->count();
    }

    /**
     * Check if all mandatory documents in a given phase are submitted.
     */
    public function areAllPhaseDocsSubmitted(string $phase): bool
    {
        $mandatoryIds = RequiredDocument::cachedOrdered()
            ->where('is_mandatory', true)
            ->where('phase', $phase)
            ->pluck('id');

        if ($mandatoryIds->isEmpty()) {
            return false;
        }

        $submittedCount = StudentDocument::query()
            ->where('student_id', $this->id)
            ->whereIn('required_document_id', $mandatoryIds)
            ->where('status', 'Submitted')
            ->where(function ($q) {
                $q->where('workflow_status', 'completed')
                  ->orWhereNull('workflow_template_id');
            })
            ->count();

        return $submittedCount >= $mandatoryIds->count();
    }

    public function areAllMonitoringDocsSubmitted(): bool
    {
        return $this->areAllPhaseDocsSubmitted('monitoring');
    }

    public function areAllPostDocsSubmitted(): bool
    {
        return $this->areAllPhaseDocsSubmitted('post');
    }

    /**
     * Check if all mandatory documents across all phases are submitted.
     */
    public function areAllMandatoryDocsSubmitted(): bool
    {
        $mandatoryIds = RequiredDocument::cachedMandatoryIds();

        if ($mandatoryIds->isEmpty()) {
            return false;
        }

        $submittedCount = StudentDocument::query()
            ->where('student_id', $this->id)
            ->whereIn('required_document_id', $mandatoryIds)
            ->where('status', 'Submitted')
            ->where(function ($q) {
                $q->where('workflow_status', 'completed')
                  ->orWhereNull('workflow_template_id');
            })
            ->count();

        return $submittedCount >= $mandatoryIds->count();
    }

    /**
     * Auto-activate pending deployment if all Pre documents are approved
     * AND the student has a company assigned.
     * Returns true if activated, false otherwise.
     */
    public function autoActivateDeployment(): bool
    {
        if (! $this->areAllPreDocsApproved()) {
            return false;
        }

        $pending = $this->deployments()
            ->where('status', 'pending')
            ->first();

        if (! $pending) {
            return false;
        }

        if (in_array($this->ojt_type, ['external', 'unplaced'])) {
            if (! $pending->company_id) {
                return false;
            }
        }

        $pending->forceFill([
            'status' => 'active',
            'start_date' => now()->toDateString(),
        ])->save();

        // Notify student + instructor
        $student = $this;
        if ($student->relationLoaded('studentAccount')) $student->load('studentAccount');
        $studentAccount = $student->studentAccount;
        if ($studentAccount) {
            \App\Services\NotificationService::notifyStudentAccount($studentAccount, __('Deployment Activated'), __('Your deployment has been activated. Full portal access is now available.'));
        }
        if ($assignedInstructor = $student->assignedInstructor) {
            \App\Services\NotificationService::notifyUsers([$assignedInstructor], __('Deployment Activated'), __(':name\'s deployment has been activated.', ['name' => $student->name]));
        }

        return true;
    }

    public function needsCompanyAssignment(): bool
    {
        if (! $this->areAllPreDocsApproved()) {
            return false;
        }

        if ($this->ojt_type === 'unplaced') {
            $pending = $this->deployments()
                ->where('status', 'pending')
                ->first();
            return $pending && is_null($pending->company_id);
        }

        return false;
    }

    public function isInternalOjt(): bool
    {
        return $this->ojt_type === 'internal';
    }

    public function isUnplaced(): bool
    {
        return $this->ojt_type === 'unplaced';
    }

    /**
     * Get total accumulated attendance minutes.
     */
    public function getTotalAttendanceMinutes(): int
    {
        return (int) Attendance::query()
            ->where('student_id', $this->id)
            ->whereNotNull('time_out_at')
            ->sum('total_minutes');
    }

    /**
     * Sync the stored progress_pct with computed value.
     */
    public function syncProgressPct(): void
    {
        $this->progress_pct = $this->progress_pct_computed;
        $this->saveQuietly();
    }
}
