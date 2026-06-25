<?php

namespace App\Support;

use App\Models\Evaluation;
use App\Models\Student;

/**
 * Final OJT grade = 70% latest HTE (industry) score + 30% latest school (instructor) score.
 * Student feedback rows are excluded from this computation.
 */
final class OjtGradeCalculator
{
    public const HTE_WEIGHT = 0.7;

    public const SCHOOL_WEIGHT = 0.3;

    public static function latestIndustryEvaluation(Student $student): ?Evaluation
    {
        return Evaluation::query()
            ->where('student_id', $student->id)
            ->where('evaluation_type', 'industry')
            ->orderByDesc('evaluated_at')
            ->orderByDesc('id')
            ->first();
    }

    public static function latestSchoolEvaluation(Student $student): ?Evaluation
    {
        return Evaluation::query()
            ->where('student_id', $student->id)
            ->where('evaluation_type', 'school')
            ->orderByDesc('evaluated_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @return array{hte_score: int|null, instructor_score: int|null, final_grade: float|null, is_complete: bool}
     */
    public static function summary(Student $student): array
    {
        $industry = self::latestIndustryEvaluation($student);
        $school = self::latestSchoolEvaluation($student);

        $hte = $industry?->score;
        $instructor = $school?->score;

        $isComplete = $hte !== null && $instructor !== null;

        $final = null;
        if ($isComplete) {
            $final = round($hte * self::HTE_WEIGHT + $instructor * self::SCHOOL_WEIGHT, 2);
        }

        return [
            'hte_score' => $hte,
            'instructor_score' => $instructor,
            'final_grade' => $final,
            'is_complete' => $isComplete,
        ];
    }
}
