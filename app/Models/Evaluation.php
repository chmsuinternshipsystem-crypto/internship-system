<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    use HasFactory;

    const CRITERIA_CATEGORIES = [
        'work_habits' => [
            'label' => 'Work Habits',
            'items' => [
                'punctual' => 'Punctual',
                'reports_regularly' => 'Reports regularly',
                'perform_without_supervision' => 'Perform tasks without much supervision',
                'self_discipline' => 'Practices self-discipline in his/her work',
                'dedication' => 'Demonstrate dedication and commitment to the task assigned to him/her',
            ],
        ],
        'work_skills' => [
            'label' => 'Work Skills',
            'items' => [
                'operate_machines' => 'Demonstrates the ability to operate machines needed on the job',
                'handles_details' => 'Handles the details of the work assigned to him/her',
                'flexibility' => 'Shows flexibility (whenever the need arises) in the process of going through his/her task',
                'thoroughness' => 'Manifest thoroughness and precise attention to detail',
                'understands_linkage' => 'Fully understands the linkage or connection between his/her task to previous, intervening, and subsequent tasks',
                'sound_suggestions' => 'Usually comes up with sound suggestions to problems',
            ],
        ],
        'social_skills' => [
            'label' => 'Social Skills',
            'items' => [
                'tact' => 'Shows tact in dealing with different people with whom he/she comes in contact',
                'respect_courtesy' => 'Shows respect and courtesy in dealing with peers and superiors',
                'willingly_helps' => 'Willingly help others (whenever necessary) in their task performance',
                'learns_from_others' => 'Is capable of learning from and listening to co-workers',
                'gratitude' => 'Shows appreciation and gratitude for any form of assistance granted to him/her by others',
                'poise_grooming' => 'Shows poise and self-confidence and is always well-groomed',
                'emotional_maturity' => 'Shows emotional maturity',
            ],
        ],
    ];

    protected static function booted(): void
    {
        static::saving(function (Evaluation $evaluation): void {
            $score = (int) ($evaluation->score ?? 0);
            $evaluation->score = max(1, min(100, $score));
        });
    }

    protected $fillable = [
        'student_id',
        'company_id',
        'evaluator_id',
        'evaluation_type',
        'score',
        'criteria_scores',
        'comments',
        'evaluated_at',
    ];

    protected function casts(): array
    {
        return [
            'evaluated_at' => 'date',
            'criteria_scores' => 'array',
        ];
    }

    public function getCriteriaAverages(): array
    {
        $scores = $this->criteria_scores ?? [];
        $categories = EvaluationCriterion::getActiveCriteria();

        $result = [];
        $allValues = [];

        foreach ($categories as $key => $category) {
            $values = [];
            foreach (array_keys($category['items']) as $itemKey) {
                $v = $scores[$key][$itemKey] ?? null;
                if ($v !== null && $v !== '') {
                    $values[] = (float) $v;
                    $allValues[] = (float) $v;
                }
            }
            $result[$key] = [
                'label' => $category['label'],
                'values' => $values,
                'total' => count($values) > 0 ? array_sum($values) : null,
                'average' => count($values) > 0 ? round(array_sum($values) / count($values), 2) : null,
            ];
        }

        $result['overall'] = count($allValues) > 0
            ? round(array_sum($allValues) / count($allValues), 2)
            : null;

        return $result;
    }

    public static function computeOverallScore(?array $criteriaScores): ?int
    {
        if (empty($criteriaScores)) {
            return null;
        }

        $categories = EvaluationCriterion::getActiveCriteria();
        $allValues = [];
        foreach ($categories as $key => $category) {
            foreach (array_keys($category['items']) as $itemKey) {
                $v = $criteriaScores[$key][$itemKey] ?? null;
                if ($v !== null && $v !== '') {
                    $allValues[] = (float) $v;
                }
            }
        }

        if (count($allValues) === 0) {
            return null;
        }

        $average = array_sum($allValues) / count($allValues);

        return (int) round($average * 20);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Label for the "Evaluator" column: student-submitted feedback is attributed to the student;
     * other evaluation types use the staff/supervisor user record.
     */
    public function evaluatorDisplayLabel(): string
    {
        if ($this->evaluation_type === 'student_feedback') {
            $name = trim((string) ($this->student?->name ?? ''));

            return $name !== ''
                ? __('Student').' - '.$name
                : '—';
        }

        if ($this->evaluator?->name) {
            return $this->evaluator->name;
        }

        if (($this->evaluation_type ?? 'industry') === 'industry') {
            $meta = $this->extractSupervisorMetaFromComments();
            $name = trim((string) ($meta['name'] ?? ''));

            return $name !== ''
                ? __('HTE Supervisor').' - '.$name
                : __('HTE Supervisor');
        }

        return __('System');
    }

    /**
     * Return supervisor details parsed from legacy HTE-formatted comments.
     *
     * @return array{name:?string,email:?string}
     */
    public function extractSupervisorMetaFromComments(): array
    {
        $comment = trim((string) ($this->comments ?? ''));
        if ($comment === '') {
            return ['name' => null, 'email' => null];
        }

        $name = null;
        $email = null;

        if (preg_match('/Supervisor:\s*(.+?)(?:\s+Supervisor Email:|\R|$)/i', $comment, $m)) {
            $name = trim((string) ($m[1] ?? ''));
        }

        if (preg_match('/Supervisor Email:\s*([^\s]+@[^\s]+|[^\s]+)/i', $comment, $m)) {
            $email = trim((string) ($m[1] ?? ''));
        }

        return [
            'name' => $name !== '' ? $name : null,
            'email' => $email !== '' ? $email : null,
        ];
    }

    /**
     * Comment text without appended legacy supervisor metadata.
     */
    public function cleanCommentsForDisplay(): ?string
    {
        $comment = trim((string) ($this->comments ?? ''));
        if ($comment === '') {
            return null;
        }

        $cleaned = preg_replace('/\s*Supervisor:\s*.+?(\s+Supervisor Email:\s*[^\s]+@[^\s]+|(\R|$))/i', '', $comment);
        $cleaned = preg_replace('/\s*Supervisor Email:\s*[^\s]+@[^\s]+/i', '', (string) $cleaned);
        $cleaned = trim((string) $cleaned);

        return $cleaned !== '' ? $cleaned : null;
    }
}
